<?php
$editing = $edit !== null;
$action  = $editing ? '/admin/redirects/edit/' . $edit['id'] : '/admin/redirects/store';
$err     = static fn (string $f) => $errors[$f] ?? null;
$val     = static fn (string $f) => old($f, $edit[$f] ?? '');
?>
<div class="page-header"><h1>Redirects</h1></div>

<p style="margin:0 0 1rem;font-size:.78rem;color:var(--dim);max-width:46rem">
  Send an old address to a new one. These apply only when nothing else on the site matches the
  address, so a redirect cannot take over a page that still exists.
  <?php if ($totalHits > 0): ?><br><strong><?= (int) $totalHits ?></strong> visits have followed a redirect so far.<?php endif ?>
</p>

<?php if (! empty($errors)): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="card form-card card-pad" style="margin-bottom:1.25rem">
  <h2 style="font-size:.95rem;margin:0 0 .75rem"><?= $editing ? 'Edit redirect' : 'Add a redirect' ?></h2>
  <form method="POST" action="<?= esc($action) ?>">
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field">
        <label>Old address *</label>
        <input type="text" name="from_path" value="<?= esc($val('from_path')) ?>" placeholder="/old-page" required>
        <p class="field-hint">A path on this site. The leading slash is added for you.</p>
        <?php if ($err('from_path')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('from_path')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>Send to *</label>
        <input type="text" name="to_path" value="<?= esc($val('to_path')) ?>" placeholder="/new-page" required>
        <p class="field-hint">Another path here, or a full web address to send visitors off-site.</p>
        <?php if ($err('to_path')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('to_path')) ?></p><?php endif ?>
      </div>
    </div>
    <div class="field">
      <label>Kind</label>
      <select name="code">
        <?php foreach ($codes as $code => $label): ?>
          <option value="<?= (int) $code ?>" <?= (int) ($val('code') ?: 301) === (int) $code ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach ?>
      </select>
      <p class="field-hint">Permanent tells search engines to update their records. Use temporary if the old address will come back.</p>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Save redirect' : 'Add redirect' ?></button>
      <?php if ($editing): ?><a href="/admin/redirects" class="btn btn-secondary">Cancel</a><?php endif ?>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Old address</th><th>Sends to</th><th>Kind</th><th>Follows</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--dim);padding:2rem">No redirects yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td style="font-size:.8rem"><?= esc($r['from_path']) ?></td>
            <td style="font-size:.8rem;color:var(--dim)">
              <?= esc($r['to_path']) ?>
              <?php if (isset($chained[(int) $r['id']])): ?>
                <div style="font-size:.7rem"><span class="pill pill-orange">Two hops</span> that address is itself redirected</div>
              <?php endif ?>
            </td>
            <td><span class="pill pill-grey"><?= (int) $r['code'] ?></span></td>
            <td style="font-size:.8rem;color:var(--dim)"><?= (int) $r['hits'] ?></td>
            <td>
              <div class="actions">
                <a href="/admin/redirects?edit=<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
                <form method="POST" action="/admin/redirects/delete/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete the redirect for <?= esc($r['from_path'], 'attr') ?>?">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
