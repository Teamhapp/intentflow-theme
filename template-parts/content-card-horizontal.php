<?php
/**
 * Horizontal Card - Image left, content right
 */
?>
<a href="<?php the_permalink(); ?>" class="card-clickable group flex flex-col sm:flex-row">
    <?php if (has_post_thumbnail()) : ?>
        <div class="sm:w-48 sm:flex-shrink-0 overflow-hidden">
            <?php the_post_thumbnail('card-horizontal', array(
                'class' => 'w-full h-full object-cover aspect-video sm:aspect-auto transition-transform duration-300 group-hover:scale-105',
            )); ?>
        </div>
    <?php endif; ?>

    <div class="p-4 flex flex-col justify-center">
        <?php
        $categories = get_the_category();
        if (!empty($categories)) :
        ?>
            <span class="category-tag text-xs w-fit"><?php echo esc_html($categories[0]->name); ?></span>
        <?php endif; ?>

        <h3 class="text-body font-semibold mt-2 group-hover:text-primary transition-colors line-clamp-2">
            <?php the_title(); ?>
        </h3>

        <div class="mt-2 flex items-center gap-2 text-small text-text-light">
            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                <?php echo esc_html(get_the_date()); ?>
            </time>
            <span>&middot;</span>
            <?php echo contentflow_reading_time(); ?>
        </div>
    </div>
</a>
