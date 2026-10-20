<?= view('admin/inbox/_tabs', ['tab' => $tab, 'status' => $status, 'counts' => $counts, 'otherNew' => $otherNew]) ?>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>From</th><th>Company</th><th>Message</th><th>Received</th><th>Status</th><th><span class="sr-only">Details</span></th></tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:2rem">Nothing here.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr data-drawer="enquiry-<?= $r['id'] ?>" tabindex="0" aria-expanded="false"
              aria-label="Enquiry from <?= esc($r['name'], 'attr') ?>: show the full message"<?= $r['status'] === 'new' ? ' style="font-weight:600"' : '' ?>>
            <td>
              <?= esc($r['name']) ?>
              <div style="font-size:.72rem;color:var(--dim);font-weight:400"><?= esc($r['email']) ?></div>
            </td>
            <td style="font-size:.8rem;font-weight:400"><?= esc($r['company'] !== '' ? $r['company'] : '—') ?></td>
            <td style="font-size:.8rem;color:var(--muted);font-weight:400"><span class="cell-clip"><?= esc((string) $r['message']) ?></span></td>
            <td style="font-size:.75rem;color:var(--dim);white-space:nowrap;font-weight:400"><?= esc(date('d M Y, H:i', strtotime((string) $r['created_at']))) ?></td>
            <td>
              <?php if ($r['status'] === 'new'): ?><span class="pill pill-red">New</span>
              <?php elseif ($r['status'] === 'archived'): ?><span class="pill pill-grey">Archived</span>
              <?php else: ?><span class="pill pill-green">Read</span><?php endif ?>
              <?php if (isset($replied[$r['id']])): ?><span class="pill pill-blue" title="Last replied <?= esc(date('d M Y', strtotime((string) $replied[$r['id']])), 'attr') ?>">Replied</span><?php endif ?>
            </td>
            <td class="row-more" aria-hidden="true">›</td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
<?php if (! empty($rows)): ?><p class="table-hint">Click a message to read it in full and reply, without leaving the list.</p><?php endif ?>

<?php foreach ($rows as $r): ?>
  <?= view('admin/partials/drawer', [
      'id'       => 'enquiry-' . $r['id'],
      'title'    => $r['name'],
      'subtitle' => 'Enquiry · ' . date('d M Y, H:i', strtotime((string) $r['created_at'])),
      'rows'     => [
          ['label' => 'Message',  'value' => $r['message'], 'type' => 'long'],
          ['label' => 'Email',    'value' => $r['email']],
          ['label' => 'Phone',    'value' => $r['phone']],
          ['label' => 'Company',  'value' => $r['company']],
          ['label' => 'Interested in', 'value' => $r['interest']],
          ['label' => 'Status',   'value' => match ($r['status']) { 'new' => ['New — not opened yet', 'red'], 'archived' => ['Archived', 'grey'], default => ['Read', 'green'] }, 'type' => 'pill'],
          ['label' => 'Replied', 'value' => isset($replied[$r['id']]) ? 'Last reply sent ' . date('d M Y, H:i', strtotime((string) $replied[$r['id']])) : 'Not yet'],
          ['label' => 'Internal note', 'value' => $r['notes'] ?? null, 'type' => 'long'],
      ],
      'actions' => view('admin/inbox/_drawer_actions', ['type' => 'enquiry', 'row' => $r, 'adminUser' => $adminUser]),
  ]) ?>
<?php endforeach ?>
<?= view('admin/partials/pagination', ['pager' => $pager, 'noun' => 'enquiries', 'one' => 'enquiry']) ?>
