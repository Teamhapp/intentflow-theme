<?php
/**
 * Safelink Custom Post Type - Revenue Maximized
 *
 * Per-safelink options:
 * - Target URL + timer duration
 * - Timer display mode (countdown circle, progress bar, text-only)
 * - Number of steps (2-step fast, 3-step standard, 4-step max revenue)
 * - Custom button texts per step
 * - Ad placement density (low, medium, high)
 * - Click tracking (impressions + clicks stored in post meta)
 * - Second page redirect (optional double-page flow for 2x impressions)
 */

function intentflow_register_safelink_cpt() {
    register_post_type('safelink', array(
        'labels' => array(
            'name'               => __('Safelinks', 'intentflow'),
            'singular_name'      => __('Safelink', 'intentflow'),
            'add_new'            => __('Add New Safelink', 'intentflow'),
            'add_new_item'       => __('Add New Safelink', 'intentflow'),
            'edit_item'          => __('Edit Safelink', 'intentflow'),
            'new_item'           => __('New Safelink', 'intentflow'),
            'view_item'          => __('View Safelink', 'intentflow'),
            'search_items'       => __('Search Safelinks', 'intentflow'),
            'not_found'          => __('No safelinks found', 'intentflow'),
            'not_found_in_trash' => __('No safelinks found in trash', 'intentflow'),
            'all_items'          => __('All Safelinks', 'intentflow'),
            'menu_name'          => __('Safelinks', 'intentflow'),
        ),
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => true,
        'has_archive'         => false,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-admin-links',
        'menu_position'       => 25,
        'supports'            => array('title', 'editor'),
        'rewrite'             => array('slug' => 'go', 'with_front' => false),
        'capability_type'     => 'post',
        'exclude_from_search' => true,
    ));

    // Register all meta fields
    $meta_fields = array(
        '_safelink_target_url' => array(
            'type'    => 'string',
            'default' => '',
        ),
        '_safelink_timer_duration' => array(
            'type'    => 'integer',
            'default' => 0,
        ),
        '_safelink_timer_mode' => array(
            'type'    => 'string',
            'default' => 'circle', // circle, progress, text
        ),
        '_safelink_steps' => array(
            'type'    => 'string',
            'default' => 'standard', // fast(2), standard(3), max(4)
        ),
        '_safelink_wait_duration' => array(
            'type'    => 'integer',
            'default' => 0,
        ),
        '_safelink_btn_generate_text' => array(
            'type'    => 'string',
            'default' => '',
        ),
        '_safelink_btn_download_text' => array(
            'type'    => 'string',
            'default' => '',
        ),
        '_safelink_ad_density' => array(
            'type'    => 'string',
            'default' => 'medium', // low, medium, high
        ),
        '_safelink_second_page' => array(
            'type'    => 'boolean',
            'default' => false,
        ),
        '_safelink_second_page_timer' => array(
            'type'    => 'integer',
            'default' => 0,
        ),
        // Analytics
        '_safelink_impressions' => array(
            'type'    => 'integer',
            'default' => 0,
        ),
        '_safelink_clicks' => array(
            'type'    => 'integer',
            'default' => 0,
        ),
    );

    foreach ($meta_fields as $key => $config) {
        register_post_meta('safelink', $key, array(
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => $config['type'],
            'default'       => $config['default'],
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));
    }
}
add_action('init', 'intentflow_register_safelink_cpt');

// Use Classic Editor for safelinks (simple textarea, not full Gutenberg)
function intentflow_safelink_classic_editor($use_block_editor, $post_type) {
    if ($post_type === 'safelink') return false;
    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'intentflow_safelink_classic_editor', 10, 2);

// Flush rewrite rules on theme activation so /go/ slug works
function intentflow_flush_rewrites() {
    intentflow_register_safelink_cpt();
    flush_rewrite_rules();
    update_option('intentflow_rewrites_flushed', true);
}
add_action('after_switch_theme', 'intentflow_flush_rewrites');

