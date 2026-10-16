<?php
/**
 * Product detail page (/machines/<slug>).
 * @var \Config\Site $site
 * @var array $p      row from Config\Site::$products
 * @var array $d      page content from App\Data\ProductDetails
 * @var array $prev
 * @var array $next
 */
$this->extend('layout');
$this->section('content');

$mail    = esc($site->email, 'attr');
$enquiry = base_url('contact') . '?machine=' . $p['slug'] . '#enquiry';
$bySlug  = array_column($all ?? $site->products, null, 'slug');
$manifest = timb_manifest();

/** responsive <img> attributes from the manifest sizes */
$sr = static function (string $key, string $sizes) use ($manifest): array {
    $f = $manifest[$key]['files'] ?? [];
    if (! isset($f['640'], $f['1600'])) {
        return ['loading' => 'lazy'];
    }

    return ['loading' => 'lazy', 'sizes' => $sizes,
        'srcset' => img_url($key, '640') . ' ' . $f['640'][0] . 'w, ' . img_url($key, '1600') . ' ' . $f['1600'][0] . 'w'];
};
$split = static fn (string $s, string $sep = ' · '): array => array_values(array_filter(array_map('trim', explode($sep, $s)), 'strlen'));

// which sections exist -> sticky sub-navigation
$nav = [];
if ($d['info'] || $d['callout']) { $nav['overview'] = 'Overview'; }
if ($d['features'] || $d['types']) { $nav['features'] = $d['types'] ? 'Types' : 'Features'; }
if ($d['mech'])      { $nav['how']     = 'How it works'; }
if ($d['apps'])      { $nav['apps']    = 'Applications'; }
if ($d['galleries']) { $nav['gallery'] = 'In the field'; }
?>

<section class="pg-hero dark" id="top" aria-labelledby="pg-title">
  <div class="pg-hero-fx" aria-hidden="true"><i></i><i></i><i></i></div>

  <div class="pg-hero-copy">
    <nav class="crumbs" aria-label="Breadcrumb" data-reveal>
      <a href="<?= base_url('/') ?>#machines">Machines</a><i aria-hidden="true">/</i><span aria-current="page"><?= esc($p['name']) ?></span>
    </nav>
    <p class="pg-kicker" data-reveal style="--d:80ms"><?= esc($p['series']) ?> <span>·</span> <?= esc($d['tag']) ?></p>
    <h1 id="pg-title" class="pg-title" data-split><?php if ($d['title'][0] !== ''): ?><?= esc($d['title'][0]) ?> <?php endif; ?><em><?= esc($d['title'][1]) ?></em><?php if ($d['title'][2] !== ''): ?> <?= esc($d['title'][2]) ?><?php endif; ?></h1>
    <p class="pg-lead" data-reveal style="--d:200ms"><?= esc($d['tagline']) ?></p>
    <div class="pg-actions" data-reveal style="--d:300ms">
      <a class="button" data-magnetic href="<?= $enquiry ?>"><span>Request a quote</span><i aria-hidden="true"></i></a>
    </div>
  </div>

  <?php if ($d['hero']): ?>
  <figure class="pg-hero-media" data-wipe>
    <div class="pg-media" data-parallax=".05"><?= img_tag($d['hero'], '1600', $p['name'] . ' – ' . $d['heroCaption'][0], ['fetchpriority' => 'high']) ?></div>
    <span class="pg-badge"><?= esc($p['series']) ?></span>
    <figcaption><strong><?= esc($d['heroCaption'][0]) ?></strong><?= esc($d['heroCaption'][1]) ?></figcaption>
  </figure>
  <?php else: ?>
  <figure class="pg-hero-media pg-hero-visual" data-wipe>
    <?= view('partials/visuals', ['kind' => $d['visual'] ?? 'gauge']) ?>
    <span class="pg-badge"><?= esc($p['series']) ?></span>
    <figcaption><strong><?= esc($d['heroCaption'][0]) ?></strong><?= esc($d['heroCaption'][1]) ?></figcaption>
  </figure>
  <?php endif; ?>

  <ul class="pg-specs" aria-label="<?= esc($d['specLabel'], 'attr') ?>">
    <?php foreach ($d['specs'] as $i => [$label, $value]): ?>
      <li data-reveal style="--d:<?= $i * 80 ?>ms">
        <span><?= esc($label) ?></span>
        <strong<?= preg_match('/^(Up to )?[^A-Za-z]*\d/', $value) ? ' data-count' : '' ?>><?= esc($value) ?></strong>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<?php if ($nav): ?>
<nav class="pg-subnav" data-subnav aria-label="On this page">
  <div class="pg-subnav-in">
    <?php foreach ($nav as $id => $label): ?><a href="#<?= $id ?>"><?= esc($label) ?></a><?php endforeach; ?>
    <a class="pg-subnav-cta" href="<?= $enquiry ?>">Enquire <i aria-hidden="true"></i></a>
  </div>
</nav>
<?php endif; ?>

