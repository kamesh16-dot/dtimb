<?php
/**
 * @var string $tab  @var string $show  @var array $rows  @var array $summary
 * @var array $settings  @var array $fields  @var bool $canEdit  @var array $limits
 */
$tabs = [
    'pages'    => 'Pages in Google',
    'settings' => 'Settings',
    'sitemap'  => 'What Google is told',
];
$pct = $summary['total'] > 0 ? (int) round($summary['clean'] / $summary['total'] * 100) : 100;
?>
<div class="page-header">
  <div>
    <h1>SEO</h1>
    <p class="page-sub">How your website shows up when people search on Google — and how to make it show up better.</p>
  </div>
</div>

<?php if ($indexingOff): ?>
  <div class="seo-alert seo-alert--red">
    <strong>Search engines are told to stay away from this website.</strong>
    It will drop out of Google results. Fine for a test copy — for the live site, turn
    “Let Google and other search engines list this website” back on in
    <a href="?tab=settings">Settings</a>.
  </div>
<?php endif ?>
<?php if ($staticRobots): ?>
  <div class="seo-alert">
    <strong>An old robots.txt file is overriding these settings.</strong>
    Ask your developer to delete <code>public/robots.txt</code> on the server, so the settings here take effect.
  </div>
<?php endif ?>

<nav class="tabs" aria-label="SEO sections">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="?tab=<?= $key ?>" class="tab<?= $tab === $key ? ' is-on' : '' ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>>
      <?= esc($label) ?>
      <?php if ($key === 'pages' && $summary['issues'] > 0): ?><span class="tab-badge"><?= (int) $summary['issues'] ?></span><?php endif ?>
    </a>
  <?php endforeach ?>
</nav>