// Auto-flush on first admin load if not yet flushed
function intentflow_maybe_flush_rewrites() {
    if (!get_option('intentflow_rewrites_flushed')) {
        intentflow_flush_rewrites();
    }
}
add_action('admin_init', 'intentflow_maybe_flush_rewrites');

// Add body class for safelink pages
function intentflow_safelink_body_class($classes) {
    if (is_singular('safelink')) {
        $classes[] = 'safelink-page';
    }
    return $classes;
}
add_filter('body_class', 'intentflow_safelink_body_class');

/**
 * Meta box for safelink settings
 */
function intentflow_safelink_meta_boxes() {
    add_meta_box(
        'safelink_settings',
        __('Safelink Settings', 'intentflow'),
        'intentflow_safelink_settings_html',
        'safelink',
        'normal',
        'high'
    );
    add_meta_box(
        'safelink_analytics',
        __('Analytics', 'intentflow'),
        'intentflow_safelink_analytics_html',
        'safelink',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'intentflow_safelink_meta_boxes');

function intentflow_safelink_settings_html($post) {
    $target_url       = get_post_meta($post->ID, '_safelink_target_url', true);
    $timer            = get_post_meta($post->ID, '_safelink_timer_duration', true);
    $timer_mode       = get_post_meta($post->ID, '_safelink_timer_mode', true) ?: 'circle';
    $steps            = get_post_meta($post->ID, '_safelink_steps', true) ?: 'standard';
    $wait_duration    = get_post_meta($post->ID, '_safelink_wait_duration', true);
    $btn_generate     = get_post_meta($post->ID, '_safelink_btn_generate_text', true);
    $btn_download     = get_post_meta($post->ID, '_safelink_btn_download_text', true);
    $ad_density       = get_post_meta($post->ID, '_safelink_ad_density', true) ?: 'medium';
    $second_page      = get_post_meta($post->ID, '_safelink_second_page', true);
    $second_timer     = get_post_meta($post->ID, '_safelink_second_page_timer', true);

    wp_nonce_field('intentflow_safelink_nonce', 'intentflow_safelink_nonce');
    ?>
    <style>
        .sf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .sf-field { margin-bottom: 15px; }
        .sf-field label { display: block; font-weight: 600; margin-bottom: 4px; }
        .sf-field small { color: #666; }
        .sf-section { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .sf-section h4 { margin: 0 0 10px 0; }
        .sf-revenue-tip { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 10px 15px; margin: 10px 0; border-radius: 0 4px 4px 0; }
        .sf-collapse{margin-bottom:14px}.sf-collapse summary{font-weight:600;padding:10px 14px;background:#f9f9f9;border-radius:8px;cursor:pointer;font-size:14px;list-style:none}.sf-collapse summary::before{content:'▸ ';color:#6B7280}.sf-collapse[open] summary::before{content:'▾ '}.sf-collapse[open] summary{border-radius:8px 8px 0 0}
    </style>

    <!-- Essential: Target URL + Steps -->
    <div class="sf-field">
        <label for="sf_target_url"><?php esc_html_e('Target URL', 'intentflow'); ?> <span style="color:#EF4444">*</span></label>
        <input type="url" id="sf_target_url" name="sf_target_url"
               value="<?php echo esc_attr($target_url); ?>" class="widefat"
               placeholder="https://example.com/download" required>
    </div>

    <details class="sf-collapse" open>
        <summary><?php esc_html_e('Timer Settings', 'intentflow'); ?></summary>
    <div class="sf-grid" style="padding:14px">
        <div>

            <div class="sf-field">
                <label for="sf_timer_duration"><?php esc_html_e('Countdown Duration (seconds)', 'intentflow'); ?></label>
                <input type="number" id="sf_timer_duration" name="sf_timer_duration"
                       value="<?php echo esc_attr($timer); ?>" min="0" max="60" class="widefat">
                <small><?php esc_html_e('0 = use global default', 'intentflow'); ?></small>
            </div>

            <div class="sf-field">
                <label for="sf_timer_mode"><?php esc_html_e('Timer Display Mode', 'intentflow'); ?></label>
                <select id="sf_timer_mode" name="sf_timer_mode" class="widefat">
                    <option value="circle" <?php selected($timer_mode, 'circle'); ?>>
                        <?php esc_html_e('Circular (SVG ring)', 'intentflow'); ?>
                    </option>
                    <option value="progress" <?php selected($timer_mode, 'progress'); ?>>
                        <?php esc_html_e('Progress Bar', 'intentflow'); ?>
                    </option>
                    <option value="text" <?php selected($timer_mode, 'text'); ?>>
                        <?php esc_html_e('Text Only (minimal)', 'intentflow'); ?>
                    </option>
                </select>
            </div>

            <div class="sf-field">
                <label for="sf_wait_duration"><?php esc_html_e('Wait/Processing Duration (seconds)', 'intentflow'); ?></label>
                <input type="number" id="sf_wait_duration" name="sf_wait_duration"
                       value="<?php echo esc_attr($wait_duration); ?>" min="0" max="30" class="widefat">
                <small><?php esc_html_e('0 = use global default', 'intentflow'); ?></small>
            </div>
        </div>
        <div>

            <div class="sf-field">
                <label for="sf_steps"><?php esc_html_e('Number of Steps', 'intentflow'); ?></label>
                <select id="sf_steps" name="sf_steps" class="widefat">
                    <option value="fast" <?php selected($steps, 'fast'); ?>>
                        <?php esc_html_e('Fast (2 steps: Timer → Download)', 'intentflow'); ?>
                    </option>
                    <option value="standard" <?php selected($steps, 'standard'); ?>>
                        <?php esc_html_e('Standard (3 steps: Timer → Generate → Download)', 'intentflow'); ?>
                    </option>
                    <option value="max" <?php selected($steps, 'max'); ?>>
                        <?php esc_html_e('Max Revenue (4 steps: Timer → Generate → Wait → Download)', 'intentflow'); ?>
                    </option>
                </select>
            </div>

            <div class="sf-field">
                <label for="sf_ad_density"><?php esc_html_e('Ad Density', 'intentflow'); ?></label>
                <select id="sf_ad_density" name="sf_ad_density" class="widefat">
                    <option value="low" <?php selected($ad_density, 'low'); ?>>
                        <?php esc_html_e('Low (1 ad below content)', 'intentflow'); ?>
                    </option>
                    <option value="medium" <?php selected($ad_density, 'medium'); ?>>
                        <?php esc_html_e('Medium (sidebar + below content)', 'intentflow'); ?>
                    </option>
                    <option value="high" <?php selected($ad_density, 'high'); ?>>
                        <?php esc_html_e('High (sidebar + below + between steps)', 'intentflow'); ?>
                    </option>
                </select>
            </div>

            <div class="sf-revenue-tip">
                <strong><?php esc_html_e('Revenue Tip:', 'intentflow'); ?></strong>
                <?php esc_html_e('"Max Revenue" flow with "High" ad density keeps visitors on page longest. Use 10-15s timers for best balance.', 'intentflow'); ?>
            </div>
        </div>
    </div>
    </details>

    <!-- Monetization -->
    <details class="sf-collapse">
        <summary><?php esc_html_e('Monetization (Ad Density & Double Page)', 'intentflow'); ?></summary>
    <div style="padding:14px">

        <div class="sf-field">
            <label>
                <input type="checkbox" name="sf_second_page" value="1" <?php checked($second_page); ?>>
                <?php esc_html_e('Enable second confirmation page (doubles ad impressions per visit)', 'intentflow'); ?>
            </label>
            <br><small><?php esc_html_e('After step 1, redirects to a second page with another timer before final download.', 'intentflow'); ?></small>
        </div>

        <div class="sf-field">
            <label for="sf_second_timer"><?php esc_html_e('Second Page Timer (seconds)', 'intentflow'); ?></label>
            <input type="number" id="sf_second_timer" name="sf_second_timer"
                   value="<?php echo esc_attr($second_timer); ?>" min="0" max="30" class="widefat">
            <small><?php esc_html_e('0 = use half of main timer', 'intentflow'); ?></small>
        </div>
    </div>
    </details>

    <!-- Custom Button Texts -->
    <details class="sf-collapse">
        <summary><?php esc_html_e('Custom Button Text', 'intentflow'); ?></summary>
    <div style="padding:14px">
        <div class="sf-grid">
            <div class="sf-field">
                <label for="sf_btn_generate"><?php esc_html_e('Generate Button', 'intentflow'); ?></label>
                <input type="text" id="sf_btn_generate" name="sf_btn_generate"
                       value="<?php echo esc_attr($btn_generate); ?>" class="widefat"
                       placeholder="Generate Link">
            </div>
            <div class="sf-field">
                <label for="sf_btn_download"><?php esc_html_e('Download Button', 'intentflow'); ?></label>
                <input type="text" id="sf_btn_download" name="sf_btn_download"
                       value="<?php echo esc_attr($btn_download); ?>" class="widefat"
                       placeholder="Continue to Download">
            </div>
        </div>
    </div>
    </details>
    <?php
}

function intentflow_safelink_analytics_html($post) {
    $impressions = (int) get_post_meta($post->ID, '_safelink_impressions', true);
    $clicks      = (int) get_post_meta($post->ID, '_safelink_clicks', true);
    $ctr         = $impressions > 0 ? round(($clicks / $impressions) * 100, 1) : 0;
    $funnel      = intentflow_get_safelink_funnel($post->ID);
    ?>
    <style>.sf-stat{text-align:center;padding:6px 0;border-bottom:1px solid #f0f0f0}.sf-stat:last-child{border:none}.sf-stat .n{font-size:22px;font-weight:700;line-height:1}.sf-stat .l{font-size:10px;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}.sf-funnel{margin-top:12px;padding-top:12px;border-top:1px solid #ddd}.sf-funnel-row{display:flex;justify-content:space-between;align-items:center;padding:4px 0;font-size:12px}.sf-funnel-bar{height:6px;background:#e5e7eb;border-radius:4px;flex:1;margin:0 8px}.sf-funnel-fill{height:100%;border-radius:4px;background:#2563EB;transition:width .3s}</style>

    <div class="sf-stat"><div class="n" style="color:#2563EB"><?php echo number_format($impressions); ?></div><div class="l"><?php esc_html_e('Page Views', 'intentflow'); ?></div></div>
    <div class="sf-stat"><div class="n" style="color:#22C55E"><?php echo number_format($clicks); ?></div><div class="l"><?php esc_html_e('Clicks', 'intentflow'); ?></div></div>
    <div class="sf-stat"><div class="n" style="color:#F59E0B"><?php echo $ctr; ?>%</div><div class="l"><?php esc_html_e('CTR', 'intentflow'); ?></div></div>

    <?php if ($funnel['timer_start'] > 0) : ?>
    <div class="sf-funnel">
        <strong style="font-size:12px;display:block;margin-bottom:8px"><?php esc_html_e('Funnel', 'intentflow'); ?></strong>

        <div class="sf-funnel-row">
            <span><?php esc_html_e('Timer Start', 'intentflow'); ?></span>
            <span style="font-weight:600"><?php echo number_format($funnel['timer_start']); ?></span>
        </div>

        <div class="sf-funnel-row">
            <span><?php esc_html_e('Timer Done', 'intentflow'); ?></span>
            <div class="sf-funnel-bar"><div class="sf-funnel-fill" style="width:<?php echo $funnel['timer_completion_rate']; ?>%"></div></div>
            <span style="font-weight:600"><?php echo $funnel['timer_completion_rate']; ?>%</span>
        </div>

        <?php if ($funnel['generate_click'] > 0) : ?>
        <div class="sf-funnel-row">
            <span><?php esc_html_e('Generate', 'intentflow'); ?></span>
            <div class="sf-funnel-bar"><div class="sf-funnel-fill" style="width:<?php echo $funnel['generate_rate']; ?>%;background:#22C55E"></div></div>
            <span style="font-weight:600"><?php echo $funnel['generate_rate']; ?>%</span>
        </div>
        <?php endif; ?>

        <div class="sf-funnel-row">
            <span><?php esc_html_e('Download', 'intentflow'); ?></span>
            <div class="sf-funnel-bar"><div class="sf-funnel-fill" style="width:<?php echo $funnel['download_rate']; ?>%;background:#F59E0B"></div></div>
            <span style="font-weight:600"><?php echo $funnel['download_rate']; ?>%</span>
        </div>

        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e5e7eb;text-align:center">
            <div style="font-size:18px;font-weight:700;color:<?php echo $funnel['overall_conversion'] > 10 ? '#22C55E' : '#EF4444'; ?>"><?php echo $funnel['overall_conversion']; ?>%</div>
            <div style="font-size:10px;color:#666;text-transform:uppercase"><?php esc_html_e('Overall Conversion', 'intentflow'); ?></div>
        </div>
    </div>
    <?php endif; ?>
    <?php
}

/**
 * Save all safelink meta fields
 */
function intentflow_save_safelink_meta($post_id) {
    if (!isset($_POST['intentflow_safelink_nonce']) ||
        !wp_verify_nonce($_POST['intentflow_safelink_nonce'], 'intentflow_safelink_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $text_fields = array(
        'sf_target_url'       => '_safelink_target_url',
        'sf_timer_mode'       => '_safelink_timer_mode',
        'sf_steps'            => '_safelink_steps',
        'sf_btn_generate'     => '_safelink_btn_generate_text',
        'sf_btn_download'     => '_safelink_btn_download_text',
        'sf_ad_density'       => '_safelink_ad_density',
    );

    foreach ($text_fields as $post_key => $meta_key) {
        if (isset($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            if ($meta_key === '_safelink_target_url') {
                $value = esc_url_raw($_POST[$post_key]);
            }
            update_post_meta($post_id, $meta_key, $value);
        }
    }

    $int_fields = array(
        'sf_timer_duration'  => '_safelink_timer_duration',
        'sf_wait_duration'   => '_safelink_wait_duration',
        'sf_second_timer'    => '_safelink_second_page_timer',
    );

    foreach ($int_fields as $post_key => $meta_key) {
        if (isset($_POST[$post_key])) {
            update_post_meta($post_id, $meta_key, absint($_POST[$post_key]));
        }
    }

    // Checkbox
    update_post_meta($post_id, '_safelink_second_page', !empty($_POST['sf_second_page']));
}
add_action('save_post_safelink', 'intentflow_save_safelink_meta');

/**
 * Track impressions — atomic increment, no data loss
 */
function intentflow_track_safelink_impression() {
    if (!is_singular('safelink')) return;

    global $wpdb;
    $post_id = get_queried_object_id();
    if (!$post_id) return;

    // Atomic increment — no read-then-write race condition
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_safelink_impressions'",
        $post_id
    ));

    if ($existing) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = '_safelink_impressions'",
            $post_id
        ));
    } else {
        add_post_meta($post_id, '_safelink_impressions', 1, true);
    }

    delete_transient('intentflow_safelink_stats');
}
add_action('template_redirect', 'intentflow_track_safelink_impression');

/**
 * AJAX: Track click-throughs
 */
function intentflow_track_safelink_click() {
    // Verify nonce for security (prevents fake click inflation)
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'intentflow_click_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

    if (!$post_id || get_post_type($post_id) !== 'safelink') {
        wp_send_json_error();
    }

    // Atomic increment
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = '_safelink_clicks'",
        $post_id
    ));
    if (!$wpdb->rows_affected) {
        add_post_meta($post_id, '_safelink_clicks', 1, true);
    }
    delete_transient('intentflow_safelink_stats');

    wp_send_json_success();
}
add_action('wp_ajax_intentflow_track_click', 'intentflow_track_safelink_click');
add_action('wp_ajax_nopriv_intentflow_track_click', 'intentflow_track_safelink_click');