<?php if ($d['info'] || $d['callout']): ?>
<section class="pg-overview sec" id="overview" aria-labelledby="ov-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Overview</p><h2 id="ov-h" class="h2" data-split>At a glance.</h2></div>
    <p data-reveal>What the <?= esc($p['name']) ?> is built to process, and how it is configured.</p>
  </div>

  <div class="pg-grid">
    <?php foreach ($d['info'] as $bi => [$label, $lines]): ?>
      <?php $steps = false; foreach ($lines as $l) { if (str_contains($l, '→')) { $steps = true; } } ?>
      <article class="pg-card" data-reveal style="--d:<?= $bi * 100 ?>ms">
        <h3><?= esc($label) ?></h3>
        <?php if ($steps): ?>
          <ol class="pg-steps">
            <?php foreach ($split(implode(' → ', $lines), ' → ') as $si => $step): ?>
              <li style="--i:<?= $si ?>"><b><?= sprintf('%02d', $si + 1) ?></b><span><?= esc($step) ?></span></li>
            <?php endforeach; ?>
          </ol>
        <?php else: ?>
          <?php $ci = 0; foreach ($lines as $line): ?>
            <ul class="pg-tags pg-tags-light">
              <?php foreach ($split($line) as $chip): ?><li style="--i:<?= $ci++ ?>"><?= esc($chip) ?></li><?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>

    <?php if ($d['callout']): ?>
      <?php [$cLabel, $cValue] = $d['callout']; [$cMain, $cNote] = array_pad(explode(' — ', $cValue, 2), 2, ''); ?>
      <article class="pg-callout" data-reveal style="--d:120ms">
        <h3><?= esc($cLabel) ?></h3>
        <ul class="pg-callout-list">
          <?php foreach ($split($cMain) as $ci => $item): ?>
            <?php [$k, $v] = str_contains($item, ': ') ? explode(': ', $item, 2) : ['', $item]; ?>
            <li style="--i:<?= $ci ?>"><strong><?= esc($v) ?></strong><?php if ($k !== ''): ?><span><?= esc($k) ?></span><?php endif; ?></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($cNote !== ''): ?><p><?= esc($cNote) ?></p><?php endif; ?>
      </article>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($d['features'] || $d['types']): ?>
<section class="pg-features sec" id="features" aria-labelledby="ft-h">
  <div class="sec-head">
    <div>
      <p class="eyebrow" data-reveal><?= esc($d['types'] ? $d['types']['title'] : $d['features']['title']) ?></p>
      <h2 id="ft-h" class="h2" data-split><?= $d['types'] ? esc($d['types']['heading'] ?? 'Choose the right swaging type.') : esc($p['name']) . ', in detail.' ?></h2>
    </div>
    <p data-reveal><?= $d['types'] ? esc($d['types']['sub'] ?? 'Three machine types, each built for a different class of component.') : 'The design decisions that matter on the shop floor.' ?></p>
  </div>

  <ul class="pg-feature-grid">
    <?php if ($d['types']): foreach ($d['types']['items'] as $i => [$tt, $td]): ?>
      <li class="pg-feature" data-spot data-reveal style="--d:<?= $i * 90 ?>ms">
        <b class="num"><?= sprintf('%02d', $i + 1) ?></b>
        <h3><?= esc($tt) ?></h3>
        <p><?= esc($td) ?></p>
      </li>
    <?php endforeach; else: foreach ($d['features']['items'] as $i => $html): ?>
      <li class="pg-feature" data-spot data-reveal style="--d:<?= ($i % 3) * 90 ?>ms">
        <b class="num"><?= sprintf('%02d', $i + 1) ?></b>
        <p><?= $html /* trusted: generated from the spec sheets, only <strong> allowed */ ?></p>
      </li>
    <?php endforeach; endif; ?>
  </ul>
</section>
<?php endif; ?>

<?php if ($d['mech']): ?>
<section class="pg-how sec" id="how" aria-labelledby="how-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>How it works</p><h2 id="how-h" class="h2" data-split>Inside the machine.</h2></div>
    <p data-reveal>Scroll through the process, stage by stage.</p>
  </div>

  <?php foreach ($d['mech'] as $mi => $mech): ?>
    <div class="pg-mech">
      <header data-reveal>
        <h3><?= esc($mech['title']) ?></h3>
        <?php if ($mech['sub'] !== ''): ?><p><?= esc($mech['sub']) ?></p><?php endif; ?>
      </header>
      <div class="flow pg-flow" data-flow>
        <div class="tube" aria-hidden="true"><i class="tube-fill"></i></div>
        <ol class="flow-list">
          <?php foreach ($mech['groups'] as $gi => $g): ?>
            <li class="flow-stage pg-stage">
              <span class="flow-node" aria-hidden="true"><?= esc($g['num'] !== '' ? $g['num'] : sprintf('%02d', $gi + 1)) ?></span>
              <div class="pg-stage-body<?= empty($g['img']) ? ' no-fig' : '' ?>">
                <?php if (! empty($g['img'])): ?>
                <figure class="pg-fig" data-wipe>
                  <div class="pg-media" data-parallax=".04"><?= img_tag($g['img'], '1600', $g['title'], $sr($g['img'], '(min-width: 960px) 38vw, 90vw')) ?></div>
                </figure>
                <?php endif; ?>
                <div class="pg-stage-text">
                  <h4><?= esc($g['title']) ?></h4>
                  <ul class="pg-bullets">
                    <?php foreach ($g['items'] as $ii => $it): ?><li style="--i:<?= $ii ?>"><?= $it /* trusted markup from ProductDetails */ ?></li><?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if ($d['apps']): ?>
