<?php
/**
 * Mobile Sticky CTA - appears on scroll
 */
$button_text = get_theme_mod('contentflow_cta_button_text', 'Download Free');
$button_url  = get_theme_mod('contentflow_cta_button_url', '#');
?>
<div class="sticky-cta hidden" id="sticky-cta">
    <a href="<?php echo esc_url($button_url); ?>"
       class="btn-secondary w-full text-center block py-3 rounded-lg font-semibold shadow-lg">
        <?php echo esc_html($button_text); ?>
    </a>
</div>
