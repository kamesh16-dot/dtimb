<?php
/** @var string $tab  @var ?string $status  @var array $counts  @var int $otherNew */
$base = $tab === 'applications' ? admin_url('inbox/applications') : admin_url('inbox');
$filters = ['new' => 'New', 'read' => 'Read', 'archived' => 'Archived', '' => 'All'];
?>
<div class="page-header">
  <h1>Inbox</h1>
  <div style="display:flex;gap:.5rem">
    <a href="<?= admin_url('inbox') ?>" class="btn <?= $tab === 'enquiries' ? 'btn-primary' : 'btn-secondary' ?>">
      Enquiries<?= $tab !== 'enquiries' && $otherNew > 0 ? ' (' . (int) $otherNew . ')' : '' ?>
    </a>
    <?php if ((session('admin_user')['role'] ?? '') === 'admin'): // applications carry CVs: admins only ?>
    <a href="<?= admin_url('inbox/applications') ?>" class="btn <?= $tab === 'applications' ? 'btn-primary' : 'btn-secondary' ?>">
      Applications<?= $tab !== 'applications' && $otherNew > 0 ? ' (' . (int) $otherNew . ')' : '' ?>
    </a>
    <?php endif ?>
  </div>
</div>

<div style="display:flex;gap:.4rem;margin-bottom:.9rem;flex-wrap:wrap">
  <?php foreach ($filters as $key => $label): ?>
    <?php $on = (string) ($status ?? '') === (string) $key; ?>
    <a href="<?= $base ?><?= $key === '' ? '?status=all' : '?status=' . $key ?>"
       class="btn <?= $on ? 'btn-primary' : 'btn-secondary' ?>"
       style="padding:.3rem .75rem;font-size:.78rem">
      <?= esc($label) ?> (<?= (int) ($counts[$key === '' ? 'all' : $key] ?? 0) ?>)
    </a>
  <?php endforeach ?>
</div>
