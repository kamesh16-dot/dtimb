<div class="page-header"><h1>Pages</h1></div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Page</th>
          <th>Browser title</th>
          <th>Description</th>
          <th>Content</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--dim);padding:2rem">No pages found. Run <code>php spark migrate</code>.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
            $blocks = json_decode((string) ($r['body_blocks'] ?? ''), true);
            $count  = is_array($blocks) ? count($blocks) : 0;
            $desc   = trim((string) ($r['seo_description'] ?? ''));
            ?>
            <tr>
              <td>
                <?= esc($r['title']) ?>
                <div style="font-size:.72rem;color:var(--dim)">/<?= esc($r['key'] === 'home' ? '' : $r['key']) ?></div>
              </td>
              <td style="font-size:.78rem"><?= esc($r['seo_title']) ?></td>
              <td style="font-size:.75rem;color:var(--dim);max-width:26rem">
                <?= $desc !== '' ? esc(mb_strimwidth($desc, 0, 90, '…')) : '<span class="pill pill-orange">Not set</span>' ?>
              </td>
              <td style="font-size:.75rem;color:var(--dim)">
                <?= $count > 0 ? esc($count) . ' block' . ($count === 1 ? '' : 's') : '—' ?>
              </td>
              <td>
                <div class="actions">
                  <a href="<?= base_url($r['key'] === 'home' ? '' : $r['key']) ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">View</a>
                  <a href="/admin/pages/edit/<?= esc($r['key'], 'url') ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
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
  Each page is a fixed part of the site, so pages cannot be added or removed here.
  Lists such as projects, clients and industries are edited in the Lists Hub.
</p>
