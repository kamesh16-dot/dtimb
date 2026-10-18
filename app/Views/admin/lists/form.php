<?php
/**
 * Add / edit one Lists Hub item. Laid out like the machine form: short fields
 * side by side, long ones full width, the error under the field it is about.
 *
 * @var App\Models\ListItemModel $list
 * @var ?int   $id      null when adding
 * @var array  $values  stored values (decoded) for an edit, defaults for an add
 * @var string $action  @var string $backUrl  @var array $images  @var array $errors
 */
$fields   = $list->fields();
$editing  = $id !== null;
$resubmit = ! empty($errors);

/** One field's current value: what was just submitted first, then what is stored. */
$val = static function (string $name) use ($values, $fields) {
    $stored = $values[$name] ?? null;
    $type   = $fields[$name]['type'];

    if ($type === 'lines') {
        return old($name) ?? implode("\n", (array) ($stored ?? []));
    }
    if ($type === 'numbers') {
        return old($name) ?? implode(', ', (array) ($stored ?? []));
    }

    return old($name, $stored ?? '');
};

// After a failed submit an unticked checkbox is absent from the input, so
// old() alone would fall back to the default and silently re-tick it.
$active = $resubmit ? old('is_active') !== null : ! empty($values['is_active']);

// Short fields pair up two to a row; text areas and line lists take a row each.
$rows = [];
$pair = [];
foreach ($fields as $name => $f) {
    if (in_array($f['type'], ['textarea', 'lines'], true)) {
        if ($pair) { $rows[] = $pair; $pair = []; }
        $rows[] = [$name];
        continue;
    }
    $pair[] = $name;
    if (count($pair) === 2) { $rows[] = $pair; $pair = []; }
}
if ($pair) { $rows[] = $pair; }

$hasImages = false;
?>
<div class="page-header">
  <h1><?= esc($editing ? 'Edit item' : 'Add to ' . $list->label()) ?></h1>
  <a href="<?= esc(admin_url($backUrl), 'attr') ?>" class="btn btn-secondary">← Back to <?= esc($list->label()) ?></a>
</div>

<p style="margin:0 0 1rem;font-size:.78rem;color:var(--dim)">
  <?= esc($def['description']) ?> Shown on: <strong><?= esc($def['consumer']) ?></strong>.
</p>

<?php if ($resubmit): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="card form-card card-pad">
  <form method="POST" action="<?= esc(admin_url($action), 'attr') ?>">
    <?= csrf_field() ?>

    <?php foreach ($rows as $names): ?>
      <div class="<?= count($names) === 2 ? 'field-row' : '' ?>">
        <?php foreach ($names as $name): ?>
          <?php
          $f       = $fields[$name];
          $fid     = 'f-' . $name;
          $isImage = str_ends_with($name, 'image_key');
          $hasImages = $hasImages || $isImage;
          $hint    = $f['hint'] ?? match ($f['type']) {
              'lines'   => 'One per line.',
              'numbers' => 'Separate numbers with commas.',
              default   => null,
          };
          ?>
          <div class="field">
            <label for="<?= esc($fid, 'attr') ?>"><?= esc($f['label']) ?><?= ! empty($f['required']) ? ' *' : '' ?></label>

            <?php if ($f['type'] === 'textarea'): ?>
              <textarea id="<?= esc($fid, 'attr') ?>" name="<?= esc($name, 'attr') ?>" rows="4"<?= ! empty($f['required']) ? ' required' : '' ?>><?= esc($val($name)) ?></textarea>

            <?php elseif ($f['type'] === 'lines'): ?>
              <textarea id="<?= esc($fid, 'attr') ?>" name="<?= esc($name, 'attr') ?>" rows="4"><?= esc($val($name)) ?></textarea>

            <?php elseif ($f['type'] === 'select'): ?>
              <select id="<?= esc($fid, 'attr') ?>" name="<?= esc($name, 'attr') ?>">
                <?php foreach ($f['options'] as $optValue => $optLabel): ?>
                  <option value="<?= esc((string) $optValue, 'attr') ?>" <?= (string) $val($name) === (string) $optValue ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
                <?php endforeach ?>
              </select>

            <?php elseif ($f['type'] === 'decimal' || $f['type'] === 'number'): ?>
              <input type="number" id="<?= esc($fid, 'attr') ?>" name="<?= esc($name, 'attr') ?>"<?= $f['type'] === 'decimal' ? ' step="any"' : '' ?>
                     value="<?= esc((string) $val($name), 'attr') ?>"<?= ! empty($f['required']) ? ' required' : '' ?>>

            <?php else: ?>
              <input type="text" id="<?= esc($fid, 'attr') ?>" name="<?= esc($name, 'attr') ?>"
                     value="<?= esc((string) $val($name), 'attr') ?>"<?= $isImage ? ' list="image-keys" autocomplete="off" data-media-picker' : '' ?><?= ! empty($f['required']) ? ' required' : '' ?>>
            <?php endif ?>

            <?php if ($hint): ?><p class="field-hint"><?= $hint /* trusted: authored in Config\Lists */ ?></p><?php endif ?>
            <?php if ($isImage): ?><p class="field-hint">Press <strong>Choose photo</strong> to pick from the Media Library.</p><?php endif ?>
            <?php if (! empty($errors[$name])): ?><p class="field-hint" style="color:var(--red)"><?= esc($errors[$name]) ?></p><?php endif ?>
          </div>
        <?php endforeach ?>
      </div>
    <?php endforeach ?>

    <?php if ($hasImages): ?>
      <datalist id="image-keys">
        <?php foreach ($images as $k): ?><option value="<?= esc($k, 'attr') ?>"><?php endforeach ?>
      </datalist>
    <?php endif ?>

    <div class="field">
      <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
        <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?> style="width:auto">
        Show on the website
      </label>
      <p class="field-hint">Untick to keep the item here without showing it on the site.</p>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add item' ?></button>
      <a href="<?= esc(admin_url($backUrl), 'attr') ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
