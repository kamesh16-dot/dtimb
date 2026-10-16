<?php
/** @var \Config\Site $site  @var array $c  @var array $all */
$this->extend('layout');
$this->section('content');
$bySlug = array_column($all, null, 'slug');
$mail   = esc($site->email, 'attr');
?>
<?= view('partials/page_hero', ['crumb' => 'Industries', 'kicker' => 'Industries', 'heading' => ['Where TIMB', 'machines', 'work.'], 'lead' => 'Automotive, railways, tube processing, EV, and heavy engineering and infrastructure.']) ?>

<?php foreach ($c['industries'] as $i => [$id, $name, $desc, $items, $machines]): ?>
<section class="ind sec<?= $i % 2 ? ' ind-alt' : '' ?>" id="<?= $id ?>" aria-labelledby="ind-<?= $id ?>">
  <div class="ind-grid">
    <b class="ind-num" aria-hidden="true" data-reveal><?= sprintf('%02d', $i + 1) ?></b>
    <div>
      <h2 id="ind-<?= $id ?>" class="h2" data-split><?= esc($name) ?></h2>
      <p class="ind-desc" data-reveal><?= esc($desc) ?></p>
      <ul class="pg-tags<?= $i % 2 ? '' : ' pg-tags-light' ?>" data-reveal><?php foreach ($items as $k => $t): ?><li style="--i:<?= $k ?>"><?= esc($t) ?></li><?php endforeach; ?></ul>
      <?php if ($machines): ?>
        <p class="pg-pairs-label" data-reveal>Machines</p>
        <ul class="ind-machines" data-reveal><?php foreach ($machines as $s): if (! isset($bySlug[$s])) { continue; } ?><li><a href="<?= base_url('machines/' . $s) ?>"><?= esc($bySlug[$s]['name']) ?><i aria-hidden="true"></i></a></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <a class="arrow-link" data-reveal href="mailto:<?= $mail ?>?subject=<?= rawurlencode('Enquiry - ' . $name) ?>"><span>Discuss a <?= esc($name) ?> project</span><i aria-hidden="true"></i></a>
    </div>
  </div>
</section>
<?php endforeach; ?>

<?= view('partials/page_cta', ['site' => $site, 'headline' => 'Tell us about your application.', 'text' => 'Every machine is custom engineered, so start with the part you need to make.', 'subject' => 'Enquiry - Application']) ?>
<?php $this->endSection(); ?>
