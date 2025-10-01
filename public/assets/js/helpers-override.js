// Custom helpers to manage sidebar collapse with consistent desktop/mobile behaviour.
(function () {
  var win = window;
  var Helpers = (win.Helpers = win.Helpers || {});
  var doc = document;
  var body = doc.body;

  if (!body) {
    return;
  }

  var DESKTOP_BREAKPOINT = Helpers.LAYOUT_BREAKPOINT || 1200;
  var STORAGE_KEY = 'opd-admin-menu-collapsed';
  var mqDesktop = win.matchMedia('(min-width: ' + DESKTOP_BREAKPOINT + 'px)');
  var overlay = doc.querySelector('.layout-overlay');
  var menuLinks = Array.from(doc.querySelectorAll('#layout-menu .menu-inner a.menu-link'));

  var state = {
    desktopCollapsed: body.classList.contains('layout-menu-collapsed'),
    mobileOpen: body.classList.contains('layout-menu-expanded')
  };

  try {
    var stored = win.localStorage.getItem(STORAGE_KEY);
    if (stored !== null) {
      state.desktopCollapsed = stored === '1';
    }
  } catch (err) {
    // Ignore storage errors.
  }

  var resizeFrame = 0;
  function queueResizeEvent() {
    if (resizeFrame) {
      win.cancelAnimationFrame(resizeFrame);
    }
    resizeFrame = win.requestAnimationFrame(function () {
      resizeFrame = win.requestAnimationFrame(function () {
        try {
          win.dispatchEvent(new Event('resize'));
        } catch (e) {
          // Older browsers without Event constructor.
        }
        resizeFrame = 0;
      });
    });
  }

  function syncToggleAffordances() {
    var expanded = mqDesktop.matches ? !state.desktopCollapsed : state.mobileOpen;
    doc.querySelectorAll('.layout-menu-toggle').forEach(function (btn) {
      btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      var icon = btn.querySelector('i');
      if (icon) {
        icon.classList.remove('bx-chevron-left', 'bx-chevron-right');
        icon.classList.add(expanded ? 'bx-chevron-left' : 'bx-chevron-right');
      }
    });
  }

  function applyState() {
    if (mqDesktop.matches) {
      body.classList.toggle('layout-menu-collapsed', state.desktopCollapsed);
      body.classList.remove('layout-menu-expanded');
      state.mobileOpen = false;
    } else {
      body.classList.remove('layout-menu-collapsed');
      body.classList.toggle('layout-menu-expanded', state.mobileOpen);
    }
    syncToggleAffordances();
    queueResizeEvent();
  }

  function setDesktopCollapsed(collapsed) {
    var next = !!collapsed;
    if (state.desktopCollapsed === next) {
      applyState();
      return;
    }
    state.desktopCollapsed = next;
    try {
      win.localStorage.setItem(STORAGE_KEY, next ? '1' : '0');
    } catch (err) {
      // Ignore storage errors.
    }
    applyState();
  }

  function setMobileOpen(open) {
    var next = !!open;
    if (state.mobileOpen === next) {
      applyState();
      return;
    }
    state.mobileOpen = next;
    applyState();
  }

  function toggleMenu() {
    if (mqDesktop.matches) {
      setDesktopCollapsed(!state.desktopCollapsed);
    } else {
      setMobileOpen(!state.mobileOpen);
    }
  }

  if (overlay) {
    overlay.addEventListener('click', function () {
      setMobileOpen(false);
    });
  }

  doc.addEventListener('keydown', function (evt) {
    if (evt.key === 'Escape' && state.mobileOpen && !mqDesktop.matches) {
      setMobileOpen(false);
    }
  });

  menuLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      if (!mqDesktop.matches) {
        setMobileOpen(false);
      }
    });
  });

  if (typeof mqDesktop.addEventListener === 'function') {
    mqDesktop.addEventListener('change', applyState);
  } else if (typeof mqDesktop.addListener === 'function') {
    mqDesktop.addListener(applyState);
  }

  Helpers.isSmallScreen = function () {
    return !mqDesktop.matches;
  };
  Helpers.toggleCollapsed = toggleMenu;
  Helpers.setCollapsed = function (collapsed) {
    if (mqDesktop.matches) {
      setDesktopCollapsed(collapsed);
    }
  };
  Helpers.setMenuCollapsed = setDesktopCollapsed;
  Helpers.setMenuOpen = setMobileOpen;

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', applyState, { once: true });
  } else {
    applyState();
  }
})();



