<?php
/**
 * Internal Linking Assistant
 *
 * Suggests related existing posts, cluster gaps, and anchor text
 * when editing a post. Uses content_type and topic_cluster taxonomies.
 */

// Add linking assistant meta box
function intentflow_linking_meta_box() {
    add_meta_box(
        'intentflow_linking',
        __('Internal Linking Assistant', 'intentflow'),
        'intentflow_linking_meta_box_html',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'intentflow_linking_meta_box');

function intentflow_linking_meta_box_html($post) {
    $post_categories = wp_get_post_categories($post->ID);
    $post_tags       = wp_get_post_tags($post->ID, array('fields' => 'ids'));
    $post_clusters   = wp_get_post_terms($post->ID, 'topic_cluster', array('fields' => 'ids'));
    $post_types      = wp_get_post_terms($post->ID, 'content_type', array('fields' => 'slugs'));
    $content         = $post->post_content;

    // Find existing internal links in content
    $existing_links = array();
    if (preg_match_all('/href=["\']([^"\']*' . preg_quote(home_url(), '/') . '[^"\']*)["\']/', $content, $matches)) {
        $existing_links = $matches[1];
    }

    // 1. Related by same category
    $cat_related = array();
    if (!empty($post_categories)) {
        $q = new WP_Query(array(
            'category__in'   => $post_categories,
            'post__not_in'   => array($post->ID),
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'fields'         => 'ids',
        ));
        $cat_related = $q->posts;
        wp_reset_postdata();
    }

    // 2. Related by same cluster
    $cluster_related = array();
    if (!empty($post_clusters) && !is_wp_error($post_clusters)) {
        $q = new WP_Query(array(
            'post__not_in'   => array_merge(array($post->ID), $cat_related),
            'posts_per_page' => 5,
            'tax_query'      => array(array(
                'taxonomy' => 'topic_cluster',
                'field'    => 'term_id',
                'terms'    => $post_clusters,
            )),
            'fields' => 'ids',
        ));
        $cluster_related = $q->posts;
        wp_reset_postdata();
    }

    // 3. Related by tags
    $tag_related = array();
    if (!empty($post_tags)) {
        $q = new WP_Query(array(
            'tag__in'        => $post_tags,
            'post__not_in'   => array_merge(array($post->ID), $cat_related, $cluster_related),
            'posts_per_page' => 5,
            'fields'         => 'ids',
        ));
        $tag_related = $q->posts;
        wp_reset_postdata();
    }

    // 4. Cluster gaps — content types missing from this cluster
    $cluster_gaps = array();
    if (!empty($post_clusters) && !is_wp_error($post_clusters)) {
        $all_types = get_terms(array('taxonomy' => 'content_type', 'hide_empty' => false, 'fields' => 'slugs'));
        $existing_types = array();

        foreach ($all_types as $type_slug) {
            $exists = new WP_Query(array(
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'tax_query'      => array(
                    'relation' => 'AND',
                    array('taxonomy' => 'topic_cluster', 'field' => 'term_id', 'terms' => $post_clusters),
                    array('taxonomy' => 'content_type', 'field' => 'slug', 'terms' => $type_slug),
                ),
            ));
            if ($exists->have_posts()) {
                $existing_types[] = $type_slug;
            }
            wp_reset_postdata();
        }

        $cluster_gaps = array_diff($all_types, $existing_types);
    }

    ?>
    <style>
        .if-link-section{margin-bottom:16px}
        .if-link-section h4{font-size:13px;font-weight:700;margin:0 0 8px;color:#1d2327}
        .if-link-item{display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f0f0f0;font-size:13px}
        .if-link-item:last-child{border-bottom:none}
        .if-link-item a{color:#2563EB;text-decoration:none}
        .if-link-item a:hover{text-decoration:underline}
        .if-link-copy{font-size:11px;color:#666;cursor:pointer;padding:2px 8px;border:1px solid #ddd;border-radius:4px;background:#f9f9f9}
        .if-link-copy:hover{background:#eee}
        .if-link-badge{font-size:10px;padding:1px 6px;border-radius:3px;font-weight:600}
        .if-link-linked{color:#22C55E;background:#dcfce7}
        .if-link-missing{color:#F59E0B;background:#fef3c7}
        .if-gap-item{display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;background:#fee2e2;color:#EF4444;margin:2px;font-weight:500}
    </style>

    <?php if (empty($cat_related) && empty($cluster_related) && empty($tag_related)) : ?>
        <p style="color:#666;font-size:13px"><?php esc_html_e('Add categories, tags, or topic clusters to get linking suggestions.', 'intentflow'); ?></p>
    <?php else : ?>

        <?php if (!empty($cat_related)) : ?>
        <div class="if-link-section">
            <h4><?php esc_html_e('Same Category', 'intentflow'); ?></h4>
            <?php foreach ($cat_related as $rid) :
                $linked = false;
                foreach ($existing_links as $elink) {
                    if (strpos($elink, get_post_field('post_name', $rid)) !== false) { $linked = true; break; }
                }
            ?>
                <div class="if-link-item">
                    <div>
                        <a href="<?php echo esc_url(get_permalink($rid)); ?>" target="_blank"><?php echo esc_html(get_the_title($rid)); ?></a>
                        <span class="if-link-badge <?php echo $linked ? 'if-link-linked' : 'if-link-missing'; ?>">
                            <?php echo $linked ? '✓ Linked' : '○ Not linked'; ?>
                        </span>
                    </div>
                    <button type="button" class="if-link-copy" onclick="navigator.clipboard.writeText('<?php echo esc_url(get_permalink($rid)); ?>');this.textContent='Copied!'">Copy URL</button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($cluster_related)) : ?>
        <div class="if-link-section">
            <h4><?php esc_html_e('Same Topic Cluster', 'intentflow'); ?></h4>
            <?php foreach ($cluster_related as $rid) :
                $linked = false;
                foreach ($existing_links as $elink) {
                    if (strpos($elink, get_post_field('post_name', $rid)) !== false) { $linked = true; break; }
                }
                $types = wp_get_post_terms($rid, 'content_type', array('fields' => 'names'));
                $type_label = !empty($types) ? $types[0] : '';
            ?>
                <div class="if-link-item">
                    <div>
                        <a href="<?php echo esc_url(get_permalink($rid)); ?>" target="_blank"><?php echo esc_html(get_the_title($rid)); ?></a>
                        <?php if ($type_label) : ?>
                            <span style="font-size:10px;color:#6B7280;margin-left:4px">(<?php echo esc_html($type_label); ?>)</span>
                        <?php endif; ?>
                        <span class="if-link-badge <?php echo $linked ? 'if-link-linked' : 'if-link-missing'; ?>">
                            <?php echo $linked ? '✓' : '○'; ?>
                        </span>
                    </div>
                    <button type="button" class="if-link-copy" onclick="navigator.clipboard.writeText('<?php echo esc_url(get_permalink($rid)); ?>');this.textContent='Copied!'">Copy URL</button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($tag_related)) : ?>
        <div class="if-link-section">
            <h4><?php esc_html_e('Related by Tags', 'intentflow'); ?></h4>
            <?php foreach ($tag_related as $rid) : ?>
                <div class="if-link-item">
                    <a href="<?php echo esc_url(get_permalink($rid)); ?>" target="_blank"><?php echo esc_html(get_the_title($rid)); ?></a>
                    <button type="button" class="if-link-copy" onclick="navigator.clipboard.writeText('<?php echo esc_url(get_permalink($rid)); ?>');this.textContent='Copied!'">Copy URL</button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!empty($cluster_gaps)) : ?>
    <div class="if-link-section" style="margin-top:12px;padding-top:12px;border-top:1px solid #ddd">
        <h4><?php esc_html_e('Cluster Gaps (missing content types)', 'intentflow'); ?></h4>
        <p style="font-size:12px;color:#666;margin-bottom:6px"><?php esc_html_e('These content types are missing from this topic cluster:', 'intentflow'); ?></p>
        <?php foreach ($cluster_gaps as $gap) : ?>
            <span class="if-gap-item"><?php echo esc_html(ucfirst($gap)); ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
}
