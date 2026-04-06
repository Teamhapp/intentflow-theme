<?php
/**
 * 404 Page — Warm, helpful recovery
 */
get_header();
?>

<main id="main-content" class="max-w-2xl mx-auto px-4 sm:px-6 py-16 text-center">
    <div class="text-6xl mb-4">&#128533;</div>
    <h1 class="text-h1 mb-3"><?php esc_html_e('Oops! Page not found', 'intentflow'); ?></h1>
    <p class="text-text-light text-body mb-8">
        <?php esc_html_e('This page may have been moved or no longer exists. Try searching or browse our popular topics below.', 'intentflow'); ?>
    </p>

    <div class="mb-8 max-w-md mx-auto">
        <?php get_search_form(); ?>
    </div>

    <!-- Category pills -->
    <?php
    $cats = get_categories(array('number' => 6, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true));
    if (!empty($cats)) :
    ?>
    <div class="flex flex-wrap justify-center gap-2 mb-8">
        <?php foreach ($cats as $cat) : ?>
            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
               class="category-tag no-underline hover:bg-blue-100">
                <?php echo esc_html($cat->name); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">
        <?php esc_html_e('Back to Homepage', 'intentflow'); ?>
    </a>

    <!-- Recent posts -->
    <?php
    $recent = new WP_Query(array('posts_per_page' => 3, 'post_type' => 'post'));
    if ($recent->have_posts()) :
    ?>
    <div class="mt-16 text-left">
        <h2 class="text-h3 text-center mb-6"><?php esc_html_e('Recent Articles', 'intentflow'); ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php while ($recent->have_posts()) : $recent->the_post();
                get_template_part('template-parts/content-card', 'compact');
            endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
