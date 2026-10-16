<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <a href="/admin/machines" class="btn btn-secondary">← Back</a>
</div>

<div class="card form-card card-pad">
  <form method="POST" action="/admin/machines/<?= empty($row) ? 'create' : 'edit/' . $row['id'] ?>">
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field">
        <label>Machine name *</label>
        <input type="text" name="name" value="<?= esc($row['name'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label>URL slug *</label>
        <input type="text" name="slug" value="<?= esc($row['slug'] ?? '') ?>" placeholder="e.g. cnc-lathe-400">
        <p class="field-hint">Used in URL: /machines/{slug}</p>
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Category</label>
        <input type="text" name="category" value="<?= esc($row['category'] ?? '') ?>">
      </div>
      <div class="field">
        <label>Sort order</label>
        <input type="number" name="sort_order" value="<?= esc($row['sort_order'] ?? 0) ?>" min="0">
      </div>
    </div>
    <div class="field">
      <label>Summary (short description)</label>
      <textarea name="summary" rows="3"><?= esc($row['summary'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label>Full description</label>
      <textarea name="description" rows="8"><?= esc($row['description'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
        <input type="checkbox" name="is_featured" value="1" <?= !empty($row['is_featured']) ? 'checked' : '' ?> style="width:auto">
        Feature this machine on the homepage
      </label>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save machine</button>
      <a href="/admin/machines" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
