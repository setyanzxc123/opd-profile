(() => {
  "use strict";

  const toArray = (value) => Array.prototype.slice.call(value);

  const initCarousels = () => {
    const carousels = document.querySelectorAll('[data-carousel]');
    if (!carousels.length) {
      return;
    }

    carousels.forEach((carousel) => {
      const slides = toArray(carousel.querySelectorAll('[data-carousel-slide]'));
      if (!slides.length) {
        return;
      }

      const dots = toArray(carousel.querySelectorAll('[data-carousel-dot]'));
      const prevBtn = carousel.querySelector('[data-carousel-prev]');
      const nextBtn = carousel.querySelector('[data-carousel-next]');
      const toggleBtn = carousel.querySelector('[data-carousel-toggle]');
      const interval = Number(carousel.getAttribute('data-carousel-interval')) || 6500;
      const reduceMotionQuery = typeof window.matchMedia === 'function' ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

      const shouldReduceMotion = () => (reduceMotionQuery ? reduceMotionQuery.matches : false);

      const updateDots = (index) => {
        dots.forEach((dot, idx) => {
          const isActive = idx === index;
          dot.classList.toggle('is-active', isActive);
          dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
          dot.setAttribute('tabindex', isActive ? '0' : '-1');
        });
      };

      const resetSlideState = (slide) => {
        slide.classList.remove(
          'is-transitioning',
          'is-entering-from-left',
          'is-entering-from-right',
          'is-exiting-to-left',
          'is-exiting-to-right'
        );
      };

      const applyImmediateState = (index) => {
        slides.forEach((slide, idx) => {
          const isActive = idx === index;
          slide.classList.toggle('is-active', isActive);
          resetSlideState(slide);
          slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });
        updateDots(index);
      };

      let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
      if (activeIndex < 0) {
        activeIndex = 0;
      }
      applyImmediateState(activeIndex);

      let timerId = null;
      let isPaused = false;
      let isAnimating = false;

      if (reduceMotionQuery) {
        const handleMotionChange = (event) => {
          if (event.matches) {
            isAnimating = false;
            applyImmediateState(activeIndex);
          }
        };
        if (typeof reduceMotionQuery.addEventListener === 'function') {
          reduceMotionQuery.addEventListener('change', handleMotionChange);
        } else if (typeof reduceMotionQuery.addListener === 'function') {
          reduceMotionQuery.addListener(handleMotionChange);
        }
      }

      const performTransition = (nextIndex, direction) => {
        if (slides.length <= 1) {
          if (nextIndex !== activeIndex) {
            activeIndex = nextIndex;
            applyImmediateState(activeIndex);
          }
          return false;
        }

        const reduceMotion = shouldReduceMotion();
        if (reduceMotion) {
          if (nextIndex !== activeIndex) {
            activeIndex = nextIndex;
            applyImmediateState(activeIndex);
          }
          return true;
        }

        if (isAnimating || nextIndex === activeIndex) {
          return false;
        }

        isAnimating = true;
        const currentIndex = activeIndex;
        const currentSlide = slides[currentIndex];
        const nextSlide = slides[nextIndex];
        const enteringClass = direction > 0 ? 'is-entering-from-right' : 'is-entering-from-left';
        const exitingClass = direction > 0 ? 'is-exiting-to-left' : 'is-exiting-to-right';

        resetSlideState(currentSlide);
        resetSlideState(nextSlide);

        currentSlide.classList.add('is-transitioning', exitingClass);
        nextSlide.classList.add('is-transitioning', enteringClass);
        nextSlide.setAttribute('aria-hidden', 'false');
        updateDots(nextIndex);

        const cleanup = () => {
          currentSlide.classList.remove('is-active');
          currentSlide.setAttribute('aria-hidden', 'true');
          resetSlideState(currentSlide);

          nextSlide.classList.add('is-active');
          nextSlide.setAttribute('aria-hidden', 'false');
          resetSlideState(nextSlide);

          activeIndex = nextIndex;
          isAnimating = false;
        };

        let didCleanup = false;
        const finish = () => {
          if (didCleanup) {
            return;
          }
          didCleanup = true;
          nextSlide.removeEventListener('transitionend', onTransitionEnd);
          cleanup();
        };

        const onTransitionEnd = (event) => {
          if (event.target !== nextSlide || event.propertyName !== 'transform') {
            return;
          }
          finish();
        };

        nextSlide.addEventListener('transitionend', onTransitionEnd);

        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            currentSlide.classList.remove('is-active');
            currentSlide.setAttribute('aria-hidden', 'true');
            nextSlide.classList.add('is-active');
          });
        });

        setTimeout(finish, 700);
        return true;
      };

      const stop = () => {
        if (timerId) {
          clearInterval(timerId);
          timerId = null;
        }
      };

      const restartTimer = () => {
        stop();
        if (!isPaused) {
          start();
        }
      };

      const move = (step, { resetTimer = true } = {}) => {
        if (!step) {
          return;
        }
        const direction = step > 0 ? 1 : -1;
        const nextIndex = (activeIndex + step + slides.length) % slides.length;
        const didChange = performTransition(nextIndex, direction);
        if (didChange && resetTimer) {
          restartTimer();
        }
      };

      const goTo = (index) => {
        const normalizedIndex = (index + slides.length) % slides.length;
        if (normalizedIndex === activeIndex) {
          return;
        }
        const rawDiff = normalizedIndex - activeIndex;
        let direction = rawDiff > 0 ? 1 : -1;
        if (Math.abs(rawDiff) > slides.length / 2) {
          direction *= -1;
        }
        const didChange = performTransition(normalizedIndex, direction);
        if (didChange) {
          restartTimer();
        }
      };

      const start = () => {
        if (slides.length <= 1 || isPaused) {
          return;
        }
        stop();
        timerId = setInterval(() => move(1, { resetTimer: false }), interval);
      };

      const pause = () => {
        isPaused = true;
        stop();
        if (toggleBtn) {
          toggleBtn.setAttribute('aria-pressed', 'true');
          toggleBtn.textContent = 'Putar';
        }
      };

      const resume = () => {
        isPaused = false;
        if (toggleBtn) {
          toggleBtn.setAttribute('aria-pressed', 'false');
          toggleBtn.textContent = 'Jeda';
        }
        start();
      };

      if (prevBtn) {
        prevBtn.addEventListener('click', () => {
          move(-1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', () => {
          move(1);
        });
      }

      dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
          goTo(idx);
          dot.focus();
        });
      });

      if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
          if (isPaused) {
            resume();
          } else {
            pause();
          }
        });
      }

      resume();
    });
  };

  const initContactForm = () => {
    const form = document.querySelector('[data-contact-form]');
    if (!form) {
      return;
    }

    const feedbackEl = form.querySelector('[data-contact-feedback]');
    const honeypotField = form.querySelector('input[name="website"]');
    const fields = toArray(form.querySelectorAll('input, textarea'))
      .filter((field) => field !== honeypotField && field.type !== 'hidden');

    const normalize = (value) => value.trim();

    const validators = {
      full_name(value) {
        const normalized = normalize(value);
        if (normalized === '') {
          return 'Nama lengkap wajib diisi.';
        }
        if (normalized.length < 3) {
          return 'Nama lengkap minimal 3 karakter.';
        }
        return '';
      },
      email(value) {
        const normalized = normalize(value);
        if (normalized === '') {
          return 'Email wajib diisi.';
        }
        if (!/^\S+@\S+\.\S+$/.test(normalized)) {
          return 'Format email tidak valid.';
        }
        return '';
      },
      subject(value) {
        const normalized = normalize(value);
        if (normalized === '') {
          return 'Subjek wajib diisi.';
        }
        if (normalized.length < 3) {
          return 'Subjek minimal 3 karakter.';
        }
        return '';
      },
      message(value) {
        const normalized = normalize(value);
        if (normalized === '') {
          return 'Pesan wajib diisi.';
        }
        if (normalized.length < 10) {
          return 'Pesan minimal 10 karakter.';
        }
        return '';
      },
      phone(value) {
        const normalized = normalize(value);
        if (normalized === '') {
          return '';
        }
        const digits = normalized.replace(/[^0-9]/g, '');
        if (digits.length < 6) {
          return 'Nomor telepon minimal 6 digit.';
        }
        return '';
      },
    };

    const showFeedback = (message) => {
      if (!feedbackEl) {
        return;
      }
      if (message) {
        feedbackEl.textContent = message;
        feedbackEl.classList.add('is-visible');
        feedbackEl.removeAttribute('hidden');
      } else {
        feedbackEl.textContent = '';
        feedbackEl.classList.remove('is-visible');
        feedbackEl.setAttribute('hidden', 'hidden');
      }
    };

    const updateFieldState = (field, message) => {
      if (message) {
        field.setCustomValidity(message);
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        field.setAttribute('aria-invalid', 'true');
      } else {
        field.setCustomValidity('');
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
        if (normalize(field.value) !== '') {
          field.classList.add('is-valid');
        } else {
          field.classList.remove('is-valid');
        }
      }
    };

    const validateField = (field) => {
      const validator = validators[field.name];
      let message = validator ? validator(field.value) : '';
      if (message === '' && !field.checkValidity()) {
        message = field.validationMessage || 'Input tidak valid.';
      }
      updateFieldState(field, message);
      return message === '';
    };

    fields.forEach((field) => {
      field.addEventListener('input', () => {
        validateField(field);
        showFeedback('');
      });
      field.addEventListener('blur', () => {
        validateField(field);
      });
    });

    form.addEventListener('submit', (event) => {
      if (honeypotField && normalize(honeypotField.value) !== '') {
        event.preventDefault();
        return;
      }

      let hasErrors = false;
      fields.forEach((field) => {
        if (!validateField(field)) {
          hasErrors = true;
        }
      });

      if (hasErrors) {
        event.preventDefault();
        showFeedback('Mohon lengkapi bidang wajib yang ditandai sebelum mengirim formulir.');
        form.classList.add('contact-form--has-error');
        form.reportValidity();
      } else {
        showFeedback('');
        form.classList.remove('contact-form--has-error');
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initCarousels();
      initContactForm();
    });
  } else {
    initCarousels();
    initContactForm();
  }
})();
