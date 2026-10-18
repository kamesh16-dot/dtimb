<?php
/**
 * The full details of one table row, opened in the side drawer when the row
 * is clicked. Rendered as a <template>, so nothing here is on screen until
 * then; the drawer itself lives once in admin/layout.php.
 *
 * @var string      $id        unique per page; the row carries data-drawer="<id>"
 * @var string      $title
 * @var string|null $subtitle
 * @var list<array{label:string,value:mixed,type?:string}> $rows
 *      type: text (default) | long (keeps line breaks) | lines (a list) |
 *            code | image (an ImageManifest key) | pill (value = [text, tone]) |
 *            html (already escaped by the caller)
 * @var string|null $actions   buttons/forms for the drawer footer, already escaped
 */
$subtitle ??= null;
$actions  ??= null;

$isEmpty = static fn ($v) => $v === null || $v === '' || $v === [];
?>
<template id="<?= esc($id, 'attr') ?>" data-title="<?= esc($title, 'attr') ?>" data-subtitle="<?= esc((string) $subtitle, 'attr') ?>">
  <dl class="drawer-list">
    <?php foreach ($rows as $row): ?>
      <?php $type = $row['type'] ?? 'text'; $v = $row['value']; ?>
      <div class="drawer-item<?= in_array($type, ['long', 'lines', 'image'], true) ? ' drawer-item--wide' : '' ?>">
        <dt><?= esc($row['label']) ?></dt>
        <dd>
          <?php if ($type !== 'html' && $isEmpty($v)): ?>
            <span class="drawer-none">Not set</span>
          <?php elseif ($type === 'html'): ?>
            <?= $v ?>
          <?php elseif ($type === 'long'): ?>
            <?php // Captions use <br> for a line break on the site; show it as one. ?>
            <?= nl2br(esc(str_ireplace(['<br>', '<br/>', '<br />'], "\n", (string) $v))) ?>
          <?php elseif ($type === 'lines'): ?>
            <ol class="drawer-lines">
              <?php foreach ((array) $v as $line): ?><li><?= esc((string) $line) ?></li><?php endforeach ?>
            </ol>
          <?php elseif ($type === 'code'): ?>
            <code><?= esc((string) $v) ?></code>
          <?php elseif ($type === 'image'): ?>
            <?php $known = isset(timb_manifest()[(string) $v]); ?>
            <?php if ($known): ?>
              <img class="drawer-img" src="<?= esc(img_url((string) $v, '640'), 'attr') ?>" alt="" loading="lazy">
            <?php endif ?>
            <code><?= esc((string) $v) ?></code>
            <?php if (! $known): ?><span class="pill pill-orange">Not in the media library</span><?php endif ?>
          <?php elseif ($type === 'pill'): ?>
            <span class="pill pill-<?= esc($v[1] ?? 'grey', 'attr') ?>"><?= esc($v[0] ?? '') ?></span>
          <?php else: ?>
            <?= nl2br(esc(str_ireplace(['<br>', '<br/>', '<br />'], "\n", (string) $v))) ?>
          <?php endif ?>
        </dd>
      </div>
    <?php endforeach ?>
  </dl>
  <?php if ($actions): ?>
    <div class="drawer-actions"><?= $actions ?></div>
  <?php endif ?>
</template>
