<?php /** @var \Config\Site $site */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php
  // Resolved once so the description, the title and the sharing tags below
  // can never disagree with each other.
  $pageDesc  = $metaDesc ?? 'TIMB engineers metal-forming, tube processing and testing machinery for industrial production.';
  $pageTitle = $title ?? ($site->name . ' | Precision Industrial Machinery');
?>
  <meta name="description" content="<?= esc($pageDesc, 'attr') ?>">
  <meta name="theme-color" content="#0f1318">
  <title><?= esc($pageTitle) ?></title>
<?= view('partials/seo_head', ['pageTitle' => $pageTitle, 'pageDesc' => $pageDesc, 'site' => $site]) ?>
  <link rel="icon" href="<?= asset_url('favicon.ico') ?>" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= asset_url('favicon-32.png') ?>">
  <link rel="apple-touch-icon" href="<?= asset_url('apple-touch-icon.png') ?>">
  <script>
    /* Motion is progressive enhancement: if the script fails, content stays visible. */
    document.documentElement.classList.add('js');
    setTimeout(function () {
      var d = document.documentElement;
      d.classList.add('ready');
      if (!d.classList.contains('booted')) d.classList.remove('js');
    }, 1400);
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php $pre = array_key_exists('preload', get_defined_vars()) ? $preload : img_url($site->heroSlides[0]['image'], '1600'); if ($pre): ?><link rel="preload" as="image" href="<?= esc($pre, 'attr') ?>" fetchpriority="high"><?php endif; ?>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wdth,wght@0,62..125,100..900;1,62..125,100..900&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
</head>
<body>
  <div class="curtain" aria-hidden="true"><span class="brand"><?= esc($site->name) ?></span></div>
  <div class="progress" aria-hidden="true"><i></i></div>
  <a class="skip-link" href="#main">Skip to content</a>

  <header class="site-header" data-header>
    <a class="brand" href="<?= base_url('/') ?>" aria-label="<?= esc($site->name) ?> home"><?= esc($site->name) ?><small><?= esc($site->tagline) ?></small></a>
    <?php
      $bySlug = array_column(array_merge($site->products, $site->solutions), null, 'slug');
      $quote  = base_url('contact') . '#enquiry';
    ?>
    <nav class="site-nav" id="site-nav" aria-label="Primary navigation">
      <a href="<?= base_url('/') ?>">Home</a>
      <a href="<?= base_url('about') ?>">About Us</a>
      <div class="nav-item has-sub">
        <a class="nav-sub-toggle" href="<?= base_url('/') ?>#machines" aria-haspopup="true" aria-expanded="false" data-sub-toggle>Products<i class="caret" aria-hidden="true"></i></a>
        <div class="mega" role="group" aria-label="Products">
          <?php foreach ($site->productMenu as $group => $slugs): ?>
            <?php $wide = count($slugs) > 4; ?>
            <div class="mega-col<?= $wide ? ' mega-col-wide' : '' ?>">
              <p><?= esc($group) ?></p>
              <ul<?= $wide ? ' style="--rows:' . (int) ceil(count($slugs) / 2) . '"' : '' ?>>
                <?php foreach ($slugs as $slug): if (! isset($bySlug[$slug])) { continue; } ?>
                  <li><a href="<?= base_url('machines/' . $slug) ?>"><?= esc($bySlug[$slug]['name']) ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="<?= base_url('projects') ?>">Projects</a>
      <a href="<?= base_url('industries') ?>">Industries</a>
      <a href="<?= base_url('infrastructure') ?>">Infrastructure</a>
      <a href="<?= base_url('news') ?>">Latest News</a>
      <a href="<?= base_url('contact') ?>">Contact Us</a>
      <a class="nav-cta" href="<?= $quote ?>">Get a Quote</a>
    </nav>
    <div class="head-brands">
      <a class="murugappa-logo" href="https://www.murugappa.com" target="_blank" rel="noopener" aria-label="Murugappa Group"><img src="<?= base_url('assets/img/murugappa-logo.png') ?>" alt="Murugappa" width="307" height="180"></a>
      <a class="social-in" href="<?= esc($site->linkedin, 'attr') ?>" target="_blank" rel="noopener" aria-label="TI Machine Building on LinkedIn"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.94 8.5H3.56V20h3.38V8.5ZM5.25 3a1.97 1.97 0 1 0 0 3.94 1.97 1.97 0 0 0 0-3.94ZM20.44 13.4c0-3.1-1.65-4.55-3.86-4.55-1.78 0-2.58.98-3.02 1.67V8.5H10.2c.04.95 0 11.5 0 11.5h3.36v-6.42c0-.34.02-.69.13-.93.28-.69.9-1.4 1.95-1.4 1.37 0 1.92 1.05 1.92 2.58V20h3.36l.02-6.6Z"/></svg></a>
    </div>
    <a class="nav-contact" href="<?= $quote ?>"><span>Get a Quote</span><i aria-hidden="true"></i></a>
    <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="site-nav" data-nav-toggle><span></span><span></span></button>
  </header>

  <main id="main">
    <?= $this->renderSection('content') ?>
  </main>

  <footer class="footer">
    <div class="footer-main">
      <a class="brand" href="<?= base_url('/') ?>"><?= esc($site->name) ?><small><?= esc($site->tagline) ?></small></a>
      <p class="footer-tag"><?= esc($site->footerTagline) ?></p>
      <div class="footer-brands">
        <a class="murugappa-logo" href="https://www.murugappa.com" target="_blank" rel="noopener" aria-label="Murugappa Group"><img src="<?= base_url('assets/img/murugappa-logo.png') ?>" alt="Murugappa" width="307" height="180" loading="lazy"></a>
        <a class="social-in" href="<?= esc($site->linkedin, 'attr') ?>" target="_blank" rel="noopener" aria-label="TI Machine Building on LinkedIn"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.94 8.5H3.56V20h3.38V8.5ZM5.25 3a1.97 1.97 0 1 0 0 3.94 1.97 1.97 0 0 0 0-3.94ZM20.44 13.4c0-3.1-1.65-4.55-3.86-4.55-1.78 0-2.58.98-3.02 1.67V8.5H10.2c.04.95 0 11.5 0 11.5h3.36v-6.42c0-.34.02-.69.13-.93.28-.69.9-1.4 1.95-1.4 1.37 0 1.92 1.05 1.92 2.58V20h3.36l.02-6.6Z"/></svg></a>
      </div>
    </div>
    <div class="footer-links">
      <h3>Quick links</h3>
      <a href="<?= base_url('/') ?>">Home</a>
      <a href="<?= base_url('about') ?>">About Us</a>
      <a href="<?= base_url('infrastructure') ?>">Infrastructure</a>
      <a href="<?= base_url('projects') ?>">Projects</a>
      <a href="<?= base_url('industries') ?>">Industries</a>
      <a href="<?= base_url('clients') ?>">Clients &amp; FAQ</a>
      <a href="<?= base_url('news') ?>">News</a>
      <a href="<?= base_url('careers') ?>">Career</a>
      <a href="<?= base_url('contact') ?>">Contact Us</a>
    </div>
    <div class="footer-links footer-machines">
      <h3>Machines</h3>
      <?php foreach (array_merge($site->products, $site->solutions) as $m): ?>
        <a href="<?= base_url('machines/' . $m['slug']) ?>"><?= esc($m['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="footer-links footer-contact">
      <h3>Contact</h3>
      <?php foreach ($site->network as $loc): ?>
        <div class="footer-loc">
          <b><?= esc($loc['name']) ?></b>
          <span><?= esc($loc['address']) ?></span>
          <span class="footer-phones"><?php foreach ($loc['phones'] as $ph): ?><a href="tel:+91<?= preg_replace('/\D/', '', $ph) ?>"><?= esc($ph) ?></a><?php endforeach; ?></span>
          <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($loc['address']) ?>" target="_blank" rel="noopener">Open in Maps</a>
        </div>
      <?php endforeach; ?>
      <a href="mailto:<?= esc($site->email, 'attr') ?>"><?= esc($site->email) ?></a>
    </div>
    <div class="footer-bottom">
      <span class="footer-copy">© <?= date('Y') ?> <?= esc($site->name) ?> All Rights Reserved</span>
      <span class="footer-legal"><a href="<?= base_url('careers') ?>">Career</a><a href="<?= base_url('disclaimer') ?>">Disclaimer</a></span>
    </div>
  </footer>
  <script src="<?= asset_url('assets/js/main.js') ?>" defer></script>
</body>
</html>
