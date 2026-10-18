<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle ?? 'Dashboard') ?> — TIMB CMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_url('admin-assets/css/admin.css') ?>">
</head>
<body class="admin-body">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24" fill="white" width="20" height="20"><path d="M12 2L2 7v10l10 5 10-5V7z"/></svg>
      </div>
      <span class="logo-text">TIMB CMS</span>
    </div>
    <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">✕</button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">
      <span class="nav-label">Main</span>
      <a href="/admin" class="nav-item <?= str_ends_with($currentRoute ?? '', '/admin') || str_ends_with($currentRoute ?? '', '/admin/') || str_ends_with($currentRoute ?? '', 'dashboard') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>
    </div>

    <div class="nav-section">
      <span class="nav-label">Content</span>
      <a href="/admin/machines" class="nav-item <?= str_contains($currentRoute ?? '', 'machines') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm0 18a8 8 0 110-16 8 8 0 010 16zm-1-11v4l3 3 1-1-2-2v-4h-2z"/></svg>
        Machines
      </a>
      <a href="/admin/news" class="nav-item <?= str_contains($currentRoute ?? '', 'news') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        News
      </a>
      <a href="/admin/pages" class="nav-item <?= str_contains($currentRoute ?? '', '/admin/pages') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
        Pages
      </a>
      <a href="/admin/lists" class="nav-item <?= str_contains($currentRoute ?? '', 'lists') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
        Lists Hub
      </a>
    </div>

    <div class="nav-section">
      <span class="nav-label">Media & Inbox</span>
      <a href="/admin/media" class="nav-item <?= str_contains($currentRoute ?? '', 'media') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        Media Library
      </a>
      <a href="/admin/inbox" class="nav-item <?= str_contains($currentRoute ?? '', 'inbox') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
        Inbox
        <?php if (($unreadEnquiries ?? 0) + ($unreadApplications ?? 0) > 0): ?>
          <span class="badge"><?= ($unreadEnquiries ?? 0) + ($unreadApplications ?? 0) ?></span>
        <?php endif ?>
      </a>
    </div>

    <div class="nav-section">
      <span class="nav-label">Settings</span>
      <a href="/admin/seo" class="nav-item <?= str_contains($currentRoute ?? '', '/admin/seo') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        SEO
      </a>
      <a href="/admin/redirects" class="nav-item <?= str_contains($currentRoute ?? '', 'redirects') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>
        Redirects
      </a>
      <?php if (($adminUser['role'] ?? '') === 'admin'): ?>
      <a href="/admin/settings" class="nav-item <?= str_contains($currentRoute ?? '', '/admin/settings') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96a7.37 7.37 0 00-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.44.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.477.477 0 00-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.37 1.04.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6a3.6 3.6 0 110-7.2 3.6 3.6 0 010 7.2z"/></svg>
        Settings
      </a>
      <a href="/admin/users" class="nav-item <?= str_contains($currentRoute ?? '', '/admin/users') ? 'active' : '' ?>">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Users
      </a>
      <?php endif ?>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= esc($adminUser['name'] ?? '') ?></div>
        <div class="user-role"><?= esc($adminUser['role'] ?? '') ?></div>
      </div>
    </div>
    <a href="/admin/logout" class="logout-btn" title="Logout">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
    </a>
  </div>
</aside>

<!-- Main content -->
<div class="main-wrap">
  <header class="topbar">
    <button class="menu-btn" id="menuBtn" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <div class="topbar-title"><?= esc($pageTitle ?? 'Dashboard') ?></div>
    <div class="topbar-actions">
      <a href="/" target="_blank" class="view-site-btn">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        View site
      </a>
    </div>
  </header>

  <?php if ($success ?? null): ?>
    <div class="flash flash-success"><?= esc($success ?? session()->getFlashdata('success')) ?></div>
  <?php endif ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="flash flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif ?>

  <main class="main-content">
    <?= $content ?? '' ?>
  </main>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<?php // Row details, filled from a <template> when a table row is clicked (admin/partials/drawer.php). ?>
<div class="drawer-backdrop" id="drawerBackdrop" hidden></div>
<aside class="drawer" id="drawer" role="dialog" aria-modal="true" aria-labelledby="drawerTitle" hidden>
  <header class="drawer-head">
    <div>
      <h2 id="drawerTitle"></h2>
      <p id="drawerSubtitle"></p>
    </div>
    <button type="button" class="drawer-close" id="drawerClose" aria-label="Close details">&times;</button>
  </header>
  <div class="drawer-body" id="drawerBody"></div>
</aside>

<script src="<?= asset_url('admin-assets/js/admin.js') ?>"></script>
</body>
</html>
