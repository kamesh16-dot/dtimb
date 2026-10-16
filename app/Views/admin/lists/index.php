<div class="page-header"><h1>Lists Hub</h1></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.875rem">
  <?php
  $lists = [
    ['label' => 'Career Areas',    'url' => '#', 'desc' => 'Manage available career departments'],
    ['label' => 'Industries',      'url' => '#', 'desc' => 'Industry sectors served by TIMB'],
    ['label' => 'Clients',         'url' => '#', 'desc' => 'Client company names & logos'],
    ['label' => 'Hero Slides',     'url' => '#', 'desc' => 'Homepage hero carousel slides'],
    ['label' => 'Stats',           'url' => '#', 'desc' => 'Company milestone numbers'],
    ['label' => 'Pillars',         'url' => '#', 'desc' => 'Core values / mission pillars'],
    ['label' => 'FAQ Items',       'url' => '#', 'desc' => 'Frequently asked questions'],
    ['label' => 'Process Steps',   'url' => '#', 'desc' => 'Manufacturing process timeline'],
    ['label' => 'Network Nodes',   'url' => '#', 'desc' => 'Sales / service network locations'],
    ['label' => 'Disclaimer Points','url' => '#','desc' => 'Legal disclaimer bullet points'],
  ];
  foreach ($lists as $l):
  ?>
  <div class="card card-pad" style="opacity:<?= $l['url'] === '#' ? '.6' : '1' ?>">
    <div style="font-weight:700;font-size:.9rem;margin-bottom:.3rem;color:var(--text)"><?= esc($l['label']) ?></div>
    <div style="font-size:.75rem;color:var(--dim);margin-bottom:.75rem"><?= esc($l['desc']) ?></div>
    <?php if ($l['url'] !== '#'): ?>
      <a href="<?= $l['url'] ?>" class="btn btn-secondary" style="padding:.3rem .7rem;font-size:.75rem">Manage →</a>
    <?php else: ?>
      <span style="font-size:.7rem;color:var(--dim)">Coming soon</span>
    <?php endif ?>
  </div>
  <?php endforeach ?>
</div>
