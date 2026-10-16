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
      <thead><tr><th>Title</th><th>Published</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--dim);padding:2rem">No articles yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['title']) ?></td>
            <td style="font-size:.78rem;color:var(--dim)"><?= esc(substr($r['published_at'] ?? '', 0, 10)) ?></td>
            <td><?= ($r['is_published'] ?? 0) ? '<span class="pill pill-green">Published</span>' : '<span class="pill pill-grey">Draft</span>' ?></td>
            <td>
              <div class="actions">
                <a href="/admin/news/edit/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
                <form method="POST" action="/admin/news/delete/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?><button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete this article?">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
