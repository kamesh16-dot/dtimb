<?php
$bytes = static function (int $n): string {
    if ($n <= 0)        { return '—'; }
    if ($n < 1024)      { return $n . ' B'; }
    if ($n < 1048576)   { return round($n / 1024) . ' KB'; }

    return round($n / 1048576, 1) . ' MB';
};
$used = array_filter($items, static fn ($i) => $i['usedBy'] !== []);
?>
<div class="page-header">
  <h1>Media Library</h1>
  <button type="button" class="btn btn-primary" id="upload-open">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    Upload an image
  </button>
</div>

<p style="margin:0 0 1rem;font-size:.78rem;color:var(--dim)">
  <?= count($items) ?> images, <?= count($used) ?> in use.
  Each one is stored at two sizes. Refer to an image elsewhere in the CMS by its name, shown under each thumbnail.
</p>

<?php if (empty($items)): ?>
  <div class="card card-pad" style="color:var(--dim);text-align:center;padding:3rem">No images yet.</div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:.875rem">
    <?php foreach ($items as $i): ?>
      <div class="card" style="overflow:hidden;display:flex;flex-direction:column">
        <?php if ($i['missing']): ?>
          <div style="height:130px;display:flex;align-items:center;justify-content:center;color:var(--red);font-size:.75rem;text-align:center;padding:.5rem">File missing<br>on disk</div>
        <?php else: ?>
          <img src="<?= esc($i['thumb']) ?>" alt="<?= esc($i['title'], 'attr') ?>" loading="lazy" decoding="async" style="width:100%;height:130px;object-fit:cover;background:#111">
        <?php endif ?>

        <div style="padding:.6rem .75rem;flex:1;display:flex;flex-direction:column;gap:.35rem">
          <div style="font-size:.78rem;font-weight:600;color:var(--text)"><?= esc($i['title']) ?></div>
          <code style="font-size:.68rem;color:var(--dim);word-break:break-all"><?= esc($i['key']) ?></code>
          <div style="font-size:.68rem;color:var(--dim)">
            <?= $i['width'] ?>×<?= $i['height'] ?> · <?= esc($bytes($i['bytes'])) ?>
            <?php if ($i['variants']): ?> · <?= esc(implode(', ', $i['variants'])) ?><?php endif ?>
          </div>

          <?php if ($i['usedBy']): ?>
            <div style="font-size:.68rem;color:var(--dim)">
              <span class="pill pill-green">In use</span>
              <?= esc(implode(', ', array_slice($i['usedBy'], 0, 2))) ?><?= count($i['usedBy']) > 2 ? ' +' . (count($i['usedBy']) - 2) : '' ?>
            </div>
          <?php else: ?>
            <div style="font-size:.68rem"><span class="pill pill-grey">Unused</span></div>
          <?php endif ?>

          <details style="margin-top:auto">
            <summary style="font-size:.7rem;color:var(--dim);cursor:pointer">Rename</summary>
            <form method="POST" action="/admin/media/rename/<?= $i['id'] ?>" style="margin-top:.4rem">
              <?= csrf_field() ?>
              <input type="text" name="title" value="<?= esc($i['title'], 'attr') ?>" style="font-size:.72rem">
              <button type="submit" class="btn btn-secondary" style="padding:.25rem .6rem;font-size:.7rem;margin-top:.35rem">Save</button>
            </form>
          </details>

          <?php if (! $i['usedBy']): ?>
            <form method="POST" action="/admin/media/delete/<?= $i['id'] ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger" style="padding:.22rem .6rem;font-size:.7rem" data-confirm="Delete '<?= esc($i['key'], 'attr') ?>' and its files permanently?">Delete</button>
            </form>
          <?php endif ?>
        </div>
      </div>
    <?php endforeach ?>
  </div>
<?php endif ?>

