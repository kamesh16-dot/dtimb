<div class="page-header"><h1>SEO</h1></div>
<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Page slug</th><th>Meta title</th><th>Meta description</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--dim);padding:2rem">No SEO data yet. Edit pages from the <a href="/admin/pages" style="color:var(--red)">Pages</a> section.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td style="font-size:.78rem"><?= esc($r['slug'] ?? '—') ?></td>
            <td style="font-size:.78rem"><?= esc($r['meta_title'] ?? '—') ?></td>
            <td style="font-size:.72rem;color:var(--muted);max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($r['meta_description'] ?? '—') ?></td>
            <td><a href="/admin/pages/edit/<?= esc($r['slug'] ?? '') ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a></td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
