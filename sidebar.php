<?php
/**
 * Sidebar - Popular posts, categories, ad
 */
?>
<aside class="hidden lg:block lg:w-80 flex-shrink-0 space-y-8">

    <!-- Popular Posts -->
    <div class="bg-white rounded-xl border border-border p-6">
        <h3 class="text-h3 mb-4"><?php esc_html_e('Popular Posts', 'intentflow'); ?></h3>
        <div class="divide-y divide-border">
            <?php
            $popular_ids = get_transient('intentflow_popular_posts');
            if (false === $popular_ids) {
                $q = new WP_Query(array(
                    'posts_per_page' => 5,
                    'orderby'        => 'comment_count',
                    'order'          => 'DESC',
                    'post_type'      => 'post',
                    'fields'         => 'ids',
                ));
                $popular_ids = $q->posts;
                set_transient('intentflow_popular_posts', $popular_ids, 3600);
                wp_reset_postdata();
            }
            $popular = new WP_Query(array(
                'post__in'       => !empty($popular_ids) ? $popular_ids : array(0),
                'orderby'        => 'post__in',
                'posts_per_page' => 5,
            ));

            if ($popular->have_posts()) :
                while ($popular->have_posts()) : $popular->the_post();
                    get_template_part('template-parts/content-card', 'compact');
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>

    <!-- Categories -->
    <div class="bg-white rounded-xl border border-border p-6">
        <h3 class="text-h3 mb-4"><?php esc_html_e('Categories', 'intentflow'); ?></h3>
        <ul class="space-y-2">
            <?php
            $categories = get_categories(array(
                'orderby'    => 'count',
                'order'      => 'DESC',
                'number'     => 10,
                'hide_empty' => true,
            ));

            foreach ($categories as $cat) :
            ?>
                <li>
                    <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                       class="flex items-center justify-between py-2 text-body text-text-dark no-underline hover:text-primary transition-colors">
                        <span><?php echo esc_html($cat->name); ?></span>
                        <span class="text-small text-text-light bg-surface px-2 py-0.5 rounded-full">
                            <?php echo esc_html($cat->count); ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Sidebar Ad -->
    <?php get_template_part('template-parts/components/ad-sidebar'); ?>

</aside>
