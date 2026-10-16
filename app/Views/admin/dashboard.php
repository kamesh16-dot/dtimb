<div class="dashboard">

  <!-- Stats row -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon stat-icon--red">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= $unreadEnquiries ?></div>
        <div class="stat-label">Unread enquiries</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon--blue">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= $unreadApplications ?></div>
        <div class="stat-label">New applications</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon--green">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.49.49 0 00-.59-.22l-2.39.96a7.37 7.37 0 00-1.62-.94l-.36-2.54A.484.484 0 0014 3h-4a.484.484 0 00-.48.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.477.477 0 00-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.37 1.04.7 1.62.94l.36 2.54c.05.24.24.41.48.41h4c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6a3.6 3.6 0 110-7.2 3.6 3.6 0 010 7.2z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= $machineCount ?></div>
        <div class="stat-label">Machines</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon--purple">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-value"><?= $newsCount ?></div>
        <div class="stat-label">News articles</div>
      </div>
    </div>
  </div>

  <!-- Quick links -->
  <div class="quick-grid">
    <h2 class="section-title">Quick actions</h2>
    <div class="quick-links">
      <a href="/admin/inbox" class="quick-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
        View Inbox
      </a>
      <a href="/admin/machines" class="quick-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
        Manage Machines
      </a>
      <a href="/admin/news" class="quick-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
        Write News
      </a>
      <a href="/admin/media" class="quick-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        Media Library
      </a>
      <a href="/admin/settings" class="quick-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.14 12.94l2.03-1.58L19.25 8l-2.39.96a7.37 7.37 0 00-1.62-.94L14 5h-4l-.36 2.54c-.59.24-1.13.57-1.62.94L5.75 8 3.86 11.36l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94L3.86 16.3l1.89 3.32 2.39-.96c.5.37 1.04.7 1.62.94L10 22h4l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96 1.89-3.32-2.03-1.58c.05-.3.07-.62.07-.94s-.02-.64-.07-.94zM12 15.6a3.6 3.6 0 110-7.2 3.6 3.6 0 010 7.2z"/></svg>
        Settings
      </a>
      <a href="/" target="_blank" class="quick-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
        View Site
      </a>
    </div>
  </div>

  <div class="status-note">
    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
    CMS is running. DB connected. Logged in as <strong><?= esc($adminUser['email'] ?? '') ?></strong>.
  </div>
</div>
