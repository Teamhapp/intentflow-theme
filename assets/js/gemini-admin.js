/**
 * IntentFlow Gemini AI — Admin JavaScript
 *
 * Handles: meta box AJAX, bulk generator, content enhancement
 */
(function () {
  'use strict';

  // ============================================================
  // POST EDITOR META BOX
  // ============================================================

  var box = document.getElementById('intentflow-ai-box');
  if (box) {
    var postId = box.dataset.postId;
    var nonce  = box.dataset.nonce;
    var status = document.getElementById('ai-status');
    var loading = document.getElementById('ai-loading');
    var loadingText = document.getElementById('ai-loading-text');

    var currentController = null;

    function showLoading(text) {
      loading.style.display = 'block';
      loadingText.textContent = text || 'Generating with Gemini AI...';
    }

    function hideLoading() {
      loading.style.display = 'none';
      currentController = null;
    }

    function setStatus(text, color) {
      status.textContent = text;
      status.style.color = color || '#6B7280';
    }

    function friendlyError(err, res) {
      if (res && res.data && res.data.message) {
        if (res.data.message.indexOf('API key') !== -1) return 'Invalid API key. Check IntentFlow Settings > AI.';
        if (res.data.message.indexOf('rate') !== -1) return 'Rate limit reached. Wait 1 minute and try again.';
        return res.data.message;
      }
      if (err && err.name === 'AbortError') return 'Request cancelled.';
      return 'Could not reach the server. Check your connection.';
    }

    function aiFetch(fd, timeout) {
      currentController = new AbortController();
      var timer = setTimeout(function () {
        if (currentController) currentController.abort();
      }, (timeout || 30) * 1000);

      return fetch(ajaxurl, { method: 'POST', body: fd, signal: currentController.signal })
        .then(function (r) { clearTimeout(timer); return r.json(); })
        .catch(function (err) { clearTimeout(timer); throw err; });
    }

    // Cancel button
    var cancelBtn = document.getElementById('ai-cancel-btn');
    if (cancelBtn) {
      cancelBtn.addEventListener('click', function () {
        if (currentController) currentController.abort();
        hideLoading();
        setStatus('Cancelled.', '#F59E0B');
      });
    }

    // Copy buttons (with fallback for HTTP sites / older browsers)
    document.querySelectorAll('.ai-copy-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = document.getElementById(btn.dataset.target);
        if (!target) return;
        var text = target.textContent.trim();

        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(text);
        } else {
          // Fallback: hidden textarea select+copy
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
        setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
      });
    });

    // --- Generate SEO ---
    var btnSeo = document.getElementById('ai-btn-seo');
    if (btnSeo) {
      btnSeo.addEventListener('click', function () {
        showLoading('Generating SEO metadata...');
        btnSeo.disabled = true;

        var fd = new FormData();
        fd.append('action', 'intentflow_ai_seo');
        fd.append('nonce', nonce);
        fd.append('post_id', postId);

        aiFetch(fd, 30)
          .then(function (res) {
            hideLoading();
            btnSeo.disabled = false;
            if (res.success) {
              var results = document.getElementById('ai-seo-results');
              results.style.display = 'block';
              document.getElementById('ai-seo-title').textContent = res.data.seo_title || '';
              document.getElementById('ai-meta-desc').textContent = res.data.meta_description || '';
              document.getElementById('ai-tags').textContent = res.data.tags || '';
              document.getElementById('ai-excerpt').textContent = res.data.excerpt || '';
              setStatus('SEO generated! Use Copy buttons to apply.', '#22C55E');
            } else {
              setStatus(friendlyError(null, res), '#EF4444');
            }
          })
          .catch(function (err) {
            hideLoading();
            btnSeo.disabled = false;
            setStatus(friendlyError(err), '#EF4444');
          });
      });
    }

    // --- Enhance Content ---
    var btnEnhance = document.getElementById('ai-btn-enhance');
    if (btnEnhance) {
      btnEnhance.addEventListener('click', function () {
        var action = document.getElementById('ai-enhance-action').value;

        // Get content from editor
        var content = '';
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
          // Block editor (Gutenberg)
          content = wp.data.select('core/editor').getEditedPostContent();
        } else if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
          // Classic editor
          content = tinyMCE.activeEditor.getContent();
        }

        if (!content || content.length < 50) {
          setStatus('Content too short to enhance', '#F59E0B');
          return;
        }

        showLoading('Enhancing content...');
        btnEnhance.disabled = true;

        var fd = new FormData();
        fd.append('action', 'intentflow_ai_enhance');
        fd.append('nonce', nonce);
        fd.append('content', content);
        fd.append('enhance_action', action);

        fetch(ajaxurl, { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            hideLoading();
            btnEnhance.disabled = false;

            if (res.success) {
              // Confirm before replacing content (prevents data loss)
              if (!confirm('Replace current content with enhanced version? This cannot be undone.')) {
                setStatus('Enhancement cancelled.', '#F59E0B');
                return;
              }

              // Update editor content safely
              if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/block-editor')) {
                wp.data.dispatch('core/block-editor').resetBlocks(
                  wp.blocks.parse(res.data.content)
                );
              } else if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
                wp.data.dispatch('core/editor').editPost({ content: res.data.content });
              } else if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
                tinyMCE.activeEditor.setContent(res.data.content);
              }
              setStatus('Content enhanced!', '#22C55E');
            } else {
              setStatus('Error: ' + res.data.message, '#EF4444');
            }
          })
          .catch(function () {
            hideLoading();
            btnEnhance.disabled = false;
            setStatus('Network error', '#EF4444');
          });
      });
    }

    // --- Generate Thumbnail ---
    var btnThumb = document.getElementById('ai-btn-thumbnail');
    if (btnThumb) {
      btnThumb.addEventListener('click', function () {
        showLoading('Generating featured image...');
        btnThumb.disabled = true;

        var fd = new FormData();
        fd.append('action', 'intentflow_ai_thumbnail');
        fd.append('nonce', nonce);
        fd.append('post_id', postId);
        fd.append('style', 'gradient');

        fetch(ajaxurl, { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            hideLoading();
            btnThumb.disabled = false;

            if (res.success && res.data.url) {
              var resultDiv = document.getElementById('ai-thumbnail-result');
              var preview   = document.getElementById('ai-thumbnail-preview');
              preview.src = res.data.url;
              resultDiv.style.display = 'block';
              setStatus('Featured image set!', '#22C55E');
            } else {
              setStatus('Error: ' + (res.data ? res.data.message : 'Unknown'), '#EF4444');
            }
          })
          .catch(function () {
            hideLoading();
            btnThumb.disabled = false;
            setStatus('Network error', '#EF4444');
          });
      });
    }

    // --- Suggest Related ---
    var btnRelated = document.getElementById('ai-btn-related');
    if (btnRelated) {
      btnRelated.addEventListener('click', function () {
        showLoading('Finding related topics...');
        btnRelated.disabled = true;

        var fd = new FormData();
        fd.append('action', 'intentflow_ai_seo'); // reuse SEO endpoint for now
        fd.append('nonce', nonce);
        fd.append('post_id', postId);

        // Actually call a custom endpoint for related
        fd.set('action', 'intentflow_ai_related');

        fetch(ajaxurl, { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            hideLoading();
            btnRelated.disabled = false;
            var resultsDiv = document.getElementById('ai-related-results');

            if (res.success && Array.isArray(res.data)) {
              var html = '<ul style="margin:0;padding-left:16px">';
              res.data.forEach(function (item) {
                html += '<li style="margin-bottom:4px"><strong>' + escHtml(item.title) + '</strong> <span style="color:#6B7280">(' + escHtml(item.type) + ')</span></li>';
              });
              html += '</ul>';
              resultsDiv.innerHTML = html;
              resultsDiv.style.display = 'block';
              setStatus('Suggestions ready!', '#22C55E');
            } else {
              setStatus('Could not get suggestions', '#F59E0B');
            }
          })
          .catch(function () {
            hideLoading();
            btnRelated.disabled = false;
            setStatus('Network error', '#EF4444');
          });
      });
    }
  }

  // ============================================================
  // BULK POST GENERATOR
  // ============================================================

  var bulkBtn = document.getElementById('bulk-generate-btn');
  if (bulkBtn) {
    var form = document.getElementById('ai-generator-form');
    var formNonce = form ? form.dataset.nonce : '';

    bulkBtn.addEventListener('click', function () {
      var keywordsRaw = document.getElementById('bulk-keywords').value.trim();
      if (!keywordsRaw) {
        alert('Please enter at least one keyword.');
        return;
      }

      var keywords = keywordsRaw.split('\n').map(function (k) { return k.trim(); }).filter(Boolean);
      if (keywords.length === 0) return;

      var contentType = document.getElementById('bulk-type').value;
      var category    = document.getElementById('bulk-category').value;
      var postStatus  = document.getElementById('bulk-status').value;

      // Show progress
      var progressDiv = document.getElementById('bulk-progress');
      var progressBar = document.getElementById('bulk-progress-bar');
      var progressText = document.getElementById('bulk-progress-text');
      var resultsTable = document.getElementById('bulk-results');
      var resultsBody  = document.getElementById('bulk-results-body');
      var emptyDiv     = document.getElementById('bulk-empty');

      progressDiv.style.display = 'block';
      resultsTable.style.display = 'table';
      emptyDiv.style.display = 'none';
      bulkBtn.disabled = true;
      bulkBtn.textContent = 'Generating...';
      resultsBody.innerHTML = '';

      var total = keywords.length;
      var done  = 0;

      // Process one at a time to avoid rate limiting
      function processNext() {
        if (done >= total) {
          bulkBtn.disabled = false;
          bulkBtn.textContent = 'Generate All Posts';
          progressText.textContent = 'All ' + total + ' posts generated!';
          progressBar.style.width = '100%';
          return;
        }

        var keyword = keywords[done];
        progressText.textContent = 'Generating ' + (done + 1) + ' of ' + total + ': "' + keyword + '"';
        progressBar.style.width = ((done / total) * 100) + '%';

        var fd = new FormData();
        fd.append('action', 'intentflow_ai_bulk');
        fd.append('nonce', formNonce);
        fd.append('keyword', keyword);
        fd.append('content_type', contentType);
        fd.append('category', category);
        fd.append('status', postStatus);

        fetch(ajaxurl, { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            done++;
            var row = document.createElement('tr');

            if (res.success) {
              row.innerHTML =
                '<td><a href="' + escAttr(res.data.edit_url) + '">' + escHtml(res.data.title) + '</a></td>' +
                '<td><span style="color:#22C55E;font-size:12px;font-weight:600">Done</span></td>' +
                '<td><a href="' + escAttr(res.data.edit_url) + '" class="button button-small">Edit</a></td>';
            } else {
              row.innerHTML =
                '<td>' + escHtml(keyword) + '</td>' +
                '<td><span style="color:#EF4444;font-size:12px;font-weight:600">Failed</span></td>' +
                '<td><span style="font-size:12px;color:#6B7280">' + escHtml(res.data.message || 'Error') + '</span></td>';
            }

            resultsBody.appendChild(row);
            processNext();
          })
          .catch(function () {
            done++;
            var row = document.createElement('tr');
            row.innerHTML =
              '<td>' + escHtml(keyword) + '</td>' +
              '<td><span style="color:#EF4444;font-size:12px">Network Error</span></td>' +
              '<td></td>';
            resultsBody.appendChild(row);
            processNext();
          });
      }

      processNext();
    });
  }

  // ============================================================
  // HELPERS
  // ============================================================

  function escHtml(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function escAttr(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }
})();
