<div class="page-header"><h1>SEO</h1></div>

<?php if ($staticRobots): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">A robots.txt file is sitting in the public folder.</strong>
    <p style="font-size:.8rem;color:var(--dim);margin:.4rem 0 0">
      The web server will serve that file instead of the one this screen generates, so the settings
      below will not take effect. Delete <code>public/robots.txt</code> to use the generated version.
    </p>
  </div>
<?php endif ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.875rem;margin-bottom:1rem">
  <div class="card card-pad">
    <div style="font-size:1.6rem;font-weight:700;color:var(--text)"><?= (int) $summary['total'] ?></div>
    <div style="font-size:.75rem;color:var(--dim)">Addresses checked</div>
  </div>
  <div class="card card-pad">
    <div style="font-size:1.6rem;font-weight:700;color:var(--text)"><?= (int) $summary['clean'] ?></div>
    <div style="font-size:.75rem;color:var(--dim)">Look fine</div>
  </div>
  <div class="card card-pad">
    <div style="font-size:1.6rem;font-weight:700;color:<?= $summary['issues'] > 0 ? 'var(--red)' : 'var(--text)' ?>"><?= (int) $summary['issues'] ?></div>
    <div style="font-size:.75rem;color:var(--dim)">Worth a look</div>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Address</th><th>Browser title</th><th>Search description</th><th>Notes</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--dim);padding:2rem">Nothing to check.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td style="white-space:nowrap">
              <?= esc($r['label']) ?>
              <div style="font-size:.72rem;color:var(--dim)"><?= esc($r['url']) ?></div>
            </td>
            <td style="font-size:.78rem;max-width:18rem">
              <?= $r['title'] !== '' ? esc($r['title']) : '<span style="color:var(--dim)">—</span>' ?>
              <div style="font-size:.68rem;color:<?= mb_strlen($r['title']) > $limits['titleMax'] ? 'var(--red)' : 'var(--dim)' ?>"><?= mb_strlen($r['title']) ?> chars</div>
            </td>
            <td style="font-size:.75rem;color:var(--dim);max-width:22rem">
              <?= $r['description'] !== '' ? esc(mb_strimwidth($r['description'], 0, 110, '…')) : '—' ?>
              <div style="font-size:.68rem;color:<?= mb_strlen($r['description']) > $limits['descriptionMax'] ? 'var(--red)' : 'var(--dim)' ?>"><?= mb_strlen($r['description']) ?> chars</div>
            </td>
            <td style="font-size:.72rem;max-width:20rem">
              <?php if ($r['issues'] === []): ?>
                <span class="pill pill-green">OK</span>
              <?php else: ?>
                <ul style="margin:0;padding-left:1rem;color:var(--dim)">
                  <?php foreach ($r['issues'] as $issue): ?><li><?= esc($issue) ?></li><?php endforeach ?>
                </ul>
              <?php endif ?>
            </td>
            <td>
              <?php if ($r['editUrl'] !== null): ?>
                <a href="<?= esc($r['editUrl']) ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
              <?php endif ?>
            </td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>

<p style="margin:.9rem 0 1.25rem;font-size:.75rem;color:var(--dim)">
  Titles and descriptions are edited on each page or machine. The lengths above are the usual
  cut-off points in search results, not hard limits.
</p>

<div class="card card-pad" style="margin-bottom:1.25rem">
  <div style="display:flex;justify-content:space-between;align-items:baseline;gap:1rem;flex-wrap:wrap">
    <h2 style="font-size:.95rem;margin:0">What search engines are told</h2>
    <div class="actions">
      <a href="<?= esc($sitemapUrl) ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Raw sitemap.xml</a>
      <a href="<?= esc($robotsUrl) ?>" target="_blank" rel="noopener" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Raw robots.txt</a>
    </div>
  </div>
  <p style="font-size:.78rem;color:var(--dim);margin:.4rem 0 .9rem">
    These <?= count($sitemap) ?> addresses are offered to Google and Bing, each with the date it last changed.
    The list is generated, so it follows whatever you publish. The raw files are written for search engines
    rather than for reading — use them only to confirm what is being served.
  </p>

  <div class="table-wrap" style="max-height:18rem;overflow:auto">
    <table class="data-table">
      <thead><tr><th>Address</th><th>Last changed</th></tr></thead>
      <tbody>
        <?php foreach ($sitemap as $u): ?>
          <tr>
            <td style="font-size:.78rem"><?= esc($u['path']) ?><div style="font-size:.68rem;color:var(--dim)"><?= esc($u['url']) ?></div></td>
            <td style="font-size:.78rem;color:var(--dim);white-space:nowrap"><?= $u['lastmod'] !== null ? esc(date('d M Y', strtotime($u['lastmod']))) : '—' ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card form-card card-pad">
  <h2 style="font-size:.95rem;margin:0 0 .75rem">Site-wide settings</h2>
  <form method="POST" action="/admin/seo/save">
    <?= csrf_field() ?>
    <?php foreach ($fields as $key => $f): ?>
      <div class="field">
        <?php if ($f['type'] === 'bool'): ?>
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
            <input type="checkbox" name="<?= esc($key, 'attr') ?>" value="1" <?= ($settings[$key] ?? '') === '1' ? 'checked' : '' ?> style="width:auto">
            <?= esc($f['label']) ?>
          </label>
        <?php elseif ($f['type'] === 'textarea'): ?>
          <label><?= esc($f['label']) ?></label>
          <textarea name="<?= esc($key, 'attr') ?>" rows="3"><?= esc($settings[$key] ?? '') ?></textarea>
        <?php else: ?>
          <label><?= esc($f['label']) ?></label>
          <input type="text" name="<?= esc($key, 'attr') ?>" value="<?= esc($settings[$key] ?? '') ?>">
        <?php endif ?>
        <p class="field-hint"><?= esc($f['hint']) ?></p>
      </div>
    <?php endforeach ?>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save settings</button>
    </div>
  </form>
</div>
