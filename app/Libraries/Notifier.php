<?php

namespace App\Libraries;

/**
 * The emails the CMS sends: an alert for every enquiry and job application
 * that comes in through the website, and a test message from Admin → Settings.
 *
 * Each alert is an HTML email (with a plain-text copy for simple mail apps)
 * laid out to be read at a glance: who, what about, their message, and two
 * buttons — reply, or open it in the CMS. Replying goes straight to the
 * visitor, because the email's Reply-To is their address.
 *
 * Sending never gets in the visitor's way: the submission is already saved
 * in the Inbox before the email is attempted, and a failure is only logged.
 */
final class Notifier
{
    /** Times in the email are shown as the business sees them. */
    private const TIMEZONE = 'Asia/Kolkata';

    /**
     * Tests replace the mailer so nothing is really sent; null in normal use.
     *
     * @var (callable(): \CodeIgniter\Email\Email)|null
     */
    public static $mailerFactory = null;

    /**
     * Whether email can be sent, and if not, why — in plain words.
     *
     * @return array{ok:bool,problem:?string}
     */
    public static function status(): array
    {
        $c = config('Email');

        if (trim($c->SMTPHost) === '' || $c->protocol !== 'smtp') {
            return ['ok' => false, 'problem' => 'No mail server is set (email.SMTPHost in .env).'];
        }
        if (trim($c->SMTPUser) !== '' && trim($c->SMTPPass) === '') {
            return ['ok' => false, 'problem' => 'The mail account’s password is missing (email.SMTPPass in .env). For Gmail this is a 16-character App Password.'];
        }
        if (self::recipients() === []) {
            return ['ok' => false, 'problem' => 'Nobody is set to receive the emails (email.recipients in .env, or the contact email in Settings).'];
        }

        return ['ok' => true, 'problem' => null];
    }

    /** @return list<string> who is told about new submissions */
    public static function recipients(): array
    {
        $list = trim(config('Email')->recipients) ?: (string) config('Site')->email;

        return array_values(array_filter(
            array_map('trim', explode(',', $list)),
            static fn ($a) => filter_var($a, FILTER_VALIDATE_EMAIL) !== false
        ));
    }

    /**
     * Tell the team about a new enquiry or job application.
     *
     * @param string      $kind       contact | career
     * @param array       $data       the submitted fields
     * @param int|null    $id         the Inbox row, for the "Open in the CMS" button
     * @param string|null $attachment absolute path of the CV, if any
     */
    public static function submission(string $kind, array $data, ?int $id, ?string $attachment = null): bool
    {
        try {
            return self::sendSubmission($kind, $data, $id, $attachment);
        } catch (\Throwable $e) {
            // The visitor's message is already saved; a mail problem must not
            // turn into an error page for them.
            log_message('error', 'Form email failed: ' . $e->getMessage());

            return false;
        }
    }

    private static function sendSubmission(string $kind, array $data, ?int $id, ?string $attachment): bool
    {
        if (! self::status()['ok']) {
            log_message('error', 'Form email not sent: ' . self::status()['problem']);

            return false;
        }

        $vars    = self::submissionVars($kind, $data, $id, $attachment !== null);
        $email   = self::mailer();
        $email->setTo(self::recipients());
        if (filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $email->setReplyTo($data['email'], (string) ($data['name'] ?? ''));
        }
        $email->setSubject($vars['subject']);
        $email->setMessage(view('emails/submission', $vars));
        $email->setAltMessage(view('emails/submission_text', $vars));
        if ($attachment !== null && is_file($attachment)) {
            $email->attach($attachment, 'attachment', $data['resume_original'] ?? basename($attachment));
        }

        $sent = $email->send(false);
        if (! $sent) {
            log_message('error', 'Form email failed: ' . self::debug($email));
        }

        return $sent;
    }

    /**
     * Send the Settings screen's test email.
     *
     * @return array{ok:bool,detail:string}
     */
    public static function test(string $sentBy): array
    {
        $status = self::status();
        if (! $status['ok']) {
            return ['ok' => false, 'detail' => $status['problem']];
        }

        $site  = config('Site');
        $email = self::mailer();
        $email->setTo(self::recipients());
        $email->setSubject('Test email from the ' . $site->name . ' website');
        $vars = [
            'site'    => $site,
            'when'    => self::now(),
            'sentBy'  => $sentBy,
            'to'      => implode(', ', self::recipients()),
            'server'  => config('Email')->SMTPHost,
            'from'    => self::fromAddress(),
            'cmsUrl'  => admin_url(),
        ];
        $email->setMessage(view('emails/test', $vars));
        $email->setAltMessage("This is a test email from the {$site->name} website.\n\n"
            . "If you can read this, email is working: new enquiries and job applications will be sent to {$vars['to']}.\n\n"
            . "Sent by {$sentBy} on {$vars['when']} through {$vars['server']}.");

        if ($email->send(false)) {
            return ['ok' => true, 'detail' => 'Sent to ' . $vars['to'] . '. Check that inbox (and the spam folder, the first time).'];
        }

        return ['ok' => false, 'detail' => self::explain(self::debug($email))];
    }

