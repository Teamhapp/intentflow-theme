<?php
/**
 * Flow Blocks
 *
 * "What do you want next?" decision blocks inserted into articles.
 * These guide users deeper into the content cluster, increasing pageviews.
 *
 * Flow blocks are automatically inserted after the content via a filter,
 * and can also be manually placed using [flow_block] shortcode.
 */

/**
 * Register the [flow_block] shortcode
 * Usage: [flow_block] — renders the flow decision block inline
 */
function intentflow_flow_block_shortcode($atts) {
    if (!is_singular('post')) {
        return '';
    }

    ob_start();
    intentflow_render_flow_block();
    return ob_get_clean();
}
add_shortcode('flow_block', 'intentflow_flow_block_shortcode');

/**
 * Render a flow block for the current post
 */
function intentflow_render_flow_block($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    $steps   = intentflow_get_next_steps($post_id);

    if (empty($steps)) {
        return;
    }
    ?>
    <div class="flow-block-divider" aria-hidden="true">
        <span><?php esc_html_e('Continue Reading', 'intentflow'); ?></span>
    </div>
    <div class="flow-block my-10">
        <div class="flow-block-header">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            <span><?php esc_html_e('What do you want next?', 'intentflow'); ?></span>
        </div>
        <div class="flow-block-options">
            <?php foreach ($steps as $step) : ?>
                <?php if (!empty($step['posts'])) : ?>
                    <?php
                    $first_post = $step['posts'][0];
                    $permalink  = get_permalink($first_post->ID);
                    ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="flow-block-option">
                        <span class="flow-block-option-icon">
                            <?php echo $step['icon']; ?>
                        </span>
                        <span class="flow-block-option-text">
                            <span class="flow-block-option-label">
                                <?php echo esc_html($step['label']); ?>
                            </span>
                            <span class="flow-block-option-title">
                                <?php echo esc_html($first_post->post_title); ?>
                            </span>
                        </span>
                        <svg class="w-5 h-5 flex-shrink-0 text-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Auto-insert flow block into article content (after 60% of paragraphs)
 */
function intentflow_auto_insert_flow_block($content) {
    if (!is_single() || !is_main_query() || get_post_type() !== 'post') {
        return $content;
    }

    // Don't double-insert if shortcode is already in content
    if (has_shortcode($content, 'flow_block')) {
        return $content;
    }

    $paragraphs = explode('</p>', $content);
    $total      = count($paragraphs);

    if ($total < 6) {
        return $content;
    }

    $insert_after = (int) floor($total * 0.6);

    ob_start();
    intentflow_render_flow_block();
    $flow_html = ob_get_clean();

    if (empty($flow_html)) {
        return $content;
    }

    $output = '';
    foreach ($paragraphs as $index => $paragraph) {
        $output .= $paragraph;
        if (!empty(trim($paragraph))) {
            $output .= '</p>';
        }
        if ($index === $insert_after - 1) {
            $output .= $flow_html;
        }
    }

    return $output;
}
add_filter('the_content', 'intentflow_auto_insert_flow_block', 15);
