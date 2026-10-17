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
          <th>Group</th>
          <th>Kind</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Menu</th>
          <th>List&nbsp;/&nbsp;Nav</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" style="text-align:center;color:var(--dim);padding:2rem">No machines yet. <a href="/admin/machines/create" style="color:var(--red)">Add one →</a></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
          <tr<?= $r['deleted_at'] ? ' style="opacity:.45"' : '' ?>>
            <td>
              <?= esc($r['name']) ?>
              <?php if ($r['deleted_at']): ?><span class="pill pill-grey">Deleted</span><?php endif ?>
              <div style="font-size:.72rem;color:var(--dim)"><?= esc($r['series_label']) ?> · <?= esc($r['metric']) ?></div>
            </td>
            <td style="font-size:.8rem"><?= esc($r['group_name']) ?></td>
            <td><span class="pill pill-grey"><?= esc(ucfirst($r['kind'])) ?></span></td>
            <td style="font-size:.78rem;color:var(--dim)"><?= esc($r['slug']) ?></td>
            <td>
              <?= $r['is_published']
                    ? '<span class="pill pill-green">Published</span>'
                    : '<span class="pill pill-orange">Draft</span>' ?>
            </td>
            <td><?= $r['show_in_menu'] ? 'Shown' : '<span style="color:var(--dim)">Hidden</span>' ?></td>
            <td style="font-size:.8rem;color:var(--dim)"><?= (int) $r['menu_order'] ?> / <?= (int) $r['nav_order'] ?></td>
            <td>
              <div class="actions">
                <?php if (! $r['deleted_at']): ?>
                  <a href="<?= base_url('machines/' . $r['slug']) ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">View</a>
                  <a href="/admin/machines/edit/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
                  <form method="POST" action="/admin/machines/delete/<?= $r['id'] ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete '<?= esc($r['name'], 'attr') ?>'? It will stop appearing on the website.">Delete</button>
                  </form>
                <?php else: ?>
                  <form method="POST" action="/admin/machines/restore/<?= $r['id'] ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Restore</button>
                  </form>
                <?php endif ?>
              </div>
            </td>
          </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>

<p style="margin-top:.9rem;font-size:.75rem;color:var(--dim)">
  <strong>List</strong> is the order on the homepage and in prev/next links.
  <strong>Nav</strong> is the order inside the header Products menu; groups follow their lowest Nav number.
</p>
