<div class="page-header"><h1>Pages</h1></div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Page</th>
          <th>Browser title</th>
          <th>Description</th>
          <th></th>
          <th><span class="sr-only">Details</span></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--dim);padding:2rem">No pages found. Run <code>php spark migrate</code>.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php $desc = trim((string) ($r['seo_description'] ?? '')); ?>
            <tr data-drawer="page-<?= esc($r['key'], 'attr') ?>" tabindex="0" aria-expanded="false" aria-label="<?= esc($r['title'], 'attr') ?>: show all details">
              <td>
                <?= esc($r['title']) ?>
                <div style="font-size:.72rem;color:var(--dim)">/<?= esc($r['key'] === 'home' ? '' : $r['key']) ?></div>
              </td>
              <td style="font-size:.78rem"><span class="cell-clip" style="max-width:16rem"><?= esc($r['seo_title']) ?></span></td>
              <td style="font-size:.75rem;color:var(--dim)">
                <?= $desc !== '' ? '<span class="cell-clip">' . esc($desc) . '</span>' : '<span class="pill pill-orange">Not set</span>' ?>
              </td>
              <td><a href="<?= admin_url('pages/edit/') ?><?= esc($r['key'], 'url') ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a></td>
              <td class="row-more" aria-hidden="true">›</td>
            </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>
<?php if (! empty($rows)): ?><p class="table-hint">Click a page to see its full title, description and opening text.</p><?php endif ?>

<?php foreach ($rows as $r): ?>
  <?php
  $blocks = json_decode((string) ($r['body_blocks'] ?? ''), true);
  $blocks = is_array($blocks) ? array_keys($blocks) : [];
  $title  = (string) ($r['seo_title'] ?? '');
  $desc   = trim((string) ($r['seo_description'] ?? ''));
  $path   = $r['key'] === 'home' ? '' : $r['key'];
  ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'page-' . $r['key'],
      'title'    => $r['title'],
      'subtitle' => '/' . $path,
      'rows'     => [
          ['label' => 'Browser title (' . mb_strlen($title) . ' characters)', 'value' => $title],
          ['label' => 'Search description (' . mb_strlen($desc) . ' characters)', 'value' => $desc, 'type' => 'long'],
          ['label' => 'Opening paragraph', 'value' => $r['intro'] ?? null, 'type' => 'long'],
          ['label' => 'Content blocks', 'value' => array_map(static fn ($b) => ucfirst(str_replace('_', ' ', (string) $b)), $blocks), 'type' => 'lines'],
          ['label' => 'Last changed', 'value' => ! empty($r['updated_at']) ? date('d M Y, H:i', strtotime((string) $r['updated_at'])) : null],
      ],
      'actions' => '<a class="btn btn-primary" href="' . admin_url('pages/edit/') . esc($r['key'], 'url') . '">Edit</a>'
                 . '<a class="btn btn-secondary" href="' . esc(base_url($path), 'attr') . '" target="_blank" rel="noopener">View on website ↗</a>',
  ]) ?>
<?php endforeach ?>
<?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'pages', 'one' => 'page']) ?>

<p style="margin-top:.9rem;font-size:.75rem;color:var(--dim)">
  Each page is a fixed part of the site, so pages cannot be added or removed here.
  Lists such as projects, clients and industries are edited in the Lists Hub.
</p>
