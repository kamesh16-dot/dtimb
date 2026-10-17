<?php
/**
 * @var array $inbox  @var array $content  @var array $attention
 * @var array $recent @var array $activity @var array $adminUser
 */
$ago = static function (?string $when): string {
    $t = strtotime((string) $when);
    if ($t === false) {
        return '';
    }
    $mins = max(0, (int) round((time() - $t) / 60));
    if ($mins < 1)    { return 'just now'; }
    if ($mins < 60)   { return $mins . ' min ago'; }
    if ($mins < 1440) { return floor($mins / 60) . ' hr ago'; }
    if ($mins < 2880) { return 'yesterday'; }

    return date('d M Y', $t);
};

/** Turn "machine_update" into "Machine updated". */
$describe = static function (string $action): string {
    $words = [
        'create' => 'added', 'update' => 'updated', 'delete' => 'removed',
        'restore' => 'restored', 'upload' => 'uploaded', 'rename' => 'renamed',
        'reorder' => 'reordered', 'hide' => 'hidden', 'show' => 'shown',
        'login' => 'signed in', 'logout' => 'signed out', 'unlock' => 'unlocked',
        'status' => 'moved', 'note' => 'annotated', 'reset' => 'reset',
    ];
    $parts = explode('_', $action);
    $verb  = $words[end($parts)] ?? end($parts);
    $thing = str_replace('_', ' ', $parts[0]);

    return in_array($action, ['login', 'logout'], true)
        ? ucfirst($verb)
        : ucfirst($thing) . ' ' . $verb;
};

