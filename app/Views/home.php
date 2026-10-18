<?php

/** @var \Config\Site $site */
/** @var list<array{key:string,alt:string}> $gallery */
$this->extend('layout');
$this->section('content');
$mail  = esc($site->email, 'attr');
$bySpec = array_column($site->products, 'slug', 'spec'); // spec-sheet key -> product page slug
$total = count($site->heroSlides);
$half  = (int) ceil(count($gallery) / 2);
$rows  = [array_slice($gallery, 0, $half), array_slice($gallery, $half)];
?>
<section class="hero" id="top" aria-roledescription="carousel" aria-label="TIMB machinery highlights" data-carousel>
  <h1 class="sr-only">TIMB – Precision Industrial Machinery</h1>
  <div class="hero-carousel">
    <?php foreach ($site->heroSlides as $i => $slide): ?>
      <article class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= $total ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
        <?= img_tag($slide['image'], '1600', '', ['class' => 'hero-img', 'role' => 'presentation'] + ($i === 0 ? ['fetchpriority' => 'high'] : ['loading' => 'lazy'])) ?>
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <p class="kicker"><?= esc($slide['label']) ?></p>
          <h2 class="hero-title" data-split><?= esc(implode(' ', $slide['title'])) ?></h2>
          <p class="hero-copy"><?= esc($slide['copy']) ?></p>
          <div class="hero-actions">
            <a class="button" href="#machines"><span>Explore machines</span><i aria-hidden="true"></i></a>
            <a class="button-link" href="<?= base_url('machines/' . ($bySpec[$slide['spec']] ?? '')) ?>">View details</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <p class="hero-count" aria-hidden="true"><b data-count-now>01</b><span>/ <?= sprintf('%02d', $total) ?></span></p>

  <div class="hero-rail">
    <ul class="rail-list">
      <?php foreach ($site->heroSlides as $i => $slide): ?>
        <li>
          <button type="button" class="rail-item<?= $i === 0 ? ' is-active' : '' ?>" data-rail="<?= $i ?>" <?= $i === 0 ? ' aria-current="true"' : '' ?> aria-label="Show <?= esc($slide['label'], 'attr') ?>">
            <?= img_tag($slide['image'], '640', '', ['class' => 'rail-thumb', 'loading' => 'lazy', 'role' => 'presentation']) ?>
            <span class="rail-text">
              <strong><?= esc($slide['name']) ?></strong>
              <small><?= esc($slide['metric']) ?></small>
            </span>
            <span class="rail-bar" aria-hidden="true"></span>
          </button>
        </li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="rail-toggle" data-carousel-toggle aria-label="Pause slideshow" aria-pressed="false">❚❚</button>
  </div>
</section>

<div class="ticker" aria-label="Machine ranges">
  <div class="ticker-track">
    <?php foreach ($site->products as $p): ?>
      <a class="ticker-item" href="#machines"><b><?= esc($p['metric']) ?></b><span><?= esc($p['name']) ?></span></a>
    <?php endforeach; ?>
  </div>
</div>

