/**
 * IntentFlow — Flow Engine
 *
 * Behavior-driven UI triggers:
 * - Show modal after N seconds
 * - Show CTA after N% scroll
 * - Exit-intent popup
 *
 * Configuration via intentflow_flow global (set by wp_localize_script)
 */
(function () {
  'use strict';

  var config = (typeof intentflow_flow !== 'undefined') ? intentflow_flow : {};
  var shown = {};

  // ============ MODAL SYSTEM ============

  function openModal(id) {
    var el = document.getElementById(id);
    if (!el || shown[id]) return;
    el.classList.add('if-modal-open');
    document.body.style.overflow = 'hidden';
    shown[id] = true;

    // Close button
    var close = el.querySelector('.if-modal-close');
    if (close) {
      close.addEventListener('click', function () { closeModal(id); });
    }

    // Click overlay to close
    el.addEventListener('click', function (e) {
      if (e.target === el) closeModal(id);
    });

    // Escape to close
    document.addEventListener('keydown', function handler(e) {
      if (e.key === 'Escape') {
        closeModal(id);
        document.removeEventListener('keydown', handler);
      }
    });
  }

  function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('if-modal-open');
    document.body.style.overflow = '';

    // Don't show again this session
    sessionStorage.setItem('if_modal_' + id, 'dismissed');
  }

  // ============ TRIGGERS ============

  // 1. Timed popup (e.g., 10 seconds)
  if (config.timed_popup && config.timed_popup.enabled) {
    var popupId = config.timed_popup.modal_id || 'intentflow-modal';
    var delay   = (config.timed_popup.delay || 10) * 1000;

    if (!sessionStorage.getItem('if_modal_' + popupId)) {
      setTimeout(function () { openModal(popupId); }, delay);
    }
  }

  // 2. Scroll-triggered CTA (e.g., 50% scroll)
  if (config.scroll_cta && config.scroll_cta.enabled) {
    var scrollTarget = config.scroll_cta.percent || 50;
    var scrollEl     = document.getElementById(config.scroll_cta.element_id || 'scroll-cta');
    var scrollFired  = false;

    if (scrollEl) {
      window.addEventListener('scroll', function () {
        if (scrollFired) return;
        var scrollPct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
        if (scrollPct >= scrollTarget) {
          scrollEl.classList.add('if-flow-visible');
          scrollFired = true;
        }
      });
    }
  }

  // 3. Exit-intent (mouse leaves viewport top)
  if (config.exit_intent && config.exit_intent.enabled) {
    var exitId = config.exit_intent.modal_id || 'intentflow-modal';

    if (!sessionStorage.getItem('if_modal_' + exitId)) {
      document.addEventListener('mouseout', function handler(e) {
        if (e.clientY < 5 && !shown[exitId]) {
          openModal(exitId);
          document.removeEventListener('mouseout', handler);
        }
      });
    }
  }

  // Expose globally for manual triggering
  window.intentflowModal = { open: openModal, close: closeModal };
})();
