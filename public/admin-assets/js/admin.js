// TIMB CMS Admin JS

(function () {
  'use strict';

  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const menuBtn  = document.getElementById('menuBtn');
  const closeBtn = document.getElementById('sidebarClose');

  function openSidebar() {
    sidebar?.classList.add('open');
    overlay?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('open');
    document.body.style.overflow = '';
  }

  menuBtn?.addEventListener('click', openSidebar);
  closeBtn?.addEventListener('click', closeSidebar);
  overlay?.addEventListener('click', closeSidebar);

  // Auto-dismiss flash messages after 4s
  document.querySelectorAll('.flash').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    }, 4000);
  });

  // "Show N per page" and similar pickers apply as soon as they change
  document.querySelectorAll('select[data-autosubmit]').forEach(el => {
    el.addEventListener('change', () => el.form?.submit());
  });

  // Live character count for text with an ideal length, e.g. a search
  // description: <textarea data-counter data-min="70" data-max="160">
  document.querySelectorAll('[data-counter]').forEach(el => {
    const min = +el.dataset.min || 0, max = +el.dataset.max || 0;
    const out = document.createElement('p');
    out.className = 'char-count';
    out.setAttribute('aria-live', 'polite');
    el.insertAdjacentElement('afterend', out);

    const update = () => {
      const n = el.value.trim().length;
      const state = n === 0 || n < min ? 'bad' : (max && n > max ? 'warn' : 'ok');
      const word  = { bad: n === 0 ? 'Empty' : 'A bit short', ok: 'Good length', warn: 'Too long — Google will cut it off' }[state];
      out.className = 'char-count is-' + state;
      out.textContent = n + ' characters · ' + word + (max ? ' (aim for ' + min + '–' + max + ')' : '');
    };
    el.addEventListener('input', update);
    update();
  });

  // Confirm dangerous actions. Delegated, so buttons copied into the
  // details drawer after load are covered too.
  document.addEventListener('click', e => {
    const el = e.target.closest('[data-confirm]');
    if (el && !confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });

  // ── Details drawer ──────────────────────────────────────────────────
  // A row with data-drawer="<id>" opens <template id="<id>"> in the side
  // drawer. Clicks on the row's own links, buttons and forms still do
  // their own thing.
  const drawer   = document.getElementById('drawer');
  const backdrop = document.getElementById('drawerBackdrop');

  if (drawer && backdrop) {
    const body     = document.getElementById('drawerBody');
    const title    = document.getElementById('drawerTitle');
    const subtitle = document.getElementById('drawerSubtitle');
    const closeBtn = document.getElementById('drawerClose');
    let opener = null;

    const openDrawer = (row) => {
      const tpl = document.getElementById(row.dataset.drawer);
      if (!tpl) return;

      body.replaceChildren(tpl.content.cloneNode(true));
      title.textContent    = tpl.dataset.title || '';
      subtitle.textContent = tpl.dataset.subtitle || '';
      subtitle.hidden      = !tpl.dataset.subtitle;

      opener?.setAttribute('aria-expanded', 'false');
      opener = row;
      row.setAttribute('aria-expanded', 'true');

      drawer.hidden = backdrop.hidden = false;
      document.body.style.overflow = 'hidden';
      requestAnimationFrame(() => {
        drawer.classList.add('open');
        backdrop.classList.add('open');
      });
      body.scrollTop = 0;
      closeBtn.focus();
    };

    const closeDrawer = () => {
      if (drawer.hidden) return;
      drawer.classList.remove('open');
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
      setTimeout(() => { drawer.hidden = backdrop.hidden = true; }, 250);
      opener?.setAttribute('aria-expanded', 'false');
      opener?.focus();
    };

    document.addEventListener('click', e => {
      const row = e.target.closest('[data-drawer]');
      if (!row) return;
      // A control inside the row (a link, button, checkbox…) does its own
      // thing. Only controls *inside* the row count: the row itself may sit
      // inside a form, as the media cards do.
      const control = e.target.closest('a, button, input, select, textarea, label');
      if (control && row.contains(control)) return;
      openDrawer(row);
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') { closeDrawer(); return; }

      const row = e.target.closest?.('[data-drawer]');
      if (row && e.target === row && (e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault();
        openDrawer(row);
      }

      // Keep Tab inside the open drawer
      if (e.key === 'Tab' && !drawer.hidden) {
        const items = drawer.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (!items.length) return;
        const first = items[0], last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);
  }

  // ── Copy to clipboard: <button data-copy="text"> ────────────────────
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    const text = btn.dataset.copy;
    const done = () => {
      const was = btn.textContent;
      btn.textContent = 'Copied ✓';
      setTimeout(() => { btn.textContent = was; }, 1500);
    };
    if (navigator.clipboard?.writeText) {
      navigator.clipboard.writeText(text).then(done, () => window.prompt('Copy this name:', text));
    } else {
      window.prompt('Copy this name:', text);
    }
  });

  // A file input that sends its form as soon as a file is chosen
  // (e.g. "Replace photo…"). Delegated, so it works inside the drawer.
  document.addEventListener('change', e => {
    const input = e.target.closest?.('input[type="file"][data-autosubmit-file]');
    if (input && input.files.length) {
      input.closest('label')?.append(' — uploading…');
      input.form.submit();
    }
  });

  // ── "Choose photo" picker ───────────────────────────────────────────
  // Any <input data-media-picker> gets a Choose photo button and a small
  // preview. The library is fetched once, the first time it is needed.
  // The admin's own address (from the layout), so this works in a subfolder too.
  const ADMIN = (document.body.dataset.adminUrl || '/admin').replace(/\/$/, '');
  let library = null;
  const loadLibrary = () => library ??= fetch(ADMIN + '/media/library', { credentials: 'same-origin' })
    .then(r => r.ok ? r.json() : [])
    .catch(() => []);

  let picker = null, target = null;

  const buildPicker = () => {
    picker = document.createElement('dialog');
    picker.className = 'picker-dialog';
    picker.setAttribute('aria-label', 'Choose a photo');
    picker.innerHTML =
      '<div class="picker-head"><h2>Choose a photo</h2>' +
      '<input type="search" placeholder="Search photos…" aria-label="Search photos">' +
      '<select aria-label="Folder"><option value="">All folders</option></select>' +
      '<a class="btn btn-secondary" href="' + ADMIN + '/media" target="_blank" rel="noopener">Upload new ↗</a>' +
      '<button type="button" class="btn btn-secondary" data-close aria-label="Close">&times;</button></div>' +
      '<div class="picker-grid" role="list"></div>';
    document.body.append(picker);

    const search = picker.querySelector('input');
    const folder = picker.querySelector('select');
    const grid   = picker.querySelector('.picker-grid');

    const render = items => {
      const q = search.value.trim().toLowerCase();
      const f = folder.value;
      const shown = items.filter(i => (!f || i.folder === f) &&
        (!q || (i.key + ' ' + i.title + ' ' + i.alt).toLowerCase().includes(q)));
      grid.innerHTML = '';
      if (!shown.length) {
        grid.innerHTML = '<p class="picker-empty">No photos match.</p>';
        return;
      }
      shown.forEach(i => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'picker-item' + (target && target.value === i.key ? ' is-current' : '');
        b.setAttribute('role', 'listitem');
        b.innerHTML = '<img loading="lazy" alt=""><span></span>';
        b.querySelector('img').src = i.thumb;
        const label = b.querySelector('span');
        label.textContent = i.title;
        const small = document.createElement('small');
        small.textContent = i.key;
        label.append(small);
        b.addEventListener('click', () => {
          target.value = i.key;
          target.dispatchEvent(new Event('input', { bubbles: true }));
          target.dispatchEvent(new Event('change', { bubbles: true }));
          picker.close();
          target.focus();
        });
        grid.append(b);
      });
    };

    loadLibrary().then(items => {
      [...new Set(items.map(i => i.folder))].sort().forEach(f => {
        const o = document.createElement('option');
        o.value = f;
        o.textContent = f.replace(/-/g, ' ');
        folder.append(o);
      });
      picker.render = () => render(items);
      search.addEventListener('input', picker.render);
      folder.addEventListener('change', picker.render);
    });

    picker.querySelector('[data-close]').addEventListener('click', () => picker.close());
    picker.addEventListener('click', e => { if (e.target === picker) picker.close(); });
  };

  const enhancePicker = input => {
    if (input.dataset.pickerReady) return;
    input.dataset.pickerReady = '1';

    const row = document.createElement('div');
    row.className = 'picker-field';
    row.innerHTML = '<button type="button" class="btn btn-secondary">Choose photo</button><img alt="" hidden><span class="picker-note"></span>';
    input.insertAdjacentElement('afterend', row);

    const img  = row.querySelector('img');
    const note = row.querySelector('.picker-note');
    const show = () => loadLibrary().then(items => {
      const key   = input.value.trim();
      const found = items.find(i => i.key === key);
      img.hidden = !found;
      if (found) img.src = found.thumb;
      note.className = 'picker-note' + (key && !found ? ' is-bad' : '');
      note.textContent = !key ? 'No photo chosen' : (found ? found.title : 'Not in the Media Library — check the name');
    });

    row.querySelector('button').addEventListener('click', () => {
      if (!picker) buildPicker();
      target = input;
      picker.querySelector('input').value = '';
      picker.showModal();
      loadLibrary().then(() => picker.render?.());
      picker.querySelector('input').focus();
    });
    input.addEventListener('input', show);
    show();
  };

  document.querySelectorAll('input[data-media-picker]').forEach(enhancePicker);
  // Rows added later (e.g. "+ Add photo" on a news article) get one too.
  new MutationObserver(records => records.forEach(r => r.addedNodes.forEach(n => {
    if (n.nodeType !== 1) return;
    if (n.matches('input[data-media-picker]')) enhancePicker(n);
    n.querySelectorAll?.('input[data-media-picker]').forEach(enhancePicker);
  }))).observe(document.body, { childList: true, subtree: true });

})();
