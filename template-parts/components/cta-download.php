<?php
/**
 * CTA Block - Download variant
 */
$title       = $args['title'] ?? get_theme_mod('contentflow_cta_title', 'Download Now');
$description = $args['description'] ?? get_theme_mod('contentflow_cta_description', 'Get the latest version of this tool for free.');
$button_text = $args['button_text'] ?? get_theme_mod('contentflow_cta_button_text', 'Download Free');
$button_url  = $args['button_url'] ?? get_theme_mod('contentflow_cta_button_url', '#');
?>
<div class="cta-block-green" id="cta-block">
    <h2 class="text-h2 text-white mb-3"><?php echo esc_html($title); ?></h2>
    <p class="text-white/90 text-body mb-6 max-w-lg mx-auto"><?php echo esc_html($description); ?></p>
    <?php
    get_template_part('template-parts/components/button', null, array(
        'variant' => 'primary',
        'text'    => $button_text,
        'url'     => $button_url,
        'size'    => 'lg',
        'class'   => 'bg-white text-cta hover:bg-gray-100 shadow-lg',
    ));
    ?>
</div>