<?php if ($tab === 'pages'): ?>
  <!-- ── Pages in Google ─────────────────────────────────────────────── -->
  <section class="card card-pad seo-health">
    <div class="seo-health-text">
      <div class="seo-health-big"><?= (int) $summary['clean'] ?> <span>of <?= (int) $summary['total'] ?> pages look good in Google</span></div>
      <p>
        <?php if ($summary['issues'] === 0): ?>
          Every page has a clear title and description. Nothing to do.
        <?php else: ?>
          <?= (int) $summary['issues'] ?> <?= $summary['issues'] === 1 ? 'page needs' : 'pages need' ?> a small fix.
          A good title and description help people choose your page in the search results.
        <?php endif ?>
      </p>
    </div>
    <div class="seo-health-bar" role="img" aria-label="<?= $pct ?>% of pages look good">
      <span style="width:<?= $pct ?>%"></span>
    </div>
  </section>

  <?php if ($photos['missing'] > 0): ?>
    <div class="seo-alert" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
      <span style="flex:1;min-width:16rem">
        <strong><?= (int) $photos['missing'] ?> of <?= (int) $photos['total'] ?> photos have no description.</strong>
        A one-sentence description helps your photos appear in Google Images, and is read aloud to blind visitors.
      </span>
      <a href="<?= admin_url('media?show=nodesc') ?>" class="btn btn-secondary">Add descriptions</a>
    </div>
  <?php endif ?>

  <div class="filter-chips" role="group" aria-label="Show">
    <?php foreach (['issues' => ['Needs work', $summary['issues']], 'good' => ['Looks good', $summary['clean']], 'all' => ['All pages', $summary['total']]] as $key => [$label, $n]): ?>
      <a href="?tab=pages&amp;show=<?= $key ?>" class="btn <?= $show === $key ? 'btn-primary' : 'btn-secondary' ?>"<?= $show === $key ? ' aria-current="true"' : '' ?>>
        <?= esc($label) ?> (<?= (int) $n ?>)
      </a>
    <?php endforeach ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Page</th><th>Type</th><th>How it looks in Google</th><th></th><th><span class="sr-only">Details</span></th></tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:2rem">
              <?= $show === 'issues' ? 'Nothing needs fixing. Every page looks good in Google.' : 'Nothing to show.' ?>
            </td></tr>
          <?php else: foreach ($rows as $i => $r): ?>
            <tr data-drawer="seo-<?= $pager->offset + $i ?>" tabindex="0" aria-expanded="false" aria-label="<?= esc($r['label'], 'attr') ?>: see how it looks in Google">
              <td style="white-space:nowrap">
                <?= esc($r['label']) ?>
                <div style="font-size:.72rem;color:var(--dim)"><?= esc($r['url']) ?></div>
              </td>
              <td><span class="pill pill-grey"><?= esc($r['kind']) ?></span></td>
              <td style="font-size:.8rem">
                <?php if ($r['problems'] === []): ?>
                  <span class="pill pill-green">Looks good</span>
                <?php else: ?>
                  <span class="pill pill-orange">Needs work</span>
                  <span class="cell-clip" style="color:var(--muted);margin-top:.25rem"><?= esc($r['problems'][0]['text']) ?><?= count($r['problems']) > 1 ? ' (+' . (count($r['problems']) - 1) . ' more)' : '' ?></span>
                <?php endif ?>
              </td>
              <td>
                <?php $fixable = array_filter($r['problems'], static fn ($p) => empty($p['locked'])) !== []; ?>
                <?php if ($r['editUrl'] !== null): ?>
                  <a href="<?= esc(admin_url($r['editUrl']), 'attr') ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem"><?= $fixable ? 'Fix' : 'Edit' ?></a>
                <?php endif ?>
              </td>
              <td class="row-more" aria-hidden="true">›</td>
            </tr>
          <?php endforeach; endif ?>
        </tbody>
      </table>
    </div>
  </div>
  <?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'pages', 'one' => 'page']) ?>
  <?php if (! empty($rows)): ?><p class="table-hint">Click a page to see exactly how it appears in Google, and what to change.</p><?php endif ?>

  <?php foreach ($rows as $i => $r): ?>
    <?php
    $preview = view('admin/seo/_google_preview', [
        'title' => $r['title'], 'description' => $r['description'], 'domain' => $domain,
        'path' => $r['url'], 'siteName' => $siteName,
        'titleMax' => $limits['titleMax'], 'descriptionMax' => $limits['descriptionMax'],
    ]);

    if ($r['problems'] === []) {
        $fix = '<span class="pill pill-green">Nothing — this page looks good</span>';
    } else {
        $fix = '<ul class="seo-fixes">';
        foreach ($r['problems'] as $p) {
            $fix .= '<li' . (! empty($p['locked']) ? ' class="is-locked"' : '') . '><strong>' . esc($p['text']) . '</strong>'
                  . '<span>' . (! empty($p['locked']) ? '' : 'How to fix: ') . esc($p['tip']) . '</span></li>';
        }
        $fix .= '</ul>';
    }

    $details = [
        ['label' => 'How it looks in Google', 'value' => $preview, 'type' => 'html'],
        ['label' => 'What to change', 'value' => $fix, 'type' => 'html'],
        ['label' => 'Title', 'value' => ($r['title'] !== '' ? esc($r['title']) : '<span class="drawer-none">Not set</span>')
            . view('admin/seo/_meter', ['length' => mb_strlen(trim($r['title'])), 'min' => $limits['titleMin'], 'max' => $limits['titleMax']]), 'type' => 'html'],
        ['label' => 'Description', 'value' => ($r['description'] !== '' ? esc($r['description']) : '<span class="drawer-none">Not set</span>')
            . view('admin/seo/_meter', ['length' => mb_strlen(trim($r['description'])), 'min' => $limits['descriptionMin'], 'max' => $limits['descriptionMax']]), 'type' => 'html'],
    ];
    if ($r['note'] !== null) {
        $details[] = ['label' => 'Good to know', 'value' => $r['note']];
    }
    ?>
    <?= view('admin/partials/drawer', [
        'id'       => 'seo-' . ($pager->offset + $i),
        'title'    => $r['label'],
        'subtitle' => $r['kind'] . ' · ' . $r['url'],
        'rows'     => $details,
        'actions'  => $r['editUrl'] !== null
            ? '<a class="btn btn-primary" href="' . esc(admin_url($r['editUrl']), 'attr') . '">'
              . (array_filter($r['problems'], static fn ($p) => empty($p['locked'])) !== [] ? 'Fix this page' : 'Edit') . '</a>'
            : null,
    ]) ?>
  <?php endforeach ?>