<section class="pg-apps sec dark" id="apps" aria-labelledby="ap-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Applications</p><h2 id="ap-h" class="h2" data-split>Where it earns its keep.</h2></div>
    <p data-reveal>Typical parts, materials and industries served by the <?= esc($p['name']) ?>.</p>
  </div>
  <?php foreach ($d['apps'] as $a): ?>
    <div class="pg-apps-block">
      <h3 data-reveal><?= esc($a['title']) ?></h3>
      <ul class="pg-tags" data-reveal>
        <?php foreach ($a['chips'] as $ci => $chip): ?><li style="--i:<?= $ci ?>"><?= esc($chip) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if ($d['galleries']): ?>
<section class="pg-gallery sec" id="gallery" aria-label="<?= esc($p['name'], 'attr') ?> in the field">
  <div class="pg-shots pg-shots-<?= count($d['galleries']) ?>">
    <?php foreach ($d['galleries'] as $gi => $g): ?>
      <figure class="pg-shot">
        <div class="pg-shot-img" data-wipe style="--d:<?= $gi * 120 ?>ms"><div class="pg-media" data-parallax=".05"><?= img_tag($g['img'], '1600', $g['title'], $sr($g['img'], count($d['galleries']) > 1 ? '(min-width: 960px) 46vw, 92vw' : '92vw')) ?></div></div>
        <figcaption data-reveal><strong><?= esc($g['title']) ?></strong><span><?= esc($g['text']) ?></span></figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($d['highlight']): $h = $d['highlight']; ?>
<section class="pg-highlight sec" aria-labelledby="hl-h">
  <div class="pg-hl-grid">
    <div>
      <p class="eyebrow" data-reveal>Why TIMB</p>
      <h2 id="hl-h" class="h2" data-split><?= esc($h['label']) ?></h2>
      <?php if (($h['text'] ?? '') !== ''): ?><p class="pg-hl-text" data-reveal><?= esc($h['text']) ?></p><?php endif; ?>
    </div>
    <div>
      <?php if (! empty($h['chips'])): ?>
        <ul class="pg-tags pg-tags-light" data-reveal>
          <?php foreach ($h['chips'] as $ci => $chip): ?><li style="--i:<?= $ci ?>"><?= esc($chip) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if (! empty($h['pairs'])): ?>
        <p class="pg-pairs-label" data-reveal>Pairs with</p>
        <ul class="pg-pairs">
          <?php foreach ($h['pairs'] as $pi => $ps): if (! isset($bySlug[$ps])) { continue; } $pp = $bySlug[$ps]; ?>
            <li data-reveal style="--d:<?= $pi * 100 ?>ms">
              <a href="<?= base_url('machines/' . $pp['slug']) ?>">
                <?php if ($pp['image']): ?><?= img_tag($pp['image'], '640', '', ['loading' => 'lazy', 'role' => 'presentation']) ?><?php else: ?><i class="pg-thumb-ph" aria-hidden="true"></i><?php endif; ?>
                <span><strong><?= esc($pp['name']) ?></strong><small><?= esc($pp['metric']) ?></small></span>
                <i aria-hidden="true"></i>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="contact sec" aria-labelledby="pg-cta-h">
  <h2 id="pg-cta-h" class="h2" data-split>Start your <?= esc($p['name']) ?> project.</h2>
  <div class="contact-side">
    <?php $cta = rtrim($d['cta'], ': '); ?>
    <p data-reveal><?= esc($cta) ?><?= preg_match('/[.?!]$/', $cta) ? '' : '.' ?></p>
    <a class="button button-light" data-reveal data-magnetic href="<?= $enquiry ?>"><span>Request a quote</span><i aria-hidden="true"></i></a>
  </div>
</section>

<nav class="pg-pager" aria-label="More machines">
  <?php foreach ([['prev', $prev, 'Previous machine'], ['next', $next, 'Next machine']] as [$dir, $m, $lbl]): ?>
    <a class="pg-pager-link is-<?= $dir ?><?= $m['image'] ? '' : ' no-img' ?>" href="<?= base_url('machines/' . $m['slug']) ?>">
      <?php if ($m['image']): ?><?= img_tag($m['image'], '640', '', ['loading' => 'lazy', 'role' => 'presentation']) ?><?php endif; ?>
      <span class="pg-pager-label"><?= $lbl ?></span>
      <strong><?= esc($m['name']) ?></strong>
      <small><?= esc($m['series']) ?> · <?= esc($m['metric']) ?></small>
      <i aria-hidden="true"></i>
    </a>
  <?php endforeach; ?>
</nav>
<?php $this->endSection(); ?>
