<?php
/**
 * A reply written in the Inbox. Reads like a personal email: the text as
 * written, a company signature, and — if chosen — their message quoted below.
 * See App\Libraries\Notifier::reply().
 *
 * @var \Config\Site $site  @var string $subject  @var string $body  @var ?string $quote
 * @var string $quoteHead  @var string $sender  @var string $email  @var string $website  @var string $websiteUrl
 */
$red = '#c8102e';

// Blank lines start a new paragraph; single line breaks are kept.
$paras = '';
foreach (preg_split('/\R{2,}/', $body) ?: [] as $p) {
    if (trim($p) !== '') {
        $paras .= '<p style="margin:0 0 14px;">' . nl2br(esc(trim($p))) . '</p>';
    }
}

$signature = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0 6px;border-top:2px solid ' . $red . ';">'
    . '<tr><td style="padding-top:10px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.6;color:#4d5660;">'
    . ($sender !== '' ? '<strong style="color:#1b2127;">' . esc($sender) . '</strong><br>' : '')
    . esc($site->name) . ' — ' . esc($site->tagline) . '<br>'
    . '<a href="mailto:' . esc($email, 'attr') . '" style="color:' . $red . ';text-decoration:none;">' . esc($email) . '</a>'
    . ' · <a href="' . esc($websiteUrl, 'attr') . '" style="color:' . $red . ';text-decoration:none;">' . esc($website) . '</a>'
    . '</td></tr></table>';

$quoted = $quote === null ? '' :
    '<div style="margin:22px 0 0;padding:12px 16px;border-left:3px solid #d5d9de;color:#6b7680;font-size:13px;line-height:1.6;">'
    . '<div style="margin-bottom:6px;">' . esc($quoteHead) . '</div>'
    . nl2br(esc($quote)) . '</div>';

echo view('emails/_frame', [
    'site'      => $site,
    'preheader' => mb_strimwidth(preg_replace('/\s+/', ' ', $body), 0, 110, '…'),
    'body'      => '<div style="font-size:15px;line-height:1.65;color:#1b2127;">' . $paras . '</div>' . $signature . $quoted,
    'footer'    => 'You are receiving this because you contacted ' . esc($site->name) . ' through ' . esc($website) . '. '
                 . 'Just reply to this email to continue the conversation.',
]);
