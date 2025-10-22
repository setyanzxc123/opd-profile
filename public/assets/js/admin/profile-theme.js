(function () {
  const initialize = function () {
    const configWrapper = document.querySelector('[data-theme-config]');
    if (!configWrapper) {
      return;
    }

    const colorItems = Array.from(configWrapper.querySelectorAll('[data-theme-item]'));
    if (colorItems.length === 0) {
      return;
    }

    const resetFlagInput = document.querySelector('[data-theme-reset-flag]');
    const previewCard = document.querySelector('[data-theme-preview]');
    const pickrAvailable = typeof Pickr !== 'undefined';

    const controllers = new Map();
    const defaultColors = {};

    const previewElements = previewCard
      ? {
          card: previewCard,
          hero: previewCard.querySelector('[data-theme-preview-hero]'),
          heroCta: previewCard.querySelector('[data-theme-preview-hero-cta]'),
          heroOutline: previewCard.querySelector('[data-theme-preview-hero-outline]'),
          surface: previewCard.querySelector('[data-theme-preview-surface]'),
          badge: previewCard.querySelector('[data-theme-preview-accent]'),
          heading: previewCard.querySelector('[data-theme-preview-heading]'),
          text: previewCard.querySelector('[data-theme-preview-text]'),
          buttonPrimary: previewCard.querySelector('[data-theme-preview-button-primary]'),
          buttonOutline: previewCard.querySelector('[data-theme-preview-button-outline]'),
          buttonMuted: previewCard.querySelector('[data-theme-preview-button-muted]'),
        }
      : {};

    const clamp = function (value, min, max) {
      return Math.min(Math.max(value, min), max);
    };

    const normalizeColor = function (value, fallback) {
      const fallbackColor = typeof fallback === 'string' && fallback !== '' ? fallback : '#05A5A8';
      let candidate = typeof value === 'string' ? value.trim() : '';

      if (candidate === '') {
        candidate = fallbackColor;
      }

      if (candidate[0] !== '#') {
        candidate = '#' + candidate;
      }

      candidate = candidate.toUpperCase();

      if (/^#[0-9A-F]{3}$/.test(candidate)) {
        candidate = '#' + candidate[1] + candidate[1] + candidate[2] + candidate[2] + candidate[3] + candidate[3];
      }

      if (!/^#[0-9A-F]{6}$/.test(candidate)) {
        return normalizeColor(fallbackColor, '#05A5A8');
      }

      return candidate;
    };

    const hexToRgb = function (hex) {
      const normalized = normalizeColor(hex, '#000000').replace('#', '');
      const bigint = parseInt(normalized, 16);
      return {
        r: (bigint >> 16) & 255,
        g: (bigint >> 8) & 255,
        b: bigint & 255,
      };
    };

    const rgbToHex = function (r, g, b) {
      return (
        '#' +
        [r, g, b]
          .map(function (v) {
            const clamped = clamp(Math.round(v), 0, 255).toString(16).toUpperCase();
            return clamped.length === 1 ? '0' + clamped : clamped;
          })
          .join('')
      );
    };

    const lighten = function (hex, ratio) {
      const { r, g, b } = hexToRgb(hex);
      const weight = clamp(ratio, 0, 1);
      return rgbToHex(
        r + (255 - r) * weight,
        g + (255 - g) * weight,
        b + (255 - b) * weight
      );
    };

    const darken = function (hex, ratio) {
      const { r, g, b } = hexToRgb(hex);
      const weight = clamp(ratio, 0, 1);
      return rgbToHex(r * (1 - weight), g * (1 - weight), b * (1 - weight));
    };

    const toRgba = function (hex, alpha) {
      const { r, g, b } = hexToRgb(hex);
      const a = clamp(alpha, 0, 1);
      return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + a + ')';
    };

    const deriveAccent = function (primary) {
      return lighten(primary, 0.35);
    };

    const readCurrentColor = function (key) {
      const controller = controllers.get(key);
      if (!controller) {
        return defaultColors[key] || '#05A5A8';
      }

      return controller.color || controller.defaultColor;
    };

    const updatePreview = function () {
      if (!previewCard) {
        return;
      }

      const primary = readCurrentColor('primary');
      const accent = controllers.has('accent') ? readCurrentColor('accent') : deriveAccent(primary);
      const surface = readCurrentColor('surface');
      const neutral = readCurrentColor('neutral');

      const primaryDark = darken(primary, 0.25);
      const primarySoft = toRgba(primary, 0.12);
      const neutralSubtle = toRgba(neutral, 0.65);

      previewCard.style.setProperty('--theme-primary', primary);
      previewCard.style.setProperty('--theme-primary-dark', primaryDark);
      previewCard.style.setProperty('--theme-primary-soft', primarySoft);
      previewCard.style.setProperty('--theme-accent', accent);
      previewCard.style.setProperty('--theme-surface', surface);
      previewCard.style.setProperty('--theme-neutral', neutral);
      previewCard.style.setProperty('--theme-neutral-subtle', neutralSubtle);

      if (previewElements.hero) {
        previewElements.hero.style.background = 'linear-gradient(135deg, ' + primary + ', ' + primaryDark + ')';
      }

      if (previewElements.heroCta) {
        previewElements.heroCta.style.backgroundColor = '#FFFFFF';
        previewElements.heroCta.style.borderColor = '#FFFFFF';
        previewElements.heroCta.style.color = primaryDark;
      }

      if (previewElements.heroOutline) {
        previewElements.heroOutline.style.color = '#FFFFFF';
        previewElements.heroOutline.style.borderColor = toRgba('#FFFFFF', 0.65);
      }

      if (previewElements.surface) {
        previewElements.surface.style.backgroundColor = surface;
        previewElements.surface.style.borderColor = toRgba(neutral, 0.08);
      }

      if (previewElements.badge) {
        previewElements.badge.style.background = accent;
        previewElements.badge.style.boxShadow = '0 0.5rem 1.25rem ' + toRgba(accent, 0.35);
      }

      if (previewElements.heading) {
        previewElements.heading.style.color = primary;
      }

      if (previewElements.text) {
        previewElements.text.style.color = neutralSubtle;
      }

      if (previewElements.buttonPrimary) {
        previewElements.buttonPrimary.style.backgroundColor = primary;
        previewElements.buttonPrimary.style.borderColor = primary;
      }

      if (previewElements.buttonOutline) {
        previewElements.buttonOutline.style.color = primary;
        previewElements.buttonOutline.style.borderColor = toRgba(primary, 0.6);
      }

      if (previewElements.buttonMuted) {
        previewElements.buttonMuted.style.color = neutral;
        previewElements.buttonMuted.style.borderColor = toRgba(neutral, 0.25);
      }
    };

    const setColor = function (key, value, options) {
      const controller = controllers.get(key);
      if (!controller) {
        return;
      }

      const settings = options || {};
      const normalized = normalizeColor(value, controller.defaultColor);

      controller.color = normalized;

      if (controller.input && controller.input.value !== normalized) {
        controller.input.value = normalized;
      }

      if (controller.pickr && !settings.skipPickr) {
        controller.pickr.setColor(normalized, true);
      }

      if (!settings.silent) {
        updatePreview();
      }
    };

    const markResetFlag = function (isReset) {
      if (resetFlagInput) {
        resetFlagInput.value = isReset ? '1' : '0';
      }
    };

    colorItems.forEach(function (item) {
      const key = item.getAttribute('data-theme-item');
      if (!key) {
        return;
      }

      const defaultColor = normalizeColor(item.getAttribute('data-default-color'), '#05A5A8');
      defaultColors[key] = defaultColor;

      const input = item.querySelector('[data-theme-input="' + key + '"]');
      const pickerEl = item.querySelector('[data-theme-picker="' + key + '"]');
      const defaultTrigger = item.querySelector('[data-theme-default-trigger="' + key + '"]');

      const initialValue = normalizeColor(input ? input.value : defaultColor, defaultColor);

      const controller = {
        key: key,
        input: input,
        pickerEl: pickerEl,
        defaultColor: defaultColor,
        color: initialValue,
        pickr: null,
      };

      controllers.set(key, controller);

      if (input) {
        input.value = initialValue;
        input.addEventListener('input', function (event) {
          const candidate = event.target.value.toUpperCase();
          event.target.value = candidate;
        });

        input.addEventListener('blur', function () {
          setColor(key, input.value);
          markResetFlag(false);
        });
      }

      if (defaultTrigger) {
        defaultTrigger.addEventListener('click', function () {
          setColor(key, controller.defaultColor);
          markResetFlag(false);
        });
      }

      if (pickerEl) {
        pickerEl.classList.add('theme-color-picker-initialized');
      }

      if (pickerEl && pickrAvailable) {
        const recommendedSwatches = [
          controller.defaultColor,
          '#0EA5E9',
          '#38BDF8',
          '#2563EB',
          '#1D4ED8',
          '#6366F1',
          '#A855F7',
          '#C084FC',
          '#22C55E',
          '#10B981',
          '#14B8A6',
          '#FACC15',
          '#FBBF24',
          '#F97316',
          '#FB7185',
          '#EF4444',
          '#E11D48',
          '#BE123C',
          '#93370D',
          '#78350F',
          '#1E293B',
          '#0F172A',
          '#334155',
          '#475569',
          '#64748B',
          '#CBD5F5',
          '#E2E8F0',
          '#F1F5F9',
          '#F5F5F9'
        ];
        const swatches = recommendedSwatches.filter(function (color, index, arr) {
          return arr.indexOf(color) === index;
        });

        controller.pickr = Pickr.create({
          el: pickerEl,
          theme: 'nano',
          default: initialValue,
          swatches: swatches,
          components: {
            preview: true,
            opacity: false,
            hue: true,
            interaction: {
              hex: true,
              input: true,
              save: true,
              cancel: true,
            },
          },
        });

        controller.pickr.on('change', function (color) {
          if (!color) {
            return;
          }
          const hex = color.toHEXA().toString();
          setColor(key, hex, { skipPickr: true });
          markResetFlag(false);
        });

        controller.pickr.on('save', function (color, instance) {
          if (color) {
            const hex = color.toHEXA().toString();
            setColor(key, hex, { skipPickr: true });
            markResetFlag(false);
          }
          instance.hide();
        });

        controller.pickr.on('cancel', function (instance) {
          setColor(key, controller.color, { skipPickr: true });
          instance.hide();
        });
      }
    });

    const resetAllButton = document.querySelector('[data-theme-reset-all]');
    if (resetAllButton) {
      resetAllButton.addEventListener('click', function () {
        controllers.forEach(function (controller) {
          setColor(controller.key, controller.defaultColor, { skipPickr: false, silent: true });
        });
        updatePreview();
        markResetFlag(true);
      });
    }

    // Apply default palette if reset flag already set from previous submission
    if (resetFlagInput && resetFlagInput.value === '1') {
      controllers.forEach(function (controller) {
        setColor(controller.key, controller.defaultColor, { skipPickr: false, silent: true });
      });
      updatePreview();
    } else {
      updatePreview();
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
  } else {
    initialize();
  }
})();

