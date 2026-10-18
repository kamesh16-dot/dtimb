<?php
/** @var string $type  @var array $row */
$isApplication = $type === 'application';
$backUrl = $isApplication ? admin_url('inbox/applications') : admin_url('inbox');
$base    = admin_url('inbox/message/') . $type . '/' . $row['id'];

$detail = $isApplication
    ? ['Email' => $row['email'], 'Phone' => $row['phone'], 'Area' => $row['area'],
       'Experience' => $row['experience'] === null ? '—' : rtrim(rtrim((string) $row['experience'], '0'), '.') . ' years']
    : ['Email' => $row['email'], 'Phone' => $row['phone'] !== '' ? $row['phone'] : '—',
       'Company' => $row['company'] !== '' ? $row['company'] : '—',
       'Interest' => $row['interest'] !== '' ? $row['interest'] : '—'];
?>
<div class="page-header">
  <h1><?= esc($row['name']) ?></h1>
  <a href="<?= $backUrl ?>" class="btn btn-secondary">← Back to inbox</a>
</div>

<div class="card card-pad">
  <div style="display:flex;justify-content:space-between;align-items:baseline;gap:1rem;flex-wrap:wrap">
    <div style="font-size:.78rem;color:var(--dim)">
      Received <?= esc(date('d M Y \a\t H:i', strtotime((string) $row['created_at']))) ?>
    </div>
    <div>
      <?php if ($row['status'] === 'archived'): ?><span class="pill pill-grey">Archived</span>
      <?php else: ?><span class="pill pill-green">Read</span><?php endif ?>
    </div>
  </div>

  <dl style="display:grid;grid-template-columns:auto 1fr;gap:.4rem 1rem;margin:1rem 0 0;font-size:.85rem">
    <?php foreach ($detail as $label => $value): ?>
      <dt style="color:var(--dim)"><?= esc($label) ?></dt>
      <dd style="margin:0">
        <?php if ($label === 'Email'): ?>
          <a href="mailto:<?= esc($value, 'attr') ?>" style="color:var(--red)"><?= esc($value) ?></a>
        <?php else: ?>
          <?= esc($value) ?>
        <?php endif ?>
      </dd>
    <?php endforeach ?>
  </dl>

  <?php if (trim((string) $row['message']) !== ''): ?>
    <div style="margin-top:1rem">
      <div style="font-size:.75rem;color:var(--dim);margin-bottom:.3rem">Message</div>
      <div style="font-size:.88rem;line-height:1.6;white-space:pre-wrap"><?= esc($row['message']) ?></div>
    </div>
  <?php endif ?>

  <?php if ($isApplication): ?>
    <div style="margin-top:1rem">
      <?php if (! empty($hasCv)): ?>
        <a href="<?= admin_url('inbox/cv/') ?><?= $row['id'] ?>" class="btn btn-secondary">Download CV</a>
      <?php else: ?>
        <p style="font-size:.78rem;color:var(--dim)">No CV file is on the server for this application.</p>
      <?php endif ?>
    </div>
  <?php endif ?>

  <?php if (! $isApplication && (($row['ip'] ?? '') !== '' || ($row['user_agent'] ?? '') !== '')): ?>
    <details style="margin-top:1rem">
      <summary style="font-size:.75rem;color:var(--dim);cursor:pointer">Submission details</summary>
      <div style="font-size:.72rem;color:var(--dim);margin-top:.4rem;word-break:break-all">
        IP <?= esc($row['ip']) ?><?php if (($row['user_agent'] ?? '') !== ''): ?><br><?= esc($row['user_agent']) ?><?php endif ?>
      </div>
    </details>
  <?php endif ?>

  <div class="btn-row" style="margin-top:1.25rem">
    <?php if ($row['status'] !== 'archived'): ?>
      <form method="POST" action="<?= $base ?>/archived" style="display:inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-secondary">Archive</button>
      </form>
    <?php else: ?>
      <form method="POST" action="<?= $base ?>/read" style="display:inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-secondary">Move back to Read</button>
      </form>
    <?php endif ?>
    <form method="POST" action="<?= $base ?>/new" style="display:inline">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-secondary">Mark unread</button>
    </form>
    <a href="mailto:<?= esc($row['email'], 'attr') ?>" class="btn btn-primary">Reply by email</a>
    <?php if (($adminUser['role'] ?? '') === 'admin'): ?>
      <form method="POST" action="<?= admin_url('inbox/delete/') ?><?= esc($type, 'url') ?>/<?= (int) $row['id'] ?>" style="display:inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger" data-confirm="Delete this <?= $type === 'application' ? 'application and its CV' : 'message' ?> permanently? This cannot be undone.">Delete</button>
      </form>
    <?php endif ?>
  </div>
</div>

<?php if (! $isApplication): ?>
  <div class="card form-card card-pad" style="margin-top:1rem">
    <h2 style="font-size:.95rem;margin:0 0 .6rem">Internal note</h2>
    <form method="POST" action="<?= admin_url('inbox/note/') ?><?= $row['id'] ?>">
      <?= csrf_field() ?>
      <div class="field">
        <textarea name="notes" rows="4" placeholder="Only visible here. The sender never sees this."><?= esc($row['notes'] ?? '') ?></textarea>
      </div>
      <div class="btn-row"><button type="submit" class="btn btn-primary">Save note</button></div>
    </form>
  </div>
<?php endif ?>
