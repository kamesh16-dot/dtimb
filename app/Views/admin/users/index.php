<div class="page-header"><h1>Users</h1>
  <a href="/admin/users/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    Add User
  </a>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last login</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= esc($r['name']) ?></td>
          <td style="font-size:.78rem"><?= esc($r['email']) ?></td>
          <td><span class="pill <?= $r['role'] === 'admin' ? 'pill-orange' : 'pill-grey' ?>"><?= esc($r['role']) ?></span></td>
          <td style="font-size:.75rem;color:var(--dim)"><?= esc(substr($r['last_login_at'] ?? 'Never', 0, 16)) ?></td>
          <td><?= $r['is_active'] ? '<span class="pill pill-green">Active</span>' : '<span class="pill pill-red">Inactive</span>' ?></td>
          <td>
            <?php if ($r['id'] !== ($adminUser['id'] ?? 0)): ?>
            <form method="POST" action="/admin/users/delete/<?= $r['id'] ?>" style="display:inline">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Remove user '<?= esc($r['name']) ?>'?">Remove</button>
            </form>
            <?php else: ?>
            <span style="font-size:.72rem;color:var(--dim)">You</span>
            <?php endif ?>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
