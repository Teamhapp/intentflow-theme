/**
 * Sticky CTA - Shows on mobile when user scrolls past in-content CTA
 */
(function () {
  'use strict';

  var stickyCta = document.getElementById('sticky-cta');
  var ctaBlock = document.getElementById('cta-block');

  if (!stickyCta || !ctaBlock) return;

  // Only show on smaller screens
  if (window.innerWidth >= 1024) return;

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          stickyCta.classList.add('hidden');
        } else if (entry.boundingClientRect.top < 0) {
          stickyCta.classList.remove('hidden');
        }
      });
    },
    { threshold: 0 }
  );

  observer.observe(ctaBlock);
})();
