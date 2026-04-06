<?php
/**
 * Theme Setup
 */

function intentflow_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('custom-logo', array(
        'height'      => 40,
        'width'       => 180,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'intentflow'),
        'footer'  => __('Footer Menu', 'intentflow'),
    ));

    // Gutenberg block editor support
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');

    add_image_size('card-vertical', 400, 250, true);
    add_image_size('card-horizontal', 200, 150, true);
    add_image_size('hero', 1200, 600, true);
}
add_action('after_setup_theme', 'intentflow_setup');

// Per-post CTA/Download URL
function intentflow_register_post_meta() {
    $meta_fields = array(
        '_intentflow_cta_url',
        '_intentflow_cta_button_text',
        '_intentflow_seo_title',
        '_intentflow_meta_description',
    );
    foreach ($meta_fields as $key) {
        register_post_meta('post', $key, array(
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
            'default'       => '',
            'auth_callback' => function () { return current_user_can('edit_posts'); },
        ));
    }
}
add_action('init', 'intentflow_register_post_meta');

function intentflow_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'intentflow'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'intentflow'),
        'before_widget' => '<section id="%1$s" class="widget mb-8 %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title text-h3 mb-4">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'intentflow_widgets_init');
