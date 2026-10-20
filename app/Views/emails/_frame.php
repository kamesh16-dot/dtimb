<?php
/**
 * Shared frame for every CMS email. Table layout and inline styles only:
 * that is what Gmail, Outlook and phone mail apps render reliably.
 *
 * @var \Config\Site $site
 * @var string $preheader  one line shown beside the subject in the inbox list
 * @var string $body       the inner HTML, already escaped
 * @var string $footer     the closing note, already escaped
 */
$red = '#c8102e';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title><?= esc($site->name) ?></title>
</head>
<body style="margin:0;padding:0;background:#f2f3f5;-webkit-text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f2f3f5;"><?= esc($preheader) ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f2f3f5;">
  <tr>
    <td align="center" style="padding:24px 12px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e3e6ea;">
        <tr>
          <td style="background:#0f1318;padding:18px 28px;border-bottom:3px solid <?= $red ?>;">
            <span style="font-family:Arial,Helvetica,sans-serif;font-size:22px;font-weight:900;font-style:italic;letter-spacing:-0.5px;color:<?= $red ?>;"><?= esc($site->name) ?></span>
            <span style="font-family:Arial,Helvetica,sans-serif;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#aeb8c1;padding-left:10px;"><?= esc($site->tagline) ?></span>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 28px 8px;font-family:Arial,Helvetica,sans-serif;color:#1b2127;font-size:15px;line-height:1.55;">
            <?= $body ?>
          </td>
        </tr>
        <tr>
          <td style="padding:16px 28px 24px;font-family:Arial,Helvetica,sans-serif;color:#6b7680;font-size:12px;line-height:1.6;border-top:1px solid #eceef1;">
            <?= $footer ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
