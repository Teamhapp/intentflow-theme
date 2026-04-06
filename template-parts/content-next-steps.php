<?php
/**
 * Next Steps Section
 * Displays the "Next Step Engine" results in the article page
 */
$steps = intentflow_get_next_steps();

if (empty($steps)) return;
?>
<div class="next-steps-section my-12">
    <h2 class="text-h2 mb-6 flex items-center gap-2">
        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
        <?php esc_html_e('Your Next Steps', 'intentflow'); ?>
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($steps as $step) : ?>
            <div class="next-step-group">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-primary"><?php echo $step['icon']; ?></span>
                    <h3 class="text-body font-semibold"><?php echo esc_html($step['label']); ?></h3>
                </div>
                <div class="space-y-2">
                    <?php foreach ($step['posts'] as $step_post) : ?>
                        <a href="<?php echo esc_url(get_permalink($step_post->ID)); ?>"
                           class="next-step-link group">
                            <?php if (has_post_thumbnail($step_post->ID)) : ?>
                                <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                                    <?php echo get_the_post_thumbnail($step_post->ID, 'thumbnail', array(
                                        'class' => 'w-full h-full object-cover',
                                    )); ?>
                                </div>
                            <?php endif; ?>
                            <span class="text-small font-medium text-text-dark group-hover:text-primary transition-colors line-clamp-2">
                                <?php echo esc_html($step_post->post_title); ?>
                            </span>
                            <svg class="w-4 h-4 flex-shrink-0 text-text-light group-hover:text-primary transition-colors ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