/**
 * AJAX: Track safelink step events (funnel analytics)
 */
function intentflow_track_safelink_step() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'intentflow_click_nonce')) {
        wp_send_json_error();
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    $step    = sanitize_text_field($_POST['step'] ?? '');

    if (!$post_id || !$step || get_post_type($post_id) !== 'safelink') {
        wp_send_json_error();
    }

    $valid_steps = array('timer_start', 'timer_complete', 'generate_click', 'download_ready', 'download_click');
    if (!in_array($step, $valid_steps, true)) {
        wp_send_json_error();
    }

    $meta_key = '_safelink_step_' . $step;
    global $wpdb;
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
        $post_id, $meta_key
    ));

    if ($existing) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
            $post_id, $meta_key
        ));
    } else {
        add_post_meta($post_id, $meta_key, 1, true);
    }

    wp_send_json_success();
}
add_action('wp_ajax_intentflow_track_step', 'intentflow_track_safelink_step');
add_action('wp_ajax_nopriv_intentflow_track_step', 'intentflow_track_safelink_step');

/**
 * Get safelink funnel data for analytics
 */
function intentflow_get_safelink_funnel($post_id) {
    $steps = array('timer_start', 'timer_complete', 'generate_click', 'download_ready', 'download_click');
    $funnel = array();

    foreach ($steps as $step) {
        $funnel[$step] = (int) get_post_meta($post_id, '_safelink_step_' . $step, true);
    }

    // Calculate rates
    $funnel['timer_completion_rate'] = $funnel['timer_start'] > 0
        ? round(($funnel['timer_complete'] / $funnel['timer_start']) * 100, 1) : 0;
    $funnel['generate_rate'] = $funnel['timer_complete'] > 0
        ? round(($funnel['generate_click'] / $funnel['timer_complete']) * 100, 1) : 0;
    $funnel['download_rate'] = $funnel['download_ready'] > 0
        ? round(($funnel['download_click'] / $funnel['download_ready']) * 100, 1) : 0;
    $funnel['overall_conversion'] = $funnel['timer_start'] > 0
        ? round(($funnel['download_click'] / $funnel['timer_start']) * 100, 1) : 0;

    return $funnel;
}

