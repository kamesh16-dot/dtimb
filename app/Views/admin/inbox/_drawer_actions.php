<?php
/**
 * Footer buttons for an inbox message's details drawer.
 *
 * @var string $type  enquiry | application
 * @var array  $row
 */
$base = '/admin/inbox/message/' . $type . '/' . $row['id'];

$moves = match ($row['status']) {
    'new'      => ['read' => 'Mark as read', 'archived' => 'Archive'],
    'archived' => ['read' => 'Move back to inbox'],
    default    => ['archived' => 'Archive'],
};
?>
<a class="btn btn-primary" href="<?= esc($base, 'attr') ?>">Open full message</a>
<?php if (! empty($row['email'])): ?>
  <a class="btn btn-secondary" href="mailto:<?= esc($row['email'], 'attr') ?>">Reply by email</a>
<?php endif ?>
<?php foreach ($moves as $status => $label): ?>
  <form method="POST" action="<?= esc($base . '/' . $status, 'attr') ?>">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-secondary"><?= esc($label) ?></button>
  </form>
<?php endforeach ?>
