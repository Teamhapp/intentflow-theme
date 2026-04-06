<?php
/**
 * CTA Block - Try Tool variant
 */
$title       = $args['title'] ?? 'Try This Tool';
$description = $args['description'] ?? 'Experience the full features with our free trial.';
$button_text = $args['button_text'] ?? 'Try Now';
$button_url  = $args['button_url'] ?? '#';
?>
<div class="cta-block" id="cta-block">
    <h2 class="text-h2 text-white mb-3"><?php echo esc_html($title); ?></h2>
    <p class="text-white/90 text-body mb-6 max-w-lg mx-auto"><?php echo esc_html($description); ?></p>
    <?php
    get_template_part('template-parts/components/button', null, array(
        'variant' => 'secondary',
        'text'    => $button_text,
        'url'     => $button_url,
        'size'    => 'lg',
        'class'   => 'shadow-lg',
    ));
    ?>
</div>
