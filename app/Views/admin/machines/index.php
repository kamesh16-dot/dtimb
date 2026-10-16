<div class="page-header">
  <h1>Machines</h1>
  <a href="/admin/machines/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    Add Machine
  </a>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Slug</th>
          <th>Featured</th>
          <th>Order</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:2rem">No machines yet. <a href="/admin/machines/create" style="color:var(--red)">Add one →</a></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['name']) ?></td>
            <td><?= esc($r['category'] ?? '—') ?></td>
            <td style="font-size:.78rem;color:var(--dim)"><?= esc($r['slug']) ?></td>
            <td><?= $r['is_featured'] ? '<span class="pill pill-green">Yes</span>' : '<span class="pill pill-grey">No</span>' ?></td>
            <td><?= esc($r['sort_order'] ?? 0) ?></td>
            <td>
              <div class="actions">
                <a href="/admin/machines/edit/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
                <form method="POST" action="/admin/machines/delete/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete '<?= esc($r['name']) ?>'?">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
