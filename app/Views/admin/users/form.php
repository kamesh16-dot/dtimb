<div class="page-header"><h1>Add User</h1><a href="/admin/users" class="btn btn-secondary">← Back</a></div>
<div class="card form-card card-pad">
  <form method="POST" action="/admin/users/create">
    <?= csrf_field() ?>
    <div class="field-row">
      <div class="field"><label>Full name *</label><input type="text" name="name" required></div>
      <div class="field"><label>Email *</label><input type="email" name="email" required></div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Temporary password *</label>
        <input type="password" name="password" placeholder="Min 8 chars" required>
        <p class="field-hint">User will be forced to change on first login.</p>
      </div>
      <div class="field">
        <label>Role</label>
        <select name="role">
          <option value="editor">Editor</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Create user</button>
      <a href="/admin/users" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
