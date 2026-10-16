<div class="page-header">
  <h1>Media Library</h1>
  <label class="btn btn-primary" style="cursor:pointer" for="fileInput">
    <svg viewBox="0 0 24 24"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
    Upload
  </label>
</div>
<form id="uploadForm" method="POST" action="/admin/media/upload" enctype="multipart/form-data" style="display:none">
  <?= csrf_field() ?>
  <input type="file" id="fileInput" name="file" accept="image/*,.pdf,.svg" onchange="document.getElementById('uploadForm').submit()">
</form>
<?php if (empty($rows)): ?>
  <div class="card card-pad" style="color:var(--dim);text-align:center;padding:3rem">No files uploaded yet.</div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.875rem">
    <?php foreach ($rows as $r):
      $isImage = str_starts_with($r['mime_type'] ?? '', 'image/');
    ?>
    <div class="card" style="overflow:hidden">
      <?php if ($isImage): ?>
        <img src="/uploads/<?= esc($r['filename']) ?>" alt="" style="width:100%;height:120px;object-fit:cover">
      <?php else: ?>
        <div style="height:120px;display:flex;align-items:center;justify-content:center;color:var(--dim);font-size:2rem">📄</div>
      <?php endif ?>
      <div style="padding:.6rem .75rem">
        <p style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($r['original']) ?></p>
        <form method="POST" action="/admin/media/delete/<?= $r['id'] ?>">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger" style="padding:.2rem .6rem;font-size:.7rem;margin-top:.4rem" data-confirm="Delete this file?">Delete</button>
        </form>
      </div>
    </div>
    <?php endforeach ?>
  </div>
<?php endif ?>
