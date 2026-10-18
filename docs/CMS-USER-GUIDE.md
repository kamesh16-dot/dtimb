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

Click **Save** after every change. Changes show on the website straight away.
(The site icon — favicon — is a file on the server; ask your developer to change it.)

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

**Working with a list:**
- **Add an item** — press **+ Add item** at the top of the list. Fill in the
  form and press **Add item**. Untick **Show on the website** to save it
  without it appearing on the site yet.
- **See everything about an item** — click its row. A panel opens on the right
  with every field in full, and buttons to **Edit**, **Hide from website** or
  **Delete** it.
- **Reorder** — use the ↑ and ↓ buttons in the row.
- **Hide rather than delete** — deleting removes an item for good; hiding
  keeps it so you can show it again later.

---

## 8. Media Library

Every photo on the website lives here. The top of the screen shows how many
photos there are, the space they use, how many are not used on any page, and
how many still need a description — click a number to see those photos.

### Finding a photo
Search by name, description or original file name, pick a folder, choose
**Not used on any page** or **No description**, and sort by name, newest or
largest file.

### Seeing everything about a photo
Click a photo. A panel opens with the picture, its name (with **Copy name**),
its description, its size, and **Used on** — every page that shows it, with a
link. A green dot means deleting it would break that page; a grey dot (the home
page photo gallery) just means it would drop out of the gallery.

### Uploading
Press **Upload photos**, then drag photos onto the box or click to choose — as
many as you like. JPG, PNG or WebP, up to 12 MB each. Both display sizes are
made for you, and the message afterwards says how much smaller they became.
You are warned about any photo narrower than 1600 pixels, which may look
blurry in the large banners. With a single photo you can also give it a
display name, its name and a description.

### Describing a photo
Open a photo and press **Edit details**. The **Description** says what the
photo shows in one sentence; it is read aloud to blind visitors and helps the
photo appear in Google Images. The SEO screen reminds you how many are missing.

### Replacing a photo
Press **Replace photo…** and choose the new file. It keeps the same name, so
every page using it shows the new picture straight away.

### Moving and deleting
Change the **Folder** on the edit page, or tick several photos and use
**Move to folder**. Everything that uses them — machines, home banners, news,
the sharing picture — is updated for you. Photos that are part of a product
page or the page design cannot be moved without your developer.

A photo that is in use on the website cannot be deleted; the message says
where it is used. Tick several unused photos and press **Delete** to remove
them in one go *(admins only)*.

### Choosing a photo elsewhere
Wherever the CMS asks for a photo — a machine, a home banner, a news article,
the sharing picture in SEO — press **Choose photo** to pick it from the
library instead of typing its name.

---

## 9. SEO

**SEO** is about how your pages appear when people search on Google. The screen
has three tabs.

### Pages in Google
- The bar at the top shows how many pages look good, e.g. *16 of 23 pages look
  good in Google*.
- The list opens on **Needs work** — the pages worth fixing. Switch to
  **Looks good** or **All pages** with the buttons above it.
- **Click a page** to see it exactly as it appears in Google (blue title, grey
  description), what is wrong in plain words, and how to fix it. The bars under
  the title and description turn green when the length is right.
- Press **Fix** (or **Fix this page**) to go to the page or machine and change it.
  While typing a title or description on a page, a counter under the box shows
  whether the length is right.
- A few machines take their description from their full product page, which
  the CMS cannot edit yet. Those say *Ask your developer* instead of offering a fix.

### Settings *(admins can change these; editors can view them)*
- **Search engines** — keep *Let Google and other search engines list this
  website* on for the live site. If it is ever off, a red warning appears at the
  top of the SEO screen.
- **When someone shares a link** — the picture and preview shown when a link is
  shared on LinkedIn, WhatsApp or Facebook. The example on the right shows how it looks.
- **Default description** — used for any page without its own description.
- **Advanced** — technical settings. Leave them unless your developer asks.

### What Google is told
The list of pages search engines are told about (the *sitemap*). It updates by
itself whenever you publish, hide or edit something; there is nothing to do here.

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
