<?= view('admin/inbox/_tabs', ['tab' => $tab, 'status' => $status, 'counts' => $counts, 'otherNew' => $otherNew]) ?>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Applicant</th><th>Area</th><th>Experience</th><th>Received</th><th>Status</th><th><span class="sr-only">Details</span></th></tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:2rem">Nothing here.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php $years = $r['experience'] === null ? null : rtrim(rtrim((string) $r['experience'], '0'), '.') . ' yrs'; ?>
          <tr data-drawer="application-<?= $r['id'] ?>" tabindex="0" aria-expanded="false"
              aria-label="Application from <?= esc($r['name'], 'attr') ?>: show all details"<?= $r['status'] === 'new' ? ' style="font-weight:600"' : '' ?>>
            <td>
              <?= esc($r['name']) ?>
              <div style="font-size:.72rem;color:var(--dim);font-weight:400"><?= esc($r['email']) ?></div>
            </td>
            <td style="font-size:.8rem;font-weight:400"><?= esc($r['area']) ?></td>
            <td style="font-size:.8rem;font-weight:400"><?= $years === null ? '—' : esc($years) ?></td>
            <td style="font-size:.75rem;color:var(--dim);white-space:nowrap;font-weight:400"><?= esc(date('d M Y, H:i', strtotime((string) $r['created_at']))) ?></td>
            <td>
              <?php if ($r['status'] === 'new'): ?><span class="pill pill-red">New</span>
              <?php elseif ($r['status'] === 'archived'): ?><span class="pill pill-grey">Archived</span>
              <?php else: ?><span class="pill pill-green">Read</span><?php endif ?>
            </td>
            <td class="row-more" aria-hidden="true">›</td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
<?php if (! empty($rows)): ?><p class="table-hint">Click an application to see it in full and download the CV.</p><?php endif ?>

<?php foreach ($rows as $r): ?>
  <?php
  $hasCv = $model->cvFile($r) !== null;
  $cv    = $hasCv
      ? '<a class="btn btn-secondary" href="/admin/inbox/cv/' . $r['id'] . '">Download CV</a>'
      : '<span class="pill pill-orange">The file is no longer on the server</span>';
  ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'application-' . $r['id'],
      'title'    => $r['name'],
      'subtitle' => 'Job application · ' . date('d M Y, H:i', strtotime((string) $r['created_at'])),
      'rows'     => [
          ['label' => 'Area of interest', 'value' => $r['area']],
          ['label' => 'Experience', 'value' => $r['experience'] === null ? null : rtrim(rtrim((string) $r['experience'], '0'), '.') . ' years'],
          ['label' => 'Email',      'value' => $r['email']],
          ['label' => 'Phone',      'value' => $r['phone']],
          ['label' => 'Message',    'value' => $r['message'], 'type' => 'long'],
          ['label' => 'CV',         'value' => $cv, 'type' => 'html'],
          ['label' => 'Status',     'value' => match ($r['status']) { 'new' => ['New — not opened yet', 'red'], 'archived' => ['Archived', 'grey'], default => ['Read', 'green'] }, 'type' => 'pill'],
      ],
      'actions' => view('admin/inbox/_drawer_actions', ['type' => 'application', 'row' => $r]),
  ]) ?>
<?php endforeach ?>
<?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'applications', 'one' => 'application']) ?>
