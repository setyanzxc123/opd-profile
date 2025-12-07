/**
 * Public Core Module
 * Loaded on all public pages - essential functionality
 * ~3KB minified
 */
(() => {
    'use strict';

    // Utilities
    window.PublicUtils = {
        debounce(fn, delay = 300) {
            let id = null;
            const d = (...args) => {
                if (id) clearTimeout(id);
                id = setTimeout(() => { id = null; fn(...args); }, delay);
            };
            d.cancel = () => { if (id) { clearTimeout(id); id = null; } };
            return d;
        },

        prefersReducedMotion() {
            return typeof window.matchMedia === 'function' &&
                window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        }
    };

    // Navbar: keep top bar visible
    const initNavbar = () => {
        const navbar = document.querySelector('.public-navbar');
        const topBar = navbar ? navbar.querySelector('.public-navbar-top') : null;
        if (!navbar || !topBar) return;
        navbar.classList.remove('public-navbar--compact');
    };

    // Dropdown: on desktop, prevent click toggle since hover is used
    const initDropdownHover = () => {
        const dropdownToggles = document.querySelectorAll('.public-navbar .dropdown-toggle');
        const isDesktop = () => window.innerWidth >= 992;

        dropdownToggles.forEach((toggle) => {
            toggle.addEventListener('click', (e) => {
                if (isDesktop()) {
                    e.stopPropagation();
                    const dropdown = toggle.closest('.dropdown');
                    if (dropdown) {
                        dropdown.classList.remove('show');
                        const menu = dropdown.querySelector('.dropdown-menu');
                        if (menu) menu.classList.remove('show');
                    }
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

        const threshold = 300;

        const updateVisibility = () => {
            const scrollY = window.scrollY || window.pageYOffset;
            const windowHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight;
            const nearBottom = scrollY + windowHeight >= docHeight - 100;
            const shouldShow = scrollY > threshold || nearBottom;

            if (shouldShow) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        };

        const scrollToTop = () => {
            if (window.PublicUtils.prefersReducedMotion()) {
                window.scrollTo(0, 0);
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };

        btn.addEventListener('click', scrollToTop);
        window.addEventListener('scroll', updateVisibility, { passive: true });
        updateVisibility();
    };

    // Initialize on DOM ready
    const ready = () => {
        initNavbar();
        initDropdownHover();
        initBackToTop();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready);
    } else {
        ready();
    }
})();