$tone = ['red' => 'pill-red', 'orange' => 'pill-orange', 'green' => 'pill-green'];
?>
<div class="dashboard">

  <!-- What needs doing ---------------------------------------------------- -->
  <h2 class="section-title" style="margin-top:0">Needs attention</h2>
  <?php if (empty($attention)): ?>
    <div class="card card-pad" style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.5rem">
      <span class="pill pill-green">All clear</span>
      <span style="font-size:.85rem;color:var(--dim)">Nothing is waiting. The inbox is empty and everything is published.</span>
    </div>
  <?php else: ?>
    <div class="card" style="margin-bottom:1.5rem">
      <?php foreach ($attention as $i => $item): ?>
        <a href="<?= esc($item['link']) ?>"
           style="display:flex;align-items:center;gap:.75rem;padding:.8rem 1.1rem;text-decoration:none;color:inherit<?= $i ? ';border-top:1px solid var(--border)' : '' ?>">
          <span class="pill <?= $tone[$item['tone']] ?? 'pill-grey' ?>" style="flex:none">&nbsp;</span>
          <span style="font-size:.85rem;color:var(--text);flex:1"><?= esc($item['text']) ?></span>
          <span style="font-size:.78rem;color:var(--dim)">Open →</span>
        </a>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <!-- The site at a glance ------------------------------------------------ -->
  <h2 class="section-title">The site at a glance</h2>
  <div class="stat-grid">
    <a class="stat-card" href="/admin/inbox" style="text-decoration:none;color:inherit">
      <div class="stat-icon stat-icon--red">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= (int) $inbox['enquiriesNew'] + (int) $inbox['applicationsNew'] ?></div>
        <div class="stat-label">Unread messages</div>
        <div style="font-size:.7rem;color:var(--dim)"><?= (int) $inbox['enquiriesTotal'] + (int) $inbox['applicationsAll'] ?> in total</div>
      </div>
    </a>

    <a class="stat-card" href="/admin/machines" style="text-decoration:none;color:inherit">
      <div class="stat-icon stat-icon--green">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zm9.14 4.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58-2-3.46-2.39.96a7.37 7.37 0 00-1.62-.94L16.5 3h-4l-.36 2.54c-.59.24-1.13.57-1.62.94L8.13 5.5l-2 3.46 2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94L6.13 14l2 3.46 2.39-.96c.5.37 1.04.7 1.62.94L12.5 20h4l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96 2-3.46-2.03-1.58z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= (int) $content['machines']['total'] ?></div>
        <div class="stat-label">Machines</div>
        <div style="font-size:.7rem;color:var(--dim)"><?= $content['machines']['draft'] ? (int) $content['machines']['draft'] . ' not published' : 'all published' ?></div>
      </div>
    </a>

    <a class="stat-card" href="/admin/news" style="text-decoration:none;color:inherit">
      <div class="stat-icon stat-icon--blue">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= (int) $content['news']['total'] ?></div>
        <div class="stat-label">News articles</div>
        <div style="font-size:.7rem;color:var(--dim)"><?= $content['news']['draft'] ? (int) $content['news']['draft'] . ' draft' : 'all published' ?></div>
      </div>
    </a>

    <a class="stat-card" href="/admin/media" style="text-decoration:none;color:inherit">
      <div class="stat-icon stat-icon--orange">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2zM8.5 13.5l2.5 3 3.5-4.5 4.5 6H5l3.5-4.5z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= (int) $content['media']['total'] ?></div>
        <div class="stat-label">Images</div>
        <div style="font-size:.7rem;color:var(--dim)"><?= (int) $content['media']['unused'] ?> unused</div>
      </div>
    </a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;margin-top:1.5rem">

    <!-- Latest messages --------------------------------------------------- -->
    <div>
      <h2 class="section-title">Latest messages</h2>
      <div class="card">
        <?php if (empty($recent)): ?>
          <div class="card-pad" style="color:var(--dim);font-size:.82rem">
            No enquiries or applications yet. They arrive here from the contact and careers forms.
          </div>
        <?php else: ?>
          <?php foreach ($recent as $i => $m): ?>
            <a href="<?= esc($m['link']) ?>"
               style="display:flex;align-items:baseline;gap:.75rem;padding:.75rem 1.1rem;text-decoration:none;color:inherit<?= $i ? ';border-top:1px solid var(--border)' : '' ?>">
              <span style="flex:1;min-width:0">
                <span style="font-size:.85rem;color:var(--text);font-weight:<?= $m['new'] ? '600' : '400' ?>"><?= esc($m['name']) ?></span>
                <?php if ($m['new']): ?><span class="pill pill-red">New</span><?php endif ?>
                <span style="display:block;font-size:.72rem;color:var(--dim);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?= esc($m['kind']) ?> · <?= esc($m['detail']) ?>
                </span>
              </span>
              <span style="font-size:.7rem;color:var(--dim);white-space:nowrap"><?= esc($ago($m['at'])) ?></span>
            </a>
          <?php endforeach ?>
        <?php endif ?>
      </div>
    </div>

    <!-- Recent changes ---------------------------------------------------- -->
    <div>
      <h2 class="section-title">Recent changes</h2>
      <div class="card">
        <?php if (empty($activity)): ?>
          <div class="card-pad" style="color:var(--dim);font-size:.82rem">
            Nothing recorded yet. Edits made through the CMS are listed here.
          </div>
        <?php else: ?>
          <?php foreach ($activity as $i => $a): ?>
            <div style="display:flex;align-items:baseline;gap:.75rem;padding:.7rem 1.1rem<?= $i ? ';border-top:1px solid var(--border)' : '' ?>">
              <span style="flex:1;min-width:0">
                <span style="font-size:.84rem;color:var(--text)"><?= esc($describe((string) $a['action'])) ?></span>
                <?php
                // "Signed in" / "Logged in" says the same thing twice.
                $detail = trim((string) $a['summary']);
                if (strcasecmp($detail, $describe((string) $a['action'])) === 0
                    || in_array($a['action'], ['login', 'logout'], true)) {
                    $detail = '';
                }
                ?>
                <?php if ($detail !== ''): ?>
                  <span style="display:block;font-size:.72rem;color:var(--dim);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($detail) ?></span>
                <?php endif ?>
              </span>
              <span style="font-size:.7rem;color:var(--dim);white-space:nowrap"><?= esc($ago($a['created_at'])) ?></span>
            </div>
          <?php endforeach ?>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- Quick actions -------------------------------------------------------- -->
  <h2 class="section-title" style="margin-top:1.5rem">Quick actions</h2>
  <div class="quick-links">
    <a href="/admin/news/create" class="quick-link">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-6 14h-2v-3H8v-2h3V9h2v3h3v2h-3v3z"/></svg>
      Write news
    </a>
    <a href="/admin/machines/create" class="quick-link">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
      Add a machine
    </a>
    <a href="/admin/media" class="quick-link">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2zM8.5 13.5l2.5 3 3.5-4.5 4.5 6H5l3.5-4.5z"/></svg>
      Upload an image
    </a>
    <a href="/admin/lists" class="quick-link">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3V5zm0 6h18v2H3v-2zm0 6h18v2H3v-2z"/></svg>
      Edit a list
    </a>
    <a href="/admin/seo" class="quick-link">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5z"/></svg>
      Check SEO
    </a>
    <a href="<?= base_url('/') ?>" target="_blank" rel="noopener" class="quick-link">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
      View the site
    </a>
  </div>

  <div class="status-note" style="margin-top:1.5rem">
    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
    Signed in as <strong><?= esc($adminUser['email'] ?? '') ?></strong>
    (<?= esc($adminUser['role'] ?? 'editor') ?>).
    <?= (int) $content['pages'] ?> pages ·
    <?= (int) $content['redirects']['total'] ?> redirects<?= $content['redirects']['hits'] ? ', followed ' . (int) $content['redirects']['hits'] . ' times' : '' ?>.
  </div>
</div>
