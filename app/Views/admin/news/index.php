<?php use App\Models\NewsModel; ?>
<div class="page-header">
  <h1>News Articles</h1>
  <a href="/admin/news/create" class="btn btn-primary">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    New Article
  </a>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Date</th>
          <th>Tag</th>
          <th>Status</th>
          <th></th>
          <th><span class="sr-only">Details</span></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:2rem">No articles yet. <a href="/admin/news/create" style="color:var(--red)">Write one →</a></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
          <tr data-drawer="news-<?= $r['id'] ?>" tabindex="0" aria-expanded="false"
              aria-label="<?= esc($r['title'], 'attr') ?>: show the full article"<?= $r['deleted_at'] ? ' style="opacity:.45"' : '' ?>>
            <td>
              <?= esc($r['title']) ?>
              <?php if ($r['deleted_at']): ?><span class="pill pill-grey">Deleted</span><?php endif ?>
              <?php if (! empty($r['summary'])): ?>
                <span class="cell-clip" style="font-size:.72rem;color:var(--dim);max-width:34rem"><?= esc($r['summary']) ?></span>
              <?php endif ?>
            </td>
            <td style="font-size:.8rem;white-space:nowrap"><?= esc(date('d M Y', strtotime((string) $r['published_on']))) ?></td>
            <td><?= $r['tag'] ? '<span class="pill pill-grey">' . esc($r['tag']) . '</span>' : '<span style="color:var(--dim)">—</span>' ?></td>
            <td>
              <?= $r['is_published']
                    ? '<span class="pill pill-green">Published</span>'
                    : '<span class="pill pill-orange">Draft</span>' ?>
            </td>
            <td>
              <?php if (! $r['deleted_at']): ?>
                <a href="/admin/news/edit/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
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
<?php if (! empty($rows)): ?><p class="table-hint">Click an article to read it in full, and to view, edit or delete it.</p><?php endif ?>
<?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'articles', 'one' => 'article']) ?>

<?php foreach ($rows as $r): ?>
  <?php
  $deleted = ! empty($r['deleted_at']);

  if ($deleted) {
      $actions = '<form method="POST" action="/admin/news/restore/' . $r['id'] . '">' . csrf_field()
          . '<button type="submit" class="btn btn-primary">Restore</button></form>';
  } else {
      $actions = '<a class="btn btn-primary" href="/admin/news/edit/' . $r['id'] . '">Edit</a>'
          . ($r['is_published']
              ? '<a class="btn btn-secondary" href="' . esc(base_url('news'), 'attr') . '" target="_blank" rel="noopener">View on website ↗</a>'
              : '')
          . '<form method="POST" action="/admin/news/delete/' . $r['id'] . '">' . csrf_field()
          . '<button type="submit" class="btn btn-danger" data-confirm="Delete \'' . esc($r['title'], 'attr') . '\'? It will stop appearing on the website.">Delete</button></form>';
  }

  $hero = ! empty($r['hero_path'])
      ? '<img class="drawer-img" src="' . esc(photo_url((string) $r['hero_path'], '640'), 'attr') . '" alt="" loading="lazy">'
        . (! empty($r['hero_alt']) ? '<span style="font-size:.8rem;color:var(--muted)">' . esc($r['hero_alt']) . '</span>' : '')
      : null;

  $photos = (int) ($galleryCounts[$r['id']] ?? 0);
  ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'news-' . $r['id'],
      'title'    => $r['title'],
      'subtitle' => date('d M Y', strtotime((string) $r['published_on'])) . ($r['tag'] ? ' · ' . $r['tag'] : ''),
      'rows'     => [
          ['label' => 'Main photo', 'value' => $hero ?? '<span class="drawer-none">Not set</span>', 'type' => 'html'],
          ['label' => 'Status',     'value' => $deleted ? ['Deleted', 'grey'] : ($r['is_published'] ? ['Published — on the News page', 'green'] : ['Draft — not on the website', 'orange']), 'type' => 'pill'],
          ['label' => 'Summary',    'value' => $r['summary'], 'type' => 'long'],
          ['label' => 'Opening paragraph', 'value' => $r['intro'], 'type' => 'long'],
          ['label' => 'Pull quote', 'value' => $r['quote'], 'type' => 'long'],
          ['label' => 'Article',    'value' => implode("\n\n", NewsModel::bodyToArray($r['body'] ?? '')), 'type' => 'long'],
          ['label' => 'Gallery',    'value' => $photos ? $photos . ' photo' . ($photos === 1 ? '' : 's') : null],
          ['label' => 'Address key', 'value' => $r['slug'], 'type' => 'code'],
      ],
      'actions' => $actions,
  ]) ?>
<?php endforeach ?>

<p style="margin-top:.9rem;font-size:.75rem;color:var(--dim)">
  Articles appear on the News page newest first. Two articles sharing a date fall back to their sort order.
</p>
