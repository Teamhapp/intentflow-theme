<?php
/**
 * Search Results Page
 */
get_header();
?>

<main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php intentflow_breadcrumbs(); ?>

    <div class="mb-8">
        <h1 class="text-h1 mb-2">
            <?php printf(esc_html__('Search Results for: %s', 'intentflow'), '<span class="text-primary">' . get_search_query() . '</span>'); ?>
        </h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1 min-w-0">
            <?php if (have_posts()) : ?>
                <div class="space-y-6">
                    <?php
                    while (have_posts()) : the_post();
                        get_template_part('template-parts/content-card', 'horizontal');
                    endwhile;
                    ?>
                </div>

                <div class="mt-12 flex justify-center">
                    <?php the_posts_pagination(array(
                        'prev_text' => '&larr; Previous',
                        'next_text' => 'Next &rarr;',
                    )); ?>
                </div>
            <?php else : ?>
                <?php get_template_part('template-parts/content', 'none'); ?>
            <?php endif; ?>
        </div>

        <?php get_sidebar(); ?>
    </div>

</main>

<?php get_footer(); ?>
