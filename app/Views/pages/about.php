<?php
/** @var \Config\Site $site  @var array $c */
$this->extend('layout');
$this->section('content');
$a = $c['about'];
?>
<?= view('partials/page_hero', ['crumb' => 'About', 'kicker' => 'About TIMB', 'heading' => ['Enabling ideas into', 'reality.', ''], 'lead' => $a['lead'], 'stats' => $a['stats']]) ?>

<section class="sec" aria-labelledby="cap-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>What we bring</p><h2 id="cap-h" class="h2" data-split>Built by engineers who build.</h2></div>
    <p data-reveal>TIMB offers a wide range of custom-engineered machines that integrate into manufacturing lines, with complete automation and Industry 4.0 features for special machines.</p>
  </div>
  <ul class="pg-feature-grid">
    <?php foreach ($a['capabilities'] as $i => [$t, $d]): ?>
      <li class="pg-feature" data-spot data-reveal style="--d:<?= $i * 90 ?>ms"><b class="num"><?= sprintf('%02d', $i + 1) ?></b><h3><?= esc($t) ?></h3><p><?= esc($d) ?></p></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="pg-how sec" aria-labelledby="tl-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Our story</p><h2 id="tl-h" class="h2" data-split>From a bicycle to a machine builder.</h2></div>
    <p data-reveal>A timeline of the Murugappa Group business TIMB grew out of. Scroll to follow it.</p>
  </div>
  <div class="flow pg-flow pg-time" data-flow>
    <div class="tube" aria-hidden="true"><i class="tube-fill"></i></div>
    <ol class="flow-list">
      <?php foreach ($a['timeline'] as $i => [$year, $t, $text]): ?>
        <li class="flow-stage pg-stage">
          <span class="flow-node" aria-hidden="true"><?= $i + 1 ?></span>
          <div class="pg-stage-body no-fig"><div class="pg-stage-text">
            <p class="pg-year"><?= esc($year) ?></p><h4><?= esc($t) ?></h4><p class="pg-body"><?= esc($text) ?></p>
          </div></div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="pg-apps sec dark" aria-labelledby="grp-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Murugappa Group</p><h2 id="grp-h" class="h2" data-split>Part of a group founded in 1900.</h2></div>
    <p data-reveal><?= esc($a['group']['text']) ?></p>
  </div>
  <ul class="pg-specs pg-facts">
    <?php foreach ($a['group']['facts'] as $i => [$v, $l]): ?>
      <li data-reveal style="--d:<?= $i * 80 ?>ms"><strong data-count><?= esc($v) ?></strong><span><?= esc($l) ?></span></li>
    <?php endforeach; ?>
  </ul>
  <div class="pg-apps-block"><h3 data-reveal>Brands from the Murugappa stable</h3>
    <ul class="pg-tags" data-reveal><?php foreach ($a['group']['brands'] as $i => $b): ?><li style="--i:<?= $i ?>"><?= esc($b) ?></li><?php endforeach; ?></ul></div>
</section>

<section class="pg-highlight sec" aria-labelledby="tii-h">
  <div class="pg-hl-grid">
    <div>
      <p class="eyebrow" data-reveal>Tube Investments of India</p>
      <h2 id="tii-h" class="h2" data-split>Engineering and metallurgy, for six decades.</h2>
      <p class="pg-hl-text" data-reveal><?= esc($a['tii']['text']) ?></p>
    </div>
    <div>
      <p class="pg-pairs-label" data-reveal>TII businesses</p>
      <ul class="pg-tags pg-tags-light" data-reveal><?php foreach ($a['tii']['companies'] as $i => $b): ?><li style="--i:<?= $i ?>"><?= esc($b) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
</section>

<section class="pg-features sec" aria-labelledby="aw-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Recognition</p><h2 id="aw-h" class="h2" data-split>Awards.</h2></div>
    <p data-reveal>Industry recognition for automation, technology and teamwork.</p>
  </div>
  <ol class="award-list">
    <?php foreach ($a['awards'] as $i => $t): ?>
      <li data-reveal style="--d:<?= $i * 70 ?>ms"><b><?= sprintf('%02d', $i + 1) ?></b><span><?= esc($t) ?></span></li>
    <?php endforeach; ?>
  </ol>
</section>

<?= view('partials/page_cta', ['site' => $site, 'headline' => 'Have an idea? Let’s make it real.', 'text' => 'Tell us what you need to build, test or automate.', 'subject' => 'Enquiry - TIMB']) ?>
<?php $this->endSection(); ?>
