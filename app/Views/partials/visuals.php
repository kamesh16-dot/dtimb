<?php
/**
 * Animated line illustrations used while a machine has no photo.
 * @var string $kind cutter | robot | handling | gauge
 */
?>
<svg class="vz vz-<?= esc($kind, 'attr') ?>" viewBox="0 0 400 360" role="img" aria-label="Illustration" focusable="false">
  <defs><pattern id="vz-grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M20 0H0V20" fill="none" stroke="currentColor" stroke-opacity=".08"/></pattern></defs>
  <rect width="400" height="360" fill="url(#vz-grid)"/>
  <?php if ($kind === 'cutter'): ?>
    <g class="vz-line"><path d="M20 250H380" stroke-dasharray="4 6"/></g>
    <g class="vz-tube-a"><rect x="30" y="170" width="170" height="64" rx="10"/><ellipse cx="200" cy="202" rx="9" ry="32"/><path d="M60 186h110M60 218h110" opacity=".35"/></g>
    <g class="vz-tube-b"><rect x="212" y="170" width="158" height="64" rx="10"/><ellipse cx="212" cy="202" rx="9" ry="32"/><path d="M240 186h110M240 218h110" opacity=".35"/></g>
    <g class="vz-blade"><path d="M178 40h44v70l-22 36-22-36z" class="vz-fill"/><path d="M200 50v70" stroke="#fff" opacity=".7"/></g>
    <g class="vz-sparks"><circle cx="190" cy="168" r="3"/><circle cx="212" cy="164" r="2.5"/><circle cx="200" cy="158" r="2"/></g>
  <?php elseif ($kind === 'robot'): ?>
    <path d="M110 310H290" class="vz-floor"/><rect x="150" y="296" width="100" height="22" rx="4"/>
    <g class="vz-a1"><rect x="188" y="170" width="24" height="132" rx="8"/>
      <g class="vz-a2"><rect x="188" y="96" width="24" height="84" rx="8"/><circle cx="200" cy="176" r="12" class="vz-fill"/>
        <g class="vz-grip"><path d="M184 96h32M190 96v-26M210 96v-26"/></g></g></g>
    <circle cx="200" cy="300" r="12" class="vz-fill"/>
    <g class="vz-part"><rect x="276" y="276" width="46" height="34" rx="4"/><path d="M276 292h46" opacity=".4"/></g>
    <g class="vz-weld"><circle cx="300" cy="268" r="3"/><circle cx="292" cy="262" r="2"/><circle cx="308" cy="262" r="2"/></g>
  <?php elseif ($kind === 'handling'): ?>
    <path d="M60 60H340" class="vz-floor"/><rect x="180" y="52" width="40" height="16" rx="3"/>
    <g class="vz-crane"><path d="M200 68V140"/><path d="M168 176 200 140 232 176"/>
      <g class="vz-bundle"><circle cx="200" cy="230" r="20"/><circle cx="240" cy="230" r="20"/><circle cx="160" cy="230" r="20"/><circle cx="220" cy="195" r="20"/><circle cx="180" cy="195" r="20"/><circle cx="220" cy="265" r="20"/><circle cx="180" cy="265" r="20"/></g></g>
    <path d="M70 320H330" class="vz-floor"/>
  <?php else: ?>
    <g class="vz-ring"><circle cx="200" cy="170" r="120"/><circle cx="200" cy="170" r="92" opacity=".5"/><circle cx="200" cy="170" r="64" opacity=".3"/></g>
    <g class="vz-part"><circle cx="200" cy="170" r="42"/><circle cx="200" cy="170" r="26" opacity=".6"/></g>
    <path class="vz-brk" d="M96 66v-26h26M304 66v-26h-26M96 274v26h26M304 274v26h-26"/>
    <g class="vz-scan"><path d="M92 170H308"/><rect x="92" y="164" width="216" height="12" class="vz-glow"/></g>
    <g class="vz-ok"><circle cx="200" cy="170" r="14" class="vz-fill"/><path d="M193 170l5 6 10-12" stroke="#fff"/></g>
    <path d="M150 330h100" class="vz-floor"/>
  <?php endif; ?>
</svg>
