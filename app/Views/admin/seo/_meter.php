<?php
/**
 * A length bar: red when missing or too short, green when right, amber when
 * Google would cut it off.
 *
 * @var int $length  @var int $min  @var int $max
 */
$tone  = $length === 0 || $length < $min ? 'bad' : ($length > $max ? 'warn' : 'ok');
$fill  = min(100, (int) round($length / max(1, $max) * 100));
$words = ['bad' => $length === 0 ? 'Missing' : 'Too short', 'ok' => 'Good length', 'warn' => 'Too long — gets cut off'];
?>
<div class="meter meter--<?= $tone ?>">
  <div class="meter-bar"><span style="width:<?= $fill ?>%"></span></div>
  <div class="meter-text"><strong><?= esc($words[$tone]) ?></strong> · <?= $length ?> characters (aim for <?= $min ?>–<?= $max ?>)</div>
</div>
