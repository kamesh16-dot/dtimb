<?php
/**
 * Pagination bar for an admin list.
 *
 * @var \App\Libraries\Paginator $pager
 * @var string                   $noun   plural, e.g. "machines"
 * @var string|null              $one    singular, e.g. "machine" (defaults to $noun)
 */
$one ??= $noun;
if ($pager->total === 0) {
    return;
}
?>
<nav class="pager" aria-label="<?= esc(ucfirst($noun), 'attr') ?> pages">
  <p class="pager-info">
    <?php if ($pager->hasPages()): ?>
      Showing <strong><?= $pager->from() ?>–<?= $pager->to() ?></strong> of <strong><?= $pager->total ?></strong> <?= esc($noun) ?>
    <?php else: ?>
      <strong><?= $pager->total ?></strong> <?= esc($pager->total === 1 ? $one : $noun) ?>
    <?php endif ?>
  </p>

  <?php if ($pager->hasPages()): ?>
    <ul class="pager-links">
      <li>
        <?php if ($pager->hasPrevious()): ?>
          <a class="pager-step" href="<?= esc($pager->url($pager->page - 1), 'attr') ?>" rel="prev">‹ <span>Previous</span></a>
        <?php else: ?>
          <span class="pager-step is-off" aria-hidden="true">‹ <span>Previous</span></span>
        <?php endif ?>
      </li>
      <?php foreach ($pager->window() as $p): ?>
        <li>
          <?php if ($p === null): ?>
            <span class="pager-gap" aria-hidden="true">…</span>
          <?php elseif ($p === $pager->page): ?>
            <span class="pager-num is-on" aria-current="page"><?= $p ?></span>
          <?php else: ?>
            <a class="pager-num" href="<?= esc($pager->url($p), 'attr') ?>" aria-label="Page <?= $p ?>"><?= $p ?></a>
          <?php endif ?>
        </li>
      <?php endforeach ?>
      <li>
        <?php if ($pager->hasNext()): ?>
          <a class="pager-step" href="<?= esc($pager->url($pager->page + 1), 'attr') ?>" rel="next"><span>Next</span> ›</a>
        <?php else: ?>
          <span class="pager-step is-off" aria-hidden="true"><span>Next</span> ›</span>
        <?php endif ?>
      </li>
    </ul>
  <?php endif ?>

  <?php if ($pager->canChoosePerPage()): ?>
    <form class="pager-per" method="get" action="">
      <?php // No page number here: a new page size starts again from page 1. ?>
      <?php foreach ($pager->hiddenFields() as $k => $v): ?>
        <input type="hidden" name="<?= esc((string) $k, 'attr') ?>" value="<?= esc((string) $v, 'attr') ?>">
      <?php endforeach ?>
      <label>
        Show
        <select name="<?= esc($pager->perParam, 'attr') ?>" data-autosubmit>
          <?php foreach ($pager->perPageOptions() as $n): ?>
            <option value="<?= $n ?>" <?= $n === $pager->perPage ? 'selected' : '' ?>><?= $n ?></option>
          <?php endforeach ?>
        </select>
        per page
      </label>
      <noscript><button type="submit" class="btn btn-secondary">Go</button></noscript>
    </form>
  <?php endif ?>
</nav>
