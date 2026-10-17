<?php
$locked = static fn (array $r): bool =>
    ! empty($r['locked_until']) && strtotime((string) $r['locked_until']) > time();
?>
<div class="page-header">
  <h1>Users</h1>
  <a href="/admin/users/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    Add User
  </a>
</div>

<p style="margin:0 0 1rem;font-size:.78rem;color:var(--dim)">
  <?= count($rows) ?> account<?= count($rows) === 1 ? '' : 's' ?>,
  <?= (int) $adminCount ?> with admin access. Admins can manage users and settings; editors can only change content.
</p>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last sign-in</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): $isMe = (int) $r['id'] === (int) $meId; ?>
        <tr>
          <td>
            <?= esc($r['name']) ?>
            <?php if ($isMe): ?><span class="pill pill-grey">You</span><?php endif ?>
            <?php if (! empty($r['force_password_change'])): ?>
              <div style="font-size:.7rem;color:var(--dim)">Must set a new password at next sign-in</div>
            <?php endif ?>
          </td>
          <td style="font-size:.78rem"><?= esc($r['email']) ?></td>
          <td><span class="pill <?= $r['role'] === 'admin' ? 'pill-orange' : 'pill-grey' ?>"><?= esc($r['role']) ?></span></td>
          <td style="font-size:.75rem;color:var(--dim);white-space:nowrap">
            <?= ! empty($r['last_login_at']) ? esc(date('d M Y, H:i', strtotime((string) $r['last_login_at']))) : 'Never' ?>
          </td>
          <td>
            <?php if ($locked($r)): ?>
              <span class="pill pill-red">Locked</span>
            <?php elseif ($r['is_active']): ?>
              <span class="pill pill-green">Active</span>
            <?php else: ?>
              <span class="pill pill-grey">Switched off</span>
            <?php endif ?>
          </td>
          <td>
            <div class="actions" style="white-space:nowrap">
              <a href="/admin/users/edit/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
              <?php if ($locked($r)): ?>
                <form method="POST" action="/admin/users/unlock/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Unlock</button>
                </form>
              <?php endif ?>
              <?php if (! $isMe): ?>
                <form method="POST" action="/admin/users/delete/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Remove <?= esc($r['name'], 'attr') ?>? The account can be restored afterwards.">Remove</button>
                </form>
              <?php endif ?>
            </div>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (! empty($deleted)): ?>
  <div class="card card-pad" style="margin-top:1rem">
    <h2 style="font-size:.9rem;margin:0 0 .6rem">Removed accounts</h2>
    <p style="font-size:.75rem;color:var(--dim);margin:0 0 .75rem">
      These cannot sign in. Their email addresses stay reserved until the account is restored or purged from the database.
    </p>
    <table class="data-table">
      <tbody>
        <?php foreach ($deleted as $r): ?>
          <tr>
            <td><?= esc($r['name']) ?></td>
            <td style="font-size:.78rem;color:var(--dim)"><?= esc($r['email']) ?></td>
            <td style="text-align:right">
              <form method="POST" action="/admin/users/restore/<?= $r['id'] ?>" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Restore</button>
              </form>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
<?php endif ?>
