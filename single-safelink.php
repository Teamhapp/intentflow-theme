<?php
/**
 * IntentFlow Safelink Page - Revenue Maximized
 *
 * Configurable per-link:
 * - Timer modes: circle, progress bar, text-only
 * - Step modes: fast (2), standard (3), max (4)
 * - Ad density: low, medium, high
 * - Double page flow for 2x impressions
 * - Click tracking via AJAX
 */
get_header();

$post_id        = get_the_ID();
$target_url     = get_post_meta($post_id, '_safelink_target_url', true);

// Show error if no target URL configured
if (empty($target_url)) : ?>
    <div class="safelink-bg"><div class="safelink-mesh safelink-mesh-1"></div><div class="safelink-mesh safelink-mesh-2"></div><div class="safelink-mesh safelink-mesh-3"></div></div>
    <main class="safelink-main">
        <div class="max-w-xl mx-auto px-4 py-24 relative z-10 text-center">
            <div class="text-5xl mb-4">&#9888;</div>
            <h1 class="text-h2 text-white mb-4"><?php esc_html_e('Link Not Configured', 'intentflow'); ?></h1>
            <p class="text-white/60 mb-6"><?php esc_html_e('This download link has not been set up yet. Please contact the site administrator.', 'intentflow'); ?></p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary"><?php esc_html_e('Back to Homepage', 'intentflow'); ?></a>
        </div>
    </main>
<?php get_footer(); return; endif;

// Target URL exists — continue with normal flow
$timer          = (int) get_post_meta($post_id, '_safelink_timer_duration', true);
$timer          = $timer > 0 ? $timer : (int) get_theme_mod('contentflow_safelink_timer', 10);
$timer_mode     = get_post_meta($post_id, '_safelink_timer_mode', true) ?: 'circle';
$steps_mode     = get_post_meta($post_id, '_safelink_steps', true) ?: 'standard';
$wait_duration  = (int) get_post_meta($post_id, '_safelink_wait_duration', true);
$wait_duration  = $wait_duration > 0 ? $wait_duration : (int) get_theme_mod('intentflow_safelink_wait_duration', 5);
$wait_text      = get_theme_mod('contentflow_safelink_text', 'Please wait while we prepare your link...');
$ad_density     = get_post_meta($post_id, '_safelink_ad_density', true) ?: 'medium';
$second_page    = get_post_meta($post_id, '_safelink_second_page', true);
$btn_generate   = get_post_meta($post_id, '_safelink_btn_generate_text', true) ?: __('Generate Link', 'intentflow');
$btn_download   = get_post_meta($post_id, '_safelink_btn_download_text', true) ?: __('Continue to Download', 'intentflow');

// Multi-page flow: step=1 (default), step=2 (verify), step=3 (final download)
$current_step   = isset($_GET['step']) ? absint($_GET['step']) : 1;
$is_page_2      = $current_step === 2;
$is_page_3      = $current_step === 3;
$second_timer   = (int) get_post_meta($post_id, '_safelink_second_page_timer', true);
$second_timer   = $second_timer > 0 ? $second_timer : max(3, (int) floor($timer / 2));
$total_pages    = $second_page ? 3 : 1;

// Page-specific settings
if ($is_page_2 && $second_page) {
    $timer      = $second_timer;
    $steps_mode = 'standard';
    $wait_text  = __('Verifying your download link...', 'intentflow');
} elseif ($is_page_3 && $second_page) {
    $timer      = max(3, (int) floor($second_timer / 2));
    $steps_mode = 'fast';
    $wait_text  = __('Almost there! Preparing final link...', 'intentflow');
}

// Determine which steps to show
$show_generate = in_array($steps_mode, array('standard', 'max'), true);
$show_wait     = $steps_mode === 'max';

// Total steps for dots indicator
$total_steps = 1; // timer always
if ($show_generate) $total_steps++;
if ($show_wait) $total_steps++;
$total_steps++; // download always
?>

