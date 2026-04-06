<?php
/**
 * IntentFlow Single Article Page
 * Smart flow: content → flow block → CTA → next steps → related
 */
get_header();
?>

<main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumbs -->
    <?php intentflow_breadcrumbs(); ?>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Article Content -->
        <article class="flex-1 min-w-0 max-w-3xl">

            <?php while (have_posts()) : the_post(); ?>

                <!-- Content type badges -->
                <div class="mb-4 flex items-center gap-2 flex-wrap">
                    <?php contentflow_category_badges(); ?>
                    <?php
                    $content_types = wp_get_post_terms(get_the_ID(), 'content_type');
                    if (!empty($content_types) && !is_wp_error($content_types)) :
                        foreach ($content_types as $ct) :
                    ?>
                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-50 text-cta">
                            <?php echo esc_html($ct->name); ?>
                        </span>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>

                <!-- Title -->
                <h1 class="text-h1 mb-4"><?php the_title(); ?></h1>

                <!-- Post Meta -->
                <div class="mb-8">
                    <?php get_template_part('template-parts/components/post-meta'); ?>
                </div>

                <!-- Featured Image -->
                <?php if (has_post_thumbnail()) : ?>
                    <div class="mb-8 rounded-xl overflow-hidden">
                        <?php the_post_thumbnail('hero', array(
                            'class' => 'w-full aspect-video object-cover',
                        )); ?>
                    </div>
                <?php endif; ?>

                <!-- Top Ad -->
                <?php contentflow_render_ad('article_top'); ?>

                <!-- Article Body
                     Mid ad injected via the_content filter (ad-helpers.php)
                     Flow block auto-injected at 60% via the_content filter (flow-blocks.php) -->
                <div class="article-content my-8">
                    <?php the_content(); ?>
                </div>

                <!-- Bottom Ad -->
                <?php contentflow_render_ad('article_bottom'); ?>

                <!-- CTA Block -->
                <div class="my-12">
                    <?php
                    $post_cta_url = get_post_meta(get_the_ID(), '_intentflow_cta_url', true);
                    get_template_part('template-parts/components/cta', 'download', array(
                        'button_url' => !empty($post_cta_url) ? $post_cta_url : null,
                    ));
                    ?>
                </div>

                <!-- Next Step Engine -->
                <?php get_template_part('template-parts/content', 'next-steps'); ?>

                <!-- Tags -->
                <?php
                $tags = get_the_tags();
                if (!empty($tags)) :
                ?>
                    <div class="flex flex-wrap gap-2 my-8">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                               class="px-3 py-1 text-small bg-surface text-text-light rounded-full no-underline hover:bg-gray-200 transition-colors">
                                #<?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Author Box -->
                <div class="bg-surface rounded-xl p-6 my-8 flex items-start gap-4">
                    <img src="<?php echo esc_url(get_avatar_url(get_the_author_meta('ID'), array('size' => 64))); ?>"
                         alt="<?php echo esc_attr(get_the_author()); ?>"
                         class="w-16 h-16 rounded-full flex-shrink-0">
                    <div>
                        <h3 class="text-body font-semibold"><?php echo esc_html(get_the_author()); ?></h3>
                        <p class="text-text-light text-small mt-1">
                            <?php echo esc_html(get_the_author_meta('description')); ?>
                        </p>
                    </div>
                </div>

            <?php endwhile; ?>

            <!-- Related Posts (category-based fallback) -->
            <?php
            $categories = get_the_category();
            if (!empty($categories)) :
                // Cached related posts — no ORDER BY RAND()
                $cache_key  = 'intentflow_related_' . get_the_ID();
                $related_ids = get_transient($cache_key);
                if (false === $related_ids) {
                    $q = new WP_Query(array(
                        'category__in'   => array($categories[0]->term_id),
                        'post__not_in'   => array(get_the_ID()),
                        'posts_per_page' => 10,
                        'orderby'        => 'date',
                        'fields'         => 'ids',
                    ));
                    $related_ids = $q->posts;
                    if (count($related_ids) > 3) {
                        shuffle($related_ids);
                        $related_ids = array_slice($related_ids, 0, 3);
                    }
                    set_transient($cache_key, $related_ids, 3600);
                    wp_reset_postdata();
                }
                $related = new WP_Query(array(
                    'post__in'       => !empty($related_ids) ? $related_ids : array(0),
                    'orderby'        => 'post__in',
                    'posts_per_page' => 3,
                ));

                if ($related->have_posts()) :
            ?>
                <div class="my-12">
                    <h2 class="text-h2 mb-6"><?php esc_html_e('You May Also Like', 'intentflow'); ?></h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <?php
                        while ($related->have_posts()) : $related->the_post();
                            get_template_part('template-parts/content-card', 'vertical');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            <?php
                endif;
            endif;
            ?>

            <!-- Comments -->
            <?php
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>

        </article>

        <!-- Sidebar -->
        <?php get_sidebar(); ?>

    </div>
</main>

<?php get_footer(); ?>
