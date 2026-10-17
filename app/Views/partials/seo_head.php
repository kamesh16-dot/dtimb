<?php
/**
 * Canonical link and sharing preview tags.
 *
 * @var string $pageTitle    the resolved <title>
 * @var string $pageDesc     the resolved meta description
 * @var \Config\Site $site
 *
 * Both blocks can be turned off in Admin → SEO. Nothing here changes what is
 * on the page itself; it only tells search engines and link previews what
 * this page is.
 */

use App\Models\SeoSettingModel;

try {
    $canonicalOn = SeoSettingModel::on('canonical_tags');
    $socialOn    = SeoSettingModel::on('social_tags');
    $indexable   = SeoSettingModel::on('allow_indexing');
    $base        = SeoSettingModel::canonicalBase();
    $shareKey    = SeoSettingModel::get('share_image');
} catch (\Throwable $e) {
    log_message('error', 'SEO settings unavailable in layout: ' . $e->getMessage());

    return;
}

// A canonical pointing at /index.php/about would be wrong, and the same page
// would then advertise two different addresses.
$root      = $base !== '' ? $base : preg_replace('#/index\.php$#', '', rtrim(base_url(), '/'));
$canonical = $root . timb_path();
$shareUrl  = $shareKey !== '' && isset(timb_manifest()[$shareKey]) ? img_url($shareKey, '1600') : null;
?>
<?php if ($canonicalOn): ?>
  <link rel="canonical" href="<?= esc($canonical, 'attr') ?>">
<?php endif ?>
<?php if (! $indexable): ?>
  <meta name="robots" content="noindex, nofollow">
<?php endif ?>
<?php if ($socialOn): ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= esc($site->name, 'attr') ?>">
  <meta property="og:title" content="<?= esc($pageTitle, 'attr') ?>">
  <meta property="og:description" content="<?= esc($pageDesc, 'attr') ?>">
  <meta property="og:url" content="<?= esc($canonical, 'attr') ?>">
  <?php if ($shareUrl !== null): ?>
    <meta property="og:image" content="<?= esc($shareUrl, 'attr') ?>">
  <?php endif ?>
  <meta name="twitter:card" content="<?= $shareUrl !== null ? 'summary_large_image' : 'summary' ?>">
  <meta name="twitter:title" content="<?= esc($pageTitle, 'attr') ?>">
  <meta name="twitter:description" content="<?= esc($pageDesc, 'attr') ?>">
<?php endif ?>
