<?php
/**
 * @var list<array> $items  @var \App\Libraries\Paginator $pager
 * @var int $total  @var int $totalBytes  @var int $unused  @var int $noDesc
 * @var array $filters  @var array $sorts  @var array $shows  @var array $folders
 * @var bool $isAdmin  @var array $errors
 */
$bytes = static function (int $n): string {
    if ($n <= 0)      { return '—'; }
    if ($n < 1024)    { return $n . ' B'; }
    if ($n < 1048576) { return round($n / 1024) . ' KB'; }

    return round($n / 1048576, 1) . ' MB';
};
$filtered = $filters['q'] !== '' || $filters['folder'] !== '' || $filters['show'] !== 'all';
?>
<div class="page-header">
  <div>
    <h1>Media Library</h1>
    <p class="page-sub">Every photo on the website. Click one to see where it is used, describe it, or replace it.</p>
  </div>
  <button type="button" class="btn btn-primary" id="upload-open">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    Upload photos
  </button>
</div>

<!-- Library at a glance -------------------------------------------------- -->
<div class="media-stats">
  <a href="?" class="media-stat"><strong><?= (int) $total ?></strong><span>photos</span></a>
  <div class="media-stat"><strong><?= esc($bytes($totalBytes)) ?></strong><span>on the server</span></div>
  <a href="?show=unused" class="media-stat<?= $unused ? '' : ' is-quiet' ?>"><strong><?= (int) $unused ?></strong><span>not used on any page</span></a>
  <a href="?show=nodesc" class="media-stat<?= $noDesc ? ' is-warn' : ' is-quiet' ?>"><strong><?= (int) $noDesc ?></strong><span>without a description</span></a>
</div>

<!-- Find ------------------------------------------------------------------- -->
<form class="media-filters" method="get" action="" role="search">
  <input type="search" name="q" value="<?= esc($filters['q'], 'attr') ?>" placeholder="Search by name, description or file…" aria-label="Search photos">
  <select name="folder" aria-label="Folder" data-autosubmit>
    <option value="">All folders</option>
    <?php foreach ($folders as $f): ?>
      <option value="<?= esc($f, 'attr') ?>" <?= $filters['folder'] === $f ? 'selected' : '' ?>><?= esc(ucwords(str_replace('-', ' ', $f))) ?></option>
    <?php endforeach ?>
  </select>
  <select name="show" aria-label="Show" data-autosubmit>
    <?php foreach ($shows as $k => $label): ?>
      <option value="<?= $k ?>" <?= $filters['show'] === $k ? 'selected' : '' ?>><?= esc($label) ?></option>
    <?php endforeach ?>
  </select>
  <select name="sort" aria-label="Sort" data-autosubmit>
    <?php foreach ($sorts as $k => $label): ?>
      <option value="<?= $k ?>" <?= $filters['sort'] === $k ? 'selected' : '' ?>>Sort: <?= esc($label) ?></option>
    <?php endforeach ?>
  </select>
  <button type="submit" class="btn btn-secondary">Search</button>
  <?php if ($filtered): ?><a href="?" class="btn btn-secondary">Clear</a><?php endif ?>
</form>

<?php if (empty($items)): ?>
  <div class="card card-pad" style="color:var(--muted);text-align:center;padding:3rem">
    <?= $filtered ? 'No photos match. Try another search, or <a href="?" style="color:#fff;text-decoration:underline">show all photos</a>.' : 'No photos yet. Press <strong>Upload photos</strong> to add some.' ?>
  </div>
