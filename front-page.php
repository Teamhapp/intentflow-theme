<?php
/**
 * IntentFlow Homepage
 */
get_header();
?>

<main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Smart Hero: Search + Intent Navigation -->
    <?php get_template_part('template-parts/content', 'smart-hero'); ?>

    <!-- Trending Tools / Featured Posts -->
    <?php
    $trending = new WP_Query(array(
        'post_type'      => 'post',
        'posts_per_page' => 4,
        'meta_key'       => '_is_featured',
        'meta_value'     => '1',
    ));

    // Fallback to most commented posts if no featured posts
    if (!$trending->have_posts()) {
        $trending = new WP_Query(array(
            'post_type'      => 'post',
            'posts_per_page' => 4,
            'orderby'        => 'comment_count',
            'order'          => 'DESC',
        ));
    }

    if ($trending->have_posts()) :
    ?>
    <section class="mb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-h2 flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.66 11.2C17.43 10.9 17.16 10.64 16.85 10.43C16.12 9.93 15.65 9.12 15.56 8.22C15.46 7.13 14.71 5.64 12.58 4.25C12.1 3.95 11.51 4.22 11.41 4.77C11.21 5.93 10.69 6.97 9.85 7.74C9.31 8.23 8.86 8.82 8.54 9.47C8 10.54 7.87 11.62 8.21 12.55C8.37 12.99 8.21 13.49 7.82 13.73C7.44 13.97 6.96 13.89 6.68 13.55C6.34 13.13 6.1 12.64 5.99 12.1C5.92 11.77 5.53 11.63 5.29 11.87C4.69 12.5 4.25 13.3 4.06 14.19C3.54 16.49 4.55 18.41 6.26 19.56C8.13 20.82 10.67 21.16 12.89 20.44C15.37 19.63 17.14 17.57 17.66 15.04C17.95 13.66 17.86 12.25 17.66 11.2Z"/>
                </svg>
                <?php esc_html_e('Trending Tools', 'intentflow'); ?>
            </h2>
            <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>"
               class="text-small text-primary font-medium hover:underline">
                <?php esc_html_e('View All', 'intentflow'); ?> &rarr;
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            while ($trending->have_posts()) : $trending->the_post();
                get_template_part('template-parts/content-card', 'vertical');
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Category Navigation Row -->
    <div class="cat-pills-wrap">
    <div class="cat-pills-scroll">
        <a href="<?php echo esc_url(home_url('/')); ?>"
           class="category-tag bg-primary text-white no-underline">
            <?php esc_html_e('All', 'intentflow'); ?>
        </a>
        <?php
        $cats = get_categories(array('number' => 8, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC'));
        foreach ($cats as $cat) :
        ?>
            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
               class="category-tag no-underline hover:bg-blue-100">
                <?php echo esc_html($cat->name); ?>
            </a>
        <?php endforeach; ?>
    </div>
    </div>

    <!-- Main Content + Sidebar -->
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Latest Guides Grid -->
        <div class="flex-1 min-w-0">
            <h2 class="text-h2 mb-6"><?php esc_html_e('Latest Guides', 'intentflow'); ?></h2>

            <?php
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $posts_query = new WP_Query(array(
                'post_type'      => 'post',
                'posts_per_page' => 9,
                'paged'          => $paged,
            ));

            if ($posts_query->have_posts()) :
            ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    while ($posts_query->have_posts()) : $posts_query->the_post();
                        get_template_part('template-parts/content-card', 'vertical');
                    endwhile;
                    ?>
                </div>

                <!-- Pagination -->
                <div class="mt-12 flex justify-center">
                    <?php
                    echo paginate_links(array(
                        'total'     => $posts_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => '&larr; Previous',
                        'next_text' => 'Next &rarr;',
                        'type'      => 'list',
                    ));
                    ?>
                </div>
            <?php
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>

        <!-- Sidebar -->
        <?php get_sidebar(); ?>

    </div>

</main>

<?php get_footer(); ?>
