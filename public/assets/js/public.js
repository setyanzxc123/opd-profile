(() => {
  "use strict";

  const toArray = (value) => Array.prototype.slice.call(value);

  const initCarousels = () => {
    const carousels = document.querySelectorAll('[data-carousel]');
    if (!carousels.length) {
      return;
    }

    const setActiveState = (slides, dots, index) => {
      slides.forEach((slide, idx) => {
        const isActive = idx === index;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        if (dots[idx]) {
          dots[idx].classList.toggle('is-active', isActive);
          dots[idx].setAttribute('aria-selected', isActive ? 'true' : 'false');
          dots[idx].setAttribute('tabindex', isActive ? '0' : '-1');
        }
      });
    };

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

      let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
      if (activeIndex < 0) {
        activeIndex = 0;
      }
      setActiveState(slides, dots, activeIndex);

      let timerId = null;
      let isPaused = false;

      const move = (step) => {
        activeIndex = (activeIndex + step + slides.length) % slides.length;
        setActiveState(slides, dots, activeIndex);
      };

      const goTo = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        setActiveState(slides, dots, activeIndex);
      };

      const stop = () => {
        if (timerId) {
          clearInterval(timerId);
          timerId = null;
        }
      };

      const start = () => {
        if (slides.length <= 1 || isPaused) {
          return;
        }
        stop();
        timerId = setInterval(() => move(1), interval);
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
          start();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', () => {
          move(1);
          start();
        });
      }

      dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
          goTo(idx);
          dot.focus();
          start();
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

      carousel.addEventListener('pointerenter', pause);
      carousel.addEventListener('pointerleave', () => {
        if (!isPaused) {
          start();
        }
      });

      carousel.addEventListener('focusin', pause);
      carousel.addEventListener('focusout', () => {
        if (!isPaused) {
          start();
        }
      });

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
