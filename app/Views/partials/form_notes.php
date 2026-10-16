<?php
/**
 * Success banner / validation errors for the contact and career forms.
 * @var string $kind  contact | career
 */
$errors = session('errors') ?? [];
?>
<?php if (session('form_ok') === $kind): ?>
  <div class="form-ok" role="status">
    <svg viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24" pathLength="1"/><path d="M15 27l8 8 15-17" pathLength="1"/></svg>
    <div><strong><?= $kind === 'career' ? 'Application received.' : 'Message sent.' ?></strong>
    <span><?= $kind === 'career' ? 'Thank you. Our team will review your application and get in touch if there is a match.' : 'Thank you. An engineer will get back to you shortly.' ?></span></div>
  </div>
<?php endif; ?>
<?php if ($errors): ?>
  <ul class="form-errors" role="alert">
    <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
  </ul>
<?php endif; ?>
