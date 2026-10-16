<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password — TIMB CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center}
.wrap{width:100%;max-width:420px;padding:2rem}
.card{background:#1e293b;border:1px solid #334155;border-radius:1rem;padding:2.5rem}
h1{font-size:1.25rem;color:#f1f5f9;font-weight:700;margin-bottom:.5rem}
p.sub{color:#64748b;font-size:.85rem;margin-bottom:1.5rem}
.alert{padding:.75rem 1rem;border-radius:.5rem;font-size:.875rem;margin-bottom:1.25rem;background:#450a0a;border:1px solid #7f1d1d;color:#fca5a5}
.field{margin-bottom:1.25rem}
label{display:block;font-size:.8rem;font-weight:500;color:#94a3b8;margin-bottom:.5rem;letter-spacing:.05em;text-transform:uppercase}
input[type=password]{width:100%;padding:.75rem 1rem;background:#0f172a;border:1px solid #334155;border-radius:.5rem;color:#f1f5f9;font-size:.9rem;font-family:inherit}
input:focus{outline:none;border-color:#dc2626}
.hint{font-size:.75rem;color:#475569;margin-top:.4rem}
.btn{display:block;width:100%;padding:.875rem;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;border-radius:.5rem;font-size:.9rem;font-weight:600;font-family:inherit;cursor:pointer}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>Set a new password</h1>
    <p class="sub">You must change your password before continuing.</p>

    <?php if ($error ?? null): ?>
      <div class="alert"><?= esc($error) ?></div>
    <?php endif ?>

    <form method="POST" action="/admin/change-password">
      <?= csrf_field() ?>
      <div class="field">
        <label for="password">New password</label>
        <input type="password" id="password" name="password" autofocus required>
        <p class="hint">Min 12 chars · 1 uppercase · 1 number · 1 symbol</p>
      </div>
      <div class="field">
        <label for="password_confirm">Confirm password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit" class="btn">Set password &amp; continue</button>
    </form>
  </div>
</div>
</body>
</html>