/**
 * Auto-convert outbound links in post content to safelinks
 */
function intentflow_auto_convert_links($content) {
    if (!is_single() || !is_main_query()) return $content;
    if (!get_theme_mod('intentflow_auto_safelink', false)) return $content;

    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);

    // Use DOMDocument for safe HTML parsing (handles all attribute formats)
    if (!class_exists('DOMDocument')) {
        return $content; // Fallback: skip if libxml not available
    }

    $doc = new DOMDocument();
    @$doc->loadHTML(
        '<?xml encoding="UTF-8"><div>' . $content . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    $links = $doc->getElementsByTagName('a');
    $modified = false;

    for ($i = $links->length - 1; $i >= 0; $i--) {
        $link = $links->item($i);
        $href = $link->getAttribute('href');

        if (empty($href)) continue;

        // Skip internal, anchor, already-converted, non-http
        $link_host = wp_parse_url($href, PHP_URL_HOST);
        if (empty($link_host) || $link_host === $site_host) continue;
        if (strpos($href, '/go/') !== false) continue;
        if (strpos($href, 'http') !== 0) continue;

        // Find or create safelink (cached)
        $cache_key = 'intentflow_sl_' . md5($href);
        $safelink_url = wp_cache_get($cache_key, 'intentflow');
        if (false === $safelink_url) {
            $safelink_url = intentflow_get_or_create_safelink($href);
            wp_cache_set($cache_key, $safelink_url, 'intentflow', 3600);
        }

        // Only rewrite if we got a safelink URL (not the original)
        if ($safelink_url !== $href) {
            $link->setAttribute('href', esc_url($safelink_url));
            $link->setAttribute('data-original', esc_attr($href));

            // Merge rel attribute (don't duplicate)
            $existing_rel = $link->getAttribute('rel');
            $rel_parts = $existing_rel ? array_filter(explode(' ', $existing_rel)) : array();
            foreach (array('nofollow', 'noopener') as $r) {
                if (!in_array($r, $rel_parts, true)) $rel_parts[] = $r;
            }
            $link->setAttribute('rel', implode(' ', $rel_parts));

            $modified = true;
        }
    }

    if (!$modified) return $content;

    // Extract inner content (skip the wrapper div)
    $inner = '';
    $wrapper = $doc->getElementsByTagName('div')->item(0);
    if ($wrapper) {
        foreach ($wrapper->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }
    }

    return $inner ?: $content;
}
add_filter('the_content', 'intentflow_auto_convert_links', 20);

