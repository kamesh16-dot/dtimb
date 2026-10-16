<?php
/** @var \Config\Site $site  @var array $c */
$this->extend('layout');
$this->section('content');
?>
<?= view('partials/page_hero', ['crumb' => 'Disclaimer', 'kicker' => 'Legal', 'heading' => ['Website', 'disclaimer.', ''], 'lead' => 'Please read this notice about the information on this website.']) ?>

<section class="sec" aria-label="Disclaimer">
  <ol class="legal-list">
    <?php foreach ($c['disclaimer'] as $i => $t): ?>
      <li data-reveal style="--d:<?= $i * 60 ?>ms"><b><?= sprintf('%02d', $i + 1) ?></b><p><?= esc($t) ?></p></li>
    <?php endforeach; ?>
  </ol>
  <p class="legal-contact" data-reveal>Questions about this notice? Email <a href="mailto:<?= esc($site->email, 'attr') ?>"><?= esc($site->email) ?></a>.</p>
</section>
<?php $this->endSection(); ?>
