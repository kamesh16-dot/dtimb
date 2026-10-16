<?php
/** @var \Config\Site $site  @var array $c  @var array $all */
$this->extend('layout');
$this->section('content');
$bySlug = array_column($all, null, 'slug');
?>
<?= view('partials/page_hero', ['crumb' => 'Projects', 'kicker' => 'Completed projects', 'heading' => ['Built, tested,', 'delivered.', ''], 'lead' => 'More than 220 major projects across machines, automation and tooling, with 12+ more running today.', 'stats' => [['220+', 'Completed projects'], ['12+', 'Running projects'], ['10+', 'Products']]]) ?>

<section class="sec" aria-labelledby="pj-h" data-projects>
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>A selection</p><h2 id="pj-h" class="h2" data-split>Projects we’re proud of.</h2></div>
    <label class="proj-filter" data-reveal><span>Filter projects</span><input type="search" placeholder="e.g. vision, welding, tube" data-proj-filter autocomplete="off"><em data-proj-count aria-live="polite"></em></label>
  </div>
  <ul class="proj-grid">
    <?php foreach ($c['projects'] as $i => [$name, $slug]): $m = $slug ? ($bySlug[$slug] ?? null) : null; ?>
      <li class="proj" data-name="<?= esc(strtolower($name), 'attr') ?>" data-spot data-reveal style="--d:<?= ($i % 4) * 70 ?>ms">
        <<?= $m ? 'a href="' . base_url('machines/' . $m['slug']) . '"' : 'div' ?> class="proj-in">
          <b class="num"><?= sprintf('%02d', $i + 1) ?></b>
          <h3><?= esc($name) ?></h3>
          <?php if ($m): ?><span class="proj-link">View machine <i aria-hidden="true"></i></span><?php endif; ?>
        </<?= $m ? 'a' : 'div' ?>>
      </li>
    <?php endforeach; ?>
  </ul>
  <p class="proj-empty" hidden>No projects match that search.</p>
</section>

<section class="pg-features sec" aria-labelledby="cs-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Case studies</p><h2 id="cs-h" class="h2" data-split>Improvement and repeat orders.</h2></div>
    <p data-reveal>Machines that earned a second order.</p>
  </div>
  <ul class="pg-feature-grid">
    <?php foreach ($c['cases'] as $i => [$t, $tag, $text]): ?>
      <li class="pg-feature" data-spot data-reveal style="--d:<?= $i * 90 ?>ms"><b class="num"><?= sprintf('%02d', $i + 1) ?></b><h3><?= esc($t) ?></h3><p class="pg-case-tag"><?= esc($tag) ?></p><p><?= esc($text) ?></p></li>
    <?php endforeach; ?>
  </ul>
</section>

<?= view('partials/page_cta', ['site' => $site, 'headline' => 'Your project could be next.', 'text' => 'Send us a specification, drawings or just the problem you want to solve.', 'subject' => 'Enquiry - New project']) ?>
<?php $this->endSection(); ?>
