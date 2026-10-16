<?php
/** @var \Config\Site $site  @var array $c  @var array $all  @var string $machine */
$this->extend('layout');
$this->section('content');
$mail = esc($site->email, 'attr');
$pick = old('interest') ?: ($machine ?? '');
?>
<?= view('partials/page_hero', ['crumb' => 'Contact Us', 'kicker' => 'Contact us', 'heading' => ['Let’s build', 'something', 'together.'], 'lead' => 'Send us a requirement, a drawing or just the problem you want to solve. An engineer will reply.']) ?>

<section class="sec contact-page" id="enquiry" aria-labelledby="ct-h">
  <div class="contact-grid">
    <div class="contact-info">
      <p class="eyebrow" data-reveal>Reach us</p>
      <h2 id="ct-h" class="h2" data-split>Works and corporate office.</h2>
      <ul class="net-places">
        <?php foreach ($site->network as $i => $n): ?>
          <li class="place place-<?= $n['type'] ?>" data-reveal style="--d:<?= $i * 90 ?>ms">
            <span class="place-tag"><i aria-hidden="true"></i><?= esc($n['sub']) ?></span>
            <h3><?= esc($n['name']) ?></h3>
            <p><?= esc($n['address']) ?></p>
            <div class="place-links">
              <?php foreach ($n['phones'] as $ph): ?><a href="tel:+91<?= preg_replace('/\D/', '', $ph) ?>"><?= esc($ph) ?></a><?php endforeach; ?>
              <a class="place-map" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($n['address']) ?>">Open in Google Maps <i aria-hidden="true"></i></a>
            </div>
          </li>
        <?php endforeach; ?>
        <li class="place place-email" data-reveal style="--d:200ms">
          <span class="place-tag"><i aria-hidden="true"></i>Email</span>
          <h3><a href="mailto:<?= $mail ?>"><?= esc($site->email) ?></a></h3>
        </li>
      </ul>
    </div>

    <form class="form-card" method="post" action="<?= base_url('contact/send') ?>" data-form data-reveal>
      <?= csrf_field() ?>
      <h3>Send an enquiry</h3>
      <?= view('partials/form_notes', ['kind' => 'contact']) ?>
      <div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="fld-row">
        <label class="fld"><span>Name *</span><input type="text" name="name" value="<?= esc(old('name'), 'attr') ?>" required minlength="2" maxlength="100" autocomplete="name"></label>
        <label class="fld"><span>Email *</span><input type="email" name="email" value="<?= esc(old('email'), 'attr') ?>" required maxlength="150" autocomplete="email"></label>
      </div>
      <div class="fld-row">
        <label class="fld"><span>Phone</span><input type="tel" name="phone" value="<?= esc(old('phone'), 'attr') ?>" maxlength="30" autocomplete="tel"></label>
        <label class="fld"><span>Company</span><input type="text" name="company" value="<?= esc(old('company'), 'attr') ?>" maxlength="120" autocomplete="organization"></label>
      </div>
      <label class="fld"><span>I’m interested in</span>
        <select name="interest">
          <option value="">General enquiry</option>
          <?php foreach ($all as $m): ?><option value="<?= esc($m['slug'], 'attr') ?>"<?= $pick === $m['slug'] ? ' selected' : '' ?>><?= esc($m['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="fld"><span>Message *</span><textarea name="message" rows="5" required minlength="10" maxlength="3000" placeholder="Part or machine, material, size range, quantity, timeline…"><?= esc(old('message')) ?></textarea></label>
      <p class="form-hint">For a quotation, include the specification, a 2D drawing, a 3D model and images, and email them to <a href="mailto:<?= $mail ?>"><?= esc($site->email) ?></a> after sending.</p>
      <button class="button" type="submit" data-submit><span>Send enquiry</span><i aria-hidden="true"></i></button>
    </form>
  </div>
</section>

<section class="pg-features sec" aria-labelledby="mp-h" data-tabs>
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>Find us</p><h2 id="mp-h" class="h2" data-split>On the map.</h2></div>
    <div class="map-tabs" role="tablist" data-reveal>
      <?php foreach ($site->network as $i => $n): ?>
        <button type="button" role="tab" class="map-tab<?= $i === 0 ? ' is-active' : '' ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="map-<?= $i ?>" data-tab="<?= $i ?>"><?= esc($n['name']) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="map-frames">
    <?php foreach ($site->network as $i => $n): ?>
      <iframe id="map-<?= $i ?>" class="map-frame<?= $i === 0 ? ' is-active' : '' ?>" title="Map: <?= esc($n['name'], 'attr') ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=<?= rawurlencode($n['address']) ?>&amp;output=embed"></iframe>
    <?php endforeach; ?>
  </div>
</section>
<?php $this->endSection(); ?>
