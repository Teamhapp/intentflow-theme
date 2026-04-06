<?php
/**
 * Hero Section - Featured post
 */
// Cached hero post ID
$hero_id = get_transient('intentflow_hero_post');
if (false === $hero_id) {
    $q = new WP_Query(array(
        'posts_per_page' => 1,
        'meta_key'       => '_is_featured',
        'meta_value'     => '1',
        'post_type'      => 'post',
        'fields'         => 'ids',
    ));
    $hero_id = !empty($q->posts) ? $q->posts[0] : 0;
    if (!$hero_id) {
        $q = new WP_Query(array('posts_per_page' => 1, 'post_type' => 'post', 'fields' => 'ids'));
        $hero_id = !empty($q->posts) ? $q->posts[0] : 0;
    }
    set_transient('intentflow_hero_post', $hero_id, 3600);
    wp_reset_postdata();
}
$featured = new WP_Query(array('p' => $hero_id, 'post_type' => 'post'));

if ($featured->have_posts()) : $featured->the_post();
?>
<a href="<?php the_permalink(); ?>" class="block group relative rounded-2xl overflow-hidden mb-12">
    <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('hero', array(
            'class' => 'w-full aspect-[2/1] object-cover transition-transform duration-500 group-hover:scale-105',
        )); ?>
    <?php else : ?>
        <div class="w-full aspect-[2/1] bg-gradient-to-br from-primary to-blue-800"></div>
    <?php endif; ?>

    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

    <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
        <?php
        $categories = get_the_category();
        if (!empty($categories)) :
        ?>
            <span class="inline-block px-3 py-1 text-small font-medium rounded-full bg-white/20 backdrop-blur-sm mb-4">
                <?php echo esc_html($categories[0]->name); ?>
            </span>
        <?php endif; ?>

        <h1 class="text-h1 text-white mb-3"><?php the_title(); ?></h1>

        <p class="text-white/80 text-body mb-4 max-w-2xl line-clamp-2">
            <?php echo esc_html(contentflow_excerpt(25)); ?>
        </p>

        <div class="flex items-center gap-3 text-small text-white/70">
            <img src="<?php echo esc_url(get_avatar_url(get_the_author_meta('ID'), array('size' => 32))); ?>"
                 alt="" class="w-8 h-8 rounded-full border-2 border-white/30">
            <span class="font-medium text-white"><?php echo esc_html(get_the_author()); ?></span>
            <span>&middot;</span>
            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                <?php echo esc_html(get_the_date()); ?>
            </time>
            <span>&middot;</span>
            <?php echo contentflow_reading_time(); ?>
        </div>
    </div>
</a>
<?php
wp_reset_postdata();
endif;
?>
