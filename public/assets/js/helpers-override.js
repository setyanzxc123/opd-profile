// Lightweight overrides to ensure sidebar collapse/expand works
// without touching vendor bundles. Loaded after vendor/helpers.js.
(function () {
  window.Helpers = window.Helpers || {};
  var H = window.Helpers;

  // Breakpoint to decide off-canvas vs. collapsed
  H.LAYOUT_BREAKPOINT = H.LAYOUT_BREAKPOINT || 1200;

  if (typeof H.isSmallScreen !== 'function') {
    H.isSmallScreen = function () {
      try {
        return window.innerWidth < H.LAYOUT_BREAKPOINT;
      } catch (e) {
        return false;
      }
    };
  }

  function updateChevronIcon() {
    try {
      var icon = document.querySelector('#layout-menu .app-brand .layout-menu-toggle i');
      if (!icon) return;
      var collapsed = document.body.classList.contains('layout-menu-collapsed');
      icon.classList.remove('bx-chevron-left', 'bx-chevron-right');
      icon.classList.add(collapsed ? 'bx-chevron-right' : 'bx-chevron-left');
    } catch (e) {
      /* no-op */
    }
  }

  // Toggle collapsed/expanded state
  H.toggleCollapsed = function () {
    if (H.isSmallScreen && H.isSmallScreen()) {
      document.body.classList.toggle('layout-menu-expanded');
    } else {
      document.body.classList.toggle('layout-menu-collapsed');
    }
    updateChevronIcon();
  };

  // Provide setCollapsed if not present (used by main.js)
  if (typeof H.setCollapsed !== 'function') {
    H.setCollapsed = function (state /* bool */, /* animate */) {
      document.body.classList.toggle('layout-menu-collapsed', !!state);
      updateChevronIcon();
    };
  }

  // Initial sync on load
  if (document.readyState === 'complete') updateChevronIcon();
  else {
    document.addEventListener('DOMContentLoaded', updateChevronIcon, { once: true });
  }
})();