<?php else: ?>
  <form method="POST" action="/admin/media/bulk" id="bulk-form">
    <?= csrf_field() ?>

    <div class="bulk-bar" id="bulk-bar" hidden>
      <strong><span id="bulk-count">0</span> selected</strong>
      <label class="bulk-move">
        Move to folder
        <input type="text" name="folder" list="folder-list" placeholder="folder name" aria-label="Folder to move to">
      </label>
      <button type="submit" name="action" value="move" class="btn btn-secondary">Move</button>
      <?php if ($isAdmin): ?>
        <button type="submit" name="action" value="delete" class="btn btn-danger"
                data-confirm="Delete the ticked photos? Photos used on the website are kept automatically.">Delete</button>
      <?php endif ?>
      <button type="button" class="btn btn-secondary" id="bulk-clear">Clear selection</button>
    </div>

    <div class="media-grid">
      <?php foreach ($items as $i): ?>
        <?php
        $blocking = array_filter($i['uses'], static fn ($u) => $u['blocks']);
        $inGallery = array_filter($i['uses'], static fn ($u) => ! $u['blocks']) !== [];
        ?>
        <div class="media-card" data-drawer="photo-<?= $i['id'] ?>" tabindex="0" aria-expanded="false"
             aria-label="<?= esc($i['title'], 'attr') ?>: show details">
          <label class="media-check" title="Select">
            <input type="checkbox" name="ids[]" value="<?= $i['id'] ?>" aria-label="Select <?= esc($i['title'], 'attr') ?>">
          </label>
          <?php if ($i['missing']): ?>
            <div class="media-thumb media-thumb--missing">File missing on the server</div>
          <?php else: ?>
            <img class="media-thumb" src="<?= esc($i['thumb'], 'attr') ?>" alt="" loading="lazy" decoding="async">
          <?php endif ?>
          <div class="media-card-body">
            <strong><?= esc($i['title']) ?></strong>
            <code><?= esc($i['key']) ?></code>
            <div class="media-tags">
              <?php if ($blocking): ?>
                <span class="pill pill-green">On <?= count($blocking) ?> page<?= count($blocking) === 1 ? '' : 's' ?></span>
              <?php elseif ($inGallery): ?>
                <span class="pill pill-grey">Home gallery only</span>
              <?php else: ?>
                <span class="pill pill-grey">Not used</span>
              <?php endif ?>
              <?php if ($i['description'] === ''): ?><span class="pill pill-orange">No description</span><?php endif ?>
            </div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </form>
  <?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'photos', 'one' => 'photo']) ?>
  <p class="table-hint">Click a photo for its details. Tick photos to move or delete several at once.</p>
<?php endif ?>

<datalist id="folder-list">
  <?php foreach ($folders as $f): ?><option value="<?= esc($f, 'attr') ?>"><?php endforeach ?>
</datalist>

<?php foreach ($items as $i): ?>
  <?php
  $usesHtml = '';
  if ($i['uses'] === []) {
      $usesHtml = '<span class="drawer-none">Not used anywhere on the website.</span>';
  } else {
      $usesHtml = '<ul class="media-uses">';
      foreach ($i['uses'] as $u) {
          $label = esc($u['label']);
          $usesHtml .= '<li class="' . ($u['blocks'] ? 'is-live' : 'is-info') . '">'
              . ($u['link'] ? '<a href="' . esc($u['link'], 'attr') . '">' . $label . '</a>' : $label)
              . '</li>';
      }
      $usesHtml .= '</ul>';
  }

  $actions = '<a class="btn btn-primary" href="/admin/media/edit/' . $i['id'] . '">Edit details</a>'
      . '<button type="button" class="btn btn-secondary" data-copy="' . esc($i['key'], 'attr') . '">Copy name</button>'
      . '<form method="POST" action="/admin/media/replace/' . $i['id'] . '" enctype="multipart/form-data" class="replace-form">'
      . csrf_field()
      . '<label class="btn btn-secondary">Replace photo…<input type="file" name="file" accept=".jpg,.jpeg,.png,.webp" data-autosubmit-file hidden></label>'
      . '</form>';

  if ($isAdmin && ! $i['blocks']) {
      $actions .= '<form method="POST" action="/admin/media/delete/' . $i['id'] . '">' . csrf_field()
          . '<button type="submit" class="btn btn-danger" data-confirm="Delete ' . esc($i['key'], 'attr') . ' permanently?'
          . ($i['uses'] !== [] ? ' It will drop out of the home page gallery.' : '') . '">Delete</button></form>';
  }

  $rows = [
      ['label' => 'Photo', 'type' => 'html', 'value' => $i['missing']
          ? '<span class="pill pill-red">The file is missing on the server</span>'
          : '<a href="' . esc($i['large'], 'attr') . '" target="_blank" rel="noopener"><img class="drawer-img" src="' . esc($i['thumb'], 'attr') . '" alt=""></a>'],
      ['label' => 'Name to use elsewhere', 'type' => 'code', 'value' => $i['key']],
      ['label' => 'Description', 'type' => 'html', 'value' => $i['description'] !== ''
          ? esc($i['description'])
          : '<span class="pill pill-orange">No description</span> <span class="drawer-none">Describe what the photo shows — it is read aloud to blind visitors and helps Google Images.</span>'],
      ['label' => 'Used on', 'type' => 'html', 'value' => $usesHtml],
      ['label' => 'Size', 'value' => $i['width'] . ' × ' . $i['height'] . ' pixels · ' . $bytes($i['bytes'])
          . ($i['width'] && $i['width'] < $sharpWidth ? ' — may look soft in large banners' : '')],
      ['label' => 'Folder', 'value' => ucwords(str_replace('-', ' ', $i['folder']))],
      ['label' => 'Original file', 'value' => $i['original']],
      ['label' => 'Added', 'value' => $i['added'] ? date('d M Y', strtotime((string) $i['added'])) : null],
  ];
  if ($i['blocks']) {
      $rows[] = ['label' => 'Delete', 'value' => 'This photo cannot be deleted while it is used on the website. Use “Replace photo” to change it everywhere at once.'];
  }
  ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'photo-' . $i['id'],
      'title'    => $i['title'],
      'subtitle' => $i['key'],
      'rows'     => $rows,
      'actions'  => $actions,
  ]) ?>
