/**
 * Reading Progress Bar
 * Shows a progress indicator at the top of the page on article pages
 */
(function () {
  'use strict';

  var bar = document.getElementById('reading-progress-bar');
  var article = document.querySelector('.article-content');

  if (!bar || !article) return;

  var ticking = false;

  function updateProgress() {
    var articleTop = article.offsetTop;
    var articleHeight = article.offsetHeight;
    var windowHeight = window.innerHeight;
    var scrollY = window.scrollY || window.pageYOffset;

    var progress = (scrollY - articleTop + windowHeight * 0.3) / articleHeight;
    progress = Math.min(Math.max(progress, 0), 1);

    bar.style.transform = 'scaleX(' + progress + ')';
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      requestAnimationFrame(updateProgress);
      ticking = true;
    }
  });

  updateProgress();
})();
