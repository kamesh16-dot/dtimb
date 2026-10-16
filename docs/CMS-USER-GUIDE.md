# TIMB CMS — User Guide

> **Audience:** editors and administrators managing content on the TIMB website.  
> Admin panel: `https://www.timbindia.com/admin/` (or your configured prefix).

---

## Roles

| Role | Can do |
|---|---|
| **Editor** | Create, edit, and preview all content; read and manage inbox; download CVs |
| **Admin** | Everything an editor can do, plus: Settings, Mail, Redirects, Users, Audit log, delete inbox items |

---

## Logging In

1. Go to `/admin/login`
2. Enter your username and password
3. After 5 failed attempts your account is locked for 15 minutes
4. The session times out after 30 minutes of inactivity
5. On first login you must set a new password before proceeding

---

## Dashboard

The dashboard shows:
- **Unread enquiries** and **unread applications** badge counts
- **System checks** (DB connection, disk space, pending migrations)
- Quick links to all content modules

---

## 1. Settings

> **Admin only**

**Site → Settings** lets you change:
- Site name, tagline, email address, LinkedIn URL, footer tagline
- Favicon — upload a square PNG (at least 180 × 180 px) and click **Generate favicons** to create `.ico`, `favicon-32.png`, and `apple-touch-icon.png`

Click **Save** after every change. Changes take effect on the next public page load (or immediately after clearing cache with `php spark cms:clear-cache`).

---

## 2. Mail

> **Admin only**

Configure SMTP so that enquiry and application emails are delivered to the inbox email address:

| Field | Example |
|---|---|
| SMTP Host | `mail.timbindia.com` |
| SMTP Port | `587` |
| Encryption | `TLS` |
| Username | `noreply@timbindia.com` |
| Password | your SMTP password (stored encrypted) |

Click **Send test email** to confirm delivery before saving.  
If mail is not configured, form submissions are still saved to the database — visitors still see "Message sent."

---

## 3. Home Page

### Hero Slides

The hero carousel shows up to 4 slides. For each slide you can edit:
- Slide image (choose from Media Library)
- Label, machine name, metric, spec-sheet link
- Three-part headline (line 1, accent line, red line)
- Body copy

Drag the handles on the left to reorder slides.

### Photo Reel

The scrolling photo gallery below the hero shows images from the Media Library tagged as "gallery". Drag to reorder. Toggle visibility with the eye icon.

### Home Lists

Seven sub-sections of the home page are editable as lists:
- **Stats** (e.g. "8 core machine platforms")
- **Pillars** (The TIMB difference — 4 items)
- **Process** (4 stages)
- **Line Stages** (production line overview — 4 steps)
- **Industries** (5 sector names)
- **Network** (Works / HQ location cards)

Each list screen shows rows you can edit, reorder, add, or delete.

---

## 4. Machines

The **Machines** module manages all product and solution detail pages (`/machines/<slug>`).

### List view
- Shows all machines with their publish status, slug, and section count
- Unpublished machines return 404 publicly; a preview link is always available

### Editing a machine
- **Sections** — repeater blocks: overview, callout, features, types, how-it-works, applications, gallery
- **Spec sheet** — upload or replace the PDF downloadable from the product page
- **Hero image** — choose from Media Library; alt text required
- **SEO** — custom title, meta description, canonical URL per machine

### Slug changes
If you change a machine's slug (URL), the CMS automatically:
1. Creates a 301 redirect from the old URL to the new one
2. Updates all internal links that referenced the old slug

### Duplicate
Use the **Duplicate** button to clone a machine as a draft. Useful for machines with similar spec structure.

### Delete / restore
Deleting a machine moves it to the **Bin** (soft-delete). It can be restored within 30 days. After that, use the Bin screen to permanently delete.

---

## 5. News

Each news article has:
- Title, date, tag (e.g. "Events"), short summary
- Hero image + caption
- Intro paragraph
- Optional gallery (up to 6 images)
- Body (rich text — bold, italic, lists, links only; `<script>` and `onerror` attributes are stripped)
- Publish / draft toggle

Drafts are hidden publicly but visible via the **Preview** link.  
Published articles appear newest-first on `/news`.

---

## 6. Pages

Static company pages: **About**, **Infrastructure**, **Projects**, **Clients & FAQ**, **Industries**, **Careers**, **Disclaimer**, **Contact**.

