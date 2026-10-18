<?php
$err = static fn (string $f) => $errors[$f] ?? null;
$val = static fn (string $f) => old($f, $settings[$f] ?? '');
?>
<div class="page-header"><h1>Settings</h1></div>

<?php if (! empty($errors)): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="card form-card card-pad">
  <h2 style="font-size:.95rem;margin:0 0 .3rem">Site identity</h2>
  <p style="font-size:.78rem;color:var(--dim);margin:0 0 1rem">
    These appear on every page of the website.
  </p>

  <form method="POST" action="<?= admin_url('settings/save') ?>">
    <?= csrf_field() ?>
    <?php foreach ($fields as $key => $f): ?>
      <div class="field">
        <label><?= esc($f['label']) ?><?= ! empty($f['required']) ? ' *' : '' ?></label>
        <input type="<?= $f['type'] === 'email' ? 'email' : ($f['type'] === 'url' ? 'url' : 'text') ?>"
               name="<?= esc($key, 'attr') ?>"
               value="<?= esc($val($key)) ?>"
               <?= ! empty($f['required']) ? 'required' : '' ?>>
        <p class="field-hint"><?= esc($f['hint']) ?></p>
        <?php if ($err($key)): ?><p class="field-hint" style="color:var(--red)"><?= esc($err($key)) ?></p><?php endif ?>
      </div>
    <?php endforeach ?>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save settings</button>
    </div>
  </form>
</div>

<div class="card card-pad" style="margin-top:1.25rem">
  <h2 style="font-size:.95rem;margin:0 0 .3rem">Outgoing email</h2>
  <?php if ($mail['configured']): ?>
    <p style="font-size:.8rem;color:var(--dim);margin:0">
      <span class="pill pill-green">Configured</span>
      Sending through <strong><?= esc($mail['host']) ?></strong><?= $mail['from'] !== '' ? ' as ' . esc($mail['from']) : '' ?>.
    </p>
  <?php else: ?>
    <p style="font-size:.8rem;color:var(--dim);margin:0">
      <span class="pill pill-orange">Not configured</span>
      Enquiries and job applications are still saved to the Inbox, but no email is sent about them.
    </p>
  <?php endif ?>
  <p style="font-size:.75rem;color:var(--dim);margin:.6rem 0 0">
    Mail server details are credentials, so they are set in the site's <code>.env</code> file rather than here.
    Ask whoever manages the server to set <code>email.SMTPHost</code>, <code>email.SMTPUser</code> and
    <code>email.SMTPPass</code>.
  </p>
</div>

<div class="card card-pad" style="margin-top:1rem">
  <h2 style="font-size:.95rem;margin:0 0 .3rem">Elsewhere</h2>
  <ul style="font-size:.8rem;color:var(--dim);margin:.4rem 0 0;padding-left:1.1rem;line-height:1.7">
    <li>Works and office addresses, and the map pins, are in <a href="<?= admin_url('lists/network_nodes') ?>" style="color:var(--red)">Lists Hub → Network Nodes</a>.</li>
    <li>Page titles and search descriptions are in <a href="<?= admin_url('seo') ?>" style="color:var(--red)">SEO</a>.</li>
    <li>The header Products menu is built from <a href="<?= admin_url('machines') ?>" style="color:var(--red)">Machines</a>.</li>
  </ul>
</div>
