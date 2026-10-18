<?php
/** @var \Config\Site $site  @var array $c */
$this->extend('layout');
$this->section('content');
?>
<?= view('partials/page_hero', ['crumb' => 'Latest News', 'kicker' => 'Latest news', 'heading' => ['News from', 'TIMB.', ''], 'lead' => 'Announcements, milestones and stories from TI Machine Building.']) ?>

<section class="sec" aria-labelledby="nw-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Stories</p><h2 id="nw-h" class="h2" data-split>Latest news.</h2></div>
    <p data-reveal>Follow TIMB for new machines, projects and milestones.</p>
  </div>

  <?php foreach ($c['news'] as $i => $a): $id = 'news-' . url_title($a['title'], '-', true); ?>
    <article class="news-article" id="<?= esc($id, 'attr') ?>" data-news data-reveal style="--d:<?= $i * 90 ?>ms">
      <header class="news-article-head">
        <time datetime="<?= esc($a['date'], 'attr') ?>"><b><?= date('d', strtotime($a['date'])) ?></b><span><?= date('M Y', strtotime($a['date'])) ?></span></time>
        <div>
          <?php if (! empty($a['tag'])): ?><p class="eyebrow"><?= esc($a['tag']) ?></p><?php endif; ?>
          <h3 id="<?= esc($id, 'attr') ?>-h"><?= esc($a['title']) ?></h3>
          <p class="news-summary"><?= esc($a['summary']) ?></p>
          <button type="button" class="arrow-link news-toggle" aria-expanded="false" aria-controls="<?= esc($id, 'attr') ?>-more" data-news-toggle data-open-label="Close article" data-closed-label="Read the article"><span>Read the article</span><i aria-hidden="true"></i></button>
        </div>
      </header>

      <div class="news-more" id="<?= esc($id, 'attr') ?>-more" role="region" aria-labelledby="<?= esc($id, 'attr') ?>-h">
        <div class="news-more-in">
          <?php if (! empty($a['hero'])): ?>
            <figure class="news-hero"><img src="<?= esc(photo_url($a['hero'][0], '1600'), 'attr') ?>" alt="<?= esc($a['hero'][1], 'attr') ?>" loading="lazy" decoding="async"></figure>
          <?php endif; ?>

          <div class="news-body">
            <?php if (! empty($a['intro'])): ?><p class="news-lead"><?= esc($a['intro']) ?></p><?php endif; ?>
            <?php if (! empty($a['quote'])): ?><blockquote class="news-quote"><p><?= esc($a['quote']) ?></p></blockquote><?php endif; ?>

            <?php if (! empty($a['gallery'])): ?>
              <div class="news-gallery">
                <?php foreach ($a['gallery'] as [$key, $alt]): ?>
                  <figure><img src="<?= esc(photo_url($key, '640'), 'attr') ?>" alt="<?= esc($alt, 'attr') ?>" loading="lazy" decoding="async"></figure>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php foreach ($a['body'] ?? [] as $para): ?><p><?= esc($para) ?></p><?php endforeach; ?>
          </div>
        </div>
      </div>
    </article>
  <?php endforeach; ?>

  <?= isset($pager) ? view('partials/pagination', ['pager' => $pager, 'noun' => 'articles', 'one' => 'article']) : '' ?>
</section>

<?= view('partials/page_cta', ['site' => $site, 'headline' => 'Have news to share with us?', 'text' => 'Talk to our team about your project.', 'subject' => 'Enquiry - TIMB']) ?>
<?php $this->endSection(); ?>
