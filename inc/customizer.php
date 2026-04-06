<?php
/**
 * Customizer — Redirects to IntentFlow Dashboard
 *
 * All theme settings are managed at Appearance > IntentFlow.
 * The Customizer handles only WordPress-native features (Site Identity, Menus).
 */

function intentflow_customize_register($wp_customize) {
    $wp_customize->add_section('intentflow_redirect', array(
        'title'       => __('IntentFlow Settings', 'intentflow'),
        'priority'    => 25,
        'description' => sprintf(
            __('All theme settings are managed in the <a href="%s">IntentFlow Dashboard</a>. Go there to configure ads, AI, safelinks, automation, and more.', 'intentflow'),
            esc_url(admin_url('themes.php?page=intentflow-settings'))
        ),
    ));

    $wp_customize->add_setting('intentflow_redirect_placeholder', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('intentflow_redirect_placeholder', array(
        'label'       => __('Theme Settings', 'intentflow'),
        'description' => sprintf(
            '<a href="%s" class="button button-primary" style="margin-top:8px;display:inline-block">%s</a>',
            esc_url(admin_url('themes.php?page=intentflow-settings')),
            esc_html__('Open IntentFlow Dashboard', 'intentflow')
        ),
        'section' => 'intentflow_redirect',
        'type'    => 'hidden',
    ));
}
add_action('customize_register', 'intentflow_customize_register');

function contentflow_sanitize_ad_code($input) {
    return $input;
}
