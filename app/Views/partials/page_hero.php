<?php
/**
 * Compact dark hero for company pages.
 * @var string      $crumb
 * @var string      $kicker
 * @var array       $heading  [before, accent, after]
 * @var string      $lead
 * @var array|null  $stats    [[value, label], ...]  (counts up)
 */
?>
<section class="pg-hero pg-hero-simple dark" id="top" aria-labelledby="pg-title">
  <div class="pg-hero-fx" aria-hidden="true"><i></i><i></i><i></i></div>
  <div class="pg-hero-copy">
    <nav class="crumbs" aria-label="Breadcrumb" data-reveal>
      <a href="<?= base_url('/') ?>">Home</a><i aria-hidden="true">/</i><span aria-current="page"><?= esc($crumb) ?></span>
    </nav>
    <p class="pg-kicker" data-reveal style="--d:80ms"><?= esc($kicker) ?></p>
    <h1 id="pg-title" class="pg-title" data-split><?php if ($heading[0] !== ''): ?><?= esc($heading[0]) ?> <?php endif; ?><em><?= esc($heading[1]) ?></em><?php if (($heading[2] ?? '') !== ''): ?> <?= esc($heading[2]) ?><?php endif; ?></h1>
    <p class="pg-lead" data-reveal style="--d:200ms"><?= esc($lead) ?></p>
  </div>
  <?php if (! empty($stats)): ?>
  <ul class="pg-specs">
    <?php foreach ($stats as $i => [$value, $label]): ?>
      <li data-reveal style="--d:<?= $i * 80 ?>ms"><strong<?= preg_match('/\d/', $value) ? ' data-count' : '' ?>><?= esc($value) ?></strong><span><?= esc($label) ?></span></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
