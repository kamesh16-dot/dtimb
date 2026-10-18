<?php
/**
 * Pagination for a public list.
 *
 * @var \App\Libraries\Paginator $pager
 * @var string                   $noun  plural, e.g. "articles"
 * @var string|null              $one   singular
 */
$one ??= $noun;
if (! $pager->hasPages()) {
    return;
}
?>
<nav class="pagination" aria-label="<?= esc(ucfirst($noun), 'attr') ?> pages" data-reveal>
  <p class="pagination-info">
    Showing <?= $pager->from() ?>–<?= $pager->to() ?> of <?= $pager->total ?> <?= esc($noun) ?>
  </p>
  <ul>
    <li>
      <?php if ($pager->hasPrevious()): ?>
        <a class="pagination-step" href="<?= esc($pager->url($pager->page - 1), 'attr') ?>" rel="prev">← <span>Newer</span></a>
      <?php else: ?>
        <span class="pagination-step is-off" aria-hidden="true">← <span>Newer</span></span>
      <?php endif; ?>
    </li>
    <?php foreach ($pager->window() as $p): ?>
      <li>
        <?php if ($p === null): ?>
          <span class="pagination-gap" aria-hidden="true">…</span>
        <?php elseif ($p === $pager->page): ?>
          <span class="pagination-num is-on" aria-current="page"><?= $p ?></span>
        <?php else: ?>
          <a class="pagination-num" href="<?= esc($pager->url($p), 'attr') ?>" aria-label="Page <?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
    <li>
      <?php if ($pager->hasNext()): ?>
        <a class="pagination-step" href="<?= esc($pager->url($pager->page + 1), 'attr') ?>" rel="next"><span>Older</span> →</a>
      <?php else: ?>
        <span class="pagination-step is-off" aria-hidden="true"><span>Older</span> →</span>
      <?php endif; ?>
    </li>
  </ul>
</nav>
