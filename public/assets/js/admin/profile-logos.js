(function () {
  'use strict';

  const ready = (fn) => {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  };

  ready(function () {
    const logoInputs = document.querySelectorAll('[data-logo-input]');
    if (!logoInputs.length) {
      return;
    }

    const inputMap = {};
    logoInputs.forEach((input) => {
      const key = input.getAttribute('data-crop-key') || input.getAttribute('data-preview-target');
      if (key) {
        inputMap[key] = input;
      }
    });

    const previewMap = {};
    document.querySelectorAll('[data-logo-preview-wrapper]').forEach((wrapper) => {
      const key = wrapper.getAttribute('data-logo-preview-wrapper');
      if (!key) {
        return;
      }
      previewMap[key] = {
        wrapper,
        image: wrapper.querySelector('[data-logo-preview-target]'),
        empty: wrapper.querySelector('[data-logo-preview-empty]')
      };
    });

    const removalMap = {};
    document.querySelectorAll('[data-logo-remove]').forEach((checkbox) => {
      const key = checkbox.getAttribute('data-preview-target');
      if (key) {
        removalMap[key] = checkbox;
      }
    });

    const metaMap = {};
    document.querySelectorAll('[data-logo-meta]').forEach((field) => {
      metaMap[field.id] = field;
    });

    const bootstrapRef = window.bootstrap || window.Bootstrap || window.Bootstrap5 || window.bootstrap5 || null;
    const supportsCropper = typeof window.Cropper !== 'undefined';
    let modalEl = document.getElementById('logoCropperModal');
    let imageEl = modalEl ? modalEl.querySelector('[data-cropper-image]') : null;
    const titleEl = modalEl ? modalEl.querySelector('[data-cropper-title]') : null;
    const maxLabelEl = modalEl ? modalEl.querySelector('[data-cropper-max]') : null;
    const confirmBtn = modalEl ? modalEl.querySelector('[data-cropper-confirm]') : null;
    const hasBootstrap = !!bootstrapRef && typeof bootstrapRef.Modal === 'function';
    let bootstrapModal = null;
    if (modalEl && hasBootstrap) {
      try {
        bootstrapModal = bootstrapRef.Modal.getInstance(modalEl) || new bootstrapRef.Modal(modalEl, {
          backdrop: 'static',
          keyboard: false,
          focus: true
        });
      } catch (error) {
        console.warn('[profile-logos] gagal inisialisasi bootstrap modal', error);
        bootstrapModal = null;
      }
    }
    const canUseModal = supportsCropper && !!bootstrapModal && !!imageEl;
    console.debug('[profile-logos] init', { supportsCropper, hasBootstrap, canUseModal });

    if (!supportsCropper) {
      console.warn('[profile-logos] Cropper.js tidak ditemukan, fallback ke unggah langsung.');
    }

    if (!hasBootstrap) {
      console.warn('[profile-logos] bootstrap.Modal tidak tersedia, fallback ke unggah langsung.');
    }

    const defaultConfig = {
      public: { aspect: null, max: 512, label: 'Logo OPD' }
    };

    const parseAspectRatioSetting = (input, key) => {
      const attrValue = input.getAttribute('data-crop-aspect');
      if (typeof attrValue === 'string') {
        const normalized = attrValue.trim().toLowerCase();
        if (normalized === '' || ['auto', 'free', 'flex', 'fluid', 'none'].includes(normalized)) {
          return null;
        }
        const parsed = parseFloat(normalized);
        if (!Number.isNaN(parsed) && parsed > 0) {
          return parsed;
        }
      }

      const fallbackConfig = defaultConfig[key];
      if (fallbackConfig && Object.prototype.hasOwnProperty.call(fallbackConfig, 'aspect')) {
        const fallbackValue = fallbackConfig.aspect;
        if (typeof fallbackValue === 'number' && fallbackValue > 0) {
          return fallbackValue;
        }
      }

      return null;
    };

    const parseMaxDimensionSetting = (input, key) => {
      const attrValue = input.getAttribute('data-crop-max');
      if (typeof attrValue === 'string') {
        const trimmed = attrValue.trim();
        if (trimmed !== '') {
          const parsed = parseInt(trimmed, 10);
          if (!Number.isNaN(parsed) && parsed > 0) {
            return parsed;
          }
        }
      }

      const fallbackConfig = defaultConfig[key];
      if (fallbackConfig && Object.prototype.hasOwnProperty.call(fallbackConfig, 'max')) {
        const fallbackValue = fallbackConfig.max;
        if (Number.isInteger(fallbackValue) && fallbackValue > 0) {
          return fallbackValue;
        }
      }

      return 512;
    };

    let cropper = null;
    let activeInput = null;
    let activeConfig = null;
    let actionConfirmed = false;

    const getPreview = (key) => previewMap[key] || null;
    const getRemoval = (key) => removalMap[key] || null;

    const setMeta = (input, meta) => {
      if (!input) {
        return;
      }
      const targetId = input.getAttribute('data-meta-target');
      if (!targetId) {
        return;
      }
      const field = metaMap[targetId];
      if (!field) {
        return;
      }
      field.value = meta ? JSON.stringify(meta) : '';
    };

    const setCurrentSrc = (key, src) => {
      const preview = getPreview(key);
      if (!preview || !preview.image) {
        return;
      }
      if (src) {
        preview.image.setAttribute('data-current-src', src);
      } else {
        preview.image.removeAttribute('data-current-src');
      }
    };

    const applyPreview = (key, overrideSrc, fallbackSrc, options = {}) => {
      const preview = getPreview(key);
      if (!preview || !preview.image || !preview.empty) {
        return;
      }

      const removal = getRemoval(key);
      const isRemoved = !!(removal && removal.checked);
      const storedSrc = preview.image.getAttribute('data-current-src') || preview.image.getAttribute('data-default-src') || '';
      let finalSrc = typeof overrideSrc === 'string' ? overrideSrc : storedSrc;

      if (!finalSrc && typeof fallbackSrc === 'string') {
        finalSrc = fallbackSrc;
      }

      if (isRemoved && !options.forceShow) {
        finalSrc = '';
      }

      if (options.setAsCurrent && finalSrc) {
        setCurrentSrc(key, finalSrc);
      }

      if (finalSrc) {
        preview.image.src = finalSrc;
        preview.image.classList.remove('d-none');
        preview.image.removeAttribute('hidden');
        preview.empty.classList.add('d-none');
        preview.empty.setAttribute('hidden', 'hidden');
        preview.wrapper.classList.add('has-image');
      } else {
        preview.image.classList.add('d-none');
        preview.image.setAttribute('hidden', 'hidden');
        const defaultSrc = preview.image.getAttribute('data-default-src');
        if (defaultSrc) {
          preview.image.src = defaultSrc;
        }
        preview.empty.classList.remove('d-none');
        preview.empty.removeAttribute('hidden');
        preview.wrapper.classList.remove('has-image');
      }

      preview.wrapper.classList.toggle('is-marked-for-removal', isRemoved);
    };

    const refreshPreviewForInput = (input, overrideSrc) => {
      const key = input.getAttribute('data-crop-key') || input.getAttribute('data-preview-target');
      if (!key) {
        return;
      }

      applyPreview(key, overrideSrc);
    };

    const handleRemovalChange = (key) => {
      const input = inputMap[key];
      if (!input) {
        applyPreview(key);
        return;
      }

      const removal = getRemoval(key);
      if (removal && removal.checked) {
        input.value = '';
        setMeta(input, null);
      }

      applyPreview(key);
    };

    Object.keys(removalMap).forEach((key) => {
      const checkbox = removalMap[key];
      checkbox.addEventListener('change', () => handleRemovalChange(key));
      handleRemovalChange(key);
    });

    const cleanUpCropper = () => {
      if (cropper) {
        cropper.destroy();
        cropper = null;
      }
    };

    const handleModalHidden = () => {
      cleanUpCropper();
      if (activeInput && !actionConfirmed) {
        activeInput.value = '';
        setMeta(activeInput, null);
        refreshPreviewForInput(activeInput);
      }
      activeInput = null;
      activeConfig = null;
      actionConfirmed = false;
      if (imageEl) {
        imageEl.src = '';
      }
    };

    const handleModalShown = () => {
      if (!supportsCropper || !imageEl || !activeConfig) {
        return;
      }

      cleanUpCropper();
      const aspectRatio = typeof activeConfig.aspect === 'number' && activeConfig.aspect > 0 ? activeConfig.aspect : NaN;
      const initialAspectRatio = Number.isNaN(aspectRatio) ? undefined : aspectRatio;
      cropper = new window.Cropper(imageEl, {
        aspectRatio,
        initialAspectRatio,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        responsive: true,
        background: false,
        zoomOnTouch: true,
        zoomOnWheel: true
      });
    };

    if (modalEl && bootstrapModal) {
      modalEl.addEventListener('hidden.bs.modal', handleModalHidden);
      modalEl.addEventListener('shown.bs.modal', handleModalShown);
    }

    const readFileAsDataUrl = (file, callback) => {
      if (!file) {
        callback('');
        return;
      }

      const reader = new FileReader();
      reader.onload = (event) => {
        callback(event.target?.result || '');
      };
      reader.onerror = () => callback('');
      reader.readAsDataURL(file);
    };

    const createFileName = (input, extension) => {
      const original = input.files && input.files[0] ? input.files[0].name : '';
      const base = original ? original.replace(/\.[^/.]+$/, '') : (input.getAttribute('data-crop-label') || 'logo');
      const safeBase = base.toLowerCase().replace(/[^a-z0-9_-]+/g, '-').replace(/^-+|-+$/g, '') || 'logo';
      return `${safeBase}-${Date.now()}.${extension}`;
    };

    const ensureModalReady = () => {
      if (!supportsCropper) {
        return { ready: false, reason: 'cropper-missing' };
      }

      if (!modalEl || !document.body.contains(modalEl)) {
        modalEl = document.getElementById('logoCropperModal');
      }

      if (!modalEl) {
        return { ready: false, reason: 'modal-missing' };
      }

      if (!imageEl || !modalEl.contains(imageEl)) {
        imageEl = modalEl.querySelector('[data-cropper-image]');
      }

      if (!imageEl) {
        return { ready: false, reason: 'image-missing' };
      }

      if (!bootstrapRef || typeof bootstrapRef.Modal !== 'function') {
        return { ready: false, reason: 'bootstrap-missing' };
      }

      if (!bootstrapModal) {
        try {
          bootstrapModal = bootstrapRef.Modal.getInstance(modalEl) || new bootstrapRef.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false,
            focus: true
          });
        } catch (error) {
          console.warn('[profile-logos] gagal membuat modal', error);
          return { ready: false, reason: 'modal-init-error' };
        }
      }

      return { ready: true };
    };

    const handleInputChange = (event) => {
      const input = event.currentTarget;
      if (!input) {
        return;
      }

      if (!input.files || !input.files.length) {
        setMeta(input, null);
        refreshPreviewForInput(input);
        return;
      }

      const key = input.getAttribute('data-crop-key') || 'public';
      const config = {
        key,
        label: input.getAttribute('data-crop-label') || (defaultConfig[key] && defaultConfig[key].label) || 'Logo',
        aspect: parseAspectRatioSetting(input, key),
        max: parseMaxDimensionSetting(input, key)
      };

      const modalState = ensureModalReady();
      if (!modalState.ready) {
        console.debug('[profile-logos] fallback upload', { key, reason: modalState.reason });
        readFileAsDataUrl(input.files[0], (result) => {
          if (result) {
            setCurrentSrc(key, result);
            applyPreview(key, result, undefined, { setAsCurrent: true });
            const dimension = Math.min(config.max,  Math.round(input.files[0].size / 1024));
            setMeta(input, {
              source: 'direct',
              timestamp: Date.now(),
              maxDimension: config.max,
              approximateSizeKb: dimension
            });
          }
        });
        return;
      }

      activeInput = input;
      activeConfig = config;
      actionConfirmed = false;

      if (titleEl) {
        titleEl.textContent = config.label;
      }
      if (maxLabelEl) {
        maxLabelEl.textContent = config.max;
      }

      console.debug('[profile-logos] open cropper modal', { key, config });

      readFileAsDataUrl(input.files[0], (result) => {
        if (!result) {
          return;
        }
        imageEl.src = result;
        bootstrapModal.show();
      });
    };

    logoInputs.forEach((input) => {
      input.addEventListener('change', handleInputChange);
      refreshPreviewForInput(input);
    });

    if (confirmBtn && bootstrapModal) {
      confirmBtn.addEventListener('click', () => {
        if (!cropper || !activeInput || !activeConfig) {
          return;
        }

        const maxDimension = activeConfig.max;
        const cropData = cropper.getData(true);

        const computeDimensions = () => {
          let targetWidth = maxDimension;
          let targetHeight = maxDimension;
          let aspect = null;

          if (cropData && cropData.width > 0 && cropData.height > 0) {
            aspect = cropData.width / cropData.height;
          } else if (typeof activeConfig.aspect === 'number' && activeConfig.aspect > 0) {
            aspect = activeConfig.aspect;
          }

          if (aspect && aspect > 0) {
            if (aspect >= 1) {
              targetWidth = maxDimension;
              targetHeight = Math.max(1, Math.round(maxDimension / aspect));
            } else {
              targetHeight = maxDimension;
              targetWidth = Math.max(1, Math.round(maxDimension * aspect));
            }
          }

          return { targetWidth, targetHeight };
        };

        const { targetWidth, targetHeight } = computeDimensions();

        const canvas = cropper.getCroppedCanvas({
          width: targetWidth,
          height: targetHeight,
          imageSmoothingEnabled: true,
          imageSmoothingQuality: 'high'
        });

        if (!canvas) {
          bootstrapModal.hide();
          return;
        }

        const preferredFormats = (activeInput.getAttribute('accept') || '').split(',').map((item) => item.trim());
        let mimeType = 'image/png';
        if (preferredFormats.includes('image/webp')) {
          mimeType = 'image/webp';
        } else if (preferredFormats.includes('image/jpeg') || preferredFormats.includes('image/jpg')) {
          mimeType = 'image/jpeg';
        }

        canvas.toBlob((blob) => {
          if (!blob) {
            bootstrapModal.hide();
            return;
          }

          const extension = mimeType.split('/')[1] || 'png';
          const file = new File([blob], createFileName(activeInput, extension), {
            type: mimeType,
            lastModified: Date.now()
          });

          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          activeInput.files = dataTransfer.files;

          const key = activeConfig.key;
          const dataUrl = canvas.toDataURL(mimeType, 0.92);
          setCurrentSrc(key, dataUrl);
          applyPreview(key, dataUrl, undefined, { setAsCurrent: true });
          setMeta(activeInput, {
            source: 'cropper',
            timestamp: Date.now(),
            width: canvas.width,
            height: canvas.height,
            maxDimension,
            mime: mimeType
          });

          const removal = getRemoval(key);
          if (removal) {
            removal.checked = false;
            removal.dispatchEvent(new Event('change'));
          }

          actionConfirmed = true;
          bootstrapModal.hide();
        }, mimeType, 0.92);
      });
    }
  });
})();
