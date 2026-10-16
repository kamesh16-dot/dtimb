<div class="page-header"><h1>Redirects</h1></div>
<div class="card form-card card-pad" style="margin-bottom:1.25rem">
  <h3 style="font-size:.8rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:1rem">Add redirect</h3>
  <form method="POST" action="/admin/redirects/store">
    <?= csrf_field() ?>
    <div class="field-row" style="grid-template-columns:1fr 1fr auto">
      <div class="field"><label>From URL</label><input type="text" name="from_url" placeholder="/old-page" required></div>
      <div class="field"><label>To URL</label><input type="text" name="to_url" placeholder="/new-page" required></div>
      <div class="field"><label>Code</label><select name="code"><option value="301">301</option><option value="302">302</option></select></div>
    </div>
    <button type="submit" class="btn btn-primary">Add</button>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>From</th><th>To</th><th>Code</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--dim);padding:2rem">No redirects yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td style="font-size:.78rem"><?= esc($r['from_url']) ?></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= esc($r['to_url']) ?></td>
            <td><span class="pill pill-grey"><?= esc($r['code']) ?></span></td>
            <td>
              <form method="POST" action="/admin/redirects/delete/<?= $r['id'] ?>">
                <?= csrf_field() ?><button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete redirect?">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
