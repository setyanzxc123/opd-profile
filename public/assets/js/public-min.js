(() => {
  'use strict';

  const toArray = (v) => Array.prototype.slice.call(v || []);
  const debounce = (fn, delay = 300) => {
    let id = null;
    const d = (...args) => {
      if (id) clearTimeout(id);
      id = setTimeout(() => { id = null; fn(...args); }, delay);
    };
    d.cancel = () => { if (id) { clearTimeout(id); id = null; } };
    return d;
  };

  const prefersReducedMotion = () =>
    typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Navbar: simple compact toggle without jump scroll
  const initNavbar = () => {
    const navbar = document.querySelector('.public-navbar');
    const topBar = navbar ? navbar.querySelector('.public-navbar-top') : null;
    if (!navbar || !topBar) return;

    let compact = false;
    const threshold = 8;
    const onScroll = () => {
      const y = Math.max(0, window.scrollY || window.pageYOffset || 0);
      const next = y > threshold;
      if (next !== compact) {
        compact = next;
        navbar.classList.toggle('public-navbar--compact', compact);
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  };

  // Carousel: minimal next/prev/dots + optional auto-advance
  const carouselsRegistry = [];
  const initCarousels = () => {
    const carousels = document.querySelectorAll('[data-carousel]');
    if (!carousels.length) return;

    const reduce = prefersReducedMotion();

    carousels.forEach((carousel) => {
      const slides = toArray(carousel.querySelectorAll('[data-carousel-slide]'));
      if (!slides.length) return;

      const dots = toArray(carousel.querySelectorAll('[data-carousel-dot]'));
      const prevBtn = carousel.querySelector('[data-carousel-prev]');
      const nextBtn = carousel.querySelector('[data-carousel-next]');
      const toggleBtn = carousel.querySelector('[data-carousel-toggle]');
      const interval = Number(carousel.getAttribute('data-carousel-interval')) || 6500;

      let index = Math.max(0, slides.findIndex(s => s.classList.contains('is-active')));
      if (index >= slides.length) index = 0;
      let timer = null;
      let paused = false;

      const apply = (i) => {
        slides.forEach((s, idx) => {
          const active = idx === i;
          s.classList.toggle('is-active', active);
          s.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach((d, idx) => {
          const active = idx === i;
          d.classList.toggle('is-active', active);
          d.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
      };
      apply(index);

      const go = (i) => {
        const next = (i + slides.length) % slides.length;
        if (next === index) return;
        index = next;
        apply(index);
      };

      const start = () => {
        if (reduce || paused || slides.length <= 1) return;
        stop();
        timer = setInterval(() => go(index + 1), interval);
      };
      const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
      const pause = () => { paused = true; stop(); if (toggleBtn) { toggleBtn.setAttribute('aria-pressed', 'true'); toggleBtn.textContent = 'Putar'; } };
      const resume = () => { paused = false; if (toggleBtn) { toggleBtn.setAttribute('aria-pressed', 'false'); toggleBtn.textContent = 'Jeda'; } start(); };

      if (prevBtn) prevBtn.addEventListener('click', () => { go(index - 1); start(); });
      if (nextBtn) nextBtn.addEventListener('click', () => { go(index + 1); start(); });
      dots.forEach((dot, i) => dot.addEventListener('click', () => { go(i); start(); dot.focus(); }));
      if (toggleBtn) toggleBtn.addEventListener('click', () => paused ? resume() : pause());

      resume();
      carouselsRegistry.push({ pause, resume });
    });
  };

  // Search: progressive enhancement with safe URL handling
  const initNavSearch = () => {
    const form = document.querySelector('[data-nav-search-form]');
    if (!form) return;

    const input = form.querySelector('[data-nav-search-input]');
    const results = form.querySelector('[data-nav-search-results]');
    const endpoint = form.getAttribute('data-nav-search-url');
    if (!input || !results || !endpoint) return;

    const min = Number(form.getAttribute('data-nav-search-min')) > 0 ? Number(form.getAttribute('data-nav-search-min')) : 2;
    const limit = Number(form.getAttribute('data-nav-search-limit')) > 0 ? Number(form.getAttribute('data-nav-search-limit')) : 5;

    let current = '';
    let controller = null;

    const setLoading = (v) => v ? form.setAttribute('aria-busy', 'true') : form.removeAttribute('aria-busy');
    const clear = () => { results.innerHTML = ''; results.setAttribute('hidden', 'hidden'); };
    const show = () => { results.removeAttribute('hidden'); };

    const allowUrl = (u) => {
      try {
        const url = new URL(u, window.location.origin);
        return url.protocol === 'http:' || url.protocol === 'https:' ? url.toString() : '#';
      } catch (_e) {
        return '#';
      }
    };

    const render = (items, q) => {
      results.innerHTML = '';
      const frag = document.createDocumentFragment();
      const list = Array.isArray(items) ? items.slice(0, limit) : [];

      if (!list.length) {
        const empty = document.createElement('div');
        empty.className = 'public-search-result public-search-result--empty';
        empty.textContent = `Tidak ada hasil untuk "${q}".`;
        frag.appendChild(empty);
      } else {
        list.forEach((item) => {
          if (!item || !item.url) return;
          const a = document.createElement('a');
          a.className = 'public-search-result public-search-result--link';
          a.href = allowUrl(item.url);

          const title = document.createElement('span');
          title.className = 'public-search-result__title';
          title.textContent = String(item.title || 'Tanpa judul');
          a.appendChild(title);

          const metaWrap = document.createElement('span');
          metaWrap.className = 'public-search-result__meta';

          if (item.published_at) {
            const d = new Date(item.published_at);
            if (!Number.isNaN(d.getTime())) {
              const date = document.createElement('span');
              date.className = 'public-search-result__date';
              date.textContent = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
              metaWrap.appendChild(date);
            }
          }
          if (item.snippet) {
            const sn = document.createElement('span');
            sn.className = 'public-search-result__snippet';
            sn.textContent = String(item.snippet);
            metaWrap.appendChild(sn);
          }
          if (metaWrap.childNodes.length) a.appendChild(metaWrap);

          frag.appendChild(a);
        });
      }

      results.appendChild(frag);
      show();
    };

    const fetchResults = async (q) => {
      if (controller) { try { controller.abort(); } catch (_) {} }
      try { controller = new AbortController(); } catch (_) { controller = null; }
      setLoading(true);
      try {
        let url;
        try { url = new URL(endpoint, window.location.origin); }
        catch (_) { url = new URL(window.location.origin + endpoint.replace(/^\//, '')); }
        url.searchParams.set('q', q);
        url.searchParams.set('limit', String(limit));
        const res = await fetch(url.toString(), { headers: { Accept: 'application/json' }, signal: controller ? controller.signal : undefined });
        if (!res.ok) throw new Error(`Status ${res.status}`);
        const payload = await res.json();
        if (current !== q) return;
        render(Array.isArray(payload.results) ? payload.results : [], q);
      } catch (e) {
        if (controller && e && e.name === 'AbortError') return;
        if (current === q) {
          const errorEl = document.createElement('div');
          errorEl.className = 'public-search-result public-search-result--error';
          errorEl.setAttribute('role', 'option');
          errorEl.textContent = 'Terjadi kesalahan saat memuat hasil.';
          results.innerHTML = '';
          results.appendChild(errorEl);
          show();
        }
      } finally {
        if (current === q) setLoading(false);
        controller = null;
      }
    };
    const debounced = debounce(fetchResults, 320);

    input.addEventListener('input', (ev) => {
      const v = String(ev.target.value || '').trim();
      current = v;
      if (v.length < min) {
        debounced.cancel();
        if (controller) { try { controller.abort(); } catch (_) {} controller = null; }
        setLoading(false);
        clear();
        return;
      }
      debounced(v);
    });
    input.addEventListener('focus', () => { if (results.children.length) show(); });
    input.addEventListener('keydown', (e) => { if (e.key === 'Escape') clear(); });
    document.addEventListener('click', (e) => { if (!form.contains(e.target)) clear(); });
    results.addEventListener('click', () => clear());
    form.addEventListener('submit', () => { debounced.cancel(); if (controller) { try { controller.abort(); } catch (_) {} controller = null; } setLoading(false); clear(); });
  };

  // Contact form: rely on HTML5, add honeypot + small feedback
  const initContactForm = () => {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;
    const feedback = form.querySelector('[data-contact-feedback]');
    const honeypot = form.querySelector('input[name="website"]');

    const showFeedback = (msg) => {
      if (!feedback) return;
      if (msg) { feedback.textContent = msg; feedback.classList.add('is-visible'); feedback.removeAttribute('hidden'); }
      else { feedback.textContent = ''; feedback.classList.remove('is-visible'); feedback.setAttribute('hidden', 'hidden'); }
    };

    form.addEventListener('submit', (e) => {
      if (honeypot && String(honeypot.value || '').trim() !== '') {
        e.preventDefault();
        return;
      }
      if (!form.checkValidity()) {
        // Let browser show native messages, add a generic banner
        e.preventDefault();
        showFeedback('Mohon lengkapi bidang wajib sebelum mengirim.');
        form.classList.add('contact-form--has-error');
        form.reportValidity();
      } else {
        showFeedback('');
        form.classList.remove('contact-form--has-error');
      }
    });
  };

  const ready = () => {
    initNavbar();
    initCarousels();
    initNavSearch();
    initContactForm();
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();

  // Pause/resume carousels when tab visibility changes
  if (typeof document.addEventListener === 'function') {
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        carouselsRegistry.forEach(c => { try { c.pause(); } catch (_) {} });
      } else {
        carouselsRegistry.forEach(c => { try { c.resume(); } catch (_) {} });
      }
    });
  }
})();
