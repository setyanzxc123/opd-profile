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

  // Navbar: keep top bar visible; Headroom disabled for now
  const initNavbar = () => {
    const navbar = document.querySelector('.public-navbar');
    const topBar = navbar ? navbar.querySelector('.public-navbar-top') : null;
    if (!navbar || !topBar) {
      return;
    }

    navbar.classList.remove('public-navbar--compact');
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

  // Full-screen Search Overlay
  const initSearchOverlay = () => {
    const trigger = document.querySelector('[data-search-trigger]');
    const overlay = document.querySelector('[data-search-overlay]');
    if (!trigger || !overlay) return;

    const form = overlay.querySelector('[data-search-form]');
    const input = overlay.querySelector('[data-search-input]');
    const resultsContainer = overlay.querySelector('[data-search-results]');
    const emptyState = overlay.querySelector('[data-search-empty]');
    const closeButtons = overlay.querySelectorAll('[data-search-close]');
    const endpoint = form ? form.getAttribute('data-search-url') : null;

    if (!input || !resultsContainer || !endpoint) return;

    let current = '';
    let controller = null;

    const openOverlay = () => {
      overlay.removeAttribute('hidden');
      requestAnimationFrame(() => {
        overlay.classList.add('is-open');
        input.focus();
        document.body.style.overflow = 'hidden';
      });
    };

    const closeOverlay = () => {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      setTimeout(() => {
        overlay.setAttribute('hidden', '');
        input.value = '';
        current = '';
        resultsContainer.innerHTML = '';
        if (emptyState) {
          resultsContainer.appendChild(emptyState);
        }
      }, 300);
    };

    const allowUrl = (u) => {
      try {
        const url = new URL(u, window.location.origin);
        return url.protocol === 'http:' || url.protocol === 'https:' ? url.toString() : '#';
      } catch (_e) {
        return '#';
      }
    };

    const renderResults = (items, q) => {
      resultsContainer.innerHTML = '';

      if (!items.length) {
        const noResults = document.createElement('div');
        noResults.className = 'search-overlay__no-results';
        noResults.innerHTML = `<i class="bx bx-search-alt"></i><p>Tidak ada hasil untuk "<strong>${q}</strong>"</p>`;
        resultsContainer.appendChild(noResults);
        return;
      }

      // Group items by type
      const groups = {};
      const groupLabels = {
        berita: { label: 'Berita', icon: 'bx-news' },
        layanan: { label: 'Layanan', icon: 'bx-briefcase' },
        dokumen: { label: 'Dokumen', icon: 'bx-file' }
      };

      items.forEach(item => {
        const type = item.type || 'other';
        if (!groups[type]) groups[type] = [];
        groups[type].push(item);
      });

      // Render groups
      Object.entries(groups).forEach(([type, groupItems]) => {
        const groupEl = document.createElement('div');
        groupEl.className = 'search-overlay__group';

        const groupInfo = groupLabels[type] || { label: type, icon: 'bx-folder' };

        const titleEl = document.createElement('div');
        titleEl.className = 'search-overlay__group-title';
        titleEl.innerHTML = `<i class="bx ${groupInfo.icon}"></i> ${groupInfo.label}`;
        groupEl.appendChild(titleEl);

        groupItems.forEach(item => {
          const a = document.createElement('a');
          a.className = 'search-overlay__item';
          a.href = allowUrl(item.url);
          a.addEventListener('click', () => closeOverlay());

          const iconWrap = document.createElement('div');
          iconWrap.className = 'search-overlay__item-icon';
          iconWrap.innerHTML = `<i class="bx ${item.icon || groupInfo.icon}"></i>`;
          a.appendChild(iconWrap);

          const content = document.createElement('div');
          content.className = 'search-overlay__item-content';

          const title = document.createElement('div');
          title.className = 'search-overlay__item-title';
          title.textContent = item.title || 'Tanpa judul';
          content.appendChild(title);

          if (item.snippet) {
            const snippet = document.createElement('div');
            snippet.className = 'search-overlay__item-snippet';
            snippet.textContent = item.snippet;
            content.appendChild(snippet);
          }

          a.appendChild(content);
          groupEl.appendChild(a);
        });

        resultsContainer.appendChild(groupEl);
      });
    };

    const fetchResults = async (q) => {
      if (controller) { try { controller.abort(); } catch (_) { } }
      try { controller = new AbortController(); } catch (_) { controller = null; }

      // Show loading
      resultsContainer.innerHTML = '<div class="search-overlay__loading"><i class="bx bx-loader-alt bx-spin"></i> Mencari...</div>';

      try {
        let url;
        try { url = new URL(endpoint, window.location.origin); }
        catch (_) { url = new URL(window.location.origin + endpoint.replace(/^\//, '')); }
        url.searchParams.set('q', q);
        url.searchParams.set('limit', '12');

        const res = await fetch(url.toString(), {
          headers: { Accept: 'application/json' },
          signal: controller ? controller.signal : undefined
        });

        if (!res.ok) throw new Error(`Status ${res.status}`);
        const payload = await res.json();
        if (current !== q) return;
        renderResults(Array.isArray(payload.results) ? payload.results : [], q);
      } catch (e) {
        if (controller && e && e.name === 'AbortError') return;
        if (current === q) {
          resultsContainer.innerHTML = '<div class="search-overlay__no-results"><i class="bx bx-error-circle"></i><p>Terjadi kesalahan saat mencari.</p></div>';
        }
      } finally {
        controller = null;
      }
    };

    const debouncedSearch = debounce(fetchResults, 350);

    // Event listeners
    trigger.addEventListener('click', openOverlay);

    closeButtons.forEach(btn => {
      btn.addEventListener('click', closeOverlay);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
        closeOverlay();
      }
    });

    input.addEventListener('input', (e) => {
      const v = String(e.target.value || '').trim();
      current = v;

      if (v.length < 2) {
        debouncedSearch.cancel();
        if (controller) { try { controller.abort(); } catch (_) { } controller = null; }
        resultsContainer.innerHTML = '';
        if (emptyState) resultsContainer.appendChild(emptyState);
        return;
      }

      debouncedSearch(v);
    });

    if (form) {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        const q = input.value.trim();
        if (q) {
          closeOverlay();
          window.location.href = form.action + '?q=' + encodeURIComponent(q);
        }
      });
    }
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

  // Dropdown: on desktop, prevent click toggle since hover is used
  const initDropdownHover = () => {
    const dropdownToggles = document.querySelectorAll('.public-navbar .dropdown-toggle');
    const isDesktop = () => window.innerWidth >= 992;

    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener('click', (e) => {
        if (isDesktop()) {
          // On desktop, prevent Bootstrap dropdown toggle, allow normal link navigation
          e.stopPropagation();
          const dropdown = toggle.closest('.dropdown');
          if (dropdown) {
            // Remove the .show class to prevent pinned state
            dropdown.classList.remove('show');
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) menu.classList.remove('show');
          }
          // Navigate to href if it exists and is not just a hash
          const href = toggle.getAttribute('href');
          if (href && href !== '#' && href !== '') {
            window.location.href = href;
          }
        }
      });
    });
  };

  // Back to Top Button
  const initBackToTop = () => {
    const btn = document.getElementById('backToTop');
    if (!btn) return;

    const footer = document.querySelector('.public-footer');
    const threshold = 300; // Show button after scrolling this much

    const updateVisibility = () => {
      const scrollY = window.scrollY || window.pageYOffset;
      const windowHeight = window.innerHeight;
      const docHeight = document.documentElement.scrollHeight;

      // Show if scrolled past threshold, or if near/at footer
      const nearBottom = scrollY + windowHeight >= docHeight - 100;
      const shouldShow = scrollY > threshold || nearBottom;

      if (shouldShow) {
        btn.classList.add('visible');
      } else {
        btn.classList.remove('visible');
      }
    };

    const scrollToTop = () => {
      const reduce = prefersReducedMotion();
      if (reduce) {
        window.scrollTo(0, 0);
      } else {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    };

    btn.addEventListener('click', scrollToTop);
    window.addEventListener('scroll', updateVisibility, { passive: true });

    // Initial check
    updateVisibility();
  };

  const ready = () => {
    initNavbar();
    initHeroSwiper();
    initSearchOverlay();
    initContactForm();
    initDropdownHover();
    initBackToTop();
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();

  // Pause/resume carousels when tab visibility changes
  if (typeof document.addEventListener === 'function') {
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        heroSwiperRegistry.forEach((entry) => { try { entry.pause(); } catch (_) { } });
      } else {
        heroSwiperRegistry.forEach((entry) => { try { entry.resume(); } catch (_) { } });
      }
    });
  }
})();
