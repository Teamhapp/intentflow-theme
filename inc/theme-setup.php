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

    add_image_size('card-vertical', 400, 250, true);
    add_image_size('card-horizontal', 200, 150, true);
    add_image_size('hero', 1200, 600, true);
}
add_action('after_setup_theme', 'intentflow_setup');

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
