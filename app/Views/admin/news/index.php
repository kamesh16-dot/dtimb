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
          <th>Slug</th>
          <th>Status</th>
          <th>Photos</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--dim);padding:2rem">No articles yet. <a href="/admin/news/create" style="color:var(--red)">Write one →</a></td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
          <tr<?= $r['deleted_at'] ? ' style="opacity:.45"' : '' ?>>
            <td>
              <?= esc($r['title']) ?>
              <?php if ($r['deleted_at']): ?><span class="pill pill-grey">Deleted</span><?php endif ?>
              <?php if (! empty($r['summary'])): ?>
                <div style="font-size:.72rem;color:var(--dim);max-width:34rem"><?= esc(mb_strimwidth($r['summary'], 0, 110, '…')) ?></div>
              <?php endif ?>
            </td>
            <td style="font-size:.8rem;white-space:nowrap"><?= esc(date('d M Y', strtotime((string) $r['published_on']))) ?></td>
            <td><?= $r['tag'] ? '<span class="pill pill-grey">' . esc($r['tag']) . '</span>' : '<span style="color:var(--dim)">—</span>' ?></td>
            <td style="font-size:.78rem;color:var(--dim)"><?= esc($r['slug']) ?></td>
            <td>
              <?= $r['is_published']
                    ? '<span class="pill pill-green">Published</span>'
                    : '<span class="pill pill-orange">Draft</span>' ?>
            </td>
            <td style="font-size:.8rem;color:var(--dim)"><?= (int) ($galleryCounts[$r['id']] ?? 0) ?></td>
            <td>
              <div class="actions">
                <?php if (! $r['deleted_at']): ?>
                  <a href="<?= base_url('news') ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">View</a>
                  <a href="/admin/news/edit/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
                  <form method="POST" action="/admin/news/delete/<?= $r['id'] ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete '<?= esc($r['title'], 'attr') ?>'? It will stop appearing on the website.">Delete</button>
                  </form>
                <?php else: ?>
                  <form method="POST" action="/admin/news/restore/<?= $r['id'] ?>" style="display:inline">
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
  Articles appear on the News page newest first. Two articles sharing a date fall back to their sort order.
</p>
