    <!-- Footer -->
    <footer class="bg-text-dark text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

                <!-- About -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">
                        <?php bloginfo('name'); ?>
                    </h3>
                    <p class="text-gray-400 text-small">
                        <?php bloginfo('description'); ?>
                    </p>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">
                        <?php esc_html_e('Categories', 'intentflow'); ?>
                    </h3>
                    <ul class="space-y-2">
                        <?php
                        $footer_cats = get_categories(array('number' => 6, 'hide_empty' => true));
                        foreach ($footer_cats as $cat) :
                        ?>
                            <li>
                                <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                                   class="text-gray-400 hover:text-white text-small no-underline transition-colors">
                                    <?php echo esc_html($cat->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Pages -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">
                        <?php esc_html_e('Pages', 'intentflow'); ?>
                    </h3>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'container'      => false,
                        'menu_class'     => 'space-y-2',
                        'fallback_cb'    => false,
                        'depth'          => 1,
                    ));
                    ?>
                </div>

                <!-- Social -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">
                        <?php esc_html_e('Follow Us', 'intentflow'); ?>
                    </h3>
                    <div class="flex gap-4">
                        <?php
                        $socials = array(
                            'facebook'  => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
                            'twitter'   => 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z',
                            'instagram' => 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M7.5 2h9A5.5 5.5 0 0122 7.5v9a5.5 5.5 0 01-5.5 5.5h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2z',
                            'youtube'   => 'M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z M9.75 15.02l5.75-3.27-5.75-3.27v6.54z',
                        );

                        foreach ($socials as $network => $path) :
                            $url = get_theme_mod("contentflow_social_{$network}", '');
                            if (empty($url)) continue;
                        ?>
                            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"
                               class="text-gray-400 hover:text-white transition-colors"
                               aria-label="<?php echo esc_attr(ucfirst($network)); ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $path; ?>"/>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Copyright -->
            <div class="mt-12 pt-8 border-t border-gray-700 text-center text-gray-400 text-small">
                <?php echo wp_kses_post(get_theme_mod('contentflow_footer_text', '&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.')); ?>
                <div class="mt-2" style="font-size:11px;color:#4B5563">
                    <?php esc_html_e('Designed by', 'intentflow'); ?> <a href="mailto:mail@aknify.com" style="color:#6B7280;text-decoration:none">Aknify</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Sticky CTA -->
    <?php if (is_singular('post')) : ?>
        <?php get_template_part('template-parts/components/cta-mobile-sticky'); ?>
    <?php endif; ?>

    <!-- Mobile Bottom Ad -->
    <?php get_template_part('template-parts/components/ad-mobile-bottom'); ?>

    <!-- Popup Modal (configurable via IntentFlow Settings) -->
    <?php
    $popup_title   = get_theme_mod('intentflow_popup_title', '');
    $popup_content = get_theme_mod('intentflow_popup_content', '');
    $popup_cta     = get_theme_mod('intentflow_popup_cta_text', '');
    $popup_url     = get_theme_mod('intentflow_popup_cta_url', '#');

    if (!empty($popup_title) || !empty($popup_content)) :
        get_template_part('template-parts/components/modal', null, array(
            'id'       => 'intentflow-modal',
            'title'    => $popup_title,
            'content'  => $popup_content,
            'cta_text' => $popup_cta,
            'cta_url'  => $popup_url,
        ));
    endif;
    ?>

    <?php wp_footer(); ?>
</body>
</html>
