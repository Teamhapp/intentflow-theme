<?php
/**
 * Button Component
 *
 * @param array $args {
 *     @type string $variant  'primary'|'secondary'|'outline'|'disabled'
 *     @type string $text     Button text
 *     @type string $url      Link URL (renders <a>), omit for <button>
 *     @type string $size     'sm'|'md'|'lg'
 *     @type string $class    Additional classes
 *     @type array  $attrs    Additional HTML attributes
 * }
 */

$defaults = array(
    'variant' => 'primary',
    'text'    => 'Click Here',
    'url'     => '',
    'size'    => 'md',
    'class'   => '',
    'attrs'   => array(),
);

$args = wp_parse_args($args ?? array(), $defaults);

$variant_classes = array(
    'primary'   => 'btn-primary',
    'secondary' => 'btn-secondary',
    'outline'   => 'btn-outline',
    'disabled'  => 'btn-disabled',
);

$size_classes = array(
    'sm' => 'px-4 py-2 text-small',
    'md' => 'px-6 py-3 text-base',
    'lg' => 'px-8 py-4 text-lg',
);

$classes = implode(' ', array_filter(array(
    $variant_classes[$args['variant']] ?? 'btn-primary',
    $size_classes[$args['size']] ?? '',
    $args['class'],
)));

$extra_attrs = '';
foreach ($args['attrs'] as $attr => $value) {
    $extra_attrs .= sprintf(' %s="%s"', esc_attr($attr), esc_attr($value));
}

if (!empty($args['url']) && $args['variant'] !== 'disabled') : ?>
    <a href="<?php echo esc_url($args['url']); ?>" class="<?php echo esc_attr($classes); ?>"<?php echo $extra_attrs; ?>>
        <?php echo esc_html($args['text']); ?>
    </a>
<?php else : ?>
    <button type="button" class="<?php echo esc_attr($classes); ?>"<?php echo $extra_attrs; ?>
        <?php echo $args['variant'] === 'disabled' ? ' disabled' : ''; ?>>
        <?php echo esc_html($args['text']); ?>
    </button>
<?php endif; ?>
