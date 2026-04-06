<?php
/**
 * Enqueue scripts and styles
 */

function intentflow_scripts() {
    wp_enqueue_style(
        'intentflow-theme',
        INTENTFLOW_URI . '/assets/css/theme.css',
        array(),
        INTENTFLOW_VERSION
    );

    wp_enqueue_script(
        'intentflow-navigation',
        INTENTFLOW_URI . '/assets/js/navigation.js',
        array(),
        INTENTFLOW_VERSION,
        true
    );

    if (is_singular('safelink')) {
        wp_enqueue_script(
            'intentflow-safelink-timer',
            INTENTFLOW_URI . '/assets/js/safelink-timer.js',
            array(),
            INTENTFLOW_VERSION,
            true
        );
        wp_localize_script('intentflow-safelink-timer', 'intentflow_sl', array(
            'nonce' => wp_create_nonce('intentflow_click_nonce'),
        ));
    }

    if (is_singular('post')) {
        wp_enqueue_script(
            'intentflow-sticky-cta',
            INTENTFLOW_URI . '/assets/js/sticky-cta.js',
            array(),
            INTENTFLOW_VERSION,
            true
        );

        wp_enqueue_script(
            'intentflow-reading-progress',
            INTENTFLOW_URI . '/assets/js/reading-progress.js',
            array(),
            INTENTFLOW_VERSION,
            true
        );

        wp_enqueue_script(
            'intentflow-copy-code',
            INTENTFLOW_URI . '/assets/js/copy-code.js',
            array(),
            INTENTFLOW_VERSION,
            true
        );
    }

    // Flow engine — modals, timed popups, exit-intent (all pages)
    wp_enqueue_script(
        'intentflow-flow-engine',
        INTENTFLOW_URI . '/assets/js/flow-engine.js',
        array(),
        INTENTFLOW_VERSION,
        true
    );
    wp_localize_script('intentflow-flow-engine', 'intentflow_flow', array(
        'timed_popup' => array(
            'enabled'  => (bool) get_theme_mod('intentflow_popup_enabled', false),
            'delay'    => (int) get_theme_mod('intentflow_popup_delay', 10),
            'modal_id' => 'intentflow-modal',
        ),
        'scroll_cta' => array(
            'enabled'    => true,
            'percent'    => 50,
            'element_id' => 'scroll-cta',
        ),
        'exit_intent' => array(
            'enabled'  => (bool) get_theme_mod('intentflow_exit_intent', false),
            'modal_id' => 'intentflow-modal',
        ),
    ));
}
add_action('wp_enqueue_scripts', 'intentflow_scripts');

/**
 * Enqueue admin scripts for Gemini AI
 */
function intentflow_admin_scripts($hook) {
    $api_key = get_theme_mod('intentflow_gemini_api_key', '');
    if (empty($api_key)) return;

    // Load on post editor and AI generator page
    $load_on = array('post.php', 'post-new.php', 'tools_page_intentflow-ai-generator');
    if (!in_array($hook, $load_on, true)) return;

    wp_enqueue_script(
        'intentflow-gemini-admin',
        INTENTFLOW_URI . '/assets/js/gemini-admin.js',
        array(),
        INTENTFLOW_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'intentflow_admin_scripts');
