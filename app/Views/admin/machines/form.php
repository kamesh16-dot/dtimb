<?php
$err = static fn (string $f) => $errors[$f] ?? null;
$val = static fn (string $f, $fallback = '') => old($f, $row[$f] ?? $fallback);
// After a failed submit, an unticked checkbox is absent from the input, so
// old() would fall back to the default and silently re-tick it.
$resubmit = ! empty($errors);
$check    = static fn (string $f) => $resubmit ? old($f) !== null : ! empty($row[$f] ?? 1);
?>
<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <a href="/admin/machines" class="btn btn-secondary">← Back</a>
</div>

<?php if (! empty($errors)): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="card form-card card-pad">
  <form method="POST" action="/admin/machines/<?= empty($row['id']) ? 'create' : 'edit/' . $row['id'] ?>">
    <?= csrf_field() ?>

    <div class="field-row">
      <div class="field">
        <label>Machine name *</label>
        <input type="text" name="name" value="<?= esc($val('name')) ?>" required>
        <?php if ($err('name')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('name')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>URL slug *</label>
        <input type="text" name="slug" value="<?= esc($val('slug')) ?>" required placeholder="e.g. tube-mill">
        <p class="field-hint">Page address: /machines/<strong><?= esc($val('slug', '…')) ?></strong>. Lowercase letters, numbers and hyphens. Changing it breaks existing links.</p>
        <?php if ($err('slug')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('slug')) ?></p><?php endif ?>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Kind *</label>
        <select name="kind">
          <option value="product"  <?= $val('kind', 'product') === 'product'  ? 'selected' : '' ?>>Product — has a photo and a spec sheet</option>
          <option value="solution" <?= $val('kind', 'product') === 'solution' ? 'selected' : '' ?>>Solution — uses an illustration instead</option>
        </select>
        <p class="field-hint">Products appear in the homepage cards and photo gallery. Solutions appear in the solutions strip.</p>
      </div>
      <div class="field">
        <label>Menu group *</label>
        <input type="text" name="group_name" value="<?= esc($val('group_name')) ?>" list="group-list" required>
        <datalist id="group-list">
          <?php foreach ($groups as $g): ?><option value="<?= esc($g, 'attr') ?>"><?php endforeach ?>
        </datalist>
        <p class="field-hint">Heading in the header Products menu. Type a new name to create a group.</p>
        <?php if ($err('group_name')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('group_name')) ?></p><?php endif ?>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Series label *</label>
        <input type="text" name="series_label" value="<?= esc($val('series_label')) ?>" required placeholder="e.g. TM-Series">
        <?php if ($err('series_label')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('series_label')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>Headline metric *</label>
        <input type="text" name="metric" value="<?= esc($val('metric')) ?>" required placeholder="e.g. 10–254 mm">
        <p class="field-hint">The figure shown on the machine card.</p>
        <?php if ($err('metric')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('metric')) ?></p><?php endif ?>
      </div>
    </div>

    <div class="field">
      <label>Summary</label>
      <textarea name="summary" rows="3" placeholder="One sentence shown under the machine name."><?= esc($val('summary')) ?></textarea>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Photo</label>
        <input type="text" name="image_key" value="<?= esc($val('image_key')) ?>" list="image-list" placeholder="e.g. tube-mill/mill-1" data-media-picker>
        <datalist id="image-list">
          <?php foreach ($images as $k): ?><option value="<?= esc($k, 'attr') ?>"><?php endforeach ?>
        </datalist>
        <p class="field-hint">Press <strong>Choose photo</strong> to pick from the Media Library. Leave blank for solutions.</p>
        <?php if ($err('image_key')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('image_key')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>Illustration</label>
        <select name="visual_key">
          <option value="">None</option>
          <?php foreach ($visuals as $v): ?>
            <option value="<?= esc($v, 'attr') ?>" <?= $val('visual_key') === $v ? 'selected' : '' ?>><?= esc(ucfirst($v)) ?></option>
          <?php endforeach ?>
        </select>
        <p class="field-hint">Animated line drawing used when there is no photo.</p>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Spec sheet</label>
        <select name="spec_sheet">
          <option value="">None</option>
          <?php foreach ($sheets as $s): ?>
            <option value="<?= esc($s, 'attr') ?>" <?= $val('spec_sheet') === $s ? 'selected' : '' ?>><?= esc($s) ?>.html</option>
          <?php endforeach ?>
        </select>
        <p class="field-hint">Downloadable sheet linked from the machine card.</p>
      </div>
      <div class="field">
        <label>Order</label>
        <div style="display:flex;gap:.5rem">
          <input type="number" name="menu_order" value="<?= esc($val('menu_order', 0)) ?>" min="0" title="List order" style="width:100%">
          <input type="number" name="nav_order" value="<?= esc($val('nav_order', 0)) ?>" min="0" title="Menu order" style="width:100%">
        </div>
        <p class="field-hint">Left: order in listings. Right: order in the header menu.</p>
      </div>
    </div>

    <div class="field">
      <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
        <input type="checkbox" name="is_published" value="1" <?= $check('is_published') ? 'checked' : '' ?> style="width:auto">
        Published — visible on the website
      </label>
      <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;margin-top:.5rem">
        <input type="checkbox" name="show_in_menu" value="1" <?= $check('show_in_menu') ? 'checked' : '' ?> style="width:auto">
        Show in the header Products menu
      </label>
    </div>

    <?php if (! empty($row['details_json'])): ?>
      <p class="field-hint" style="margin-top:.75rem">
        This machine also has a full product page (specifications, features, galleries) stored with it.
        That content is not editable here yet and is preserved when you save.
      </p>
    <?php endif ?>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save machine</button>
      <a href="/admin/machines" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