/**
 * Find existing safelink for a URL or create one
 */
function intentflow_get_or_create_safelink($url) {
    // Look for existing safelink with this target URL
    $existing = get_posts(array(
        'post_type'   => 'safelink',
        'meta_key'    => '_safelink_target_url',
        'meta_value'  => $url,
        'numberposts' => 1,
        'fields'      => 'ids',
    ));

    if (!empty($existing)) {
        return get_permalink($existing[0]);
    }

    // Only create new safelinks if current user is admin (never on anonymous frontend)
    if (!current_user_can('manage_options')) {
        return $url; // Return original URL — don't create posts from frontend
    }

    $title = wp_parse_url($url, PHP_URL_HOST);
    $title = $title ? 'Download from ' . $title : 'Download Link';

    $post_id = wp_insert_post(array(
        'post_type'   => 'safelink',
        'post_title'  => $title,
        'post_status' => 'publish',
    ));

    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_safelink_target_url', esc_url_raw($url));
        return get_permalink($post_id);
    }

    return $url; // Fallback to original URL
}

/**
 * Add safelink columns to admin list
 */
function intentflow_safelink_admin_columns($columns) {
    $new = array();
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['sf_target']      = __('Target URL', 'intentflow');
            $new['sf_steps']       = __('Steps', 'intentflow');
            $new['sf_impressions'] = __('Views', 'intentflow');
            $new['sf_clicks']      = __('Clicks', 'intentflow');
            $new['sf_ctr']         = __('CTR', 'intentflow');
        }
    }
    return $new;
}
add_filter('manage_safelink_posts_columns', 'intentflow_safelink_admin_columns');

