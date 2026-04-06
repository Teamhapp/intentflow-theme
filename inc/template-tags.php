<?php
/**
 * Template Tags
 */

function contentflow_reading_time($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    $content = get_post_field('post_content', $post_id);
    $words   = str_word_count(strip_tags($content));
    $minutes = max(1, ceil($words / 200));

    return sprintf(
        '<span class="reading-time">%d min read</span>',
        $minutes
    );
}

function contentflow_posted_on() {
    $time_string = sprintf(
        '<time datetime="%1$s">%2$s</time>',
        esc_attr(get_the_date(DATE_W3C)),
        esc_html(get_the_date())
    );

    printf(
        '<span class="posted-on text-text-light text-small">%s</span>',
        $time_string
    );
}

function contentflow_posted_by() {
    printf(
        '<span class="byline text-text-light text-small">by <a href="%1$s" class="text-text-dark font-medium hover:text-primary">%2$s</a></span>',
        esc_url(get_author_posts_url(get_the_author_meta('ID'))),
        esc_html(get_the_author())
    );
}

function contentflow_category_badges() {
    $categories = get_the_category();
    if (empty($categories)) {
        return;
    }

    echo '<div class="flex flex-wrap gap-2">';
    foreach ($categories as $cat) {
        printf(
            '<a href="%s" class="category-tag hover:bg-blue-100 no-underline">%s</a>',
            esc_url(get_category_link($cat->term_id)),
            esc_html($cat->name)
        );
    }
    echo '</div>';
}

function contentflow_excerpt($length = 20) {
    $excerpt = get_the_excerpt();
    $words   = explode(' ', $excerpt);
    if (count($words) > $length) {
        $excerpt = implode(' ', array_slice($words, 0, $length)) . '...';
    }
    return $excerpt;
}