<section class="intro sec" id="about" aria-labelledby="intro-heading">
  <div class="intro-grid">
    <div class="intro-text">
      <h2 id="intro-heading" class="h2" data-split>Process know-how, made physical.</h2>
      <p class="lead" data-reveal>TIMB turns complex forming and finishing requirements into reliable, high-performance machinery.</p>
      <p data-reveal style="--d:80ms">We combine in-house engineering with an understanding of every stage of production, from coil handling and forming to cold drawing, finishing and final pressure testing.</p>
      <a class="arrow-link" data-reveal style="--d:140ms" href="mailto:<?= $mail ?>"><span>Build your line with TIMB</span><i aria-hidden="true"></i></a>
    </div>
    <figure class="intro-media" data-wipe>
      <div class="intro-media-main" data-parallax="0.06">
        <?= img_tag('pilger-mill/pilger-3', '1600', 'HPTR-Series pilger mill built by TIMB', ['loading' => 'lazy']) ?>
      </div>
      <figcaption><b>HPTR-Series</b> Pilger Mill<span>±0.03 mm</span></figcaption>
      <div class="intro-media-inset" data-parallax="-0.08">
        <?= img_tag('hydrotester/welspun-hydrotester', '640', 'TIMB hydrotester', ['loading' => 'lazy']) ?>
      </div>
    </figure>
  </div>

  <ol class="pillars">
    <?php foreach ($site->pillars as $i => [$n, $title, $copy]): ?>
      <li data-reveal style="--d:<?= $i * 90 ?>ms"><b><?= esc($n) ?></b><strong><?= esc($title) ?></strong><span><?= esc($copy) ?></span></li>
    <?php endforeach; ?>
  </ol>

  <div class="stats">
    <?php foreach ($site->stats as $i => [$value, $label]): ?>
      <div data-reveal style="--d:<?= $i * 90 ?>ms"><strong data-count><?= esc($value) ?></strong><span><?= safe_inline($label) ?></span></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="machines sec dark" id="machines" aria-labelledby="machines-heading">
  <div class="sec-head">
    <h2 id="machines-heading" class="h2" data-split>The right machine. The complete line.</h2>
    <p data-reveal>Engineered as standalone equipment or configured into a complete, synchronized production system.</p>
  </div>
  <div class="machine-grid">
    <?php foreach ($site->products as $i => $p): ?>
      <article class="mcard" data-wipe data-spot style="--d:<?= ($i % 3) * 100 ?>ms">
        <?= img_tag($p['image'], ($i === 0 || $i === count($site->products) - 1) ? '1600' : '640', $p['name'] . ' machinery', ['loading' => 'lazy']) ?>
        <div class="mcard-shade"></div>
        <div class="mcard-info">
          <span class="mcard-series"><?= esc($p['series']) ?></span>
          <h3><?= esc($p['name']) ?></h3>
          <div class="mcard-more">
            <div>
              <p><?= esc($p['summary']) ?></p>
              <footer>
                <b><?= esc($p['metric']) ?></b>
                <span class="mcard-actions">
                  <a class="spec-link" href="<?= base_url('machines/' . $p['slug']) ?>">Details</a>
                  <a class="enquire" href="mailto:<?= $mail ?>?subject=<?= rawurlencode('Enquiry - ' . $p['name']) ?>" aria-label="Enquire about <?= esc($p['name'], 'attr') ?>"><i aria-hidden="true"></i></a>
                </span>
              </footer>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <div class="more-machines" data-reveal>
    <span>Also from TIMB</span>
    <?php foreach ($site->solutions as $m): ?><a href="<?= base_url('machines/' . $m['slug']) ?>"><?= esc($m['name']) ?><i aria-hidden="true"></i></a><?php endforeach; ?>
  </div>
</section>

<section class="line sec" id="solutions" aria-labelledby="line-heading">
  <div class="sec-head">
    <h2 id="line-heading" class="h2" data-split>From raw material to ready-to-ship.</h2>
    <div data-reveal>
      <p>Choose an individual machine or connect the stages into an end-to-end production line with one engineering partner.</p>
      <a class="arrow-link" href="mailto:<?= $mail ?>?subject=<?= rawurlencode('Enquiry - Production line') ?>"><span>Plan your line</span><i aria-hidden="true"></i></a>
    </div>
  </div>

  <div class="flow" data-flow>
    <div class="tube" aria-hidden="true"><i class="tube-fill"></i></div>
    <ol class="flow-list">
      <?php foreach ($site->lineStages as [$n, $title, $metric, $metricLabel, $machines]): ?>
        <li class="flow-stage">
          <span class="flow-node" aria-hidden="true"><?= esc($n) ?></span>
          <div class="flow-body">
            <h3><?= esc($title) ?></h3>
            <p class="flow-metric"><strong><?= esc($metric) ?></strong><small><?= esc($metricLabel) ?></small></p>
            <ul class="chips" aria-label="Machines in this stage">
              <?php foreach ($machines as $m): ?><li><a href="#machines"><?= esc($m) ?></a></li><?php endforeach; ?>
            </ul>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>

  <div class="flow-cta" data-reveal>
    <p><strong>Not every project needs the full line.</strong> Start with a single machine and connect further stages as production grows.</p>
    <a class="button" href="mailto:<?= $mail ?>"><span>Talk to an engineer</span><i aria-hidden="true"></i></a>
  </div>
</section>

<section class="sectors sec dark" id="industries" aria-labelledby="industry-heading">
  <div class="sectors-grid">
    <div class="sectors-intro">
      <h2 id="industry-heading" class="h2" data-split>When failure isn't an option.</h2>
      <p data-reveal>Tube, pipe and precision components for critical sectors need accuracy, repeatability and proven integrity. That is how every TIMB machine and line is engineered and tested.</p>
      <ul class="proof" data-reveal>
        <li><strong>±0.03 mm</strong><span>Cold pilgering accuracy</span></li>
        <li><strong>600 bar</strong><span>Max hydrotest pressure</span></li>
        <li><strong>8</strong><span>Core machine platforms</span></li>
      </ul>
      <a class="button" data-reveal href="mailto:<?= $mail ?>"><span>Discuss your application</span><i aria-hidden="true"></i></a>
    </div>
    <ul class="sector-list">
      <?php foreach ($site->industries as $i => $name): ?>
        <li data-reveal style="--d:<?= $i * 70 ?>ms"><a class="sector" href="<?= base_url('industries') ?>">
            <strong><?= esc($name) ?></strong>
            <i aria-hidden="true"></i>
          </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php
