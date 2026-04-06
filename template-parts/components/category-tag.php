<?php
/**
 * Category Tag Component
 */
$category = $args['category'] ?? get_the_category()[0] ?? null;

if (!$category) return;
?>
<a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"
   class="category-tag hover:bg-blue-100 no-underline">
    <?php echo esc_html($category->name); ?>
</a>
