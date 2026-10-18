<?php
/**
 * @var array $item  @var array $row  @var array $folders  @var string $backUrl  @var array $errors
 */
$err = static fn (string $f) => $errors[$f] ?? null;
$val = static fn (string $f, string $fallback) => (string) old($f, $fallback);
?>
<div class="page-header">
  <div>
    <h1>Edit photo</h1>
    <p class="page-sub"><code><?= esc($item['key']) ?></code></p>
  </div>
  <a href="<?= esc($backUrl, 'attr') ?>" class="btn btn-secondary">← Back to Media Library</a>
</div>

<?php if (! empty($errors)): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--muted)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="media-edit">
  <figure class="media-edit-preview">
    <?php if ($item['missing']): ?>
      <div class="media-thumb media-thumb--missing">The file is missing on the server</div>
    <?php else: ?>
      <img src="<?= esc($item['large'], 'attr') ?>" alt="">
    <?php endif ?>
    <figcaption><?= (int) $item['width'] ?> × <?= (int) $item['height'] ?> pixels</figcaption>

    <form method="POST" action="/admin/media/replace/<?= (int) $item['id'] ?>" enctype="multipart/form-data" class="replace-form" style="margin-top:.75rem">
      <?= csrf_field() ?>
      <label class="btn btn-secondary">Replace photo…<input type="file" name="file" accept=".jpg,.jpeg,.png,.webp" data-autosubmit-file hidden></label>
      <p class="field-hint">Keeps the same name, so every page using it shows the new picture.</p>
    </form>
  </figure>

  <div class="card form-card card-pad">
    <form method="POST" action="/admin/media/edit/<?= (int) $item['id'] ?>">
      <?= csrf_field() ?>

      <div class="field">
        <label for="m-title">Display name *</label>
        <input type="text" id="m-title" name="title" value="<?= esc($val('title', $item['title']), 'attr') ?>" required>
        <p class="field-hint">How the photo is listed here in the library.</p>
        <?php if ($err('title')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('title')) ?></p><?php endif ?>
      </div>

      <div class="field">
        <label for="m-alt">Description</label>
        <textarea id="m-alt" name="alt_text" rows="2" maxlength="250" data-counter data-min="20" data-max="125"
                  placeholder="e.g. A TM-Series tube mill forming steel strip into tube on the factory floor"><?= esc($val('alt_text', $item['description'])) ?></textarea>
        <p class="field-hint">Say what the photo shows, in one sentence. It is read aloud to blind visitors and helps the photo appear in Google Images.</p>
        <?php if ($err('alt_text')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('alt_text')) ?></p><?php endif ?>
      </div>

      <div class="field">
        <label for="m-folder">Folder</label>
        <?php if ($item['canMove']): ?>
          <input type="text" id="m-folder" name="folder" value="<?= esc($val('folder', $item['folder']), 'attr') ?>" list="m-folders">
          <datalist id="m-folders"><?php foreach ($folders as $f): ?><option value="<?= esc($f, 'attr') ?>"><?php endforeach ?></datalist>
          <p class="field-hint">Changing the folder moves the photo and updates every machine, banner, article and setting that uses it.</p>
        <?php else: ?>
          <input type="text" id="m-folder" value="<?= esc($item['folder'], 'attr') ?>" disabled>
          <p class="field-hint">This photo is part of a product page or the page design, so it cannot be moved without your developer.</p>
        <?php endif ?>
        <?php if ($err('folder')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('folder')) ?></p><?php endif ?>
      </div>

      <div class="field">
        <label>Used on</label>
        <?php if ($item['uses'] === []): ?>
          <p class="drawer-none" style="font-size:.85rem">Not used anywhere on the website.</p>
        <?php else: ?>
          <ul class="media-uses">
            <?php foreach ($item['uses'] as $u): ?>
              <li class="<?= $u['blocks'] ? 'is-live' : 'is-info' ?>">
                <?= $u['link'] ? '<a href="' . esc($u['link'], 'attr') . '">' . esc($u['label']) . '</a>' : esc($u['label']) ?>
              </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="<?= esc($backUrl, 'attr') ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
