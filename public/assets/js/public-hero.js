/**
 * Hero Swiper Module
 * Loaded only on home page - handles hero slider with Swiper
 * Requires: Swiper library, public-core.js
 */
(() => {
    'use strict';

    const heroSwiperRegistry = [];

    const initHeroSwiper = () => {
        const slider = document.querySelector('[data-hero-swiper]');
        if (!slider) return;

        const slideCount = slider.querySelectorAll('[data-hero-slide]').length;
        if (!slideCount) return;

        const interval = Number(slider.getAttribute('data-autoplay-interval')) || 6500;
        const reduce = window.PublicUtils?.prefersReducedMotion?.() || false;
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
                    init(sw) { applyAria(sw); },
                    slideChange(sw) { applyAria(sw); }
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

        // Retry if Swiper not loaded yet
        let attempts = 0;
        const maxAttempts = 40;
        const timer = setInterval(() => {
            attempts += 1;
            if (setup() || attempts >= maxAttempts) {
                clearInterval(timer);
            }
        }, 120);
    };

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

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroSwiper);
    } else {
        initHeroSwiper();
    }
})();