<?php endforeach ?>

<!-- Upload ----------------------------------------------------------------- -->
<dialog id="upload-dialog" class="upload-dialog" data-open-on-load="<?= empty($errors) ? '0' : '1' ?>" aria-labelledby="upload-title">
  <form method="POST" action="/admin/media/upload" enctype="multipart/form-data" class="form-card" id="upload-form">
    <?= csrf_field() ?>

    <div class="upload-dialog-head">
      <h2 id="upload-title" style="font-size:1rem;margin:0">Upload photos</h2>
      <button type="button" class="btn btn-secondary" id="upload-close" aria-label="Close" style="padding:.25rem .6rem;font-size:.9rem;line-height:1">&times;</button>
    </div>

    <?php if (! empty($errors)): ?>
      <div class="upload-dialog-errors">
        <strong style="font-size:.85rem">Upload failed:</strong>
        <ul style="margin:.4rem 0 0 1rem;font-size:.8rem;color:var(--muted)">
          <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <div class="upload-dialog-body">
      <label class="dropzone" id="dropzone">
        <input type="file" name="files[]" id="upload-files" accept=".jpg,.jpeg,.png,.webp" multiple required>
        <strong>Drop photos here</strong>
        <span>or click to choose. <?= esc(strtoupper(implode(', ', $accepted))) ?>, up to <?= (int) $maxMb ?> MB each.
          Both display sizes are made for you.</span>
      </label>
      <ul class="upload-list" id="upload-list" hidden></ul>

      <div class="field" style="margin-top:1rem">
        <label for="upload-folder">Folder *</label>
        <input type="text" name="folder" id="upload-folder" list="folder-list" required placeholder="e.g. tube-mill">
        <p class="field-hint">Groups the photos — usually the machine they show.</p>
      </div>

      <div id="single-fields">
        <div class="field-row">
          <div class="field">
            <label for="upload-title-field">Display name</label>
            <input type="text" name="title" id="upload-title-field" placeholder="Taken from the file name">
          </div>
          <div class="field">
            <label for="upload-name">Name to use elsewhere</label>
            <input type="text" name="name" id="upload-name" placeholder="Taken from the file name">
            <p class="field-hint">Becomes <code>folder/name</code>.</p>
          </div>
        </div>
        <div class="field">
          <label for="upload-alt">Description</label>
          <input type="text" name="alt_text" id="upload-alt" maxlength="250" placeholder="e.g. A TM-Series tube mill on the factory floor">
          <p class="field-hint">What the photo shows, in one sentence. Read aloud to blind visitors and used by Google Images.</p>
        </div>
      </div>
      <p class="field-hint" id="multi-note" hidden>Several photos: each is named after its file. Add descriptions afterwards from each photo’s details.</p>
    </div>

    <div class="upload-dialog-foot">
      <button type="submit" class="btn btn-primary" id="upload-submit">Upload</button>
      <button type="button" class="btn btn-secondary" id="upload-cancel">Cancel</button>
    </div>
  </form>
