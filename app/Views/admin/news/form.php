<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <a href="/admin/news" class="btn btn-secondary">← Back</a>
</div>
<div class="card form-card card-pad">
  <form method="POST" action="/admin/news/<?= empty($row) ? 'create' : 'edit/' . $row['id'] ?>">
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field">
        <label>Title *</label>
        <input type="text" name="title" value="<?= esc($row['title'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label>Slug</label>
        <input type="text" name="slug" value="<?= esc($row['slug'] ?? '') ?>">
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Publish date</label>
        <input type="date" name="published_at" value="<?= esc(substr($row['published_at'] ?? date('Y-m-d'), 0, 10)) ?>">
      </div>
      <div class="field">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;margin-top:1.4rem">
          <input type="checkbox" name="is_published" value="1" <?= !empty($row['is_published']) ? 'checked' : '' ?> style="width:auto">
          Published
        </label>
      </div>
    </div>
    <div class="field">
      <label>Excerpt</label>
      <textarea name="excerpt" rows="3"><?= esc($row['excerpt'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label>Body</label>
      <textarea name="body" rows="12"><?= esc($row['body'] ?? '') ?></textarea>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save article</button>
      <a href="/admin/news" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