<dialog id="upload-dialog" class="upload-dialog" data-open-on-load="<?= empty($errors) ? '0' : '1' ?>" aria-labelledby="upload-title">
  <form method="POST" action="/admin/media/upload" enctype="multipart/form-data" class="form-card">
    <?= csrf_field() ?>

    <div class="upload-dialog-head">
      <h2 id="upload-title" style="font-size:1rem;margin:0">Upload an image</h2>
      <button type="button" class="btn btn-secondary" id="upload-close" aria-label="Close" style="padding:.25rem .6rem;font-size:.9rem;line-height:1">&times;</button>
    </div>

    <?php if (! empty($errors)): ?>
      <div class="upload-dialog-errors">
        <strong style="font-size:.85rem">Upload failed:</strong>
        <ul style="margin:.4rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
          <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <div class="upload-dialog-body">
      <div class="field">
        <label>Image file *</label>
        <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp" required>
        <p class="field-hint">
          <?= esc(strtoupper(implode(', ', $accepted))) ?>, up to <?= (int) $maxMb ?> MB.
          Both display sizes are generated for you.
        </p>
      </div>
      <div class="field-row">
        <div class="field">
          <label>Folder *</label>
          <input type="text" name="folder" list="folder-list" required placeholder="e.g. tube-mill">
          <datalist id="folder-list">
            <?php foreach ($folders as $f): ?><option value="<?= esc($f, 'attr') ?>"><?php endforeach ?>
          </datalist>
          <p class="field-hint">Groups the image. Usually the machine it belongs to.</p>
        </div>
        <div class="field">
          <label>File name</label>
          <input type="text" name="name" placeholder="Taken from the file name">
          <p class="field-hint">Together these form the name you refer to it by: <code>folder/file-name</code>.</p>
        </div>
      </div>
      <div class="field">
        <label>Display name</label>
        <input type="text" name="title" placeholder="Taken from the file name">
        <p class="field-hint">Shown in this library and used to describe the image on the home page gallery.</p>
      </div>
    </div>

    <div class="upload-dialog-foot">
      <button type="submit" class="btn btn-primary">Upload</button>
      <button type="button" class="btn btn-secondary" id="upload-cancel">Cancel</button>
    </div>
  </form>
</dialog>

<style>
  .upload-dialog {
    /* Centre it: the UA stylesheet's auto margins only centre a dialog whose
       inset is zeroed, and the admin stylesheet resets margins. */
    position: fixed;
    inset: 0;
    margin: auto;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--steel);
    color: var(--text);
    padding: 0;
    width: min(620px, calc(100vw - 2rem));
    max-height: calc(100vh - 4rem);
    overflow: auto;
    box-shadow: 0 24px 60px rgba(0,0,0,.55);
  }
  .upload-dialog::backdrop { background: rgba(0,0,0,.6); }
  .upload-dialog-head,
  .upload-dialog-foot {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1rem 1.25rem;
  }
  .upload-dialog-head { justify-content: space-between; border-bottom: 1px solid var(--border); }
  .upload-dialog-foot { border-top: 1px solid var(--border); }
  .upload-dialog-body { padding: 1.25rem; }
  .upload-dialog-errors {
    margin: 1rem 1.25rem -.25rem;
    padding: .75rem 1rem;
    border: 1px solid var(--red);
    border-radius: 8px;
  }
  /* Without scripting the dialog can never be opened, so show it in place. */
</style>
<noscript>
  <style>
    .upload-dialog { display: block; position: static; margin: 1.25rem 0 0; width: auto; box-shadow: none; }
  </style>
</noscript>

<script>
(function () {
  var dialog = document.getElementById('upload-dialog');
  var open   = document.getElementById('upload-open');
  if (!dialog || !open || typeof dialog.showModal !== 'function') return;

  function show() { dialog.showModal(); dialog.querySelector('input[name="file"]').focus(); }
  function hide() { dialog.close(); }

  open.addEventListener('click', show);
  document.getElementById('upload-close').addEventListener('click', hide);
  document.getElementById('upload-cancel').addEventListener('click', hide);

  // Clicking the backdrop closes it; clicking the card does not.
  dialog.addEventListener('click', function (e) {
    if (e.target === dialog) hide();
  });

  // Escape is handled by the browser for a modal dialog, but not every
  // browser does it while focus sits in a file input.
  dialog.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { e.preventDefault(); hide(); }
  });

  // Reopen straight away when the server sent the form back with errors.
  if (dialog.dataset.openOnLoad === '1') show();
})();
</script>