<?php elseif ($tab === 'settings'): ?>
  <!-- ── Settings ────────────────────────────────────────────────────── -->
  <?php
  $field = static function (string $key) use ($fields, $settings, $canEdit, $limits): string {
      $f  = $fields[$key];
      $id = 'seo-' . $key;
      $v  = (string) ($settings[$key] ?? '');
      $dis = $canEdit ? '' : ' disabled';

      if ($f['type'] === 'bool') {
          return '<div class="field"><label class="switch-row" for="' . $id . '">'
              . '<input type="checkbox" id="' . $id . '" name="' . esc($key, 'attr') . '" value="1"' . ($v === '1' ? ' checked' : '') . $dis . '>'
              . '<span><strong>' . esc($f['label']) . '</strong><span class="field-hint">' . esc($f['hint']) . '</span></span>'
              . '</label></div>';
      }

      $counter = $key === 'default_description'
          ? ' data-counter data-min="' . $limits['descriptionMin'] . '" data-max="' . $limits['descriptionMax'] . '"'
          : '';
      $input = $f['type'] === 'textarea'
          ? '<textarea id="' . $id . '" name="' . esc($key, 'attr') . '" rows="3"' . $counter . $dis . '>' . esc($v) . '</textarea>'
          : '<input type="text" id="' . $id . '" name="' . esc($key, 'attr') . '" value="' . esc($v, 'attr') . '"'
            . ($key === 'share_image' ? ' list="seo-images" autocomplete="off"' . ($dis === '' ? ' data-media-picker' : '') : '') . $dis . '>';

      return '<div class="field"><label for="' . $id . '">' . esc($f['label']) . '</label>' . $input
          . '<p class="field-hint">' . esc($f['hint']) . '</p></div>';
  };

  $shareKey = (string) ($settings['share_image'] ?? '');
  $shareOk  = $shareKey !== '' && isset(timb_manifest()[$shareKey]);
  $defDesc  = (string) ($settings['default_description'] ?? '');
  ?>

  <?php if (! $canEdit): ?>
    <div class="seo-alert">Only admins can change these settings. You can see them here.</div>
  <?php endif ?>

  <form method="POST" action="<?= admin_url('seo/save') ?>" class="seo-settings">
    <?= csrf_field() ?>

    <section class="card card-pad">
      <h2 class="seo-h2">Search engines</h2>
      <p class="seo-lead">Whether Google, Bing and others may show this website in their results.</p>
      <?= $field('allow_indexing') ?>
    </section>

    <section class="card card-pad">
      <h2 class="seo-h2">When someone shares a link</h2>
      <p class="seo-lead">What appears when a link to the site is pasted into LinkedIn, WhatsApp or Facebook.</p>
      <div class="seo-share">
        <div>
          <?= $field('social_tags') ?>
          <?= $field('share_image') ?>
          <datalist id="seo-images">
            <?php foreach ($images as $k): ?><option value="<?= esc($k, 'attr') ?>"><?php endforeach ?>
          </datalist>
          <?php if ($shareKey !== '' && ! $shareOk): ?>
            <p class="field-hint" style="color:var(--heat)">“<?= esc($shareKey) ?>” is not in the Media Library, so no picture is shown.</p>
          <?php endif ?>
        </div>
        <figure class="share-card" aria-label="Example of a shared link">
          <?php if ($shareOk): ?>
            <img src="<?= esc(img_url($shareKey, '640'), 'attr') ?>" alt="">
          <?php else: ?>
            <div class="share-card-noimg">No picture</div>
          <?php endif ?>
          <figcaption>
            <span class="share-card-domain"><?= esc(strtoupper($domain)) ?></span>
            <strong><?= esc($shareTitle) ?></strong>
            <span><?= esc(mb_strimwidth($defDesc, 0, 110, '…')) ?></span>
          </figcaption>
        </figure>
      </div>
    </section>

    <section class="card card-pad">
      <h2 class="seo-h2">Default description</h2>
      <p class="seo-lead">A back-up sentence for any page that has no description of its own.</p>
      <?= $field('default_description') ?>
    </section>

    <details class="card card-pad seo-advanced">
      <summary>
        <span class="seo-h2">Advanced</span>
        <span class="seo-lead">Technical settings. Leave these as they are unless your developer asks.</span>
      </summary>
      <?= $field('canonical_base') ?>
      <?= $field('canonical_tags') ?>
      <?= $field('robots_extra') ?>
    </details>

    <?php if ($canEdit): ?>
      <div class="btn-row"><button type="submit" class="btn btn-primary">Save settings</button></div>
    <?php endif ?>
  </form>

<?php else: ?>
  <!-- ── What Google is told ─────────────────────────────────────────── -->
  <section class="card card-pad">
    <h2 class="seo-h2">Google is told about these <?= count($sitemap) ?> pages</h2>
    <p class="seo-lead">
      This list (called a <em>sitemap</em>) helps search engines find every page and notice when one changes.
      It updates by itself whenever you publish, hide or edit something — there is nothing to do here.
    </p>
    <div class="table-wrap" style="margin-top:.9rem">
      <table class="data-table">
        <thead><tr><th>Page</th><th>Last changed</th></tr></thead>
        <tbody>
          <?php foreach ($sitemap as $u): ?>
            <tr>
              <td style="font-size:.82rem"><?= esc($u['path']) ?><div style="font-size:.7rem;color:var(--dim)"><?= esc($u['url']) ?></div></td>
              <td style="font-size:.8rem;color:var(--muted);white-space:nowrap"><?= $u['lastmod'] !== null ? esc(date('d M Y', strtotime($u['lastmod']))) : '—' ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
    <p class="field-hint" style="margin-top:.9rem">
      For your developer: the files search engines read are
      <a href="<?= esc($sitemapUrl, 'attr') ?>" target="_blank" rel="noopener">sitemap.xml</a> and
      <a href="<?= esc($robotsUrl, 'attr') ?>" target="_blank" rel="noopener">robots.txt</a>.
    </p>
  </section>
<?php endif ?>
