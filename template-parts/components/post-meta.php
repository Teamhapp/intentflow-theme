<?php
/**
 * Post Meta Component - Author, date, reading time
 */
?>
<div class="flex items-center gap-3 text-small text-text-light">
    <img src="<?php echo esc_url(get_avatar_url(get_the_author_meta('ID'), array('size' => 32))); ?>"
         alt="<?php echo esc_attr(get_the_author()); ?>"
         class="w-8 h-8 rounded-full">
    <span class="font-medium text-text-dark"><?php echo esc_html(get_the_author()); ?></span>
    <span>&middot;</span>
    <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
        <?php echo esc_html(get_the_date()); ?>
    </time>
    <span>&middot;</span>
    <?php echo contentflow_reading_time(); ?>
</div>
