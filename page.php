<?php
/**
 * Generic Page Template
 */
get_header();
?>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <h1 class="text-h1 mb-8"><?php the_title(); ?></h1>

        <div class="article-content">
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
