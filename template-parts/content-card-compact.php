<?php
/**
 * Compact Card - Small thumbnail + title (for sidebar)
 */
?>
<a href="<?php the_permalink(); ?>" class="flex items-center gap-3 group py-3 no-underline">
    <?php if (has_post_thumbnail()) : ?>
        <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden">
            <?php the_post_thumbnail('thumbnail', array(
                'class' => 'w-full h-full object-cover',
            )); ?>
        </div>
    <?php endif; ?>

    <div class="flex-1 min-w-0">
        <h4 class="text-small font-medium text-text-dark group-hover:text-primary transition-colors line-clamp-2">
            <?php the_title(); ?>
        </h4>
        <time class="text-xs text-text-light" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
            <?php echo esc_html(get_the_date()); ?>
        </time>
    </div>
</a>
