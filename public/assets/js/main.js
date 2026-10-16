(() => {
  'use strict';

  const root = document.documentElement;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const canHover = matchMedia('(hover: hover) and (pointer: fine)');
  const clamp = (n, a = 0, b = 1) => Math.min(b, Math.max(a, n));

  /* ---------- 1. split headings into masked words ---------- */
  $$('[data-split]').forEach((el) => {
    let i = 0;
    const build = (parent) => {
      const frag = document.createDocumentFragment();
      parent.childNodes.forEach((node) => {
        if (node.nodeType === 3) {
          node.textContent.split(/(\s+)/).forEach((part) => {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.append(' '); return; }
            const w = document.createElement('span');
            const inner = document.createElement('span');
            w.className = 'w';
            inner.textContent = part;
            inner.style.setProperty('--i', i++);
            w.append(inner);
            frag.append(w);
          });
        } else if (node.nodeType === 1 && node.childNodes.length) {
          const clone = node.cloneNode(false); // keep <em>/<span> accents, split the words inside them
          clone.append(build(node));
          frag.append(clone);
        } else {
          frag.append(node.cloneNode(true));
        }
      });
      return frag;
    };
    el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
    el.replaceChildren(build(el));
    $$('.w', el).forEach((w) => w.setAttribute('aria-hidden', 'true'));
  });

  /* ---------- 2. reveal on scroll ---------- */
  const revealables = $$('[data-split]:not(.hero-title), [data-reveal], [data-wipe]');
  if ('IntersectionObserver' in window && !reduced.matches) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        e.target.classList.add('in');
        io.unobserve(e.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    // fully clipped elements are ignored by IntersectionObserver, so wipes keep a 3px strip and use threshold 0
    const wipeIo = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        e.target.classList.add('in');
        wipeIo.unobserve(e.target);
      });
    }, { threshold: 0, rootMargin: '0px 0px -10% 0px' });
    revealables.forEach((el) => (el.hasAttribute('data-wipe') ? wipeIo : io).observe(el));
  } else {
    revealables.forEach((el) => el.classList.add('in'));
  }

  /* ---------- 3. count-up numbers ("600 Bar", "±0.03 mm", "8") ---------- */
  const counters = $$('[data-count]');
  const runCount = (el) => {
    const text = el.textContent;
    const m = text.match(/\d[\d,]*(?:\.\d+)?/);
    if (!m) return;
    const comma = m[0].includes(',');
    const target = parseFloat(m[0].replace(/,/g, ''));
    const dec = (m[0].split('.')[1] || '').length;
    const [pre, post] = [text.slice(0, m.index), text.slice(m.index + m[0].length)];
    const t0 = performance.now(), dur = 1600;
    const tick = (t) => {
      const k = clamp((t - t0) / dur);
      const eased = 1 - Math.pow(1 - k, 4);
      const v = target * eased;
      el.textContent = pre + (comma ? Math.round(v).toLocaleString('en-US') : v.toFixed(dec)) + post;
      if (k < 1) requestAnimationFrame(tick); else el.textContent = text;
    };
    requestAnimationFrame(tick);
  };
  if ('IntersectionObserver' in window && !reduced.matches) {
    const co = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { runCount(e.target); co.unobserve(e.target); } });
    }, { threshold: 0.6 });
    counters.forEach((el) => co.observe(el));
  }

  /* ---------- 4. marquees (gallery): clone once for a seamless loop ---------- */
  $$('[data-marquee]').forEach((track) => {
    if (reduced.matches) return;
    const originals = [...track.children];
    // fill until one set is wider than the viewport, then duplicate the whole set
    let guard = 0;
    while (track.scrollWidth < innerWidth * 1.2 && guard++ < 6) {
      originals.forEach((n) => track.append(n.cloneNode(true)));
    }
    const setA = [...track.children];
    setA.forEach((n) => { const c = n.cloneNode(true); c.setAttribute('aria-hidden', 'true'); c.removeAttribute('loading'); track.append(c); });
    const width = track.scrollWidth / 2;
    track.style.setProperty('--dur', `${width / Number(track.dataset.speed || 60)}s`);
    track.classList.add('is-ready');
    if (track.classList.contains('is-reverse')) track.style.animationDirection = 'reverse';
  });

  /* ---------- 5. header: solid after scroll, hide on scroll down, mobile menu ---------- */
  const header = $('[data-header]');
  const navBtn = $('[data-nav-toggle]');
  const setNav = (open) => {
    navBtn.setAttribute('aria-expanded', String(open));
    navBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    root.classList.toggle('nav-open', open);
    header.classList.toggle('nav-open', open);
  };
  navBtn.addEventListener('click', () => setNav(navBtn.getAttribute('aria-expanded') !== 'true'));
  $$('.site-nav a:not([data-sub-toggle])').forEach((a) => a.addEventListener('click', () => setNav(false)));
  addEventListener('keydown', (e) => { if (e.key === 'Escape') setNav(false); });
  matchMedia('(min-width: 941px)').addEventListener('change', (e) => { if (e.matches) setNav(false); });

  /* ---------- 6. flow: tube fills as you scroll, stages light up ---------- */
  const flows = $$('[data-flow]').map((el) => ({ el, stages: $$('.flow-stage', el) }));
  const updateFlow = ({ el, stages }) => {
    const r = el.getBoundingClientRect();
    const vh = innerHeight;
    if (r.bottom < -200 || r.top > vh + 200) return;
    const p = clamp((vh * 0.62 - r.top) / r.height);
    el.style.setProperty('--p', p.toFixed(4));
    stages.forEach((s) => {
      const node = $('.flow-node', s);
      const at = (s.offsetTop + node.offsetTop + node.offsetHeight / 2) / r.height;
      s.classList.toggle('is-lit', p >= at - 0.001);
    });
  };
  flows.forEach(({ el }) => el.setAttribute('data-live', ''));

  /* ---------- 7. parallax ---------- */
  const para = $$('[data-parallax]');
  const updateParallax = () => {
    para.forEach((el) => {
      const r = el.getBoundingClientRect();
      if (r.bottom < -100 || r.top > innerHeight + 100) return;
      const k = Number(el.dataset.parallax);
      const d = (r.top + r.height / 2 - innerHeight / 2) * k;
      const img = el.querySelector('img');
      if (el.classList.contains('intro-media-main') || el.classList.contains('pg-media')) img.style.transform = `translate3d(0,${(-d).toFixed(1)}px,0)`;
      else el.style.transform = `translate3d(0,${(-d * 2).toFixed(1)}px,0)`;
    });
  };

  /* ---------- 8. one scroll loop for everything scroll-linked ---------- */
  let lastY = scrollY, ticking = false;
  const onScroll = () => {
    const y = scrollY;
    const max = root.scrollHeight - innerHeight;
    root.style.setProperty('--scroll', max > 0 ? (y / max).toFixed(4) : 0);
    header.classList.toggle('scrolled', y > 24);
    const hide = y > lastY && y > innerHeight * 0.7 && !root.classList.contains('nav-open');
    header.classList.toggle('hide', hide);
    root.classList.toggle('head-hidden', hide);
    lastY = y;
    if (!reduced.matches) { flows.forEach(updateFlow); updateParallax(); } else flows.forEach(({ el, stages }) => { el.style.setProperty('--p', 1); stages.forEach((s) => s.classList.add('is-lit')); });
    ticking = false;
  };
  const queue = () => { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } };
  addEventListener('scroll', queue, { passive: true });
  addEventListener('resize', queue);
  addEventListener('load', queue);
  onScroll();

  /* ---------- 9. card spotlight + magnetic buttons (mouse only) ---------- */
  if (canHover.matches && !reduced.matches) {
    $$('[data-spot]').forEach((c) => c.addEventListener('pointermove', (e) => {
      const r = c.getBoundingClientRect();
      c.style.setProperty('--mx', `${e.clientX - r.left}px`);
      c.style.setProperty('--my', `${e.clientY - r.top}px`);
    }));
    $$('.button, [data-magnetic]').forEach((b) => {
      b.addEventListener('pointermove', (e) => {
        const r = b.getBoundingClientRect();
        const x = (e.clientX - r.left - r.width / 2) * 0.22;
        const y = (e.clientY - r.top - r.height / 2) * 0.32;
        b.style.transform = `translate(${x.toFixed(1)}px,${y.toFixed(1)}px)`;
      });
      b.addEventListener('pointerleave', () => { b.style.transform = ''; });
    });
  }

  /* ---------- 10. hero carousel ----------
     The rail's progress bar drives autoplay (animationend -> next slide), so pausing
     the CSS animation pauses the slideshow and the two can never drift apart. */
  const hero = $('[data-carousel]');
  if (hero) {
    const slides = $$('.hero-slide', hero);
    const items = $$('[data-rail]', hero);
    const toggle = $('[data-carousel-toggle]', hero);
    const now = $('[data-count-now]', hero);
    let current = 0, userPaused = reduced.matches;

    const show = (n) => {
      current = (n + slides.length) % slides.length;
      slides.forEach((s, i) => {
        const on = i === current;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', String(!on));
        if (on) s.querySelector('img')?.removeAttribute('loading');
      });
      items.forEach((it) => { it.classList.remove('is-active'); it.removeAttribute('aria-current'); });
      void hero.offsetWidth; // restart the progress animation, even when re-selecting the same slide
      items[current].classList.add('is-active');
      items[current].setAttribute('aria-current', 'true');
      if (now) now.textContent = String(current + 1).padStart(2, '0');
    };
    const syncToggle = () => {
      hero.classList.toggle('is-paused', userPaused);
      toggle.setAttribute('aria-pressed', String(userPaused));
      toggle.setAttribute('aria-label', userPaused ? 'Play slideshow' : 'Pause slideshow');
      toggle.textContent = userPaused ? '▶' : '❚❚';
    };
    $('.hero-rail', hero).addEventListener('animationend', (e) => {
      if (e.animationName === 'rail-fill') show(current + 1);
    });
    items.forEach((it, i) => it.addEventListener('click', () => show(i)));
    toggle.addEventListener('click', () => { userPaused = !userPaused; syncToggle(); });

    // hold the slideshow while the person is interacting with it; don't run it off-screen
    const hold = (on) => hero.classList.toggle('is-hover', on);
    hero.addEventListener('mouseenter', () => hold(true));
    hero.addEventListener('mouseleave', () => hold(false));
    hero.addEventListener('focusin', () => hold(true));
    hero.addEventListener('focusout', () => hold(false));
    document.addEventListener('visibilitychange', () => hero.classList.toggle('is-away', document.hidden));
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([e]) => hero.classList.toggle('is-away', !e.isIntersecting)).observe(hero);
    }

    let x0 = null, y0 = null;
    hero.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
    hero.addEventListener('touchend', (e) => {
      if (x0 === null) return;
      const dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0; x0 = null;
      if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) show(current + (dx < 0 ? 1 : -1));
    });
    hero.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') show(current + 1);
      if (e.key === 'ArrowLeft') show(current - 1);
    });
    syncToggle();
  }

  /* ---------- 11. network map: shows first, pins appear one by one, rings ping while visible ---------- */
  $$('[data-network]').forEach((stage) => {
    const wrap = stage.closest('.network');
    if (!('IntersectionObserver' in window) || reduced.matches) {
      stage.classList.add('in');
    } else {
      new IntersectionObserver(([e]) => {
        if (e.isIntersecting) stage.classList.add('in');
        stage.classList.toggle('live', e.isIntersecting);
      }, { threshold: 0.35 }).observe(stage);
    }
    // hover/focus a location card (or a pin) -> highlight its pin, callout and card together
    const link = (id, on) => $$(`[data-pin="${id}"]`, wrap).forEach((el) => el.classList.toggle('is-active', on));
    $$('[data-pin]', wrap).forEach((el) => {
      const id = el.dataset.pin;
      el.addEventListener('mouseenter', () => link(id, true));
      el.addEventListener('mouseleave', () => link(id, false));
      el.addEventListener('focusin', () => link(id, true));
      el.addEventListener('focusout', () => link(id, false));
    });
  });

  /* ---------- 12. product page: sticky sub-nav highlights the section in view ---------- */
  $$('[data-subnav]').forEach((nav) => {
    const links = $$('a[href^="#"]', nav);
    const map = new Map(links.map((a) => [document.getElementById(a.getAttribute('href').slice(1)), a]));
    if (!('IntersectionObserver' in window)) return;
    const so = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        links.forEach((l) => l.classList.toggle('is-active', l === map.get(e.target)));
        const a = map.get(e.target);
        const box = a.parentElement; // keep the active link visible on narrow screens
        if (box.scrollWidth > box.clientWidth) box.scrollTo({ left: a.offsetLeft - 24, behavior: reduced.matches ? 'auto' : 'smooth' });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    map.forEach((a, sec) => sec && so.observe(sec));
  });

  /* ---------- 13. FAQ accordion + project filter ---------- */
  $$('[data-faq] .faq-q').forEach((btn) => btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const open = !item.classList.contains('is-open');
    item.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', String(open));
  }));
  /* ---------- 13c. keep the products mega-menu inside the viewport ---------- */
  $$('.has-sub').forEach((item) => {
    const mega = $('.mega', item); if (!mega) return;
    const fit = () => {
      if (getComputedStyle(mega).position !== 'absolute') { mega.style.removeProperty('--mx'); return; }
      const r = item.getBoundingClientRect(), w = mega.offsetWidth, m = 12;
      const left = r.left + r.width / 2 - w / 2;
      const shift = Math.max(m - left, Math.min(0, innerWidth - m - (left + w)), 0) || Math.min(0, innerWidth - m - (left + w));
      mega.style.setProperty('--mx', shift + 'px');
    };
    ['mouseenter', 'focusin'].forEach((ev) => item.addEventListener(ev, fit));
    addEventListener('resize', fit);
  });
  /* ---------- 13b. news: card expands in place to show the full article ---------- */
  $$('[data-news]').forEach((card) => {
    const btn = $('[data-news-toggle]', card), label = $('span', btn);
    const set = (open) => {
      card.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', String(open));
      label.textContent = open ? btn.dataset.openLabel : btn.dataset.closedLabel;
    };
    btn.addEventListener('click', () => set(!card.classList.contains('is-open')));
    if (location.hash === '#' + card.id) set(true);   // deep link to an article opens it
  });
  $$('[data-projects]').forEach((box) => {
    const input = $('[data-proj-filter]', box), count = $('[data-proj-count]', box), empty = $('.proj-empty', box);
    const items = $$('.proj', box);
    const run = () => {
      const q = input.value.trim().toLowerCase();
      let n = 0;
      items.forEach((li) => { const hit = !q || li.dataset.name.includes(q); li.hidden = !hit; if (hit) n++; });
      count.textContent = q ? `${n} of ${items.length} projects` : '';
      empty.hidden = n > 0;
    };
    input.addEventListener('input', run);
  });

  /* ---------- 14. header Products menu (mobile accordion), forms, map tabs ---------- */
  $$('[data-sub-toggle]').forEach((a) => {
    const item = a.closest('.has-sub');
    const mobile = matchMedia('(max-width: 940px)');
    a.addEventListener('click', (e) => {
      if (!mobile.matches) return; // desktop: the link goes to #machines, the menu opens on hover/focus
      e.preventDefault();
      const open = item.classList.toggle('is-open');
      a.setAttribute('aria-expanded', String(open));
    });
    addEventListener('keydown', (e) => { if (e.key === 'Escape') { item.classList.remove('is-open'); a.setAttribute('aria-expanded', 'false'); } });
  });
  $$('[data-form]').forEach((form) => {
    const btn = $('[data-submit]', form);
    form.addEventListener('submit', () => { if (form.checkValidity() && btn) { btn.classList.add('is-busy'); const t = $('span', btn); if (t) t.textContent = 'Sending…'; } });
    const file = $('[data-file]', form);
    if (file) file.addEventListener('change', () => {
      const f = file.files[0], label = $('[data-file-name]', form);
      file.closest('.fld-file').classList.toggle('has-file', !!f);
      if (f && f.size > 5 * 1024 * 1024) { file.value = ''; label.textContent = 'File is larger than 5 MB'; file.closest('.fld-file').classList.remove('has-file'); }
      else label.textContent = f ? f.name : 'Choose a PDF or Word file';
    });
  });
  $$('[data-tabs]').forEach((box) => {
    const tabs = $$('[data-tab]', box), frames = $$('.map-frame', box);
    tabs.forEach((t) => t.addEventListener('click', () => {
      tabs.forEach((x, i) => { const on = x === t; x.classList.toggle('is-active', on); x.setAttribute('aria-selected', String(on)); frames[i].classList.toggle('is-active', on); });
    }));
  });
  // arriving at /contact#enquiry or /careers#apply after a submit: bring the form (and its message) into view
  if (location.hash && ($('.form-ok') || $('.form-errors'))) $(location.hash)?.scrollIntoView({ behavior: reduced.matches ? 'auto' : 'smooth', block: 'start' });

  root.classList.add('booted');
  // curtain (~1s) is CSS-only; release the hero choreography right as it lifts
  setTimeout(() => root.classList.add('ready'), reduced.matches ? 0 : 600);
})();
