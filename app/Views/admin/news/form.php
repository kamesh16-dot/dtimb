<?php
$err = static fn (string $f) => $errors[$f] ?? null;
$val = static fn (string $f, $fallback = '') => old($f, $row[$f] ?? $fallback);
// After a failed submit, an unticked checkbox is absent from the input, so
// old() would fall back to the default and silently re-tick it.
$resubmit = ! empty($errors);
$check    = static fn (string $f) => $resubmit ? old($f) !== null : ! empty($row[$f] ?? 1);

// Gallery rows: repopulate from the failed submit when there is one.
$rows = [];
if ($resubmit && is_array(old('gallery_path'))) {
    foreach (old('gallery_path') as $i => $p) {
        $rows[] = ['image_path' => $p, 'alt' => old('gallery_alt')[$i] ?? ''];
    }
} else {
    $rows = $gallery;
}
?>
<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <a href="<?= admin_url('news') ?>" class="btn btn-secondary">← Back</a>
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
  <form method="POST" action="<?= admin_url('news/') ?><?= empty($row['id']) ? 'create' : 'edit/' . $row['id'] ?>">
    <?= csrf_field() ?>

    <div class="field-row">
      <div class="field">
        <label>Headline *</label>
        <input type="text" name="title" value="<?= esc($val('title')) ?>" required>
        <?php if ($err('title')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('title')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>URL slug *</label>
        <input type="text" name="slug" value="<?= esc($val('slug')) ?>" required placeholder="e.g. imtex-forming-2020">
        <p class="field-hint">Identifies the article. Lowercase letters, numbers and hyphens.</p>
        <?php if ($err('slug')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('slug')) ?></p><?php endif ?>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Date *</label>
        <input type="date" name="published_on" value="<?= esc($val('published_on', date('Y-m-d'))) ?>" required>
        <p class="field-hint">Shown beside the headline. Newest articles appear first.</p>
        <?php if ($err('published_on')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('published_on')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>Tag</label>
        <input type="text" name="tag" value="<?= esc($val('tag')) ?>" placeholder="e.g. Events">
        <p class="field-hint">Small label above the headline.</p>
      </div>
    </div>

    <div class="field">
      <label>Summary</label>
      <textarea name="summary" rows="3" placeholder="The teaser shown before a reader opens the article."><?= esc($val('summary')) ?></textarea>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Main photo</label>
        <input type="text" name="hero_path" value="<?= esc($val('hero_path')) ?>" placeholder="e.g. news/imtex-2020-booth" data-media-picker>
        <p class="field-hint">Press <strong>Choose photo</strong> to pick from the Media Library.</p>
      </div>
      <div class="field">
        <label>Hero image description</label>
        <input type="text" name="hero_alt" value="<?= esc($val('hero_alt')) ?>">
        <p class="field-hint">Read aloud by screen readers. Describe what the photo shows.</p>
      </div>
    </div>

    <div class="field">
      <label>Opening paragraph</label>
      <textarea name="intro" rows="3" placeholder="Styled larger than the rest of the article."><?= esc($val('intro')) ?></textarea>
    </div>

    <div class="field">
      <label>Pull quote</label>
      <textarea name="quote" rows="3" placeholder="Shown as a highlighted quote. Leave blank for none."><?= esc($val('quote')) ?></textarea>
    </div>

    <div class="field">
      <label>Article body</label>
      <textarea name="body" rows="12" placeholder="Leave a blank line between paragraphs."><?= esc(old('body', $bodyText)) ?></textarea>
      <p class="field-hint">Separate paragraphs with a blank line. Each becomes its own paragraph on the page.</p>
    </div>

    <div class="field">
      <label>Photo gallery</label>
      <p class="field-hint" style="margin-top:0">Shown as a row of photos inside the article. Paths work like the hero image above.</p>
      <div id="gallery-rows">
        <?php foreach ($rows as $g): ?>
          <div class="field-row gallery-row" style="margin-bottom:.5rem">
            <div class="field"><input type="text" name="gallery_path[]" value="<?= esc($g['image_path']) ?>" placeholder="news/photo-1" data-media-picker></div>
            <div class="field" style="display:flex;gap:.5rem">
              <input type="text" name="gallery_alt[]" value="<?= esc($g['alt'] ?? '') ?>" placeholder="What the photo shows" style="width:100%">
              <button type="button" class="btn btn-secondary gallery-remove" style="padding:.3rem .7rem;font-size:.75rem" title="Remove this photo">×</button>
            </div>
          </div>
        <?php endforeach ?>
      </div>
      <button type="button" id="gallery-add" class="btn btn-secondary" style="padding:.35rem .8rem;font-size:.78rem">+ Add photo</button>
    </div>

    <div class="field-row">
      <div class="field">
        <label>Sort order</label>
        <input type="number" name="sort_order" value="<?= esc($val('sort_order', 0)) ?>" min="0">
        <p class="field-hint">Only used to break a tie between articles sharing a date.</p>
      </div>
      <div class="field">
        <label>Visibility</label>
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
          <input type="checkbox" name="is_published" value="1" <?= $check('is_published') ? 'checked' : '' ?> style="width:auto">
          Published — visible on the News page
        </label>
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save article</button>
      <a href="<?= admin_url('news') ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<script>
(function () {
  var rows = document.getElementById('gallery-rows');
  var add  = document.getElementById('gallery-add');
  if (!rows || !add) return;

  add.addEventListener('click', function () {
    var row = document.createElement('div');
    row.className = 'field-row gallery-row';
    row.style.marginBottom = '.5rem';
    row.innerHTML =
      '<div class="field"><input type="text" name="gallery_path[]" placeholder="news/photo-1" data-media-picker></div>' +
      '<div class="field" style="display:flex;gap:.5rem">' +
      '<input type="text" name="gallery_alt[]" placeholder="What the photo shows" style="width:100%">' +
      '<button type="button" class="btn btn-secondary gallery-remove" style="padding:.3rem .7rem;font-size:.75rem" title="Remove this photo">×</button>' +
      '</div>';
    rows.appendChild(row);
    row.querySelector('input').focus();
  });

  rows.addEventListener('click', function (e) {
    if (e.target.classList.contains('gallery-remove')) {
      e.target.closest('.gallery-row').remove();
    }
  });
})();
</script>
