<?php
/** @var \Config\Site $site  @var array $c */
$this->extend('layout');
$this->section('content');
$n = $c['infrastructure'];
$tools = static function (array $rows): void {
    foreach ($rows as $i => [$t, $d]) {
        echo '<li class="tool" data-reveal style="--d:' . ($i * 70) . 'ms"><b>' . esc($t) . '</b><span>' . esc($d) . '</span></li>';
    }
};
?>
<?= view('partials/page_hero', ['crumb' => 'Infrastructure', 'kicker' => 'Infrastructure', 'heading' => ['Design, build', 'and test.', ''], 'lead' => $n['lead'], 'stats' => $n['stats']]) ?>

<section class="sec" aria-labelledby="dt-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Design tools</p><h2 id="dt-h" class="h2" data-split>From first sketch to PLC code.</h2></div>
    <p data-reveal>Mechanical, electrical and controls design all happen in-house, using industry-standard tools.</p>
  </div>
  <div class="tool-cols">
    <div><h3 class="tool-h" data-reveal>Mechanical design</h3><ul class="tool-list"><?php $tools($n['mech']); ?></ul></div>
    <div><h3 class="tool-h" data-reveal>Electrical &amp; controls</h3><ul class="tool-list"><?php $tools($n['elec']); ?></ul></div>
  </div>
</section>

<section class="pg-highlight sec" aria-labelledby="mf-h">
  <div class="pg-hl-grid">
    <div><p class="eyebrow" data-reveal>Manufacturing</p><h2 id="mf-h" class="h2" data-split>Backed by Shanthi Gears.</h2></div>
    <p class="pg-hl-text" data-reveal><?= esc($n['make']['text']) ?></p>
  </div>
</section>

<section class="pg-apps sec dark" aria-labelledby="iq-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Inspection &amp; quality</p><h2 id="iq-h" class="h2" data-split>Measured, then shipped.</h2></div>
    <p data-reveal>Precision measurement and materials laboratory equipment support every build.</p>
  </div>
  <ul class="lab-grid">
    <?php foreach (array_merge($n['inspect'], $n['lab']) as $i => [$t, $d]): ?>
      <li class="lab" data-spot data-reveal style="--d:<?= ($i % 4) * 80 ?>ms"><b class="num"><?= sprintf('%02d', $i + 1) ?></b><h3><?= esc($t) ?></h3><p><?= esc($d) ?></p></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="pg-highlight sec" aria-labelledby="sv-h">
  <div class="pg-hl-grid">
    <div><p class="eyebrow" data-reveal>After-sales</p><h2 id="sv-h" class="h2" data-split>Service that stays with the machine.</h2></div>
    <ul class="pg-tags pg-tags-light" data-reveal><?php foreach ($n['service'] as $i => $s): ?><li style="--i:<?= $i ?>"><?= esc($s) ?></li><?php endforeach; ?></ul>
  </div>
</section>

<?= view('partials/page_cta', ['site' => $site, 'headline' => 'Want to see the floor?', 'text' => 'Talk to us about a visit, a quotation or a project.', 'subject' => 'Enquiry - Infrastructure']) ?>
<?php $this->endSection(); ?>
