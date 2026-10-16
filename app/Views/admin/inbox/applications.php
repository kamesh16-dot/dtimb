<div class="page-header">
  <h1>Inbox</h1>
  <div style="display:flex;gap:.5rem">
    <a href="/admin/inbox" class="btn btn-secondary">Enquiries</a>
    <a href="/admin/inbox/applications" class="btn btn-primary">Applications</a>
  </div>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Email</th><th>Area</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:2rem">No applications yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['name'] ?? '—') ?></td>
            <td style="font-size:.78rem"><?= esc($r['email'] ?? '—') ?></td>
            <td><?= esc($r['career_area'] ?? '—') ?></td>
            <td style="font-size:.75rem;color:var(--dim)"><?= esc(substr($r['created_at'] ?? '', 0, 10)) ?></td>
            <td><?= ($r['status'] ?? 'new') === 'new' ? '<span class="pill pill-red">New</span>' : '<span class="pill pill-grey">Read</span>' ?></td>
            <td>
              <?php if (($r['status'] ?? 'new') === 'new'): ?>
              <form method="POST" action="/admin/inbox/read/<?= $r['id'] ?>/application">
                <?= csrf_field() ?><button type="submit" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Mark read</button>
              </form>
              <?php endif ?>
            </td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
