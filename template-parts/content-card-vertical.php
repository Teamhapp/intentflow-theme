<?php
/**
 * Vertical Card - Image top, title, category, excerpt, meta
 */
?>
<a href="<?php the_permalink(); ?>" class="card-clickable group">
    <?php if (has_post_thumbnail()) : ?>
        <div class="relative overflow-hidden">
            <?php the_post_thumbnail('card-vertical', array(
                'class' => 'w-full aspect-video object-cover transition-transform duration-300 group-hover:scale-105',
            )); ?>
        </div>
    <?php else : ?>
        <div class="w-full aspect-video bg-surface flex items-center justify-center">
            <svg class="w-12 h-12 text-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    <?php endif; ?>

    <div class="p-6">
        <?php
        $categories = get_the_category();
        if (!empty($categories)) :
        ?>
            <span class="category-tag text-xs"><?php echo esc_html($categories[0]->name); ?></span>
        <?php endif; ?>

        <h3 class="text-h3 mt-3 group-hover:text-primary transition-colors">
            <?php the_title(); ?>
        </h3>

        <p class="text-text-light text-small mt-2 line-clamp-2">
            <?php echo esc_html(contentflow_excerpt(15)); ?>
        </p>

        <div class="mt-4">
            <?php get_template_part('template-parts/components/post-meta'); ?>
        </div>
    </div>
</a>
