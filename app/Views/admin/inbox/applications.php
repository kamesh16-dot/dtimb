<?= view('admin/inbox/_tabs', ['tab' => $tab, 'status' => $status, 'counts' => $counts, 'otherNew' => $otherNew]) ?>

<div class="card">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Applicant</th><th>Area</th><th>Experience</th><th>CV</th><th>Received</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--dim);padding:2rem">Nothing here.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr<?= $r['status'] === 'new' ? ' style="font-weight:600"' : '' ?>>
            <td>
              <?= esc($r['name']) ?>
              <div style="font-size:.72rem;color:var(--dim);font-weight:400"><?= esc($r['email']) ?> · <?= esc($r['phone']) ?></div>
            </td>
            <td style="font-size:.8rem;font-weight:400"><?= esc($r['area']) ?></td>
            <td style="font-size:.8rem;font-weight:400"><?= $r['experience'] === null ? '—' : esc(rtrim(rtrim((string) $r['experience'], '0'), '.')) . ' yrs' ?></td>
            <td>
              <?php if ($model->cvFile($r) !== null): ?>
                <a href="/admin/inbox/cv/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.25rem .6rem;font-size:.72rem">Download</a>
              <?php else: ?>
                <span style="font-size:.72rem;color:var(--dim)">Missing</span>
              <?php endif ?>
            </td>
            <td style="font-size:.75rem;color:var(--dim);white-space:nowrap;font-weight:400"><?= esc(date('d M Y, H:i', strtotime((string) $r['created_at']))) ?></td>
            <td>
              <?php if ($r['status'] === 'new'): ?><span class="pill pill-red">New</span>
              <?php elseif ($r['status'] === 'archived'): ?><span class="pill pill-grey">Archived</span>
              <?php else: ?><span class="pill pill-green">Read</span><?php endif ?>
            </td>
            <td><a href="/admin/inbox/message/application/<?= $r['id'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Open</a></td>
          </tr>
        <?php endforeach; endif ?>
      </tbody>
    </table>
  </div>
</div>
