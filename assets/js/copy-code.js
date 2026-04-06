/**
 * Copy Code Button — adds a "Copy" button to all <pre><code> blocks
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var codeBlocks = document.querySelectorAll('.article-content pre');
    if (!codeBlocks.length) return;

    codeBlocks.forEach(function (pre) {
      // Wrap in relative container
      pre.style.position = 'relative';

      // Create copy button
      var btn = document.createElement('button');
      btn.className = 'if-copy-code';
      btn.textContent = 'Copy';
      btn.setAttribute('type', 'button');

      btn.addEventListener('click', function () {
        var code = pre.querySelector('code');
        var text = code ? code.textContent : pre.textContent;

        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(text);
        } else {
          var ta = document.createElement('textarea');
          ta.value = text;
          ta.style.position = 'fixed';
          ta.style.left = '-9999px';
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        }

        btn.textContent = 'Copied!';
        btn.classList.add('if-copy-done');
        setTimeout(function () {
          btn.textContent = 'Copy';
          btn.classList.remove('if-copy-done');
        }, 2000);
      });

      pre.appendChild(btn);
    });
  });
})();
