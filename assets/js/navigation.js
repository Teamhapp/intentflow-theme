/**
 * Mobile Navigation & Search Toggle
 */
(function () {
  'use strict';

  const menuToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  const iconOpen = document.getElementById('menu-icon-open');
  const iconClose = document.getElementById('menu-icon-close');
  const searchToggle = document.getElementById('search-toggle');
  const searchOverlay = document.getElementById('search-overlay');

  if (!menuToggle || !mobileMenu) return;

  let menuOpen = false;

  function toggleMenu() {
    menuOpen = !menuOpen;
    mobileMenu.classList.toggle('hidden', !menuOpen);
    iconOpen.classList.toggle('hidden', menuOpen);
    iconClose.classList.toggle('hidden', !menuOpen);
    document.body.style.overflow = menuOpen ? 'hidden' : '';
  }

  function closeMenu() {
    if (!menuOpen) return;
    menuOpen = false;
    mobileMenu.classList.add('hidden');
    iconOpen.classList.remove('hidden');
    iconClose.classList.add('hidden');
    document.body.style.overflow = '';
  }

  menuToggle.addEventListener('click', toggleMenu);

  // Close on outside click
  document.addEventListener('click', function (e) {
    if (menuOpen && !mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
      closeMenu();
    }
  });

  // Close on Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeMenu();
      if (searchOverlay) searchOverlay.classList.add('hidden');
    }
  });

  // Search toggle
  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', function () {
      searchOverlay.classList.toggle('hidden');
      const input = searchOverlay.querySelector('input[type="search"]');
      if (input && !searchOverlay.classList.contains('hidden')) {
        input.focus();
      }
    });
  }
})();