Each page has:
- **SEO** tab — title, meta description, canonical URL
- **Content** tab — page-specific blocks (e.g. About has Timeline, Group info, Awards; Infrastructure has Mech/Elec/Make/Inspect/Lab/Service)
- **Publish** toggle — unpublishing returns 404 publicly (preview link still works)
- **Copy** button — duplicates the page as a draft

---

## 7. Lists Hub

Shared lists used across multiple pages:
- **Projects** — list of completed projects with optional machine link
- **Case studies** — client success stories
- **Clients** — delivered projects + client name list
- **FAQ** — question-answer pairs (drag to reorder, toggle visibility)
- **Career areas** — job function tags shown in the application form dropdown
- **Disclaimer points** — legal text on `/disclaimer`
- **Industries** — the five sectors shown on the home and industries pages

---

## 8. Media Library

The media library stores all images used across the CMS.

### Uploading
- Drag and drop or click **Upload files**
- Up to 20 files at once; max 10 MB per file
- Accepted: JPG, PNG, GIF, WebP, SVG, PDF
- **Alt text is required** before an image can be saved
- Images are automatically re-encoded as WebP at 640 px and 1600 px (GD library)
- SVGs are sanitised (strips `<script>`, `onload`, `javascript:`)
- Files that look like PHP renamed as `.jpg` are rejected

### Replacing an image
Click **Replace** on any image to upload a new file at the same URL. All pages that use the image update automatically.

### "Used in"
Each image shows a list of content items that reference it, so you know what will be affected before deleting.

### Deleting
Soft-delete moves the image to the Bin. It can be restored. Once permanently deleted, any content using it will show a broken image.

---

## 9. SEO

The **SEO** screen sets global defaults:
- Default title suffix (e.g. `| TIMB`)
- Default meta description
- Google Site Verification tag
- robots.txt rules (noindex paths, disallowed bots)

Per-page SEO is set on each machine / page / news article's own **SEO** tab.

The CMS automatically generates:
- `<link rel="canonical">` on every page
- Open Graph `<meta>` tags (title, description, image)
- JSON-LD structured data (Organisation, BreadcrumbList, Product where applicable)
- `sitemap.xml` — updated on every save

---

## 10. Redirects

> **Admin only**

Manage 301 permanent redirects:
- **Manual** — add any from/to pair (e.g. old campaign URLs → contact page)
- **Automatic** — created by the CMS when a machine or page slug changes

The redirects are evaluated before the router, so they work even if the original page no longer exists.

---

## 11. Forms Inbox

The inbox collects all contact enquiries and career applications.

### Tabs
- **Enquiries** — contact form submissions
- **Applications** — career applications (with CV download)

### Status workflow

| Status | Meaning |
|---|---|
| **New** | Just received; shown in bold |
| **Read** | Opened (set automatically when you view the detail) |
| **Reviewing** | You are actively working on this enquiry |
| **Closed** | Resolved / no further action needed |

### Actions
- **Detail** — click any row to open the full submission. Opening an enquiry marks it **Read**; opening an application marks it **Reviewing**.
- **Status + Notes** — update the status and add internal notes visible only to staff
- **Bulk mark as** — tick multiple rows and set status in one action
- **CSV export** — downloads the current filtered view as a UTF-8 BOM CSV (safe for Excel)
- **CV download** — authenticated link; the original filename is preserved for download even though the stored file uses a random name
- **Delete / Restore** — admin only; soft-deleted items appear in the Bin tab and can be restored

### Search and filter
Use the search box (searches name, email, company, message) and date-range picker together with the status tabs to filter the list before exporting.

---

## 12. Users

> **Admin only**

Manage admin and editor accounts:
- **Create** — set name, email, username, role (admin / editor), temporary password
- **Edit** — change name, email, role; force a password reset on next login
- **Deactivate** — prevents login without deleting the account and its audit trail
- **Audit log** — every admin action (login, save, delete) is logged with timestamp and IP

Password rules: minimum 12 characters, at least one uppercase, one number, one symbol.

---

## Tips

- **Preview before publishing** — every machine and page has a Preview link that works even when the item is unpublished or in draft.
- **Clear cache after bulk changes** — run `php spark cms:clear-cache` from the server if you make many changes in quick succession and the public site seems stale.
- **SMTP test** — use Mail → **Send test email** before important campaigns to confirm delivery.
- **Don't rename slugs unless necessary** — the CMS handles the redirect, but external sites linking to the old URL will get a 301 for one year; then the link goes cold.