</dialog>

<noscript>
  <style>
    .upload-dialog { display: block; position: static; margin: 1.25rem 0 0; width: auto; box-shadow: none; }
    .bulk-bar { display: flex !important; }
  </style>
</noscript>

<script>
(function () {
  // ── Upload dialog ──
  var dialog = document.getElementById('upload-dialog');
  var open   = document.getElementById('upload-open');
  if (dialog && open && typeof dialog.showModal === 'function') {
    var input  = document.getElementById('upload-files');
    var list   = document.getElementById('upload-list');
    var zone   = document.getElementById('dropzone');
    var single = document.getElementById('single-fields');
    var multi  = document.getElementById('multi-note');
    var submit = document.getElementById('upload-submit');
    var sharp  = <?= (int) $sharpWidth ?>;
    var maxB   = <?= (int) $maxMb ?> * 1048576;

    var show = function () { dialog.showModal(); };
    var hide = function () { dialog.close(); };
    open.addEventListener('click', show);
    document.getElementById('upload-close').addEventListener('click', hide);
    document.getElementById('upload-cancel').addEventListener('click', hide);
    dialog.addEventListener('click', function (e) { if (e.target === dialog) hide(); });
    dialog.addEventListener('keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); hide(); } });

    var size = function (n) { return n < 1048576 ? Math.round(n / 1024) + ' KB' : (n / 1048576).toFixed(1) + ' MB'; };

    // List the chosen files with a thumbnail and any warning, before upload.
    var describe = function () {
      var files = Array.prototype.slice.call(input.files || []);
      list.innerHTML = '';
      list.hidden = files.length === 0;
      single.hidden = files.length > 1;
      multi.hidden  = files.length <= 1;
      submit.textContent = files.length > 1 ? 'Upload ' + files.length + ' photos' : 'Upload';

      files.forEach(function (f) {
        var li  = document.createElement('li');
        var img = document.createElement('img');
        var txt = document.createElement('span');
        img.alt = '';
        txt.textContent = f.name + ' · ' + size(f.size);
        li.append(img, txt);
        list.append(li);

        if (f.size > maxB) {
          li.className = 'is-bad';
          txt.textContent += ' — too large, will be skipped';
          return;
        }
        var url = URL.createObjectURL(f);
        img.onload = function () {
          if (img.naturalWidth < sharp) {
            li.className = 'is-warn';
            txt.textContent += ' — only ' + img.naturalWidth + 'px wide, may look blurry in large banners';
          }
        };
        img.src = url;
      });
    };
    input.addEventListener('change', describe);

    ['dragenter', 'dragover'].forEach(function (t) {
      zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.add('is-over'); });
    });
    ['dragleave', 'drop'].forEach(function (t) {
      zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.remove('is-over'); });
    });
    zone.addEventListener('drop', function (e) {
      if (e.dataTransfer && e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        describe();
      }
    });

    if (dialog.dataset.openOnLoad === '1') show();
  }

  // ── Bulk selection ──
  var form = document.getElementById('bulk-form');
  if (form) {
    var bar   = document.getElementById('bulk-bar');
    var count = document.getElementById('bulk-count');
    var boxes = form.querySelectorAll('input[name="ids[]"]');
    var sync  = function () {
      var n = form.querySelectorAll('input[name="ids[]"]:checked').length;
      count.textContent = n;
      bar.hidden = n === 0;
      boxes.forEach(function (b) { b.closest('.media-card').classList.toggle('is-picked', b.checked); });
    };
    form.addEventListener('change', sync);
    document.getElementById('bulk-clear').addEventListener('click', function () {
      boxes.forEach(function (b) { b.checked = false; });
      sync();
    });
  }
})();
</script>
