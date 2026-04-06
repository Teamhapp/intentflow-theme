<?php
/**
 * Next Step Engine
 *
 * Automatically suggests the next logical content for the user:
 * - If reading a "listicle" → suggest tutorials for top items
 * - If reading a "tutorial" → suggest comparisons + fix articles
 * - If reading a "comparison" → suggest tutorials + download/try
 * - If reading a "fix" → suggest tutorials + related fixes
 *
 * Falls back to category + tag based suggestions when clusters aren't set.
 */

/**
 * Get next step suggestions for a post
 *
 * @param int $post_id
 * @return array Array of suggestion groups with label, icon, and posts
 */
function intentflow_get_next_steps($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    $steps   = array();

    // Try cluster-based suggestions first
    $cluster_posts = intentflow_get_cluster_posts($post_id);

    if (!empty($cluster_posts)) {
        $current_type = wp_get_post_terms($post_id, 'content_type', array('fields' => 'slugs'));
        $current_type = !empty($current_type) ? $current_type[0] : '';

        // Define suggestion priority based on current content type
        $suggestion_map = array(
            'listicle'   => array('tutorial', 'comparison', 'review'),
            'tutorial'   => array('comparison', 'fix', 'review'),
            'comparison' => array('tutorial', 'review', 'listicle'),
            'fix'        => array('tutorial', 'fix', 'guide'),
            'review'     => array('comparison', 'tutorial', 'listicle'),
            'guide'      => array('tutorial', 'comparison', 'fix'),
        );

        $priority = isset($suggestion_map[$current_type])
            ? $suggestion_map[$current_type]
            : array('tutorial', 'comparison', 'fix');

        $icons = array(
            'tutorial'   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
            'comparison' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
            'fix'        => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            'guide'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
            'review'     => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>',
            'listicle'   => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',
        );

        foreach ($priority as $type_slug) {
            if (isset($cluster_posts[$type_slug]) && count($steps) < 3) {
                $steps[] = array(
                    'label' => $cluster_posts[$type_slug]['label'],
                    'icon'  => $icons[$type_slug] ?? '',
                    'posts' => $cluster_posts[$type_slug]['posts'],
                );
            }
        }
    }

    // Fallback: category + tag based suggestions
    if (empty($steps)) {
        $steps = intentflow_fallback_next_steps($post_id);
    }

    return $steps;
}

/**
 * Fallback suggestions using categories and tags
 */
function intentflow_fallback_next_steps($post_id) {
    $steps = array();
    $categories = wp_get_post_categories($post_id);
    $tags = wp_get_post_tags($post_id, array('fields' => 'ids'));

    // Related by category
    if (!empty($categories)) {
        $related = new WP_Query(array(
            'category__in'   => $categories,
            'post__not_in'   => array($post_id),
            'posts_per_page' => 3,
            'orderby'        => 'date',
        ));

        if ($related->have_posts()) {
            $steps[] = array(
                'label' => __('Related Guides', 'intentflow'),
                'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                'posts' => $related->posts,
            );
            wp_reset_postdata();
        }
    }

    // Related by tags
    if (!empty($tags)) {
        $tag_related = new WP_Query(array(
            'tag__in'        => $tags,
            'post__not_in'   => array($post_id),
            'posts_per_page' => 3,
            'orderby'        => 'date',
        ));

        if ($tag_related->have_posts()) {
            $steps[] = array(
                'label' => __('More on This Topic', 'intentflow'),
                'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
                'posts' => $tag_related->posts,
            );
            wp_reset_postdata();
        }
    }

    return $steps;
}