    /**
     * Send a reply written in the Inbox to the person who wrote in.
     *
     * It comes from the site's mail account, and Reply-To is the team's
     * address (the first recipient), so when the visitor answers it reaches
     * the team rather than an unread sending account.
     *
     * @param string $type    enquiry | application
     * @param array  $row     the enquiry or application being answered
     * @param array  $sender  ['name' => …, 'email' => …] of the admin user
     * @return array{ok:bool,detail:string}
     */
    public static function reply(string $type, array $row, string $subject, string $body, bool $quote, array $sender, bool $copyToSender): array
    {
        $status = self::status();
        if (! $status['ok']) {
            return ['ok' => false, 'detail' => $status['problem']];
        }
        $to = trim((string) ($row['email'] ?? ''));
        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            return ['ok' => false, 'detail' => 'This message has no valid email address to reply to.'];
        }

        try {
            $vars  = self::replyVars($type, $row, $subject, $body, $quote, $sender);
            $email = self::mailer((string) ($sender['name'] ?? ''));
            $email->setTo($to);
            $team = self::recipients()[0] ?? null;
            if ($team !== null) {
                $email->setReplyTo($team, config('Site')->name);
            }
            if ($copyToSender && filter_var($sender['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $email->setBCC($sender['email']);
            }
            $email->setSubject($subject);
            $email->setMessage(view('emails/reply', $vars));
            $email->setAltMessage(view('emails/reply_text', $vars));

            if ($email->send(false)) {
                return ['ok' => true, 'detail' => 'Reply sent to ' . $to . '.'];
            }

            return ['ok' => false, 'detail' => self::explain(self::debug($email))];
        } catch (\Throwable $e) {
            log_message('error', 'Inbox reply failed: ' . $e->getMessage());

            return ['ok' => false, 'detail' => 'The reply could not be sent: ' . $e->getMessage()];
        }
    }

    /** The subject and opening a reply form starts with. */
    public static function replyDraft(string $type, array $row, string $senderName): array
    {
        $site  = config('Site');
        $first = strtok(trim((string) ($row['name'] ?? '')), ' ') ?: 'there';
        $topic = trim((string) ($row['interest'] ?? ''));

        $subject = $type === 'application'
            ? 'Your application to ' . $site->name . (($row['area'] ?? '') !== '' ? ' — ' . $row['area'] : '')
            : 'Re: your enquiry to ' . $site->name . ($topic !== '' ? ' about ' . $topic : '');

        $opening = $type === 'application'
            ? 'Thank you for applying to ' . $site->name . (($row['area'] ?? '') !== '' ? ' for a role in ' . $row['area'] : '') . '.'
            : 'Thank you for your enquiry' . ($topic !== '' ? ' about our ' . $topic : '') . '.';

        return [
            'subject' => $subject,
            // Ends at "Kind regards," — the signature under the message supplies the name.
            'body'    => "Dear {$first},\n\n{$opening}\n\n\n\nKind regards,",
        ];
    }

    /** @return array<string,mixed> what the reply templates show */
    public static function replyVars(string $type, array $row, string $subject, string $body, bool $quote, array $sender): array
    {
        $site     = config('Site');
        $received = strtotime((string) ($row['created_at'] ?? '')) ?: time();

        return [
            'site'      => $site,
            'subject'   => $subject,
            'body'      => trim($body),
            'quote'     => $quote && trim((string) ($row['message'] ?? '')) !== '' ? trim((string) $row['message']) : null,
            'quoteHead' => 'On ' . (new \DateTimeImmutable('@' . $received))->setTimezone(new \DateTimeZone(self::TIMEZONE))->format('j M Y')
                         . ', ' . trim((string) ($row['name'] ?? 'you')) . ' wrote:',
            'sender'    => trim((string) ($sender['name'] ?? '')),
            'email'     => (string) $site->email,
            'website'   => preg_replace('#^https?://#', '', rtrim((string) $site->website, '/')),
            'websiteUrl'=> (string) $site->website,
        ];
    }

    // ── Internals ────────────────────────────────────────────────────────────

    /** @return array<string,mixed> everything the two submission templates show */
    public static function submissionVars(string $kind, array $data, ?int $id, bool $hasCv): array
    {
        $site     = config('Site');
        $career   = $kind === 'career';
        $name     = trim((string) ($data['name'] ?? '')) ?: 'Someone';
        $company  = trim((string) ($data['company'] ?? ''));
        $interest = trim((string) ($data['interest'] ?? ''));
        $area     = trim((string) ($data['area'] ?? ''));

        if ($career) {
            $subject = 'New job application: ' . $name . ($area !== '' ? ' — ' . $area : '');
            $summary = $name . ' has applied for a role in ' . ($area !== '' ? $area : 'your team') . '.';
        } else {
            $subject = 'New enquiry: ' . $name . ($company !== '' ? ' (' . $company . ')' : '');
            $summary = $name . ($company !== '' ? ' from ' . $company : '') . ' has sent an enquiry'
                . ($interest !== '' ? ' about ' . $interest : '') . '.';
        }

        $years   = trim((string) ($data['experience'] ?? ''));
        $details = $career
            ? [
                'Name'          => $name,
                'Email'         => (string) ($data['email'] ?? ''),
                'Phone'         => (string) ($data['phone'] ?? ''),
                'Area'          => $area,
                'Experience'    => $years === '' ? '' : rtrim(rtrim($years, '0'), '.') . ' years',
                'CV'            => $hasCv ? (string) ($data['resume_original'] ?? 'Attached') . ' (attached)' : 'Not attached',
            ]
            : [
                'Name'          => $name,
                'Email'         => (string) ($data['email'] ?? ''),
                'Phone'         => (string) ($data['phone'] ?? ''),
                'Company'       => $company,
                'Interested in' => $interest,
            ];

        $replySubject = $career
            ? 'Your application to ' . $site->name
            : 'Re: your enquiry to ' . $site->name;

        return [
            'site'       => $site,
            'career'     => $career,
            'subject'    => $subject,
            'heading'    => $career ? 'New job application' : 'New enquiry',
            'summary'    => $summary,
            'name'       => $name,
            'details'    => array_filter($details, static fn ($v) => trim((string) $v) !== ''),
            'message'    => trim((string) ($data['message'] ?? '')),
            'replyUrl'   => 'mailto:' . rawurlencode((string) ($data['email'] ?? '')) . '?subject=' . rawurlencode($replySubject),
            'cmsUrl'     => $id !== null ? admin_url('inbox/message/' . ($career ? 'application' : 'enquiry') . '/' . $id) : null,
            'when'       => self::now(),
            'source'     => $career ? 'the Careers page' : 'the Contact page',
            'website'    => preg_replace('#^https?://#', '', rtrim((string) $site->website, '/')),
        ];
    }

    /** A fresh mailer per message. $person, when given, is shown as "Name · Site". */
    private static function mailer(string $person = ''): \CodeIgniter\Email\Email
    {
        $email = self::$mailerFactory !== null ? (self::$mailerFactory)() : service('email', null, false);
        $email->setMailType('html');

        $site = config('Site')->name;
        $name = $person !== '' ? $person . ' · ' . $site : (config('Email')->fromName ?: $site . ' Website');
        $email->setFrom(self::fromAddress(), $name);

        return $email;
    }

    private static function fromAddress(): string
    {
        $c = config('Email');

        return trim($c->fromEmail) ?: (trim($c->SMTPUser) ?: (string) config('Site')->email);
    }

    private static function now(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE)))->format('j M Y, g:i A') . ' IST';
    }

    /** The mail server's reply, without the message body. */
    private static function debug(\CodeIgniter\Email\Email $email): string
    {
        return trim(strip_tags((string) $email->printDebugger(['headers'])));
    }

    /** The common SMTP failures, in words someone can act on. */
    private static function explain(string $debug): string
    {
        $d = strtolower($debug);

        return match (true) {
            str_contains($d, '535') || str_contains($d, 'username and password not accepted') || str_contains($d, 'authentication')
                => 'The mail server refused the sign-in. For Gmail, email.SMTPPass must be an App Password (Google Account → Security → 2-Step Verification → App passwords), not the normal password.',
            str_contains($d, 'unable to connect') || str_contains($d, 'connection') || str_contains($d, 'timed out')
                => 'Could not reach the mail server. Check email.SMTPHost and email.SMTPPort (Gmail: smtp.gmail.com, 587), and that the server allows outgoing connections.',
            str_contains($d, 'starttls') || str_contains($d, 'crypto')
                => 'The secure connection failed. For port 587 use email.SMTPCrypto = tls; for port 465 use ssl.',
            default => 'The email could not be sent. The mail server said: ' . mb_strimwidth($debug, 0, 300, '…'),
        };
    }
}
