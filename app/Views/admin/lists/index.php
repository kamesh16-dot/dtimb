<div class="page-header"><h1>Lists Hub</h1></div>

<p style="margin:0 0 1rem;font-size:.8rem;color:var(--dim);max-width:46rem">
  The small repeating sets of content behind the site. Editing one here changes
  the page it appears on straight away.
</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:.875rem">
  <?php foreach ($cards as $c): ?>
    <a href="<?= admin_url('lists/') ?><?= esc($c['key'], 'url') ?>" class="card card-pad" style="display:block;text-decoration:none;color:inherit">
      <div style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem">
        <span style="font-weight:700;font-size:.9rem;color:var(--text)"><?= esc($c['label']) ?></span>
        <?php if ($c['count'] === null): ?>
          <span class="pill pill-red">Unreadable</span>
        <?php else: ?>
          <span class="pill pill-grey"><?= (int) $c['count'] ?></span>
        <?php endif ?>
      </div>
      <div style="font-size:.75rem;color:var(--dim);margin:.3rem 0 .5rem"><?= esc($c['description']) ?></div>
      <div style="font-size:.7rem;color:var(--dim)">
        <?= esc($c['consumer']) ?>
        <?php if ($c['count'] !== null && $c['active'] < $c['count']): ?>
          · <?= (int) ($c['count'] - $c['active']) ?> hidden
        <?php endif ?>
      </div>
    </a>
  <?php endforeach ?>
</div>