// India map: pins + callouts (positions come from Config\Site::$network)
$pins = [];
foreach ($site->network as $n) {
  [$x, $y] = india_xy($n['lat'], $n['lon']);
  $pins[] = $n + ['x' => $x, 'y' => $y];
}
$step = count($pins) <= 4 ? 1100 : 90; // ms between pins appearing
?>
<section class="network sec" id="network" aria-labelledby="network-heading">
  <div class="network-grid">
    <div class="network-copy">
      <h2 id="network-heading" class="h2" data-split>Where TIMB machines are built.</h2>
      <p data-reveal>Manufactured at our works near Chennai and supported from our corporate office in Guindy. Talk to us about your line, spares or service.</p>
      <ul class="net-places">
        <?php foreach ($pins as $i => $n): ?>
          <li class="place place-<?= $n['type'] ?>" data-reveal style="--d:<?= $i * 90 ?>ms" data-pin="<?= $i ?>" tabindex="0">
            <span class="place-tag"><i aria-hidden="true"></i><?= esc($n['sub']) ?></span>
            <h3><?= esc($n['name']) ?></h3>
            <p><?= esc($n['address']) ?></p>
            <div class="place-links">
              <?php foreach ($n['phones'] as $ph): ?>
                <a href="tel:+91<?= preg_replace('/\D/', '', $ph) ?>"><?= esc($ph) ?></a>
              <?php endforeach; ?>
              <a class="place-map" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($n['address']) ?>">Open in Google Maps <i aria-hidden="true"></i></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="button" data-reveal href="mailto:<?= $mail ?>?subject=<?= rawurlencode('Enquiry - TIMB') ?>"><span>Talk to our team</span><i aria-hidden="true"></i></a>
    </div>
    <div class="net-stage" data-network style="--step:<?= $step ?>ms">
      <?= view('partials/india_map') ?>
      <svg class="net-leads" viewBox="0 0 1000 1122" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <?php foreach ($pins as $i => $n): ?>
          <path class="lead lead-<?= $n['type'] ?>" style="--i:<?= $i ?>" pathLength="1" d="M<?= round($n['x'] * 10, 1) ?> <?= round($n['y'] * 11.2226, 1) ?> L<?= $n['callout'][0] * 10 - 8 ?> <?= round($n['callout'][1] * 11.2226, 1) ?>" />
        <?php endforeach; ?>
      </svg>
      <?php foreach ($pins as $i => $n): ?>
        <div class="pin pin-<?= $n['type'] ?>" data-pin="<?= $i ?>" style="--x:<?= $n['x'] ?>%;--y:<?= $n['y'] ?>%;--i:<?= $i ?>">
          <button type="button" class="dot" aria-label="<?= esc($n['name']) ?>, <?= esc($n['place']) ?>"></button>
        </div>
        <div class="callout callout-<?= $n['type'] ?>" data-pin="<?= $i ?>" style="--cx:<?= $n['callout'][0] ?>%;--cy:<?= $n['callout'][1] ?>%;--i:<?= $i ?>">
          <b><?= esc($n['name']) ?></b><span><?= esc($n['place']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="gallery sec" aria-labelledby="gallery-heading">
  <div class="sec-head">
    <h2 id="gallery-heading" class="h2" data-split>Engineering in action.</h2>
    <p data-reveal>Explore the TIMB machinery portfolio, from high-capacity integrated lines to precision forming systems.</p>
  </div>
</section>
<div class="reel" aria-label="Photo gallery">
  <?php foreach ($rows as $r => $row): ?>
    <div class="reel-row<?= $r ? ' is-reverse' : '' ?>" data-marquee data-speed="<?= $r ? 35 : 45 ?>">
      <?php foreach ($row as $g): ?><?= img_tag($g['key'], '640', $g['alt'], ['loading' => 'lazy']) ?><?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>

<section class="contact sec" aria-labelledby="contact-heading">
  <h2 id="contact-heading" class="h2" data-split>Let's engineer what's next.</h2>
  <div class="contact-side">
    <p data-reveal>Tell us what you need to make. Our team will help you shape the right machine, the right line and the right outcome.</p>
    <a class="button button-light" data-reveal data-magnetic href="mailto:<?= $mail ?>"><span>Talk to TIMB</span><i aria-hidden="true"></i></a>
  </div>
</section>
<?php $this->endSection(); ?>