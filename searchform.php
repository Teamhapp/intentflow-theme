<?php
/**
 * Custom Search Form
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="sr-only" for="search-field-<?php echo esc_attr(wp_unique_id()); ?>">
        <?php esc_html_e('Search', 'intentflow'); ?>
    </label>
    <div class="relative">
        <input type="search"
               id="search-field-<?php echo esc_attr(wp_unique_id()); ?>"
               name="s"
               placeholder="<?php esc_attr_e('Search...', 'intentflow'); ?>"
               value="<?php echo get_search_query(); ?>"
               class="w-full px-4 py-3 pr-12 rounded-lg border border-border bg-white text-body text-text-dark placeholder-text-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-text-light hover:text-primary transition-colors"
                aria-label="<?php esc_attr_e('Search', 'intentflow'); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>
    </div>
</form>
