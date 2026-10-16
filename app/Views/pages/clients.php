<?php
/** @var \Config\Site $site  @var array $c */
$this->extend('layout');
$this->section('content');
$cl = $c['clients'];
?>
<?= view('partials/page_hero', ['crumb' => 'Clients & FAQ', 'kicker' => 'Clients & FAQ', 'heading' => ['Trusted by', 'industry', 'leaders.'], 'lead' => 'From automotive suppliers to tube makers, customers come back to TIMB for the next machine.']) ?>

<section class="sec" aria-labelledby="dl-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Delivered for</p><h2 id="dl-h" class="h2" data-split>What clients commissioned.</h2></div>
    <p data-reveal>A few of the machines and automation systems TIMB has delivered.</p>
  </div>
  <ul class="pg-feature-grid">
    <?php foreach ($cl['delivered'] as $i => [$who, $what]): ?>
      <li class="pg-feature" data-spot data-reveal style="--d:<?= $i * 90 ?>ms"><b class="num"><?= sprintf('%02d', $i + 1) ?></b><h3><?= esc($who) ?></h3><p><?= esc($what) ?></p></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="pg-apps sec dark name-band" aria-label="Customers and partners">
  <p class="eyebrow" data-reveal>Customers &amp; partners</p>
  <div class="name-marquee" aria-hidden="true"><div class="name-track" data-marquee data-speed="38"><?php foreach ($cl['names'] as $n): ?><span><?= esc($n) ?></span><?php endforeach; ?></div></div>
  <ul class="sr-only"><?php foreach ($cl['names'] as $n): ?><li><?= esc($n) ?></li><?php endforeach; ?></ul>
</section>

<section class="sec" id="faq" aria-labelledby="faq-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>FAQ</p><h2 id="faq-h" class="h2" data-split>Frequently asked questions.</h2></div>
    <p data-reveal>Quotations, documents, industries and services.</p>
  </div>
  <ul class="faq" data-faq>
    <?php foreach ($c['faq'] as $i => [$q, $a]): ?>
      <li class="faq-item" data-reveal style="--d:<?= $i * 70 ?>ms">
        <h3><button type="button" class="faq-q" aria-expanded="false" aria-controls="faq-a<?= $i ?>" id="faq-q<?= $i ?>"><b><?= sprintf('%02d', $i + 1) ?></b><span><?= esc($q) ?></span><i aria-hidden="true"></i></button></h3>
        <div class="faq-a" id="faq-a<?= $i ?>" role="region" aria-labelledby="faq-q<?= $i ?>"><div><p><?= esc($a) ?></p></div></div>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<?= view('partials/page_cta', ['site' => $site, 'headline' => 'Ready to request a quote?', 'text' => 'Send the specification, 2D drawing, 3D model and images, and we will take it from there.', 'subject' => 'Request for quotation']) ?>
<?php $this->endSection(); ?>
