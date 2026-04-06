<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-white'); ?>>
<?php wp_body_open(); ?>

<!-- Skip to content (accessibility) -->
<a href="#main-content" class="skip-link">
    <?php esc_html_e('Skip to content', 'intentflow'); ?>
</a>

<!-- Notification Bar -->
<?php
$notif_text = get_theme_mod('intentflow_notif_text', '');
$notif_url  = get_theme_mod('intentflow_notif_url', '');
$notif_enabled = get_theme_mod('intentflow_notif_enabled', false);
if ($notif_enabled && !empty($notif_text)) :
?>
<div class="if-notif-bar" id="if-notif-bar">
    <div class="if-notif-inner">
        <?php if (!empty($notif_url)) : ?>
            <a href="<?php echo esc_url($notif_url); ?>" class="if-notif-link">
                <?php echo esc_html($notif_text); ?> &rarr;
            </a>
        <?php else : ?>
            <span><?php echo esc_html($notif_text); ?></span>
        <?php endif; ?>
        <button type="button" class="if-notif-close" onclick="document.getElementById('if-notif-bar').style.display='none';sessionStorage.setItem('if_notif_dismissed','1');" aria-label="<?php esc_attr_e('Dismiss', 'intentflow'); ?>">&times;</button>
    </div>
</div>
<script>if(sessionStorage.getItem('if_notif_dismissed')==='1'){document.getElementById('if-notif-bar').style.display='none';}</script>
<?php endif; ?>

<!-- Reading Progress Bar -->
<?php if (is_singular('post')) : ?>
    <div class="reading-progress" aria-hidden="true">
        <div class="reading-progress-bar" id="reading-progress-bar"></div>
    </div>
<?php endif; ?>

<!-- Navbar -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-border">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <div class="flex-shrink-0">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="text-lg sm:text-h2 font-bold text-text-dark no-underline">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'flex items-center gap-6',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                    'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                    'link_before'    => '',
                    'link_after'     => '',
                ));
                ?>
            </div>

            <!-- Search + CTA (Desktop) -->
            <div class="hidden lg:flex items-center gap-4">
                <button type="button" id="search-toggle"
                        class="relative min-w-[44px] min-h-[44px] flex items-center justify-center p-2.5 text-text-light hover:text-primary transition-colors rounded-lg"
                        aria-label="<?php esc_attr_e('Search', 'intentflow'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="search-indicator"></span>
                </button>
            </div>

            <!-- Mobile Hamburger -->
            <button type="button" id="mobile-menu-toggle"
                    class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center p-2.5 text-text-dark rounded-lg"
                    aria-label="<?php esc_attr_e('Menu', 'intentflow'); ?>">
                <svg class="w-6 h-6" id="menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg class="w-6 h-6 hidden" id="menu-icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Panel -->
        <div class="lg:hidden hidden" id="mobile-menu">
            <div class="py-4 border-t border-border">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'space-y-1',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                    'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                ));
                ?>
                <div class="mt-4 pt-4 border-t border-border">
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>

        <!-- Search Overlay -->
        <div class="hidden" id="search-overlay">
            <div class="py-4 border-t border-border">
                <?php get_search_form(); ?>
            </div>
        </div>
    </nav>
</header>

<!-- Header Banner Ad -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 hidden lg:block">
    <?php get_template_part('template-parts/components/ad-banner'); ?>
</div>
