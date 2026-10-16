<?php
/**
 * Red contact band.  @var string $headline  @var string $text  @var string $subject
 */
$mail = esc($site->email, 'attr');
?>
<section class="contact sec" aria-labelledby="pg-cta-h">
  <h2 id="pg-cta-h" class="h2" data-split><?= esc($headline) ?></h2>
  <div class="contact-side">
    <p data-reveal><?= esc($text) ?></p>
    <a class="button button-light" data-reveal data-magnetic href="<?= base_url('contact') ?>#enquiry"><span>Talk to our team</span><i aria-hidden="true"></i></a>
  </div>
</section>
