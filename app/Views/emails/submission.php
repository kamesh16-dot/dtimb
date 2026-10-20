<?php
/**
 * Alert for a new enquiry or job application. See App\Libraries\Notifier.
 *
 * @var \Config\Site $site  @var bool $career  @var string $heading  @var string $summary
 * @var string $name  @var array $details  @var string $message  @var string $replyUrl
 * @var ?string $cmsUrl  @var string $when  @var string $source  @var string $website
 */
$red    = '#c8102e';
$label  = 'padding:9px 14px 9px 0;color:#6b7680;font-size:13px;white-space:nowrap;vertical-align:top;border-bottom:1px solid #eceef1;width:120px;';
$value  = 'padding:9px 0;color:#1b2127;font-size:14px;vertical-align:top;border-bottom:1px solid #eceef1;';
$btn    = 'display:inline-block;padding:12px 22px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;text-decoration:none;';

ob_start();
?>
<p style="margin:0 0 4px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:<?= $red ?>;font-weight:bold;"><?= esc($heading) ?></p>
<h1 style="margin:0 0 10px;font-size:22px;line-height:1.3;color:#0f1318;"><?= esc($summary) ?></h1>
<p style="margin:0 0 22px;color:#6b7680;font-size:13px;">Received <?= esc($when) ?> through <?= esc($source) ?>.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
  <?php foreach ($details as $k => $v): ?>
    <tr>
      <td style="<?= $label ?>"><?= esc($k) ?></td>
      <td style="<?= $value ?>">
        <?php if ($k === 'Email'): ?>
          <a href="mailto:<?= esc($v, 'attr') ?>" style="color:<?= $red ?>;text-decoration:none;"><?= esc($v) ?></a>
        <?php elseif ($k === 'Phone'): ?>
          <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $v), 'attr') ?>" style="color:#1b2127;text-decoration:none;"><?= esc($v) ?></a>
        <?php else: ?>
          <?= esc($v) ?>
        <?php endif ?>
      </td>
    </tr>
  <?php endforeach ?>
</table>

<?php if ($message !== ''): ?>
  <p style="margin:0 0 8px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#6b7680;font-weight:bold;"><?= $career ? 'Their note' : 'Their message' ?></p>
  <div style="margin:0 0 26px;padding:14px 16px;background:#f6f7f9;border-left:3px solid <?= $red ?>;border-radius:4px;font-size:15px;line-height:1.6;color:#1b2127;"><?= nl2br(esc($message)) ?></div>
<?php endif ?>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;">
  <tr>
    <td style="padding:0 10px 10px 0;">
      <a href="<?= esc($replyUrl, 'attr') ?>" style="<?= $btn ?>background:<?= $red ?>;color:#ffffff;">Reply to <?= esc(strtok($name, ' ')) ?></a>
    </td>
    <?php if ($cmsUrl): ?>
      <td style="padding:0 0 10px;">
        <a href="<?= esc($cmsUrl, 'attr') ?>" style="<?= $btn ?>background:#ffffff;color:#0f1318;border:1px solid #c9ced4;">Open in the CMS</a>
      </td>
    <?php endif ?>
  </tr>
</table>
<?php
$body = ob_get_clean();

$footer = 'You can also just press <strong>Reply</strong> — your answer goes straight to ' . esc($name) . '.'
    . ($career ? ' The CV is attached to this email.' : '')
    . '<br>' . ($cmsUrl ? 'It is saved in the Inbox in the CMS, so nothing is lost if this email is deleted.' : 'It could not be saved to the Inbox, so keep this email.')
    . '<br>Automatic message from the ' . esc($site->name) . ' website · ' . esc($website);

echo view('emails/_frame', [
    'site'      => $site,
    'preheader' => $summary . ($message !== '' ? ' “' . mb_strimwidth($message, 0, 90, '…') . '”' : ''),
    'body'      => $body,
    'footer'    => $footer,
]);
