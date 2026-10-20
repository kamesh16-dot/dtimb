<?php
/**
 * Footer buttons for an inbox message's details drawer.
 *
 * @var string $type  enquiry | application
 * @var array  $row
 * @var array  $adminUser  passed through so Delete shows for admins only
 */
$base = admin_url('inbox/message/') . $type . '/' . $row['id'];

$moves = match ($row['status']) {
    'new'      => ['read' => 'Mark as read', 'archived' => 'Archive'],
    'archived' => ['read' => 'Move back to inbox'],
    default    => ['archived' => 'Archive'],
};
?>
<a class="btn btn-primary" href="<?= esc($base, 'attr') ?>">Open full message</a>
<?php if (! empty($row['email'])): ?>
  <a class="btn btn-secondary" href="<?= esc($base, 'attr') ?>#reply">Reply</a>
<?php endif ?>
<?php foreach ($moves as $status => $label): ?>
  <form method="POST" action="<?= esc($base . '/' . $status, 'attr') ?>">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-secondary"><?= esc($label) ?></button>
  </form>
<?php endforeach ?>
<?php if (($adminUser['role'] ?? '') === 'admin'): ?>
  <form method="POST" action="<?= admin_url('inbox/delete/') ?><?= esc($type, 'url') ?>/<?= (int) $row['id'] ?>" style="display:inline">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-danger" data-confirm="Delete this <?= $type === 'application' ? 'application and its CV' : 'message' ?> permanently? This cannot be undone.">Delete</button>
  </form>
<?php endif ?>
