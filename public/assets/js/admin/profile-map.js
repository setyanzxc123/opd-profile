/* eslint-disable no-void */
(function () {
  'use strict';

  const ready = (fn) => {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    }
  };

  const debounce = (fn, wait) => {
    let timer = null;
    return (...args) => {
      if (timer) {
        clearTimeout(timer);
      }
      timer = setTimeout(() => {
        fn(...args);
      }, wait);
    };
  };

  const formatCoordinate = (value) => {
    const parsed = Number.parseFloat(value);
    if (Number.isNaN(parsed) || !Number.isFinite(parsed)) {
      return '';
    }
    return parsed.toFixed(6);
  };

  ready(() => {
    const leafletAvailable = typeof window.L !== 'undefined' && typeof window.L.map === 'function';
    const mapElement = document.getElementById('profile-map');

    if (!leafletAvailable || !mapElement) {
      if (!leafletAvailable) {
        console.warn('[profile-map] Leaflet tidak ditemukan.');
      }
      return;
    }

    const latField = document.querySelector('input[name="latitude"]');
    const lngField = document.querySelector('input[name="longitude"]');
    const zoomField = document.querySelector('input[name="map_zoom"]');
    const displayField = document.getElementById('field-map-display');
    const feedbackEl = document.querySelector('[data-map-feedback]');
    const searchInput = document.querySelector('[data-map-search-input]');
    const clearButton = document.querySelector('[data-map-search-clear]');
    const resultsContainer = document.querySelector('[data-map-search-results]');

    const dataset = mapElement.dataset || {};
    const defaultLat = Number.parseFloat(dataset.defaultLat);
    const defaultLng = Number.parseFloat(dataset.defaultLng);
    const defaultZoom = Number.parseInt(dataset.defaultZoom || '5', 10);
    const searchUrl = dataset.searchUrl || '';

    const fallbackLat = Number.isFinite(defaultLat) ? defaultLat : -2.548926;
    const fallbackLng = Number.isFinite(defaultLng) ? defaultLng : 118.0148634;
    const fallbackZoom = Number.isInteger(defaultZoom) ? defaultZoom : 5;

    const currentLat = latField && latField.value !== '' ? Number.parseFloat(latField.value) : NaN;
    const currentLng = lngField && lngField.value !== '' ? Number.parseFloat(lngField.value) : NaN;
    const currentZoom = zoomField && zoomField.value !== '' ? Number.parseInt(zoomField.value, 10) : NaN;

    const initialLat = Number.isFinite(currentLat) ? currentLat : fallbackLat;
    const initialLng = Number.isFinite(currentLng) ? currentLng : fallbackLng;
    const initialZoom = Number.isInteger(currentZoom) ? currentZoom : fallbackZoom;

    const map = window.L.map(mapElement, {
      zoomControl: true,
      scrollWheelZoom: true,
    }).setView([initialLat, initialLng], initialZoom);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
      maxZoom: 19,
    }).addTo(map);

    const marker = window.L.marker([initialLat, initialLng], {
      draggable: true,
      autoPan: true,
    }).addTo(map);

    const updateFormFields = (lat, lng) => {
      if (latField) {
        latField.value = formatCoordinate(lat);
      }
      if (lngField) {
        lngField.value = formatCoordinate(lng);
      }
    };

    const showFeedback = (message, type = 'warning') => {
      if (!feedbackEl) {
        return;
      }
      if (!message) {
        feedbackEl.classList.remove('active', 'alert-warning', 'alert-danger', 'alert-info', 'alert-success');
        feedbackEl.textContent = '';
        return;
      }
      feedbackEl.textContent = message;
      feedbackEl.classList.add('active');
      const classes = ['alert-warning', 'alert-danger', 'alert-info', 'alert-success'];
      classes.forEach((cls) => feedbackEl.classList.remove(cls));
      feedbackEl.classList.add(`alert-${type}`);
    };

    const moveMarker = (latlng, options = {}) => {
      if (!latlng) {
        return;
      }
      marker.setLatLng(latlng);
      if (options.panTo !== false) {
        map.panTo(latlng);
      }
      updateFormFields(latlng.lat, latlng.lng);
      if (displayField && !displayField.checked) {
        showFeedback('Peta saat ini tidak ditampilkan di situs publik. Aktifkan sakelar "Tampilkan peta" untuk menampilkan marker.', 'info');
      } else {
        showFeedback('');
      }
    };

    moveMarker(window.L.latLng(initialLat, initialLng), { panTo: false });
    if (zoomField && !Number.isInteger(currentZoom)) {
      zoomField.value = String(map.getZoom());
    }

    map.on('click', (event) => {
      moveMarker(event.latlng);
    });

    map.on('zoomend', () => {
      if (zoomField) {
        zoomField.value = String(map.getZoom());
      }
    });

    marker.on('dragend', (event) => {
      const latlng = event.target.getLatLng();
      moveMarker(latlng, { panTo: false });
    });

    let searchAbort = null;
    let latestResults = [];
    let activeIndex = -1;

    const hideResults = () => {
      if (!resultsContainer) {
        return;
      }
      resultsContainer.classList.add('d-none');
      resultsContainer.innerHTML = '';
      activeIndex = -1;
    };

    const renderMessage = (message, type = 'muted') => {
      if (!resultsContainer) {
        return;
      }
      resultsContainer.innerHTML = '';
      const item = document.createElement('div');
      item.className = `list-group-item small text-${type}`;
      item.textContent = message;
      resultsContainer.appendChild(item);
    };

    const setActiveResult = (index) => {
      if (!resultsContainer) {
        return;
      }
      const options = resultsContainer.querySelectorAll('[data-map-result-index]');
      options.forEach((option) => option.classList.remove('active'));

      if (index < 0 || index >= options.length) {
        activeIndex = -1;
        return;
      }

      const option = options[index];
      option.classList.add('active');
      option.scrollIntoView({ block: 'nearest' });
      activeIndex = index;
    };

    const selectResult = (index) => {
      if (!Number.isInteger(index) || index < 0 || index >= latestResults.length) {
        return;
      }

      const item = latestResults[index];
      const lat = Number.parseFloat(item.lat ?? item.latitude);
      const lng = Number.parseFloat(item.lng ?? item.lon ?? item.longitude);
      if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return;
      }

      if (searchInput) {
        searchInput.value = item.label || item.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        searchInput.focus();
      }

      const latLng = window.L.latLng(lat, lng);
      moveMarker(latLng);

      const bbox = Array.isArray(item.boundingBox) ? item.boundingBox : item.boundingbox;
      if (Array.isArray(bbox) && bbox.length === 4) {
        const parts = bbox.map((value) => Number.parseFloat(value));
        if (parts.every((value) => Number.isFinite(value))) {
          const bounds = window.L.latLngBounds(
            [parts[0], parts[2]],
            [parts[1], parts[3]],
          );
          map.fitBounds(bounds);
        }
      }

      hideResults();
    };

    const renderResults = (items) => {
      if (!resultsContainer) {
        return;
      }

      latestResults = items;
      resultsContainer.innerHTML = '';
      activeIndex = -1;

      if (!items.length) {
        renderMessage('Lokasi tidak ditemukan. Coba kata kunci lain.', 'muted');
        return;
      }

      items.forEach((item, index) => {
        const lat = Number.parseFloat(item.lat ?? item.latitude);
        const lng = Number.parseFloat(item.lng ?? item.lon ?? item.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
          return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action text-start';
        button.dataset.mapResultIndex = String(index);
        button.setAttribute('role', 'option');
        button.innerHTML = `
          <div class="fw-semibold">${item.label || item.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`}</div>
          <div class="small text-muted">${lat.toFixed(6)}, ${lng.toFixed(6)}</div>
        `;

        button.addEventListener('click', () => selectResult(index));
        button.addEventListener('mouseenter', () => setActiveResult(index));

        resultsContainer.appendChild(button);
      });
    };

    const performSearch = debounce((keyword) => {
      if (!resultsContainer || !searchUrl) {
        return;
      }
      if (keyword.length < 3) {
        hideResults();
        return;
      }

      if (searchAbort) {
        searchAbort.abort();
      }
      searchAbort = new AbortController();
      const { signal } = searchAbort;

      resultsContainer.classList.remove('d-none');
      renderMessage('Mencari lokasi…', 'muted');
      showFeedback('Mencari lokasi…', 'info');

      fetch(`${searchUrl}?q=${encodeURIComponent(keyword)}`, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        signal,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`Status HTTP ${response.status}`);
          }
          return response.json();
        })
        .then((payload) => {
          const items = Array.isArray(payload?.data) ? payload.data : [];
          renderResults(items);
          showFeedback('');
        })
        .catch((error) => {
          if (error.name === 'AbortError') {
            return;
          }
          console.error('[profile-map] pencarian gagal', error);
          resultsContainer.classList.remove('d-none');
          renderMessage('Gagal mengambil data lokasi. Coba lagi nanti.', 'danger');
          showFeedback('Gagal mengambil data lokasi. Coba lagi nanti.', 'danger');
        });
    }, 350);

    if (searchInput) {
      searchInput.addEventListener('input', (event) => {
        const keyword = String(event.target.value || '').trim();
        if (keyword.length < 3) {
          hideResults();
          return;
        }
        performSearch(keyword);
      });
    }

    if (clearButton) {
      clearButton.addEventListener('click', () => {
        if (searchInput) {
          searchInput.value = '';
          searchInput.focus();
        }
        latestResults = [];
        hideResults();
      });
    }

    if (resultsContainer && searchInput) {
      searchInput.addEventListener('keydown', (event) => {
        const visible = !resultsContainer.classList.contains('d-none');
        if (!visible) {
          return;
        }

        if (event.key === 'ArrowDown') {
          event.preventDefault();
          setActiveResult(activeIndex + 1);
          return;
        }
        if (event.key === 'ArrowUp') {
          event.preventDefault();
          setActiveResult(activeIndex - 1);
          return;
        }
        if (event.key === 'Enter') {
          if (activeIndex >= 0) {
            event.preventDefault();
            selectResult(activeIndex);
          }
          return;
        }
        if (event.key === 'Escape') {
          hideResults();
        }
      });
    }

    if (resultsContainer) {
      document.addEventListener('click', (event) => {
        if (resultsContainer.classList.contains('d-none')) {
          return;
        }
        if (event.target === searchInput || resultsContainer.contains(event.target)) {
          return;
        }
        hideResults();
      });
    }

    setTimeout(() => {
      map.invalidateSize();
    }, 250);

    const tabTrigger = document.querySelector('button[data-bs-target="#tab-kontak"]');
    if (tabTrigger) {
      tabTrigger.addEventListener('shown.bs.tab', () => {
        map.invalidateSize();
      });
    }

    if (displayField) {
      displayField.addEventListener('change', () => {
        if (displayField.checked) {
          showFeedback('');
        }
      });
      if (!displayField.checked) {
        showFeedback('Peta saat ini tidak ditampilkan di situs publik. Aktifkan sakelar "Tampilkan peta" untuk menampilkan marker.', 'info');
      }
    }
  });
})();
