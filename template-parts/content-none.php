<?php
/**
 * No Results Template
 */
?>
<section class="text-center py-16">
    <h2 class="text-h2 mb-4"><?php esc_html_e('Nothing Found', 'intentflow'); ?></h2>

    <?php if (is_search()) : ?>
        <p class="text-text-light text-body mb-8">
            <?php esc_html_e('Sorry, no results matched your search. Please try different keywords.', 'intentflow'); ?>
        </p>
        <?php get_search_form(); ?>
    <?php else : ?>
        <p class="text-text-light text-body mb-8">
            <?php esc_html_e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'intentflow'); ?>
        </p>
        <?php get_search_form(); ?>
    <?php endif; ?>
</section>
