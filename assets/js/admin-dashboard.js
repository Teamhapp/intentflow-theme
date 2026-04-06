/**
 * IntentFlow Admin Dashboard — Tab Switching + Active Tab Persistence
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var tabs = document.querySelectorAll('.if-tab');
        var panels = document.querySelectorAll('.if-panel');
        var hiddenField = document.getElementById('intentflow_active_tab');

        if (!tabs.length || !panels.length) return;

        function activateTab(tabName) {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            panels.forEach(function(p) { p.classList.remove('active'); });

            // Find and activate the tab button
            tabs.forEach(function(t) {
                if (t.getAttribute('data-tab') === tabName) {
                    t.classList.add('active');
                }
            });

            // Activate the panel
            var panel = document.getElementById('tab-' + tabName);
            if (panel) {
                panel.classList.add('active');
            }

            // Update hidden field for form persistence
            if (hiddenField) {
                hiddenField.value = tabName;
            }
        }

        // Tab click handler
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                activateTab(tab.getAttribute('data-tab'));
                window.scrollTo(0, 0);
            });
        });

        // Restore active tab from hidden field (after form save + page reload)
        if (hiddenField && hiddenField.value && hiddenField.value !== 'overview') {
            activateTab(hiddenField.value);
        }
    });
})();
