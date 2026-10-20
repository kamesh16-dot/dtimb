<?php

namespace Tests\Unit;

use App\Libraries\Notifier;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The enquiry / application alert emails. Everything in them comes from a
 * public form, so it must arrive escaped; and the status check is what the
 * Settings screen, the dashboard and cms:status all rely on.
 */
final class NotifierTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'site']);
    }

    protected function tearDown(): void
    {
        Factories::reset('config');
        parent::tearDown();
    }

    private function email(array $values): void
    {
        $c = config('Email');
        foreach ($values as $k => $v) {
            $c->{$k} = $v;
        }
    }

    private function ready(): void
    {
        $this->email(['protocol' => 'smtp', 'SMTPHost' => 'smtp.gmail.com', 'SMTPUser' => 'a@example.com', 'SMTPPass' => 'secret', 'recipients' => 'team@example.com']);
    }

    public function testStatusNamesWhatIsMissing(): void
    {
        $this->email(['protocol' => 'mail', 'SMTPHost' => '']);
        $this->assertStringContainsString('SMTPHost', Notifier::status()['problem']);

        $this->email(['protocol' => 'smtp', 'SMTPHost' => 'smtp.gmail.com', 'SMTPUser' => 'a@example.com', 'SMTPPass' => '']);
        $this->assertStringContainsString('password', Notifier::status()['problem']);

        $this->ready();
        $this->assertTrue(Notifier::status()['ok']);
    }

    public function testRecipientsComeFromEnvOrFallBackToTheContactEmail(): void
    {
        $this->email(['recipients' => 'one@example.com, two@example.com, not-an-email']);
        $this->assertSame(['one@example.com', 'two@example.com'], Notifier::recipients());

        $this->email(['recipients' => '']);
        config('Site')->email = 'contact@example.com';
        $this->assertSame(['contact@example.com'], Notifier::recipients());
    }

    public function testGmailAppPasswordSpacesAreIgnored(): void
    {
        $_ENV['email.SMTPHost'] = 'smtp.gmail.com';
        $_ENV['email.SMTPPass'] = 'abcd efgh ijkl mnop';
        try {
            $this->assertSame('abcdefghijklmnop', (new \Config\Email())->SMTPPass);
        } finally {
            unset($_ENV['email.SMTPHost'], $_ENV['email.SMTPPass']);
        }
    }

    public function testAnEnquiryReadsAtAGlance(): void
    {
        $v = Notifier::submissionVars('contact', [
            'name' => 'Priya Raman', 'email' => 'priya@example.com', 'phone' => '',
            'company' => 'Sundaram Tubes', 'interest' => 'Tube Mill', 'message' => 'Please quote.',
        ], 42, false);

        $this->assertSame('New enquiry: Priya Raman (Sundaram Tubes)', $v['subject']);
        $this->assertSame('Priya Raman from Sundaram Tubes has sent an enquiry about Tube Mill.', $v['summary']);
        $this->assertArrayNotHasKey('Phone', $v['details'], 'empty fields are left out');
        $this->assertStringEndsWith('/admin/inbox/message/enquiry/42', $v['cmsUrl']);
        $this->assertStringStartsWith('mailto:priya%40example.com?subject=', $v['replyUrl']);
    }

    public function testAnApplicationNamesTheAreaAndTheCv(): void
    {
        $v = Notifier::submissionVars('career', [
            'name' => 'Arjun Mehta', 'email' => 'a@example.com', 'phone' => '9876543210', 'area' => 'Machine design',
            'experience' => '6.0', 'resume_original' => 'Arjun-CV.pdf',
        ], 7, true);

        $this->assertSame('New job application: Arjun Mehta — Machine design', $v['subject']);
        $this->assertSame('6 years', $v['details']['Experience']);
        $this->assertSame('Arjun-CV.pdf (attached)', $v['details']['CV']);
    }

    public function testNoCmsLinkWhenTheMessageCouldNotBeSaved(): void
    {
        $this->assertNull(Notifier::submissionVars('contact', ['name' => 'X', 'email' => 'x@example.com'], null, false)['cmsUrl']);
    }

    public function testEverythingAVisitorTypesIsEscapedInTheEmail(): void
    {
        $v = Notifier::submissionVars('contact', [
            'name' => '<script>alert(1)</script>', 'email' => 'x@example.com',
            'company' => '<b onmouseover=x>Co</b>', 'message' => "<img src=x onerror=alert(1)>\nline two",
        ], 1, false);
        $html = view('emails/submission', $v);

        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('<b onmouseover', $html);
        $this->assertStringContainsString('line two', $html);
    }

    public function testThePlainTextCopyCarriesTheSameFacts(): void
    {
        $v    = Notifier::submissionVars('contact', ['name' => 'Priya Raman', 'email' => 'p@example.com', 'message' => 'Hello there'], 3, false);
        $text = view('emails/submission_text', $v);

        $this->assertStringContainsString('NEW ENQUIRY', $text);
        $this->assertStringContainsString('p@example.com', $text);
        $this->assertStringContainsString('Hello there', $text);
        $this->assertStringContainsString('/admin/inbox/message/enquiry/3', $text);
    }

    public function testNothingIsSentWhenEmailIsNotReady(): void
    {
        $this->email(['protocol' => 'smtp', 'SMTPHost' => '']);

        $this->assertFalse(Notifier::submission('contact', ['name' => 'X', 'email' => 'x@example.com'], 1));
    }
}
