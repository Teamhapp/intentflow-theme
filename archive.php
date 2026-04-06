<?php
/**
 * Archive / Category Page
 */
get_header();
?>

<main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php intentflow_breadcrumbs(); ?>

    <!-- Archive Header -->
    <div class="mb-8">
        <?php
        the_archive_title('<h1 class="text-h1 mb-2">', '</h1>');
        the_archive_description('<p class="text-text-light text-body">', '</p>');
        ?>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Post Grid -->
        <div class="flex-1 min-w-0">
            <?php if (have_posts()) : ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    while (have_posts()) : the_post();
                        get_template_part('template-parts/content-card', 'vertical');
                    endwhile;
                    ?>
                </div>

                <!-- Pagination -->
                <div class="mt-12 flex justify-center">
                    <?php
                    the_posts_pagination(array(
                        'prev_text' => '&larr; Previous',
                        'next_text' => 'Next &rarr;',
                    ));
                    ?>
                </div>
            <?php else : ?>
                <?php get_template_part('template-parts/content', 'none'); ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <?php get_sidebar(); ?>

    </div>

</main>

<?php get_footer(); ?>
