<div class="page-header"><h1>Settings</h1></div>
<div class="card form-card card-pad">
  <form method="POST" action="/admin/settings/save">
    <?= csrf_field() ?>
    <?php
    $defaults = [
      'site_name'    => 'TIMB',
      'site_tagline' => 'Precision Industrial Machinery',
      'contact_email'=> '',
      'contact_phone'=> '',
      'contact_address' => '',
      'social_linkedin'  => '',
      'social_twitter'   => '',
      'footer_text'  => '',
    ];
    $s = array_merge($defaults, $settings ?? []);
    ?>
    <h3 style="font-family:var(--font-display);font-size:.95rem;margin-bottom:1rem;color:var(--muted);font-weight:700;letter-spacing:.04em;text-transform:uppercase">Site Info</h3>
    <div class="field-row">
      <div class="field"><label>Site name</label><input type="text" name="setting_site_name" value="<?= esc($s['site_name']) ?>"></div>
      <div class="field"><label>Tagline</label><input type="text" name="setting_site_tagline" value="<?= esc($s['site_tagline']) ?>"></div>
    </div>
    <div class="field-row">
      <div class="field"><label>Contact email</label><input type="email" name="setting_contact_email" value="<?= esc($s['contact_email']) ?>"></div>
      <div class="field"><label>Contact phone</label><input type="text" name="setting_contact_phone" value="<?= esc($s['contact_phone']) ?>"></div>
    </div>
    <div class="field"><label>Address</label><textarea name="setting_contact_address" rows="3"><?= esc($s['contact_address']) ?></textarea></div>
    <h3 style="font-family:var(--font-display);font-size:.95rem;margin:1.25rem 0 1rem;color:var(--muted);font-weight:700;letter-spacing:.04em;text-transform:uppercase">Social</h3>
    <div class="field-row">
      <div class="field"><label>LinkedIn URL</label><input type="url" name="setting_social_linkedin" value="<?= esc($s['social_linkedin']) ?>"></div>
      <div class="field"><label>Twitter URL</label><input type="url" name="setting_social_twitter" value="<?= esc($s['social_twitter']) ?>"></div>
    </div>
    <div class="field"><label>Footer text</label><textarea name="setting_footer_text" rows="2"><?= esc($s['footer_text']) ?></textarea></div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Save settings</button></div>
  </form>
</div>
