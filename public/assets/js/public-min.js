(() => {
  'use strict';

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

    if (typeof window.Headroom === 'function') {
      const headroom = new window.Headroom(navbar, {
        offset: 0,
        tolerance: { up: 8, down: 0 },
        onTop: () => {
          requestAnimationFrame(() => {
            if ((window.scrollY || window.pageYOffset || 0) <= 0) {
              navbar.classList.remove('public-navbar--compact');
            }
          });
        },
        onNotTop: () => navbar.classList.add('public-navbar--compact')
      });
      headroom.init();
      return;
    }

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

  // Hero slider: Swiper-based with graceful fallback
  const heroSwiperRegistry = [];
  const initHeroSwiper = () => {
    const slider = document.querySelector('[data-hero-swiper]');
    if (!slider) return;

    const slideCount = slider.querySelectorAll('[data-hero-slide]').length;
    if (!slideCount) return;

    const interval = Number(slider.getAttribute('data-autoplay-interval')) || 6500;
    const reduce = prefersReducedMotion();
    const prevEl = slider.querySelector('.swiper-button-prev');
    const nextEl = slider.querySelector('.swiper-button-next');
    const paginationEl = slider.querySelector('.swiper-pagination');

    const applyAria = (sw) => {
      if (!sw || !sw.slides || typeof sw.slides.forEach !== 'function') return;
      sw.slides.forEach((slide) => {
        if (!slide || !slide.hasAttribute('data-hero-slide')) return;
        const isDuplicate = slide.classList.contains('swiper-slide-duplicate');
        if (isDuplicate) {
          slide.setAttribute('aria-hidden', 'true');
          return;
        }
        const active = slide.classList.contains('swiper-slide-active');
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      });
    };

    const setup = () => {
      if (typeof window.Swiper !== 'function') return false;

      const swiper = new window.Swiper(slider, {
        loop: slideCount > 1,
        slidesPerView: 1,
        allowTouchMove: slideCount > 1,
        speed: reduce ? 0 : 600,
        autoplay: !reduce && slideCount > 1
          ? {
              delay: interval,
              disableOnInteraction: false,
              pauseOnMouseEnter: true
            }
          : false,
        navigation: slideCount > 1 && prevEl && nextEl ? { prevEl, nextEl } : undefined,
        pagination: slideCount > 1 && paginationEl
          ? {
              el: paginationEl,
              clickable: true
            }
          : undefined,
        on: {
          init(sw) {
            applyAria(sw);
          },
          slideChange(sw) {
            applyAria(sw);
          }
        }
      });

      if (swiper.autoplay) {
        const controller = {
          pause() {
            if (swiper.autoplay && typeof swiper.autoplay.stop === 'function') {
              swiper.autoplay.stop();
            }
          },
          resume() {
            if (swiper.autoplay && typeof swiper.autoplay.start === 'function') {
              swiper.autoplay.start();
            }
          }
        };
        heroSwiperRegistry.push(controller);
      }

      return true;
    };

    if (setup()) return;

    let attempts = 0;
    const maxAttempts = 40;
    const timer = setInterval(() => {
      attempts += 1;
      if (setup() || attempts >= maxAttempts) {
        clearInterval(timer);
      }
    }, 120);
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
    initHeroSwiper();
    initNavSearch();
    initContactForm();
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();

  // Pause/resume carousels when tab visibility changes
  if (typeof document.addEventListener === 'function') {
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        heroSwiperRegistry.forEach((entry) => { try { entry.pause(); } catch (_) {} });
      } else {
        heroSwiperRegistry.forEach((entry) => { try { entry.resume(); } catch (_) {} });
      }
    });
  }
})();
