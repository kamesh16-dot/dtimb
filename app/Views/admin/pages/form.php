<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <a href="/admin/pages" class="btn btn-secondary">← Back</a>
</div>
<div class="card form-card card-pad">
  <form method="POST" action="/admin/pages/edit/<?= esc($slug ?? '') ?>">
    <?= csrf_field() ?>
    <div class="field">
      <label>Meta title</label>
      <input type="text" name="meta_title" value="<?= esc($row['meta_title'] ?? '') ?>" placeholder="TIMB — Page Name">
    </div>
    <div class="field">
      <label>Meta description</label>
      <textarea name="meta_description" rows="3"><?= esc($row['meta_description'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label>Custom content / HTML notes</label>
      <textarea name="content" rows="10"><?= esc($row['content'] ?? '') ?></textarea>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save</button>
      <a href="/admin/pages" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
