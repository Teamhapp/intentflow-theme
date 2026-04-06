<?php
/**
 * Author Archive Page — E-E-A-T signals for Google
 */
get_header();

$author_id   = get_queried_object_id();
$author_name = get_the_author_meta('display_name', $author_id);
$author_bio  = get_the_author_meta('description', $author_id);
$author_url  = get_the_author_meta('user_url', $author_id);
$post_count  = count_user_posts($author_id, 'post', true);
?>

<main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php intentflow_breadcrumbs(); ?>

    <!-- Author Card -->
    <div class="bg-surface rounded-2xl p-8 mb-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <img src="<?php echo esc_url(get_avatar_url($author_id, array('size' => 128))); ?>"
             alt="<?php echo esc_attr($author_name); ?>"
             class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-md flex-shrink-0">

        <div class="text-center sm:text-left">
            <h1 class="text-h1 mb-2"><?php echo esc_html($author_name); ?></h1>

            <?php if (!empty($author_bio)) : ?>
                <p class="text-text-light text-body mb-4 max-w-2xl"><?php echo esc_html($author_bio); ?></p>
            <?php endif; ?>

            <div class="flex flex-wrap gap-4 justify-center sm:justify-start text-small text-text-light">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <?php printf(esc_html(_n('%d Article', '%d Articles', $post_count, 'intentflow')), $post_count); ?>
                </span>
                <?php if (!empty($author_url)) : ?>
                    <a href="<?php echo esc_url($author_url); ?>" class="flex items-center gap-1 text-primary no-underline hover:underline" target="_blank" rel="noopener">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <?php esc_html_e('Website', 'intentflow'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Author Posts -->
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-h2 mb-6"><?php printf(esc_html__('Articles by %s', 'intentflow'), esc_html($author_name)); ?></h2>

            <?php if (have_posts()) : ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while (have_posts()) : the_post();
                        get_template_part('template-parts/content-card', 'vertical');
                    endwhile; ?>
                </div>

                <div class="mt-12 flex justify-center">
                    <?php the_posts_pagination(array(
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
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
