/**
 * IntentFlow Safelink - Revenue Maximized Timer
 *
 * Supports:
 * - 3 timer modes: circle (SVG), progress (bar), text (minimal)
 * - 3 step configs: fast (timer→download), standard (+generate), max (+wait)
 * - Click tracking via AJAX
 * - SessionStorage anti-replay
 * - Smooth CSS class transitions
 */
(function () {
  'use strict';

  var app = document.getElementById('safelink-app');
  if (!app) return;

  // ---- Config ----
  var cfg = {
    timer:       parseInt(app.dataset.timer, 10) || 10,
    wait:        parseInt(app.dataset.wait, 10) || 5,
    target:      app.dataset.target || '#',
    showGen:     app.dataset.showGenerate === '1',
    showWait:    app.dataset.showWait === '1',
    mode:        app.dataset.timerMode || 'circle',
    postId:      app.dataset.postId || '',
    ajaxUrl:     app.dataset.ajaxUrl || ''
  };

  // ---- DOM ----
  var els = {
    timer:    document.getElementById('step-timer'),
    generate: document.getElementById('step-generate'),
    wait:     document.getElementById('step-wait'),
    download: document.getElementById('step-download'),
    display:  document.getElementById('timer-display'),
    progress: document.getElementById('timer-progress'),
    btnGen:   document.getElementById('btn-generate'),
    btnDl:    document.getElementById('btn-download'),
    waitDisp: document.getElementById('wait-timer-display'),
    status:   document.getElementById('safelink-status'),
    icon:     document.getElementById('safelink-icon')
  };

  var circumference = 2 * Math.PI * 54;
  var storageKey = 'sf_' + window.location.pathname + window.location.search;

  // ---- Skip if already completed ----
  if (sessionStorage.getItem(storageKey) === 'done') {
    goToStep('download');
    setStatus('Your link is ready!');
    unlockIcon();
    return;
  }

  // ---- Init timer display ----
  if (cfg.mode === 'circle' && els.progress) {
    els.progress.style.strokeDasharray = circumference;
    els.progress.style.strokeDashoffset = '0';
  } else if (cfg.mode === 'progress' && els.progress) {
    els.progress.style.width = '0%';
  }

  // ======== STEP 1: COUNTDOWN ========
  var remaining = cfg.timer;
  updateTimerDisplay(remaining);

  var timerInterval = setInterval(function () {
    remaining--;
    updateTimerDisplay(remaining);
    updateTimerVisual(remaining);

    if (remaining <= 0) {
      clearInterval(timerInterval);
      if (cfg.showGen) {
        goToStep('generate');
        setStatus('Click the button to generate your link');
      } else {
        finishFlow();
      }
    }
  }, 1000);

  // ======== STEP 2: GENERATE ========
  if (els.btnGen) {
    els.btnGen.addEventListener('click', function () {
      if (cfg.showWait) {
        startWait();
      } else {
        finishFlow();
      }
    });
  }

  // ======== STEP 3: WAIT ========
  function startWait() {
    goToStep('wait');
    setStatus('Almost there...');
    var wr = cfg.wait;
    if (els.waitDisp) els.waitDisp.textContent = 'Please wait ' + wr + ' seconds...';

    var wi = setInterval(function () {
      wr--;
      if (els.waitDisp) {
        els.waitDisp.textContent = wr > 0 ? 'Please wait ' + wr + ' seconds...' : '';
      }
      if (wr <= 0) {
        clearInterval(wi);
        finishFlow();
      }
    }, 1000);
  }

  // ======== FINAL: DOWNLOAD ========
  function finishFlow() {
    sessionStorage.setItem(storageKey, 'done');
    goToStep('download');
    setStatus('Your link is ready!');
    unlockIcon();

    if (els.btnDl) {
      els.btnDl.href = cfg.target;
    }
  }

  // ======== CLICK TRACKING ========
  if (els.btnDl) {
    els.btnDl.addEventListener('click', function () {
      if (!cfg.postId || !cfg.ajaxUrl) return;

      // Fire-and-forget AJAX tracking with nonce
      var nonce = (typeof intentflow_sl !== 'undefined') ? intentflow_sl.nonce : '';
      var fd = new FormData();
      fd.append('action', 'intentflow_track_click');
      fd.append('post_id', cfg.postId);
      fd.append('nonce', nonce);

      if (navigator.sendBeacon) {
        navigator.sendBeacon(cfg.ajaxUrl, fd);
      } else {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.ajaxUrl, true);
        xhr.send(fd);
      }
    });
  }

  // ======== HELPERS ========

  function updateTimerDisplay(sec) {
    if (els.display) els.display.textContent = Math.max(0, sec);
  }

  function updateTimerVisual(sec) {
    if (!els.progress) return;

    if (cfg.mode === 'circle') {
      var offset = circumference * (1 - sec / cfg.timer);
      els.progress.style.strokeDashoffset = offset;
    } else if (cfg.mode === 'progress') {
      var pct = ((cfg.timer - sec) / cfg.timer) * 100;
      els.progress.style.width = pct + '%';
    }
  }

  function goToStep(name) {
    var stepMap = { timer: els.timer, generate: els.generate, wait: els.wait, download: els.download };

    // Exit current
    Object.keys(stepMap).forEach(function (k) {
      var el = stepMap[k];
      if (!el) return;
      if (el.classList.contains('safelink-step-active')) {
        el.classList.remove('safelink-step-active');
        el.classList.add('safelink-step-exit');
        (function (e) {
          setTimeout(function () { e.classList.remove('safelink-step-exit'); }, 300);
        })(el);
      }
    });

    // Enter new
    setTimeout(function () {
      if (stepMap[name]) {
        stepMap[name].classList.add('safelink-step-active');
        if (name === 'download') stepMap[name].classList.add('safelink-step-pop');
      }
    }, 300);
  }

  function setStatus(text) {
    if (!els.status) return;
    els.status.style.opacity = '0';
    setTimeout(function () {
      els.status.textContent = text;
      els.status.style.opacity = '1';
    }, 200);
  }

  function unlockIcon() {
    if (!els.icon) return;
    els.icon.classList.add('safelink-icon-unlocked');
    els.icon.innerHTML =
      '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
      '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' +
      'd="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>' +
      '</svg>';
  }
})();
