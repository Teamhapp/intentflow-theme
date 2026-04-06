/**
 * IntentFlow Admin Dashboard — Tab Switching + Save Handling
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var tabs = document.querySelectorAll('.if-tab');
        var panels = document.querySelectorAll('.if-panel');
        var hiddenField = document.getElementById('intentflow_active_tab');
        var form = document.getElementById('intentflow-settings-form');

        if (!tabs.length || !panels.length) return;

        function activateTab(tabName) {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            panels.forEach(function(p) { p.classList.remove('active'); });

            tabs.forEach(function(t) {
                if (t.getAttribute('data-tab') === tabName) {
                    t.classList.add('active');
                }
            });

            var panel = document.getElementById('tab-' + tabName);
            if (panel) panel.classList.add('active');

            if (hiddenField) hiddenField.value = tabName;
        }

        // Tab click
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                activateTab(tab.getAttribute('data-tab'));
                window.scrollTo(0, 0);
            });
        });

        // Save buttons — update tab before natural form submit
        if (form) {
            form.addEventListener('submit', function() {
                var activeTab = document.querySelector('.if-tab.active');
                if (activeTab && hiddenField) {
                    hiddenField.value = activeTab.getAttribute('data-tab');
                }
            });
        }

        // Restore active tab after page reload
        if (hiddenField && hiddenField.value && hiddenField.value !== 'overview') {
            activateTab(hiddenField.value);
        }
    });
})();