<!-- Mesh Gradient Background -->
<div class="safelink-bg">
    <div class="safelink-mesh safelink-mesh-1"></div>
    <div class="safelink-mesh safelink-mesh-2"></div>
    <div class="safelink-mesh safelink-mesh-3"></div>
</div>

<main class="safelink-main">
    <div class="safelink-container relative z-10">

        <?php while (have_posts()) : the_post(); ?>

        <div class="safelink-layout">

            <!-- Main Card -->
            <div class="safelink-layout-main">

                <?php if ($second_page && $total_pages > 1) : ?>
                    <!-- Step progress -->
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15">
                            <?php for ($s = 1; $s <= $total_pages; $s++) : ?>
                                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center <?php echo $s === $current_step ? 'bg-primary text-white' : ($s < $current_step ? 'bg-cta text-white' : 'bg-white/20 text-white/50'); ?>">
                                    <?php echo $s < $current_step ? '✓' : $s; ?>
                                </span>
                                <?php if ($s < $total_pages) : ?>
                                    <span class="w-4 h-px <?php echo $s < $current_step ? 'bg-cta' : 'bg-white/20'; ?>"></span>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="text-xs text-white/50 mt-2">
                            <?php printf(esc_html__('Step %d of %d', 'intentflow'), $current_step, $total_pages); ?>
                            <?php if ($is_page_2) : ?> — <?php esc_html_e('Verification', 'intentflow'); ?><?php endif; ?>
                            <?php if ($is_page_3) : ?> — <?php esc_html_e('Final Download', 'intentflow'); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="safelink-card"
                     id="safelink-app"
                     data-timer="<?php echo esc_attr($timer); ?>"
                     data-wait="<?php echo esc_attr($wait_duration); ?>"
                     data-target="<?php echo esc_url(intentflow_safelink_next_url($current_step, $total_pages, $target_url, get_permalink())); ?>"
                     data-show-generate="<?php echo $show_generate ? '1' : '0'; ?>"
                     data-show-wait="<?php echo $show_wait ? '1' : '0'; ?>"
                     data-timer-mode="<?php echo esc_attr($timer_mode); ?>"
                     data-post-id="<?php echo esc_attr($post_id); ?>"
                     data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">

                    <!-- Lock Icon -->
                    <div class="flex justify-center mb-6">
                        <div class="safelink-icon" id="safelink-icon">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>

                    <h1 class="text-h2 text-center mb-2"><?php the_title(); ?></h1>
                    <p class="text-text-light text-center text-small mb-8" id="safelink-status">
                        <?php echo esc_html($wait_text); ?>
                    </p>

                    <!-- Post content -->
                    <?php if (get_the_content() && !$is_page_2) : ?>
                        <div class="safelink-content mb-8">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- AdSense compliance: max 2 ads per safelink page (sidebar + below) -->
                    <!-- Between-steps ad removed to comply with ad density policy -->

                    <!-- ============ STEP 1: Timer ============ -->
                    <div class="safelink-step safelink-step-active" id="step-timer">

                        <?php if ($timer_mode === 'circle') : ?>
                            <!-- Circular SVG Timer -->
                            <div class="timer-circle mb-6">
                                <svg viewBox="0 0 120 120">
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="#2563EB" stroke-width="8"
                                            stroke-dasharray="339.292" stroke-dashoffset="0"
                                            stroke-linecap="round" id="timer-progress"/>
                                </svg>
                                <div class="timer-text" id="timer-display"><?php echo esc_html($timer); ?></div>
                            </div>

                        <?php elseif ($timer_mode === 'progress') : ?>
                            <!-- Progress Bar Timer -->
                            <div class="safelink-progress-timer mb-6">
                                <div class="text-center mb-3">
                                    <span class="text-4xl font-bold text-white" id="timer-display"><?php echo esc_html($timer); ?></span>
                                    <span class="text-white/50 text-small ml-1"><?php esc_html_e('seconds', 'intentflow'); ?></span>
                                </div>
                                <div class="safelink-progress-track">
                                    <div class="safelink-progress-fill" id="timer-progress"></div>
                                </div>
                            </div>

                        <?php else : ?>
                            <!-- Text-Only Timer -->
                            <div class="text-center mb-6">
                                <div class="text-5xl font-bold text-white mb-2" id="timer-display">
                                    <?php echo esc_html($timer); ?>
                                </div>
                                <p class="text-white/50 text-small">
                                    <?php esc_html_e('seconds remaining', 'intentflow'); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Step dots -->
                        <div class="safelink-steps-indicator">
                            <?php
                            $step_labels = array(__('Wait', 'intentflow'));
                            if ($show_generate) $step_labels[] = __('Generate', 'intentflow');
                            if ($show_wait) $step_labels[] = __('Verify', 'intentflow');
                            $step_labels[] = __('Download', 'intentflow');
                            for ($i = 0; $i < $total_steps; $i++) : ?>
                                <span class="step-dot-wrap <?php echo $i === 0 ? 'active' : ''; ?>">
                                    <span class="step-dot <?php echo $i === 0 ? 'step-dot-active' : ''; ?>"></span>
                                    <span class="step-dot-label"><?php echo esc_html($step_labels[$i] ?? ''); ?></span>
                                </span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- ============ STEP 2: Generate (if enabled) ============ -->
                    <?php if ($show_generate) : ?>
                    <div class="safelink-step" id="step-generate">
                        <div class="text-center">
                            <div class="mb-4">
                                <svg class="w-12 h-12 mx-auto text-cta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <p class="text-body font-medium mb-6">
                                <?php esc_html_e('Your link is ready to be generated', 'intentflow'); ?>
                            </p>
                            <button type="button" id="btn-generate" class="btn-secondary text-lg px-8 py-4 w-full sm:w-auto">
                                <?php echo esc_html($btn_generate); ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ============ STEP 3: Wait (if max mode) ============ -->
                    <?php if ($show_wait) : ?>
                    <div class="safelink-step" id="step-wait">
                        <div class="text-center">
                            <div class="safelink-spinner mb-6">
                                <div class="safelink-spinner-ring"></div>
                                <div class="safelink-spinner-ring"></div>
                                <div class="safelink-spinner-ring"></div>
                            </div>
                            <p class="text-body font-medium mb-2">
                                <?php esc_html_e('Generating your secure link...', 'intentflow'); ?>
                            </p>
                            <p class="text-small text-text-light" id="wait-timer-display"></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ============ FINAL: Download ============ -->
                    <div class="safelink-step" id="step-download">
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-16 h-16 mx-auto bg-cta/10 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-cta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-body font-semibold text-cta mb-2">
                                <?php
                                if ($current_step >= $total_pages) {
                                    esc_html_e('Link Ready!', 'intentflow');
                                } elseif ($current_step === 1) {
                                    esc_html_e('Step 1 Complete!', 'intentflow');
                                } else {
                                    esc_html_e('Almost there!', 'intentflow');
                                }
                                ?>
                            </p>
                            <p class="text-small text-text-light mb-6">
                                <?php
                                if ($current_step >= $total_pages) {
                                    esc_html_e('Click below to get your download', 'intentflow');
                                } elseif ($current_step === 1) {
                                    esc_html_e('Click below to proceed to verification', 'intentflow');
                                } else {
                                    esc_html_e('Click below for the final step', 'intentflow');
                                }
                                ?>
                            </p>
                            <a href="#" id="btn-download"
                               class="btn-secondary text-lg px-8 py-4 w-full sm:w-auto shadow-lg"
                               rel="nofollow noopener"
                               data-target="<?php echo esc_url(intentflow_safelink_next_url($current_step, $total_pages, $target_url, get_permalink())); ?>">
                                <?php echo esc_html($btn_download); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Sidebar Ad (desktop only) -->
            <?php if (in_array($ad_density, array('medium', 'high'), true)) : ?>
                <div class="safelink-layout-side">
                    <?php contentflow_render_ad('safelink', array('lazy' => false)); ?>
                </div>
            <?php endif; ?>

        </div>

        <?php endwhile; ?>

        <!-- AD 2: Between timer card and rotating content -->
        <div class="mt-8">
            <?php contentflow_render_ad('safelink'); ?>
        </div>

        <!-- Rotating Content — keeps visitors engaged while timer runs -->
        <?php
        $rotating = new WP_Query(array(
            'post_type'      => 'post',
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'post_status'    => 'publish',
        ));
        if ($rotating->have_posts()) :
        ?>
        <div class="mt-10" id="rotating-content">
            <h2 class="text-h2 mb-4 text-center text-white"><?php esc_html_e('While You Wait...', 'intentflow'); ?></h2>
            <p class="text-center text-white/50 text-small mb-6"><?php esc_html_e('Check out these popular articles', 'intentflow'); ?></p>

            <!-- Article cards that rotate every 5 seconds -->
            <div class="safelink-rotating-wrap">
                <?php $idx = 0; while ($rotating->have_posts()) : $rotating->the_post(); ?>
                <div class="safelink-rotate-item <?php echo $idx < 2 ? 'safelink-rotate-visible' : ''; ?>" data-rotate-idx="<?php echo $idx; ?>">
                    <a href="<?php the_permalink(); ?>" class="safelink-rotate-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('card-horizontal', array('class' => 'safelink-rotate-img')); ?>
                        <?php endif; ?>
                        <div class="safelink-rotate-body">
                            <?php
                            $cats = get_the_category();
                            if (!empty($cats)) :
                            ?>
                                <span class="safelink-rotate-tag"><?php echo esc_html($cats[0]->name); ?></span>
                            <?php endif; ?>
                            <div class="safelink-rotate-title"><?php the_title(); ?></div>
                            <div class="safelink-rotate-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15)); ?></div>
                            <div class="safelink-rotate-meta"><?php echo esc_html(get_the_date()); ?> &middot; <?php echo contentflow_reading_time(); ?></div>
                        </div>
                    </a>
                </div>
                <?php $idx++; endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- AD 3: After rotating content -->
        <div class="mt-8">
            <?php contentflow_render_ad('safelink'); ?>
        </div>

        <!-- More Articles -->
        <?php
        $more = new WP_Query(array(
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'offset'         => 6,
            'orderby'        => 'date',
        ));
        if ($more->have_posts()) :
        ?>
            <div class="mt-12">
                <h2 class="text-h2 mb-6 text-center text-white"><?php esc_html_e('More Articles', 'intentflow'); ?></h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <?php while ($more->have_posts()) : $more->the_post();
                        get_template_part('template-parts/content-card', 'vertical');
                    endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- AD 4: After more articles -->
        <div class="mt-8">
            <?php contentflow_render_ad('safelink'); ?>
        </div>

        <!-- Back link -->
        <div class="mt-6 text-center">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="text-white/70 text-small hover:text-white transition-colors">
                &larr; <?php esc_html_e('Back to Homepage', 'intentflow'); ?>
            </a>
        </div>

        <!-- Rotation script -->
        <script>
        (function(){
            var items = document.querySelectorAll('.safelink-rotate-item');
            if (items.length < 3) return;
            var total = items.length;
            var showing = [0, 1];
            setInterval(function(){
                // Hide current pair
                items[showing[0]].classList.remove('safelink-rotate-visible');
                items[showing[1]].classList.remove('safelink-rotate-visible');
                // Show next pair
                showing[0] = (showing[1] + 1) % total;
                showing[1] = (showing[0] + 1) % total;
                items[showing[0]].classList.add('safelink-rotate-visible');
                items[showing[1]].classList.add('safelink-rotate-visible');
            }, 5000);
        })();
        </script>

    </div>
</main>

<?php get_footer(); ?>
