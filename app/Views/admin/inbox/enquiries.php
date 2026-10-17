<?= view('admin/inbox/_tabs', ['tab' => $tab, 'status' => $status, 'counts' => $counts, 'otherNew' => $otherNew]) ?>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>From</th><th>Company</th><th>Interest</th><th>Message</th><th>Received</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--dim);padding:2rem">Nothing here.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr<?= $r['status'] === 'new' ? ' style="font-weight:600"' : '' ?>>
            <td>
              <?= esc($r['name']) ?>
              <div style="font-size:.72rem;color:var(--dim);font-weight:400"><?= esc($r['email']) ?></div>
            </td>
            <td style="font-size:.8rem;font-weight:400"><?= esc($r['company'] !== '' ? $r['company'] : '—') ?></td>
            <td style="font-size:.8rem;font-weight:400"><?= esc($r['interest'] !== '' ? $r['interest'] : '—') ?></td>
            <td style="max-width:22rem;font-size:.8rem;color:var(--dim);font-weight:400"><?= esc(mb_strimwidth((string) $r['message'], 0, 90, '…')) ?></td>
            <td style="font-size:.75rem;color:var(--dim);white-space:nowrap;font-weight:400"><?= esc(date('d M Y, H:i', strtotime((string) $r['created_at']))) ?></td>
            <td>
              <?php if ($r['status'] === 'new'): ?><span class="pill pill-red">New</span>
              <?php elseif ($r['status'] === 'archived'): ?><span class="pill pill-grey">Archived</span>
              <?php else: ?><span class="pill pill-green">Read</span><?php endif ?>
            </td>
            <td><a href="/admin/inbox/message/enquiry/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Open</a></td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
