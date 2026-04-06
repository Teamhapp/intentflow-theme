<?php
/**
 * Modal/Popup Component
 *
 * Usage: get_template_part('template-parts/components/modal', null, $args)
 *
 * @param array $args {
 *   @type string $id      Unique modal ID
 *   @type string $title   Modal heading
 *   @type string $content Modal body HTML
 *   @type string $cta_text   Button text
 *   @type string $cta_url    Button URL
 *   @type string $variant    'default' | 'email' | 'offer'
 * }
 */

$defaults = array(
    'id'       => 'intentflow-modal',
    'title'    => '',
    'content'  => '',
    'cta_text' => '',
    'cta_url'  => '#',
    'variant'  => 'default',
);
$args = wp_parse_args($args ?? array(), $defaults);
?>
<div class="if-modal-overlay" id="<?php echo esc_attr($args['id']); ?>" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($args['title']); ?>">
    <div class="if-modal">
        <button type="button" class="if-modal-close" aria-label="<?php esc_attr_e('Close', 'intentflow'); ?>">&times;</button>

        <?php if (!empty($args['title'])) : ?>
            <h3 class="if-modal-title"><?php echo esc_html($args['title']); ?></h3>
        <?php endif; ?>

        <?php if (!empty($args['content'])) : ?>
            <div class="if-modal-body"><?php echo wp_kses_post($args['content']); ?></div>
        <?php endif; ?>

        <?php if (!empty($args['cta_text'])) : ?>
            <a href="<?php echo esc_url($args['cta_url']); ?>" class="if-modal-cta btn-primary">
                <?php echo esc_html($args['cta_text']); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