function intentflow_safelink_admin_column_data($column, $post_id) {
    switch ($column) {
        case 'sf_target':
            $url = get_post_meta($post_id, '_safelink_target_url', true);
            if ($url) {
                $host = wp_parse_url($url, PHP_URL_HOST);
                printf('<a href="%s" target="_blank" title="%s">%s</a>',
                    esc_url($url), esc_attr($url), esc_html($host));
            }
            break;
        case 'sf_steps':
            $steps = get_post_meta($post_id, '_safelink_steps', true) ?: 'standard';
            $labels = array('fast' => '2-step', 'standard' => '3-step', 'max' => '4-step');
            echo esc_html($labels[$steps] ?? $steps);
            $second = get_post_meta($post_id, '_safelink_second_page', true);
            if ($second) echo ' + 2nd page';
            break;
        case 'sf_impressions':
            echo number_format((int) get_post_meta($post_id, '_safelink_impressions', true));
            break;
        case 'sf_clicks':
            echo number_format((int) get_post_meta($post_id, '_safelink_clicks', true));
            break;
        case 'sf_ctr':
            $imp = (int) get_post_meta($post_id, '_safelink_impressions', true);
            $clk = (int) get_post_meta($post_id, '_safelink_clicks', true);
            echo $imp > 0 ? round(($clk / $imp) * 100, 1) . '%' : '—';
            break;
    }
}
add_action('manage_safelink_posts_custom_column', 'intentflow_safelink_admin_column_data', 10, 2);
