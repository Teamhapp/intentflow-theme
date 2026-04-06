<?php
/**
 * Smart Hero Section
 * Search bar + Intent Navigation buttons
 */

$hero_title = get_theme_mod('intentflow_hero_title', 'Find the Best Tools for Your Work');
$hero_subtitle = get_theme_mod('intentflow_hero_subtitle', 'Tutorials, comparisons, and fixes for the tools you use every day.');

$intent_buttons = array(
    array(
        'label' => __('Start a Blog', 'intentflow'),
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
        'cat'   => 'blogging',
    ),
    array(
        'label' => __('Edit Videos', 'intentflow'),
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
        'cat'   => 'video-editing',
    ),
    array(
        'label' => __('Grow Business', 'intentflow'),
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
        'cat'   => 'business',
    ),
    array(
        'label' => __('Fix Software Issues', 'intentflow'),
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'cat'   => 'troubleshooting',
    ),
);
?>

<!-- Smart Hero -->
<section class="smart-hero mb-12">
    <div class="text-center max-w-3xl mx-auto mb-8">
        <h1 class="text-h1 mb-4"><?php echo esc_html($hero_title); ?></h1>
        <p class="text-text-light text-lg mb-8"><?php echo esc_html($hero_subtitle); ?></p>

        <!-- Search Bar -->
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
              class="relative max-w-xl mx-auto">
            <input type="search" name="s" placeholder="<?php esc_attr_e('Search tools, tutorials, fixes...', 'intentflow'); ?>"
                   value="<?php echo get_search_query(); ?>"
                   class="w-full px-6 py-4 pr-14 rounded-xl border-2 border-border bg-white text-body text-text-dark placeholder-text-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
            <button type="submit"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-primary text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </form>
    </div>

    <!-- Intent Navigation Buttons -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 max-w-3xl mx-auto">
        <?php foreach ($intent_buttons as $btn) :
            $cat = get_category_by_slug($btn['cat']);
            $url = $cat ? get_category_link($cat->term_id) : get_search_link($btn['label']);
        ?>
            <a href="<?php echo esc_url($url); ?>"
               class="intent-nav-btn group">
                <span class="intent-nav-icon">
                    <?php echo $btn['icon']; ?>
                </span>
                <span class="text-xs sm:text-small font-medium text-text-dark group-hover:text-primary transition-colors truncate">
                    <?php echo esc_html($btn['label']); ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
