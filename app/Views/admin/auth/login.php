<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle ?? 'Sign In') ?> — TIMB CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,700;0,800;1,900&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#0f1318;--steel:#1a2027;--steel-2:#252d36;--border:rgba(255,255,255,.08);--text:#f0f4f6;--muted:#8796a3;--red:#c8102e;--ease:cubic-bezier(.2,.8,.2,1)}
body{font-family:'Hanken Grotesk',Arial,sans-serif;background:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;-webkit-font-smoothing:antialiased;color:var(--text)}

/* curtain animation matching public site */
.curtain{position:fixed;inset:0;z-index:40;background:var(--ink);display:grid;place-items:center;pointer-events:none;animation:curtain .6s var(--ease) .35s forwards}
@keyframes curtain{to{transform:translateY(-100%);visibility:hidden}}
.curtain-brand{font:italic 900 clamp(3rem,10vw,6rem)/.8 'Archivo',sans-serif;letter-spacing:-.05em;color:var(--red);animation:brand-in .45s var(--ease) both}
@keyframes brand-in{from{opacity:0;transform:translateY(24px) skewX(-6deg)}}

.wrap{width:100%;max-width:400px;padding:2rem;animation:form-in .5s var(--ease) .75s both}
@keyframes form-in{from{opacity:0;transform:translateY(16px)}}

.card{background:var(--steel);border:1px solid var(--border);border-radius:.75rem;padding:2.5rem;box-shadow:0 32px 64px rgba(0,0,0,.5)}
.logo{text-align:center;margin-bottom:2.25rem}
.logo-mark{display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;background:var(--red);border-radius:.5rem;margin-bottom:1rem}
.logo-mark svg{width:24px;height:24px;fill:#fff}
.logo-title{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.25rem;letter-spacing:-.04em;color:#fff}
.logo-sub{font-size:.72rem;color:var(--muted);margin-top:.3rem;letter-spacing:.06em;text-transform:uppercase}

.alert{padding:.7rem .9rem;border-radius:.4rem;font-size:.82rem;font-weight:500;margin-bottom:1.25rem}
.alert-error  {background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.25);color:#fca5a5}
.alert-success{background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);color:#6ee7b7}

.field{margin-bottom:1.1rem}
label{display:block;font-size:.7rem;font-weight:600;color:var(--muted);margin-bottom:.4rem;letter-spacing:.05em;text-transform:uppercase}
input[type=email],input[type=password]{width:100%;padding:.7rem .9rem;background:var(--ink);border:1px solid rgba(255,255,255,.1);border-radius:.4rem;color:var(--text);font-size:.9rem;font-family:inherit;transition:border-color .2s var(--ease)}
input:focus{outline:none;border-color:var(--red);box-shadow:0 0 0 3px rgba(200,16,46,.15)}

.btn{display:block;width:100%;padding:.85rem;background:var(--red);color:#fff;border:none;border-radius:999px;font-size:.88rem;font-weight:600;font-family:inherit;cursor:pointer;position:relative;overflow:hidden;isolation:isolate;transition:transform .2s var(--ease)}
.btn::before{content:'';position:absolute;inset:0;background:#fff;transform:translateY(101%);border-radius:inherit;transition:transform .4s var(--ease);z-index:-1}
.btn:hover{color:var(--ink)}
.btn:hover::before{transform:none}
.btn:active{transform:scale(.97)}

.footer{text-align:center;margin-top:1.5rem;font-size:.72rem;color:rgba(255,255,255,.2);letter-spacing:.04em;text-transform:uppercase}
</style>
</head>
<body>
<div class="curtain"><span class="curtain-brand">TIMB</span></div>
<div class="wrap">
  <div class="card">
    <div class="logo">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24"><path d="M12 2L2 7v10l10 5 10-5V7z"/></svg>
      </div>
      <div class="logo-title">TIMB CMS</div>
      <div class="logo-sub">Content Management System</div>
    </div>

    <?php if ($error ?? null): ?>
      <div class="alert alert-error"><?= esc($error) ?></div>
    <?php endif ?>
    <?php if ($success ?? null): ?>
      <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif ?>

    <form method="POST" action="/admin/login" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="email" autofocus required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn">Sign in</button>
    </form>
  </div>
  <p class="footer">TIMB — Precision Industrial Machinery</p>
</div>
</body>
</html>
