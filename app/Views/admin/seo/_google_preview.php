<?php
/**
 * How a page shows up as a Google result: site name and address, blue title,
 * grey description — cut where Google usually cuts them.
 *
 * @var string $title  @var string $description  @var string $domain
 * @var string $path   @var string $siteName
 * @var int    $titleMax  @var int $descriptionMax
 */
$clip = static fn (string $s, int $max) => mb_strlen($s) > $max ? rtrim(mb_substr($s, 0, $max - 1)) . '…' : $s;

$crumbs = trim($path, '/') === '' ? '' : ' › ' . implode(' › ', explode('/', trim($path, '/')));
?>
<div class="g-preview" aria-label="Preview of the Google search result">
  <div class="g-site">
    <span class="g-favicon" aria-hidden="true"><?= esc(mb_substr($siteName, 0, 1)) ?></span>
    <span>
      <span class="g-name"><?= esc($siteName) ?></span>
      <span class="g-url"><?= esc($domain . $crumbs) ?></span>
    </span>
  </div>
  <div class="g-title"><?= $title !== '' ? esc($clip($title, $titleMax)) : '<em>' . esc($domain . $path) . '</em>' ?></div>
  <div class="g-desc"><?= $description !== '' ? esc($clip($description, $descriptionMax)) : '<em>Google will pick some text from the page itself.</em>' ?></div>
</div>
