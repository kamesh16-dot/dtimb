<?php
$err = static fn (string $f) => $errors[$f] ?? null;
$val = static fn (string $f, $fallback = '') => old($f, $row[$f] ?? $fallback);
$desc = (string) $val('seo_description');
?>
<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <div class="actions">
    <a href="<?= esc($viewUrl) ?>" target="_blank" rel="noopener" class="btn btn-secondary">View page</a>
    <a href="<?= admin_url('pages') ?>" class="btn btn-secondary">← Back</a>
  </div>
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
  <form method="POST" action="<?= admin_url('pages/edit/') ?><?= esc($key, 'url') ?>">
    <?= csrf_field() ?>

    <div class="field-row">
      <div class="field">
        <label>Page name *</label>
        <input type="text" name="title" value="<?= esc($val('title')) ?>" required>
        <p class="field-hint">Used inside the CMS only. Visitors do not see it.</p>
        <?php if ($err('title')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('title')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>Address</label>
        <input type="text" value="/<?= esc($key === 'home' ? '' : $key) ?>" disabled>
        <p class="field-hint">Fixed. Each page has its own route and layout.</p>
      </div>
    </div>

    <div class="field">
      <label>Browser title *</label>
      <p class="field-hint" style="margin:0 0 .4rem">The blue headline people click in Google, and the text on the browser tab.</p>
      <input type="text" name="seo_title" value="<?= esc($val('seo_title')) ?>" required
             data-counter data-min="<?= \App\Libraries\SeoAudit::TITLE_MIN ?>" data-max="<?= \App\Libraries\SeoAudit::TITLE_MAX ?>">
      <?php if ($err('seo_title')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('seo_title')) ?></p><?php endif ?>
    </div>

    <div class="field">
      <label>Search description</label>
      <p class="field-hint" style="margin:0 0 .4rem">The grey sentence under the headline in Google. One or two sentences about what is on the page.</p>
      <textarea name="seo_description" rows="3"
                data-counter data-min="<?= \App\Libraries\SeoAudit::DESCRIPTION_MIN ?>" data-max="<?= \App\Libraries\SeoAudit::DESCRIPTION_MAX ?>"><?= esc($desc) ?></textarea>
      <?php if ($err('seo_description')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('seo_description')) ?></p><?php endif ?>
    </div>

    <div class="field">
      <label>Opening paragraph</label>
      <textarea name="intro" rows="4"><?= esc($val('intro')) ?></textarea>
      <p class="field-hint">
        <?php if ($blockNames): ?>
          The lead paragraph at the top of this page's content.
        <?php else: ?>
          This page draws its content from the list tables, so the opening paragraph is not shown on it yet.
        <?php endif ?>
      </p>
    </div>

    <?php if ($blockNames || trim($blocksText) !== ''): ?>
      <div class="field">
        <label>Content blocks</label>
        <p class="field-hint" style="margin-top:0">
          Advanced. This page's structured content, stored as JSON:
          <?php foreach ($blockNames as $b): ?><code><?= esc($b) ?></code> <?php endforeach ?>
        </p>
        <textarea name="body_blocks" rows="18" spellcheck="false" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.78rem"><?= esc(old('body_blocks', $blocksText)) ?></textarea>
        <p class="field-hint">
          The structure must stay as it is — only change the words inside the quotes.
          Saving is blocked if the JSON is broken, so a mistake cannot take the page down.
        </p>
        <?php if ($err('body_blocks')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('body_blocks')) ?></p><?php endif ?>
      </div>
    <?php endif ?>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save page</button>
      <a href="<?= admin_url('pages') ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
