<?php
/**
 * @var array $todo   @var array $sections @var array $tasks
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

    return date('j M Y', $t);
};

/** Audit action => what the person did, as the end of a sentence. */
$did = [
    'machine_create' => 'added a machine',        'machine_update' => 'edited a machine',
    'machine_delete' => 'deleted a machine',      'machine_restore' => 'restored a machine',
    'news_create'    => 'wrote a news article',   'news_update'    => 'edited a news article',
    'news_delete'    => 'deleted a news article', 'news_restore'   => 'restored a news article',
    'page_update'    => 'edited a page',
    'list_create'    => 'added to a list',        'list_update'    => 'edited a list',
    'list_delete'    => 'removed from a list',    'list_reorder'   => 'reordered a list',
    'list_show'      => 'showed a list item',     'list_hide'      => 'hid a list item',
    'media_upload'   => 'uploaded a photo',       'media_rename'   => 'renamed a photo',
    'media_delete'   => 'deleted a photo',
    'redirect_create' => 'added a redirect',      'redirect_update' => 'edited a redirect',
    'redirect_delete' => 'deleted a redirect',
    'seo_settings_update' => 'changed the SEO settings',
    'settings_update'     => 'changed the site settings',
    'user_create'    => 'added a user',           'user_update'    => 'edited a user',
    'user_delete'    => 'removed a user',         'user_restore'   => 'restored a user',
    'user_unlock'    => 'unlocked a user',        'user_password_reset' => 'reset a user’s password',
    'password_change' => 'changed their password',
    'inbox_status'   => 'updated a message',      'inbox_note'     => 'added a note to a message',
];
// Summaries that only repeat what the sentence already says.
$noDetail = ['seo_settings_update', 'settings_update', 'password_change', 'inbox_status', 'inbox_note'];

$icons = [
    'mail'     => '<path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>',
    'person'   => '<path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-3.34 0-10 1.67-10 5v3h20v-3c0-3.33-6.66-5-10-5z"/>',
    'lock'     => '<path d="M18 8h-1V6A5 5 0 007 6v2H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V10a2 2 0 00-2-2zm-6 9a2 2 0 110-4 2 2 0 010 4zm3.1-9H8.9V6a3.1 3.1 0 016.2 0v2z"/>',
    'eye'      => '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>',
    'bell'     => '<path d="M12 22a2 2 0 002-2h-4a2 2 0 002 2zm6-6V11c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 00-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>',
    'search'   => '<path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0a4.5 4.5 0 110-9 4.5 4.5 0 010 9z"/>',
    'gear'     => '<path d="M12 8a4 4 0 100 8 4 4 0 000-8zm9.14 4.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58-2-3.46-2.39.96a7.37 7.37 0 00-1.62-.94L16.5 3h-4l-.36 2.54c-.59.24-1.13.57-1.62.94L8.13 5.5l-2 3.46 2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94L6.13 14l2 3.46 2.39-.96c.5.37 1.04.7 1.62.94L12.5 20h4l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96 2-3.46-2.03-1.58z"/>',
    'news'     => '<path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>',
    'page'     => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
    'image'    => '<path d="M21 19V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2zM8.5 13.5l2.5 3 3.5-4.5 4.5 6H5l3.5-4.5z"/>',
    'list'     => '<path d="M3 5h2v2H3V5zm4 0h14v2H7V5zM3 11h2v2H3v-2zm4 0h14v2H7v-2zm-4 6h2v2H3v-2zm4 0h14v2H7v-2z"/>',
    'settings' => '<path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>',
    'check'    => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>',
    'arrow'    => '<path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>',
    'external' => '<path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>',
];
$icon = static fn (string $name) => '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$name] ?? '') . '</svg>';

$first  = strtok(trim((string) ($adminUser['name'] ?? '')), ' ') ?: 'there';
$urgent = count(array_filter($todo, static fn ($t) => $t['level'] === 'urgent'));
$others = count($todo) - $urgent;

