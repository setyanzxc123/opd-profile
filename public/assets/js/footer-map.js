(() => {
  const escapeHtml = (value) => {
    if (typeof value !== 'string' || value === '') {
      return '';
    }

    if (typeof L !== 'undefined' && L.Util && typeof L.Util.escapeHTML === 'function') {
      return L.Util.escapeHTML(value);
    }

    const div = document.createElement('div');
    div.appendChild(document.createTextNode(value));
    return div.innerHTML;
  };

  const initializeFooterMap = () => {
    const mapContainer = document.getElementById('footer-map');

    if (!mapContainer || typeof L === 'undefined') {
      return;
    }

    const dataset = mapContainer.dataset || {};
    const lat = parseFloat(dataset.lat);
    const lng = parseFloat(dataset.lng);

    if (Number.isNaN(lat) || Number.isNaN(lng)) {
      return;
    }

    const zoom = parseInt(dataset.zoom || '16', 10);
    const title = escapeHtml(dataset.title || 'Lokasi OPD');
    const addressRaw = dataset.address || '';
    const address = escapeHtml(addressRaw).replace(/\n/g, '<br>');

    const map = L.map(mapContainer, {
      scrollWheelZoom: false,
      dragging: true,
      tap: true,
    }).setView([lat, lng], Number.isNaN(zoom) ? 16 : zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
      maxZoom: 19,
    }).addTo(map);

    const marker = L.marker([lat, lng]).addTo(map);

    let popupHtml = `<strong>${title}</strong>`;
    if (address) {
      popupHtml += `<div class="mt-1">${address}</div>`;
    }

    const directionUrl = `https://www.google.com/maps?q=${lat},${lng}`;
    popupHtml += `<div class="mt-2"><a href="${directionUrl}" target="_blank" rel="noopener">Buka di Google Maps</a></div>`;

    marker.bindPopup(popupHtml);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFooterMap, { once: true });
  } else {
    initializeFooterMap();
  }
})();
