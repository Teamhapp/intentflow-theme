<?php
/**
 * AdSense-Compliant Ad Helper Functions
 *
 * Compliance features:
 * - "Advertisement" label on every ad unit (required by AdSense)
 * - Responsive ad containers with proper dimensions
 * - Minimum content length check before mid-article ads
 * - Lazy loading via IntersectionObserver for below-fold ads
 * - CLS prevention with explicit width/height reservations
 * - Clear visual separation between ads and content
 * - No ads on interstitial or gated content
 */

/**
 * Render an ad unit with proper AdSense-compliant wrapper
 *
 * @param string $position  Ad position key
 * @param array  $options   Override options
 */
function contentflow_render_ad($position, $options = array()) {
    $enabled = get_theme_mod("contentflow_ad_{$position}_enabled", true);
    $code    = get_theme_mod("contentflow_ad_{$position}_code", '');

    if (!$enabled || empty($code)) {
        return;
    }

    $defaults = array(
        'lazy'  => true,   // Lazy load below-fold ads
        'label' => true,   // Show "Advertisement" label
    );
    $opts = wp_parse_args($options, $defaults);

    // Container class based on position
    $class_map = array(
        'header_banner'  => 'ad-container ad-container-banner',
        'article_top'    => 'ad-container ad-container-inline',
        'article_mid'    => 'ad-container ad-container-inline',
        'article_bottom' => 'ad-container ad-container-inline',
        'sidebar'        => 'ad-container ad-container-sidebar',
        'mobile_bottom'  => 'ad-container-mobile-bottom',
        'safelink'       => 'ad-container ad-container-inline',
    );

    $class = isset($class_map[$position]) ? $class_map[$position] : 'ad-container ad-container-inline';

    // Determine if this ad should lazy load
    $above_fold = in_array($position, array('header_banner', 'article_top'), true);
    $lazy       = $opts['lazy'] && !$above_fold;

    ?>
    <div class="<?php echo esc_attr($class); ?>" data-ad-position="<?php echo esc_attr($position); ?>">
        <?php if ($opts['label']) : ?>
            <span class="ad-label"><?php esc_html_e('Advertisement', 'intentflow'); ?></span>
        <?php endif; ?>

        <?php if ($lazy) : ?>
            <div class="ad-slot ad-lazy" data-ad-lazy>
                <noscript><?php echo $code; ?></noscript>
            </div>
            <script>
            (function(){
                var slot = document.currentScript.previousElementSibling;
                if (!('IntersectionObserver' in window)) {
                    slot.innerHTML = slot.querySelector('noscript').textContent;
                    slot.classList.remove('ad-lazy');
                    return;
                }
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            slot.innerHTML = slot.querySelector('noscript').textContent;
                            slot.classList.remove('ad-lazy');
                            observer.disconnect();
                        }
                    });
                }, { rootMargin: '200px' });
                observer.observe(slot);
            })();
            </script>
        <?php else : ?>
            <div class="ad-slot">
                <?php echo $code; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Insert inline ad after Nth paragraph in single posts
 * Only inserts if content has sufficient length (min 300 words)
 */
function contentflow_insert_inline_ad($content) {
    if (!is_single() || !is_main_query() || get_post_type() !== 'post') {
        return $content;
    }

    $enabled = get_theme_mod('contentflow_ad_article_mid_enabled', true);
    $code    = get_theme_mod('contentflow_ad_article_mid_code', '');

    if (!$enabled || empty($code)) {
        return $content;
    }

    // AdSense compliance: only show mid-article ad if content is long enough
    $word_count = str_word_count(strip_tags($content));
    if ($word_count < 300) {
        return $content;
    }

    $insert_after = get_theme_mod('intentflow_ad_mid_paragraph', 3);
    // Strip Gutenberg block comments before counting paragraphs
    $clean_content = preg_replace('/<!--\s*\/?wp:\w+.*?-->/s', '', $content);
    $paragraphs    = explode('</p>', $clean_content);

    if (count($paragraphs) < ($insert_after + 2)) {
        return $content;
    }

    $ad_html = '<div class="ad-container ad-container-inline" data-ad-position="article_mid">'
             . '<span class="ad-label">' . esc_html__('Advertisement', 'intentflow') . '</span>'
             . '<div class="ad-slot ad-lazy" data-ad-lazy>'
             . '<noscript>' . $code . '</noscript>'
             . '</div>'
             . '<script>'
             . '(function(){'
             . 'var s=document.currentScript.previousElementSibling;'
             . 'if(!("IntersectionObserver" in window)){s.innerHTML=s.querySelector("noscript").textContent;s.classList.remove("ad-lazy");return;}'
             . 'var o=new IntersectionObserver(function(e){e.forEach(function(en){if(en.isIntersecting){s.innerHTML=s.querySelector("noscript").textContent;s.classList.remove("ad-lazy");o.disconnect();}});},{rootMargin:"200px"});'
             . 'o.observe(s);'
             . '})();'
             . '</script>'
             . '</div>';

    $output = '';
    foreach ($paragraphs as $index => $paragraph) {
        $output .= $paragraph;
        if (!empty(trim($paragraph))) {
            $output .= '</p>';
        }
        if ($index === $insert_after - 1) {
            $output .= $ad_html;
        }
    }

    return $output;
}
add_filter('the_content', 'contentflow_insert_inline_ad');

/**
 * Output AdSense auto-ads code in <head> if configured
 */
function intentflow_adsense_head_code() {
    $publisher_id = get_theme_mod('intentflow_adsense_publisher_id', '');
    $auto_ads     = get_theme_mod('intentflow_adsense_auto_ads', false);

    if (!empty($publisher_id) && $auto_ads) {
        printf(
            '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=%s" crossorigin="anonymous"></script>' . "\n",
            esc_attr($publisher_id)
        );
    }
}
add_action('wp_head', 'intentflow_adsense_head_code', 1);

/**
 * Serve ads.txt from theme if no physical file exists
 */
function intentflow_ads_txt() {
    if (!isset($_SERVER['REQUEST_URI'])) {
        return;
    }

    $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($request !== '/ads.txt') {
        return;
    }

    $ads_txt = get_theme_mod('intentflow_ads_txt_content', '');

    if (empty($ads_txt)) {
        return;
    }

    // Only serve if no physical ads.txt file exists
    if (file_exists(ABSPATH . 'ads.txt')) {
        return;
    }

    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: public, max-age=86400');
    echo $ads_txt;
    exit;
}
add_action('init', 'intentflow_ads_txt', 0);