if ($urgent > 0) {
    $summary = $urgent === 1 ? 'One thing needs your attention.' : $urgent . ' things need your attention.';
    if ($others > 0) {
        $summary .= ' There ' . ($others === 1 ? 'is also 1 suggestion' : 'are also ' . $others . ' suggestions') . ' below.';
    }
} elseif ($others > 0) {
    $summary = 'Nothing urgent. ' . ($others === 1 ? 'There is 1 suggestion' : 'There are ' . $others . ' suggestions') . ' to make the site better.';
} else {
    $summary = 'Everything is up to date. No new messages and nothing waiting.';
}
?>
<div class="db">

  <!-- Greeting ------------------------------------------------------------- -->
  <header class="db-hello">
    <div>
      <h1>Welcome back, <?= esc($first) ?></h1>
      <p><?= esc($summary) ?></p>
    </div>
    <a class="btn btn-secondary" href="<?= base_url('/') ?>" target="_blank" rel="noopener">
      <?= $icon('external') ?> View the website
    </a>
  </header>

  <!-- To do ---------------------------------------------------------------- -->
  <section class="db-section" aria-labelledby="db-todo">
    <div class="db-head">
      <h2 id="db-todo">To do <?php if ($todo): ?><span class="db-count"><?= count($todo) ?></span><?php endif ?></h2>
      <p>Things that need you, most important first.</p>
    </div>

    <?php if (empty($todo)): ?>
      <div class="db-allclear">
        <span class="db-ic db-ic--ok"><?= $icon('check') ?></span>
        <div>
          <strong>You’re all caught up</strong>
          <span>No new messages, and everything is published.</span>
        </div>
      </div>
    <?php else: ?>
      <ul class="db-todo">
        <?php foreach ($todo as $t): ?>
          <li class="db-todo-item db-todo-item--<?= esc($t['level'], 'attr') ?>">
            <span class="db-ic db-ic--<?= $t['level'] === 'urgent' ? 'urgent' : 'warn' ?>"><?= $icon($t['icon']) ?></span>
            <div class="db-todo-text">
              <span class="db-tag db-tag--<?= esc($t['level'], 'attr') ?>"><?= $t['level'] === 'urgent' ? 'Needs you' : 'Suggestion' ?></span>
              <strong><?= esc($t['title']) ?></strong>
              <span><?= esc($t['why']) ?></span>
            </div>
            <?php if (! empty($t['link'])): ?>
              <a class="btn <?= $t['level'] === 'urgent' ? 'btn-primary' : 'btn-secondary' ?> db-todo-btn" href="<?= esc($t['link'], 'attr') ?>">
                <?= esc($t['action']) ?>
              </a>
            <?php endif ?>
          </li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </section>

  <!-- Your website --------------------------------------------------------- -->
  <section class="db-section" aria-labelledby="db-site">
    <div class="db-head">
      <h2 id="db-site">Your website</h2>
      <p>Each part of the site, and what you can change there. Click a box to open it.</p>
    </div>

    <div class="db-sections">
      <?php foreach ($sections as $s): ?>
        <a class="db-card" href="<?= esc($s['link'], 'attr') ?>">
          <div class="db-card-top">
            <span class="db-ic db-ic--plain"><?= $icon($s['icon']) ?></span>
            <span class="db-card-num"><?= (int) $s['count'] ?></span>
          </div>
          <strong class="db-card-label"><?= esc($s['label']) ?></strong>
          <span class="db-card-desc"><?= esc($s['desc']) ?></span>
          <span class="db-card-foot">
            <?php if ($s['note'] !== null): ?>
              <span class="db-dot db-dot--<?= esc($s['state'], 'attr') ?>"></span><?= esc($s['note']) ?>
            <?php endif ?>
            <span class="db-card-go">Open <?= $icon('arrow') ?></span>
          </span>
        </a>
      <?php endforeach ?>
    </div>
  </section>

  <!-- Common tasks --------------------------------------------------------- -->
  <section class="db-section" aria-labelledby="db-tasks">
    <div class="db-head">
      <h2 id="db-tasks">What would you like to do?</h2>
      <p>Shortcuts to the jobs people do most often.</p>
    </div>

    <div class="db-tasks">
      <?php foreach ($tasks as $t): ?>
        <a class="db-task" href="<?= esc($t['link'], 'attr') ?>">
          <span class="db-ic db-ic--plain"><?= $icon($t['icon']) ?></span>
          <span>
            <strong><?= esc($t['title']) ?></strong>
            <span><?= esc($t['desc']) ?></span>
          </span>
        </a>
      <?php endforeach ?>
    </div>
  </section>

  <div class="db-split">

    <!-- Latest messages ---------------------------------------------------- -->
    <section class="db-section" aria-labelledby="db-msgs">
      <div class="db-head db-head--row">
        <div>
          <h2 id="db-msgs">Latest messages</h2>
          <p>From the Contact and Careers forms.</p>
        </div>
        <a class="db-link" href="/admin/inbox">Open inbox <?= $icon('arrow') ?></a>
      </div>
      <div class="card">
        <?php if (empty($recent)): ?>
          <p class="db-empty">No messages yet. When someone fills in the Contact or Careers form, it will appear here.</p>
        <?php else: ?>
          <ul class="db-rows">
            <?php foreach ($recent as $m): ?>
              <li>
                <a class="db-row" href="<?= esc($m['link'], 'attr') ?>">
                  <span class="db-row-main">
                    <strong<?= $m['new'] ? ' class="is-new"' : '' ?>><?= esc($m['name']) ?></strong>
                    <?php if ($m['new']): ?><span class="pill pill-red">New</span><?php endif ?>
                    <span class="db-row-sub"><?= esc($m['kind']) ?><?= trim((string) $m['detail']) !== '' ? ' · ' . esc($m['detail']) : '' ?></span>
                  </span>
                  <span class="db-row-when"><?= esc($ago($m['at'])) ?></span>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    </section>

    <!-- Recent activity ---------------------------------------------------- -->
    <section class="db-section" aria-labelledby="db-activity">
      <div class="db-head">
        <h2 id="db-activity">Recent changes</h2>
        <p>Who changed what on the website.</p>
      </div>
      <div class="card">
        <?php if (empty($activity)): ?>
          <p class="db-empty">No changes yet. Every edit made here is listed, with who made it.</p>
        <?php else: ?>
          <ul class="db-rows">
            <?php foreach ($activity as $a): ?>
              <?php
              $action = (string) $a['action'];
              $who    = trim((string) ($a['user_name'] ?? '')) ?: 'Someone';
              $what   = $did[$action] ?? str_replace('_', ' ', $action);
              $detail = in_array($action, $noDetail, true) ? '' : trim((string) $a['summary']);
              ?>
              <li class="db-row">
                <span class="db-row-main">
                  <span><strong><?= esc($who) ?></strong> <?= esc($what) ?></span>
                  <?php if ($detail !== ''): ?><span class="db-row-sub"><?= esc($detail) ?></span><?php endif ?>
                </span>
                <span class="db-row-when"><?= esc($ago($a['created_at'])) ?></span>
              </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    </section>
  </div>

  <p class="db-foot">
    Signed in as <strong><?= esc($adminUser['email'] ?? '') ?></strong> ·
    <?= ($adminUser['role'] ?? 'editor') === 'admin' ? 'Admin — can change everything, including users and settings.' : 'Editor — can change website content.' ?>
  </p>
</div>
