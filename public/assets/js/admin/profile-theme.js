(function () {
  const clamp = function (value, min, max) {
    return Math.min(Math.max(value, min), max);
  };

  const normalizeHex = function (value, fallback) {
    const fallbackColor = typeof fallback === 'string' && fallback !== '' ? fallback : '#05A5A8';
    let candidate = typeof value === 'string' ? value.trim() : '';

    if (candidate === '') {
      candidate = fallbackColor;
    }

    if (candidate[0] !== '#') {
      candidate = '#' + candidate.replace(/^#+/, '');
    }

    candidate = candidate.toUpperCase();

    if (/^#[0-9A-F]{3}$/.test(candidate)) {
      candidate = '#' + candidate[1] + candidate[1] + candidate[2] + candidate[2] + candidate[3] + candidate[3];
    }

    if (!/^#[0-9A-F]{6}$/.test(candidate)) {
      return normalizeHex(fallbackColor, '#05A5A8');
    }

    return candidate;
  };

  const hexToRgb = function (hex) {
    const normalized = normalizeHex(hex, '#000000').replace('#', '');
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

  const relativeLuminance = function (hex) {
    const { r, g, b } = hexToRgb(hex);
    const toLinear = function (channel) {
      const value = channel / 255;
      return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
    };
    const rLinear = toLinear(r);
    const gLinear = toLinear(g);
    const bLinear = toLinear(b);
    return 0.2126 * rLinear + 0.7152 * gLinear + 0.0722 * bLinear;
  };

  const deriveNeutralFromSurface = function (surface) {
    const background = normalizeHex(surface, '#F5F5F9');
    const isLightSurface = relativeLuminance(background) >= 0.6;
    const darkPalette = ['#111827', '#162033', '#1E293B', '#22303E', '#2D3748'];
    const lightPalette = ['#F8FAFC', '#F5F5F9', '#F3F4F6', '#EEF2FF', '#FFFFFF'];
    const palette = isLightSurface ? darkPalette : lightPalette;

    for (let i = 0; i < palette.length; i += 1) {
      const candidate = palette[i];
      const luminanceForeground = relativeLuminance(candidate);
      const luminanceBackground = relativeLuminance(background);
      const lighter = Math.max(luminanceForeground, luminanceBackground);
      const darker = Math.min(luminanceForeground, luminanceBackground);
      const ratio = (lighter + 0.05) / (darker + 0.05);
      if (ratio >= 4.5) {
        return candidate;
      }
    }

    return isLightSurface ? '#111111' : '#FFFFFF';
  };

  const deriveAccent = function (primary) {
    return lighten(primary, 0.35);
  };

  const readPresetColors = function (card) {
    return {
      primary: normalizeHex(card.getAttribute('data-theme-primary') || '#05A5A8', '#05A5A8'),
      surface: normalizeHex(card.getAttribute('data-theme-surface') || '#F5F5F9', '#F5F5F9'),
    };
  };

  const updatePreview = function (previewCard, colors) {
    if (!previewCard) {
      return;
    }

    const primary = colors.primary;
    const surface = colors.surface;
    const neutral = deriveNeutralFromSurface(surface);
    const primaryDark = darken(primary, 0.25);
    const primarySoft = toRgba(primary, 0.12);
    const neutralSubtle = toRgba(neutral, 0.65);
    const accent = deriveAccent(primary);

    const previewElements = {
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
    };

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

  const getCheckedMode = function (modeInputs) {
    const checked = modeInputs.find(function (input) {
      return input.checked;
    });

    return checked ? checked.value : 'preset';
  };

  const init = function () {
    const presetGrid = document.querySelector('[data-theme-preset-grid]');
    const presetCards = presetGrid ? Array.from(presetGrid.querySelectorAll('[data-theme-preset-card]')) : [];
    const modeInputs = Array.from(document.querySelectorAll('[data-theme-mode-input]'));
    const panes = Array.from(document.querySelectorAll('[data-theme-pane]'));
    const previewCard = document.querySelector('[data-theme-preview]');
    const defaultPresetSlug = presetGrid ? presetGrid.getAttribute('data-theme-default') || '' : '';
    const presetFilterButtons = Array.from(document.querySelectorAll('[data-theme-preset-filter]'));
    let activePresetFilter = presetGrid ? presetGrid.getAttribute('data-theme-active-filter') || 'all' : 'all';
    const resetPresetButton = document.querySelector('[data-theme-preset-reset]');
    const resetCustomButton = document.querySelector('[data-theme-custom-reset]');
    const customInputs = {
      primary: document.querySelector('[data-theme-custom-input="primary"]'),
      surface: document.querySelector('[data-theme-custom-input="surface"]'),
    };
    const customPickers = {
      primary: document.querySelector('[data-color-picker="primary"]'),
      surface: document.querySelector('[data-color-picker="surface"]'),
    };
    const defaultColors = {
      primary: normalizeHex(customInputs.primary ? customInputs.primary.getAttribute('data-default-color') : '#05A5A8', '#05A5A8'),
      surface: normalizeHex(customInputs.surface ? customInputs.surface.getAttribute('data-default-color') : '#F5F5F9', '#F5F5F9'),
    };

    const state = {
      activeCard: null,
    };

    const showActivePane = function () {
      const mode = getCheckedMode(modeInputs);
      panes.forEach(function (pane) {
        const paneName = pane.getAttribute('data-theme-pane');
        if (!paneName) {
          return;
        }
        pane.classList.toggle('d-none', paneName !== mode);
      });
    };

    const readCustomColors = function () {
      return {
        primary: normalizeHex(customInputs.primary ? customInputs.primary.value : '#05A5A8', defaultColors.primary),
        surface: normalizeHex(customInputs.surface ? customInputs.surface.value : '#F5F5F9', defaultColors.surface),
      };
    };

    const findFirstVisibleCard = function () {
      return presetCards.find(function (card) {
        return !card.classList.contains('d-none');
      }) || null;
    };

    const readPresetSelection = function () {
      if (state.activeCard && !state.activeCard.classList.contains('d-none')) {
        return readPresetColors(state.activeCard);
      }

      const fallback = findFirstVisibleCard() || presetCards[0];

      return fallback ? readPresetColors(fallback) : { primary: '#05A5A8', surface: '#F5F5F9' };
    };

    const refreshPreview = function () {
      const mode = getCheckedMode(modeInputs);
      if (mode === 'custom') {
        updatePreview(previewCard, readCustomColors());
      } else {
        updatePreview(previewCard, readPresetSelection());
      }
    };

    const setActiveCard = function (card, options) {
      if (card && card.classList.contains('d-none')) {
        card = null;
      }

      if (!card || card === state.activeCard) {
        if (!options || !options.silent) {
          refreshPreview();
        }
        return;
      }

      if (state.activeCard) {
        state.activeCard.classList.remove('is-active');
      }

      state.activeCard = card;
      state.activeCard.classList.add('is-active');

      if (!options || !options.silent) {
        refreshPreview();
      }
    };

    presetCards.forEach(function (card) {
      const input = card.querySelector('[data-theme-preset-input]');
      if (!input) {
        return;
      }

      if (card.classList.contains('is-active') && !state.activeCard) {
        state.activeCard = card;
      }

      const handleSelection = function () {
        setActiveCard(card);
      };

      card.addEventListener('click', function (event) {
        if (event.target && event.target.tagName === 'INPUT') {
          return;
        }
        input.checked = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      input.addEventListener('change', handleSelection);
    });

    if (presetFilterButtons.length > 0) {
      presetFilterButtons.forEach(function (button) {
        button.addEventListener('change', function () {
          if (!button.checked) {
            return;
          }
          applyPresetFilter(button.value);
        });
      });

      if (activePresetFilter !== 'all') {
        applyPresetFilter(activePresetFilter, { silent: true });
      }
    }

    if (!state.activeCard && presetCards.length > 0) {
      const fallbackCard = presetCards.find(function (card) {
        return card.getAttribute('data-theme-preset') === defaultPresetSlug;
      }) || presetCards[0];
      setActiveCard(fallbackCard, { silent: true });
      const fallbackInput = fallbackCard.querySelector('[data-theme-preset-input]');
      if (fallbackInput) {
        fallbackInput.checked = true;
      }
    }

    const activatePresetSlug = function (slug) {
      let card = presetCards.find(function (item) {
        return item.getAttribute('data-theme-preset') === slug;
      });

      if (card && card.classList.contains('d-none')) {
        applyPresetFilter('all', { silent: true });
        card = presetCards.find(function (item) {
          return item.getAttribute('data-theme-preset') === slug;
        });
      }

      if (card) {
        const input = card.querySelector('[data-theme-preset-input]');
        if (input) {
          input.checked = true;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
          setActiveCard(card);
        }
      }
    };

    const applyPresetFilter = function (filterValue, options) {
      activePresetFilter = filterValue;
      if (presetGrid) {
        presetGrid.setAttribute('data-theme-active-filter', filterValue);
      }

      presetFilterButtons.forEach(function (button) {
        if (button.value === filterValue) {
          button.checked = true;
        }
      });

      let visibleCount = 0;
      presetCards.forEach(function (card) {
        const tone = card.getAttribute('data-theme-tone') || 'dark';
        const shouldHide = filterValue !== 'all' && tone !== filterValue;
        card.classList.toggle('d-none', shouldHide);
        if (!shouldHide) {
          visibleCount += 1;
        }
      });

      if (visibleCount === 0 && filterValue !== 'all') {
        applyPresetFilter('all', { silent: true });
        return;
      }

      if (state.activeCard && state.activeCard.classList.contains('d-none')) {
        const fallbackCard = findFirstVisibleCard();
        if (fallbackCard) {
          setActiveCard(fallbackCard, { silent: true });
          const fallbackInput = fallbackCard.querySelector('[data-theme-preset-input]');
          if (fallbackInput) {
            fallbackInput.checked = true;
          }
        } else {
          state.activeCard = null;
        }
      }

      if (!options || !options.silent) {
        refreshPreview();
      }
    };

    if (resetPresetButton) {
      resetPresetButton.addEventListener('click', function () {
        applyPresetFilter('all', { silent: true });
        activatePresetSlug(defaultPresetSlug || (presetCards[0] ? presetCards[0].getAttribute('data-theme-preset') : ''));
        const presetModeInput = modeInputs.find(function (input) {
          return input.value === 'preset';
        });
        if (presetModeInput) {
          presetModeInput.checked = true;
        }
        showActivePane();
        refreshPreview();
      });
    }

    const formatRawHexInput = function (value) {
      let candidate = (value || '').toUpperCase().replace(/[^0-9A-F#]/g, '');
      if (candidate === '') {
        return '';
      }

      candidate = candidate.replace(/#+/g, '');
      candidate = '#' + candidate;

      if (candidate.length > 7) {
        candidate = candidate.slice(0, 7);
      }

      return candidate;
    };

    const setCustomColor = function (key, value, options) {
      const input = customInputs[key];
      const picker = customPickers[key];
      const defaults = defaultColors[key] || '#05A5A8';
      const normalized = normalizeHex(value, defaults);
      const settings = options || {};

      if (input) {
        input.value = normalized;
      }

      if (picker && !settings.skipPicker) {
        picker.color = normalized;
      }

      if (!settings.silent) {
        const mode = getCheckedMode(modeInputs);
        if (mode === 'custom') {
          refreshPreview();
        }
      }
    };

    Object.keys(customInputs).forEach(function (key) {
      const input = customInputs[key];
      if (!input) {
        return;
      }

      input.addEventListener('input', function (event) {
        const formatted = formatRawHexInput(event.target.value);
        event.target.value = formatted;
      });

      input.addEventListener('blur', function (event) {
        setCustomColor(key, event.target.value || defaultColors[key]);
      });

      setCustomColor(key, input.value, { silent: true });
    });

    Object.keys(customPickers).forEach(function (key) {
      const picker = customPickers[key];
      if (!picker) {
        return;
      }

      picker.color = normalizeHex(picker.getAttribute('color'), defaultColors[key]);

      picker.addEventListener('color-changed', function (event) {
        const rawValue = event && event.detail ? event.detail.value : null;
        if (!rawValue) {
          return;
        }
        setCustomColor(key, rawValue, { skipPicker: true });
      });
    });

    if (resetCustomButton) {
      resetCustomButton.addEventListener('click', function () {
        setCustomColor('primary', defaultColors.primary, { silent: true });
        setCustomColor('surface', defaultColors.surface, { silent: true });
        const customModeInput = modeInputs.find(function (input) {
          return input.value === 'custom';
        });
        if (customModeInput) {
          customModeInput.checked = true;
        }
        showActivePane();
        refreshPreview();
      });
    }

    modeInputs.forEach(function (input) {
      input.addEventListener('change', function () {
        showActivePane();
        refreshPreview();
      });
    });

    showActivePane();
    refreshPreview();
  };

  const start = function () {
    const whenReady = (window.customElements && typeof window.customElements.whenDefined === 'function')
      ? customElements.whenDefined('hex-color-picker').catch(function () {
          return undefined;
        })
      : Promise.resolve();

    whenReady.finally(init);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
})();
