<?php
$editing = ! empty($row['id']);
$isMe    = $editing && (int) $row['id'] === (int) $meId;
$err     = static fn (string $f) => $errors[$f] ?? null;
$val     = static fn (string $f, $d = '') => old($f, $row[$f] ?? $d);
$resubmit = ! empty($errors);
?>
<div class="page-header">
  <h1><?= esc($pageTitle) ?></h1>
  <a href="<?= admin_url('users') ?>" class="btn btn-secondary">← Back</a>
</div>

<?php if (! empty($errors)): ?>
  <div class="card card-pad" style="border-color:var(--red);margin-bottom:1rem">
    <strong style="font-size:.85rem">Please correct the following:</strong>
    <ul style="margin:.5rem 0 0 1rem;font-size:.8rem;color:var(--dim)">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="card form-card card-pad">
  <form method="POST" action="<?= admin_url('users/') ?><?= $editing ? 'edit/' . $row['id'] : 'create' ?>">
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field">
        <label>Full name *</label>
        <input type="text" name="name" value="<?= esc($val('name')) ?>" required>
        <?php if ($err('name')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('name')) ?></p><?php endif ?>
      </div>
      <div class="field">
        <label>Email *</label>
        <input type="email" name="email" value="<?= esc($val('email')) ?>" required>
        <p class="field-hint">Used to sign in.</p>
        <?php if ($err('email')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('email')) ?></p><?php endif ?>
      </div>
    </div>

    <?php if (! $editing): ?>
      <div class="field">
        <label>Temporary password *</label>
        <input type="password" name="password" required autocomplete="new-password">
        <p class="field-hint">
          At least <?= (int) $minLength ?> characters, with an uppercase letter, a number and a symbol.
          They will be asked to set their own at first sign-in.
        </p>
        <?php if ($err('password')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('password')) ?></p><?php endif ?>
      </div>
    <?php endif ?>

    <div class="field-row">
      <div class="field">
        <label>Role</label>
        <select name="role" <?= $lastAdmin ? 'disabled' : '' ?>>
          <?php foreach ($roles as $key => $label): ?>
            <option value="<?= esc($key, 'attr') ?>" <?= (string) $val('role', 'editor') === (string) $key ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach ?>
        </select>
        <?php if ($lastAdmin): ?>
          <input type="hidden" name="role" value="admin">
          <p class="field-hint">This is the only active admin, so the role is fixed. Give someone else admin access first.</p>
        <?php endif ?>
        <?php if ($err('role')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('role')) ?></p><?php endif ?>
      </div>
      <?php if ($editing): ?>
        <div class="field">
          <label>Access</label>
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
            <input type="checkbox" name="is_active" value="1"
                   <?= ($resubmit ? old('is_active') !== null : ! empty($row['is_active'])) ? 'checked' : '' ?>
                   <?= ($isMe || $lastAdmin) ? 'disabled' : '' ?> style="width:auto">
            Can sign in
          </label>
          <?php if ($isMe || $lastAdmin): ?>
            <input type="hidden" name="is_active" value="1">
            <p class="field-hint"><?= $isMe ? 'You cannot switch off your own account.' : 'The only active admin cannot be switched off.' ?></p>
          <?php endif ?>
          <?php if ($err('is_active')): ?><p class="field-hint" style="color:var(--red)"><?= esc($err('is_active')) ?></p><?php endif ?>
        </div>
      <?php endif ?>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Save account' : 'Create user' ?></button>
      <a href="<?= admin_url('users') ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php if ($editing): ?>
  <div class="card form-card card-pad" style="margin-top:1rem">
    <h2 style="font-size:.95rem;margin:0 0 .6rem">Set a new password</h2>
    <p style="font-size:.78rem;color:var(--dim);margin:0 0 .75rem">
      Use this when someone is locked out or has forgotten their password. They will be asked to
      choose their own at the next sign-in.
    </p>
    <form method="POST" action="<?= admin_url('users/password/') ?><?= $row['id'] ?>">
      <?= csrf_field() ?>
      <div class="field">
        <label>New password</label>
        <input type="password" name="password" required autocomplete="new-password">
        <p class="field-hint">At least <?= (int) $minLength ?> characters, with an uppercase letter, a number and a symbol.</p>
      </div>
      <div class="btn-row"><button type="submit" class="btn btn-secondary">Set password</button></div>
    </form>
  </div>
<?php endif ?>
