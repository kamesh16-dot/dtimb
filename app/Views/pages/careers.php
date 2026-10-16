<?php
/** @var \Config\Site $site  @var array $c */
$this->extend('layout');
$this->section('content');
$mail = esc($site->email, 'attr');
?>
<?= view('partials/page_hero', ['crumb' => 'Careers', 'kicker' => 'Careers', 'heading' => ['Build the', 'machines', 'that build.'], 'lead' => 'TIMB’s engineers design, assemble and test custom machines, automation and tooling for the Murugappa Group and for customers across industry.']) ?>

<section class="sec" aria-labelledby="wk-h">
  <div class="sec-head">
    <div><p class="eyebrow" data-reveal>How we work</p><h2 id="wk-h" class="h2" data-split>One team, from design to inspection.</h2></div>
    <p data-reveal>Engineers, designers, the production team and automation experts work together under a project manager.</p>
  </div>
  <ul class="pg-feature-grid">
    <?php foreach ($c['about']['capabilities'] as $i => [$t, $d]): ?>
      <li class="pg-feature" data-spot data-reveal style="--d:<?= $i * 90 ?>ms"><b class="num"><?= sprintf('%02d', $i + 1) ?></b><h3><?= esc($t) ?></h3><p><?= esc($d) ?></p></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="pg-highlight sec" id="apply" aria-labelledby="ap-h">
  <div class="contact-grid">
    <div class="contact-info">
      <p class="eyebrow" data-reveal>Apply</p>
      <h2 id="ap-h" class="h2" data-split>Send us your CV.</h2>
      <p class="pg-hl-text" data-reveal>Tell us about your experience in machine design, automation, assembly or quality inspection. Attach your CV (PDF or Word, up to 5 MB).</p>
      <p class="form-hint" data-reveal>Prefer email? Write to <a href="mailto:<?= $mail ?>?subject=<?= rawurlencode('Application - TIMB') ?>"><?= esc($site->email) ?></a>.</p>
    </div>

    <form class="form-card" method="post" action="<?= base_url('careers/apply') ?>" enctype="multipart/form-data" data-form data-reveal>
      <?= csrf_field() ?>
      <h3>Application form</h3>
      <?= view('partials/form_notes', ['kind' => 'career']) ?>
      <div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="fld-row">
        <label class="fld"><span>Full name *</span><input type="text" name="name" value="<?= esc(old('name'), 'attr') ?>" required minlength="2" maxlength="100" autocomplete="name"></label>
        <label class="fld"><span>Email *</span><input type="email" name="email" value="<?= esc(old('email'), 'attr') ?>" required maxlength="150" autocomplete="email"></label>
      </div>
      <div class="fld-row">
        <label class="fld"><span>Phone *</span><input type="tel" name="phone" value="<?= esc(old('phone'), 'attr') ?>" required minlength="6" maxlength="30" autocomplete="tel"></label>
        <label class="fld"><span>Experience (years)</span><input type="number" name="experience" value="<?= esc(old('experience'), 'attr') ?>" min="0" max="59" step="0.5" inputmode="decimal"></label>
      </div>
      <label class="fld"><span>Area of interest *</span>
        <select name="area" required>
          <option value="">Select…</option>
          <?php foreach ($c['careerAreas'] as $a): ?><option<?= old('area') === $a ? ' selected' : '' ?>><?= esc($a) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="fld"><span>About you</span><textarea name="message" rows="4" maxlength="2000" placeholder="Current role, key skills, notice period…"><?= esc(old('message')) ?></textarea></label>
      <label class="fld fld-file"><span>CV / resume *</span>
        <input type="file" name="resume" accept=".pdf,.doc,.docx,application/pdf" required data-file>
        <em data-file-name>Choose a PDF or Word file</em>
      </label>
      <button class="button" type="submit" data-submit><span>Submit application</span><i aria-hidden="true"></i></button>
    </form>
  </div>
</section>
<?php $this->endSection(); ?>
