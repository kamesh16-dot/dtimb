<?php
/**
 * Test email from Admin → Settings. See App\Libraries\Notifier::test().
 *
 * @var \Config\Site $site  @var string $when  @var string $sentBy  @var string $to
 * @var string $server  @var string $from  @var string $cmsUrl
 */
$row = static fn (string $k, string $v) => '<tr><td style="padding:8px 14px 8px 0;color:#6b7680;font-size:13px;white-space:nowrap;border-bottom:1px solid #eceef1;">'
    . esc($k) . '</td><td style="padding:8px 0;color:#1b2127;font-size:14px;border-bottom:1px solid #eceef1;">' . esc($v) . '</td></tr>';

$body = '<p style="margin:0 0 4px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#1f8a4c;font-weight:bold;">Email is working</p>'
    . '<h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;color:#0f1318;">This is a test email from the ' . esc($site->name) . ' website.</h1>'
    . '<p style="margin:0 0 20px;">If you can read this, everything is set up. From now on, every enquiry and job application sent through the website arrives here as an email like this one.</p>'
    . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">'
    . $row('Sent to', $to) . $row('Sent from', $from) . $row('Mail server', $server) . $row('Requested by', $sentBy) . $row('Time', $when)
    . '</table>';

$footer = 'Requested from Admin → Settings in the CMS. Nothing else needs doing.'
    . '<br>Automatic message from the ' . esc($site->name) . ' website';

echo view('emails/_frame', ['site' => $site, 'preheader' => 'Email from the website is working.', 'body' => $body, 'footer' => $footer]);
