<?php
/**
 * Content Cluster System
 *
 * Organizes posts into topic clusters:
 *   Main Topic → Tutorial → Comparison → Fix
 *
 * Uses a custom taxonomy "content_type" to classify posts
 * and a custom taxonomy "topic_cluster" to group them.
 */

function intentflow_register_cluster_taxonomies() {

    // Content Type: tutorial, comparison, fix, guide, review
    register_taxonomy('content_type', 'post', array(
        'labels' => array(
            'name'              => __('Content Types', 'intentflow'),
            'singular_name'     => __('Content Type', 'intentflow'),
            'search_items'      => __('Search Content Types', 'intentflow'),
            'all_items'         => __('All Content Types', 'intentflow'),
            'edit_item'         => __('Edit Content Type', 'intentflow'),
            'update_item'       => __('Update Content Type', 'intentflow'),
            'add_new_item'      => __('Add New Content Type', 'intentflow'),
            'new_item_name'     => __('New Content Type Name', 'intentflow'),
            'menu_name'         => __('Content Types', 'intentflow'),
        ),
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'show_admin_column' => true,
        'rewrite'       => array('slug' => 'type'),
    ));

    // Topic Cluster: groups related content together
    register_taxonomy('topic_cluster', 'post', array(
        'labels' => array(
            'name'              => __('Topic Clusters', 'intentflow'),
            'singular_name'     => __('Topic Cluster', 'intentflow'),
            'search_items'      => __('Search Topic Clusters', 'intentflow'),
            'all_items'         => __('All Topic Clusters', 'intentflow'),
            'edit_item'         => __('Edit Topic Cluster', 'intentflow'),
            'update_item'       => __('Update Topic Cluster', 'intentflow'),
            'add_new_item'      => __('Add New Topic Cluster', 'intentflow'),
            'new_item_name'     => __('New Topic Cluster Name', 'intentflow'),
            'menu_name'         => __('Topic Clusters', 'intentflow'),
        ),
        'hierarchical'  => true,
        'public'        => true,
        'show_in_rest'  => true,
        'show_admin_column' => true,
        'rewrite'       => array('slug' => 'topic'),
    ));
}
add_action('init', 'intentflow_register_cluster_taxonomies');

/**
 * Insert default content types on theme activation
 */
function intentflow_insert_default_content_types() {
    $types = array(
        'tutorial'   => 'Tutorial',
        'comparison' => 'Comparison',
        'fix'        => 'Fix / Troubleshoot',
        'guide'      => 'Guide',
        'review'     => 'Review',
        'listicle'   => 'Listicle / Best Of',
    );

    foreach ($types as $slug => $name) {
        if (!term_exists($slug, 'content_type')) {
            wp_insert_term($name, 'content_type', array('slug' => $slug));
        }
    }
}
add_action('after_switch_theme', 'intentflow_insert_default_content_types');

/**
 * Get cluster posts grouped by content type
 *
 * @param int $post_id Current post ID
 * @return array Associative array keyed by content_type slug
 */
function intentflow_get_cluster_posts($post_id = null) {
    $post_id = $post_id ?: get_the_ID();

    $clusters = wp_get_post_terms($post_id, 'topic_cluster');
    if (empty($clusters) || is_wp_error($clusters)) {
        return array();
    }

    $cluster_ids = wp_list_pluck($clusters, 'term_id');

    $content_types = get_terms(array(
        'taxonomy'   => 'content_type',
        'hide_empty' => true,
    ));

    $grouped = array();

    foreach ($content_types as $type) {
        $query = new WP_Query(array(
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'post__not_in'   => array($post_id),
            'tax_query'      => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'topic_cluster',
                    'field'    => 'term_id',
                    'terms'    => $cluster_ids,
                ),
                array(
                    'taxonomy' => 'content_type',
                    'field'    => 'term_id',
                    'terms'    => array($type->term_id),
                ),
            ),
        ));

        if ($query->have_posts()) {
            $grouped[$type->slug] = array(
                'label' => $type->name,
                'posts' => $query->posts,
            );
        }

        wp_reset_postdata();
    }

    return $grouped;
}
