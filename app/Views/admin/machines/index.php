<div class="page-header">
  <h1>Machines</h1>
  <a href="<?= admin_url('machines/create') ?>" class="btn btn-primary">
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
          <th>Status</th>
          <th>Menu</th>
          <th>Added</th>
          <th></th>
          <th><span class="sr-only">Details</span></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" style="text-align:center;color:var(--dim);padding:2rem">No machines yet. <a href="<?= admin_url('machines/create') ?>" style="color:var(--red)">Add one →</a></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
          <tr data-drawer="machine-<?= $r['id'] ?>" tabindex="0" aria-expanded="false"
              aria-label="<?= esc($r['name'], 'attr') ?>: show all details"<?= $r['deleted_at'] ? ' style="opacity:.45"' : '' ?>>
            <td>
              <?= esc($r['name']) ?>
              <?php if ($r['deleted_at']): ?><span class="pill pill-grey">Deleted</span><?php endif ?>
              <div style="font-size:.72rem;color:var(--dim)"><?= esc($r['series_label']) ?> · <?= esc($r['metric']) ?></div>
            </td>
            <td style="font-size:.8rem"><?= esc($r['group_name']) ?></td>
            <td><span class="pill pill-grey"><?= esc(ucfirst($r['kind'])) ?></span></td>
            <td>
              <?= $r['is_published']
                    ? '<span class="pill pill-green">Published</span>'
                    : '<span class="pill pill-orange">Draft</span>' ?>
            </td>
            <td><?= $r['show_in_menu'] ? 'Shown' : '<span style="color:var(--dim)">Hidden</span>' ?></td>
            <td style="font-size:.78rem;color:var(--dim);white-space:nowrap">
              <?= ! empty($r['created_at']) ? esc(date('d M Y', strtotime((string) $r['created_at']))) : 'At launch' ?>
            </td>
            <td>
              <?php if (! $r['deleted_at']): ?>
                <a href="<?= admin_url('machines/edit/') ?><?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
              <?php endif ?>
            </td>
            <td class="row-more" aria-hidden="true">›</td>
          </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
<?php if (! empty($rows)): ?><p class="table-hint">Click a row to see everything about a machine, and to view, edit or delete it.</p><?php endif ?>
<?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'machines', 'one' => 'machine']) ?>

<?php foreach ($rows as $r): ?>
  <?php
  $deleted = ! empty($r['deleted_at']);

  if ($deleted) {
      $actions = '<form method="POST" action="' . admin_url('machines/restore/') . $r['id'] . '">' . csrf_field()
          . '<button type="submit" class="btn btn-primary">Restore</button></form>';
  } else {
      $actions = '<a class="btn btn-primary" href="' . admin_url('machines/edit/') . $r['id'] . '">Edit</a>'
          . ($r['is_published']
              ? '<a class="btn btn-secondary" href="' . esc(base_url('machines/' . $r['slug']), 'attr') . '" target="_blank" rel="noopener">View on website ↗</a>'
              : '')
          . '<form method="POST" action="' . admin_url('machines/delete/') . $r['id'] . '">' . csrf_field()
          . '<button type="submit" class="btn btn-danger" data-confirm="Delete \'' . esc($r['name'], 'attr') . '\'? It will stop appearing on the website.">Delete</button></form>';
  }

  $status = match (true) {
      $deleted              => ['Deleted', 'grey'],
      (bool) $r['is_published'] => ['Published — visible on the website', 'green'],
      default               => ['Draft — hidden from the website', 'orange'],
  };
  ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'machine-' . $r['id'],
      'title'    => $r['name'],
      'subtitle' => trim($r['series_label'] . ' · ' . ucfirst($r['kind'])),
      'rows'     => [
          ['label' => 'Photo',              'value' => $r['image_key'], 'type' => 'image'],
          ['label' => 'Status',             'value' => $status, 'type' => 'pill'],
          ['label' => 'Summary',            'value' => $r['summary'], 'type' => 'long'],
          ['label' => 'Headline metric',    'value' => $r['metric']],
          ['label' => 'Series',             'value' => $r['series_label']],
          ['label' => 'Page address',       'value' => '/machines/' . $r['slug'], 'type' => 'code'],
          ['label' => 'Menu group',         'value' => $r['group_name']],
          ['label' => 'In the Products menu', 'value' => $r['show_in_menu'] ? 'Shown' : 'Hidden'],
          ['label' => 'Order',              'value' => 'Listings ' . (int) $r['menu_order'] . ' · Menu ' . (int) $r['nav_order']],
          ['label' => 'Spec sheet',         'value' => $r['spec_sheet'] ? $r['spec_sheet'] . '.html' : null],
          ['label' => 'Illustration',       'value' => $r['visual_key'] ? ucfirst($r['visual_key']) : null],
          ['label' => 'Full product page',  'value' => ! empty($r['details_json']) ? 'Specifications, features and galleries are stored' : 'Basic page built from the fields above'],
          ['label' => 'Added',              'value' => ! empty($r['created_at']) ? date('d M Y, H:i', strtotime((string) $r['created_at'])) : 'At launch'],
          ['label' => 'Last changed',       'value' => ! empty($r['updated_at']) ? date('d M Y, H:i', strtotime((string) $r['updated_at'])) : null],
      ],
      'actions' => $actions,
  ]) ?>
<?php endforeach ?>

<p style="margin-top:.9rem;font-size:.75rem;color:var(--dim)">
  Newest machines are listed first.
  The order on the website is set by each machine's <strong>Listings</strong> and <strong>Menu</strong> numbers, shown in its details.
</p>
