<?php
/** @var App\Models\ListItemModel $list */
$fields  = $list->fields();
$editing = $edit !== null;
$action  = $editing
    ? '/admin/lists/' . $list->key() . '/edit/' . $edit['id']
    : '/admin/lists/' . $list->key() . '/add';

/** One field's current value: old input first, then the row being edited. */
$val = static function (string $name) use ($edit, $fields) {
    $stored = $edit['values'][$name] ?? null;
    $type   = $fields[$name]['type'];

    if ($type === 'lines') {
        $old = old($name);

        return $old !== null ? $old : implode("\n", (array) ($stored ?? []));
    }
    if ($type === 'numbers') {
        $old = old($name);

        return $old !== null ? $old : implode(', ', (array) ($stored ?? []));
    }

    return old($name, $stored ?? '');
};

/** A row summarised for the table: its first two text-ish fields. */
$summary = static function (array $values) use ($fields): array {
    $out = [];
    foreach ($fields as $name => $f) {
        $v = $values[$name] ?? '';
        if (is_array($v)) {
            $v = implode(' · ', $v);
        }
        $out[] = ['label' => $f['label'], 'value' => (string) $v];
    }

    return $out;
};
?>
<div class="page-header">
  <h1><?= esc($list->label()) ?></h1>
  <a href="/admin/lists" class="btn btn-secondary">← All lists</a>
</div>

<p style="margin:0 0 1rem;font-size:.78rem;color:var(--dim)">
  <?= esc($def['description']) ?> Shown on: <strong><?= esc($def['consumer']) ?></strong>.
</p>

<?php if (! empty($errors)): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:3rem">#</th>
          <?php foreach ($fields as $f): ?><th><?= esc($f['label']) ?></th><?php endforeach ?>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="<?= count($fields) + 3 ?>" style="text-align:center;color:var(--dim);padding:2rem">Nothing in this list yet. Add the first item below.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $i => $r): ?>
          <tr<?= $r['is_active'] ? '' : ' style="opacity:.5"' ?>>
            <td style="color:var(--dim);font-size:.78rem"><?= $i + 1 ?></td>
            <?php foreach ($summary($r['values']) as $cell): ?>
              <td style="font-size:.8rem;max-width:22rem"><?= esc(mb_strimwidth(strip_tags($cell['value']), 0, 120, '…')) ?></td>
            <?php endforeach ?>
            <td>
              <?= $r['is_active']
                    ? '<span class="pill pill-green">Shown</span>'
                    : '<span class="pill pill-orange">Hidden</span>' ?>
            </td>
            <td>
              <div class="actions" style="white-space:nowrap">
                <form method="POST" action="/admin/lists/<?= esc($list->key(), 'url') ?>/move/<?= $r['id'] ?>/up" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-secondary" style="padding:.25rem .5rem;font-size:.75rem" title="Move up" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
                </form>
                <form method="POST" action="/admin/lists/<?= esc($list->key(), 'url') ?>/move/<?= $r['id'] ?>/down" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-secondary" style="padding:.25rem .5rem;font-size:.75rem" title="Move down" <?= $i === count($rows) - 1 ? 'disabled' : '' ?>>↓</button>
                </form>
                <a href="/admin/lists/<?= esc($list->key(), 'url') ?>?edit=<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Edit</a>
                <form method="POST" action="/admin/lists/<?= esc($list->key(), 'url') ?>/toggle/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem"><?= $r['is_active'] ? 'Hide' : 'Show' ?></button>
                </form>
                <form method="POST" action="/admin/lists/<?= esc($list->key(), 'url') ?>/delete/<?= $r['id'] ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-danger" style="padding:.3rem .7rem;font-size:.75rem" data-confirm="Delete this item permanently? Hiding it instead keeps it recoverable.">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card form-card card-pad" style="margin-top:1rem">
  <h2 style="font-size:.95rem;margin:0 0 .75rem"><?= $editing ? 'Edit item' : 'Add an item' ?></h2>
  <form method="POST" action="<?= esc($action) ?>">
    <?= csrf_field() ?>
    <?php foreach ($fields as $name => $f): ?>
      <div class="field">
        <label><?= esc($f['label']) ?><?= ! empty($f['required']) ? ' *' : '' ?></label>

        <?php if ($f['type'] === 'textarea'): ?>
          <textarea name="<?= esc($name, 'attr') ?>" rows="3"><?= esc($val($name)) ?></textarea>

        <?php elseif ($f['type'] === 'lines'): ?>
          <textarea name="<?= esc($name, 'attr') ?>" rows="4"><?= esc($val($name)) ?></textarea>

        <?php elseif ($f['type'] === 'select'): ?>
          <select name="<?= esc($name, 'attr') ?>">
            <?php foreach ($f['options'] as $optValue => $optLabel): ?>
              <option value="<?= esc($optValue, 'attr') ?>" <?= (string) $val($name) === (string) $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
            <?php endforeach ?>
          </select>

        <?php elseif ($f['type'] === 'decimal'): ?>
          <input type="number" step="any" name="<?= esc($name, 'attr') ?>" value="<?= esc($val($name)) ?>">

        <?php elseif ($f['type'] === 'number'): ?>
          <input type="number" name="<?= esc($name, 'attr') ?>" value="<?= esc($val($name)) ?>">

        <?php else: ?>
          <input type="text" name="<?= esc($name, 'attr') ?>" value="<?= esc($val($name)) ?>">
        <?php endif ?>

        <?php if (! empty($f['hint'])): ?><p class="field-hint"><?= $f['hint'] /* trusted: authored in Config\Lists */ ?></p><?php endif ?>
        <?php if (! empty($errors[$name])): ?><p class="field-hint" style="color:var(--red)"><?= esc($errors[$name]) ?></p><?php endif ?>
      </div>
    <?php endforeach ?>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add item' ?></button>
      <?php if ($editing): ?>
        <a href="/admin/lists/<?= esc($list->key(), 'url') ?>" class="btn btn-secondary">Cancel</a>
      <?php endif ?>
    </div>
  </form>
</div>
