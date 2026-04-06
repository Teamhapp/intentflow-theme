<?php
/**
 * IntentFlow Admin Dashboard
 *
 * Centralized settings page with tabbed UI:
 * - Overview (health check, quick stats)
 * - Ads (all ad placements in one view)
 * - AI (Gemini config, usage stats, generation history)
 * - Safelinks (global settings, analytics overview)
 * - Advanced (export/import, cache, performance)
 */

// ============================================================
// ADMIN MENU
// ============================================================

function intentflow_admin_menu_dashboard() {
    add_theme_page(
        __('IntentFlow Settings', 'intentflow'),
        __('IntentFlow', 'intentflow'),
        'manage_options',
        'intentflow-settings',
        'intentflow_settings_page'
    );
}
add_action('admin_menu', 'intentflow_admin_menu_dashboard');

// ============================================================
// ADMIN STYLES (inline for self-contained theme)
// ============================================================

function intentflow_admin_styles($hook) {
    if ($hook !== 'appearance_page_intentflow-settings') return;

    wp_enqueue_style('intentflow-admin', INTENTFLOW_URI . '/assets/css/admin.css', array(), INTENTFLOW_VERSION);
    wp_enqueue_script('intentflow-admin-dashboard', INTENTFLOW_URI . '/assets/js/admin-dashboard.js', array(), INTENTFLOW_VERSION, true);

    // Legacy inline styles kept as fallback comment — actual styles in admin.css
    if (false) { wp_add_inline_style('intentflow-admin', '
        .if-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .if-header h1 { font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .if-header .if-version { background: #eff6ff; color: #2563EB; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .if-tabs { display: flex; gap: 0; border-bottom: 2px solid #e5e7eb; margin-bottom: 24px; }
        .if-tab { padding: 10px 20px; font-size: 14px; font-weight: 500; color: #6B7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .2s; background: none; border-top: none; border-left: none; border-right: none; }
        .if-tab:hover { color: #111827; }
        .if-tab.active { color: #2563EB; border-bottom-color: #2563EB; font-weight: 600; }
        .if-panel { display: none; }
        .if-panel.active { display: block; }
        .if-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .if-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; }
        .if-card-stat { text-align: center; }
        .if-card-stat .num { font-size: 32px; font-weight: 700; line-height: 1; }
        .if-card-stat .label { font-size: 12px; color: #6B7280; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; }
        .if-card-stat .num.blue { color: #2563EB; }
        .if-card-stat .num.green { color: #22C55E; }
        .if-card-stat .num.orange { color: #F59E0B; }
        .if-card-stat .num.red { color: #EF4444; }
        .if-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        .if-section h3 { font-size: 16px; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
        .if-field { margin-bottom: 16px; }
        .if-field label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; color: #111827; }
        .if-field small { display: block; font-size: 12px; color: #6B7280; margin-top: 2px; }
        .if-field input[type=text], .if-field input[type=url], .if-field input[type=number], .if-field textarea, .if-field select {
            width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .if-field textarea { min-height: 80px; font-family: monospace; font-size: 12px; }
        .if-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .if-toggle { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .if-toggle input[type=checkbox] { width: 18px; height: 18px; accent-color: #2563EB; }
        .if-toggle label { font-weight: 500; font-size: 14px; margin: 0; }
        .if-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .if-badge-ok { background: #dcfce7; color: #16a34a; }
        .if-badge-warn { background: #fef3c7; color: #d97706; }
        .if-badge-err { background: #fee2e2; color: #dc2626; }
        .if-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #d1d5db; background: #fff; color: #111827; transition: all .2s; }
        .if-btn:hover { background: #f9fafb; border-color: #9ca3af; }
        .if-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
        .if-btn-primary:hover { background: #1d4ed8; }
        .if-btn-green { background: #22C55E; color: #fff; border-color: #22C55E; }
        .if-ad-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .if-log { max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; background: #0f172a; color: #94a3b8; border-radius: 8px; padding: 12px; }
        .if-log-item { padding: 4px 0; border-bottom: 1px solid rgba(255,255,255,.05); }
        .if-log-item .time { color: #475569; margin-right: 8px; }
        .if-log-item.success { color: #22C55E; }
        .if-log-item.error { color: #EF4444; }
        .if-log-item.info { color: #60A5FA; }
        .if-queue-item { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .if-queue-status { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 4px; }
        .if-queue-pending { background: #fef3c7; color: #D97706; }
        .if-queue-done { background: #dcfce7; color: #16a34a; }
        .if-queue-failed { background: #fee2e2; color: #dc2626; }
        @media (max-width: 768px) { .if-ad-grid, .if-field-row { grid-template-columns: 1fr; } .if-cards { grid-template-columns: 1fr 1fr; } }
        .if-status-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .if-status-row:last-child { border-bottom: none; }
        .if-export-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-top: 12px; }
        .if-export-box textarea { width: 100%; min-height: 120px; font-family: monospace; font-size: 11px; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; }
    '); }
}
add_action('admin_enqueue_scripts', 'intentflow_admin_styles');

// ============================================================
// SETTINGS PAGE
// ============================================================

function intentflow_render_onboarding() {
    ?>
    <div class="if-wrap">
        <div style="max-width:600px;margin:40px auto;text-align:center">
            <h1 style="font-size:28px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:10px">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <?php esc_html_e('Welcome to IntentFlow', 'intentflow'); ?>
            </h1>
            <p style="color:#6B7280;margin-bottom:32px"><?php esc_html_e('Let\'s get your site ready in 3 quick steps.', 'intentflow'); ?></p>

            <!-- Progress -->
            <div style="display:flex;justify-content:center;gap:8px;margin-bottom:32px">
                <span class="onb-dot active" id="onb-dot-1">1</span>
                <span class="onb-dot" id="onb-dot-2">2</span>
                <span class="onb-dot" id="onb-dot-3">3</span>
            </div>
            <style>
                .onb-dot{width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:#f3f4f6;color:#9CA3AF;transition:all .3s}
                .onb-dot.active{background:#2563EB;color:#fff}
                .onb-dot.done{background:#22C55E;color:#fff}
                .onb-step{display:none;text-align:left}.onb-step.active{display:block}
            </style>

            <form method="post">
            <?php wp_nonce_field('intentflow_settings_nonce'); ?>

            <!-- Step 1: AI -->
            <div class="onb-step active" id="onb-step-1">
                <div class="if-section">
                    <h3 style="text-align:center">&#129302; <?php esc_html_e('Step 1: Connect AI', 'intentflow'); ?></h3>
                    <p style="color:#6B7280;font-size:13px;margin-bottom:16px;text-align:center"><?php esc_html_e('Paste your Google Gemini API key to enable AI content generation.', 'intentflow'); ?></p>
                    <div class="if-field">
                        <label><?php esc_html_e('Gemini API Key', 'intentflow'); ?></label>
                        <input type="text" name="onboard_api_key" placeholder="AIza..." value="<?php echo esc_attr(get_theme_mod('intentflow_gemini_api_key', '')); ?>">
                        <small><?php esc_html_e('Get your free key at ai.google.dev — or skip this step for now.', 'intentflow'); ?></small>
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
                        <button type="button" class="if-btn" onclick="onbSkip(2)"><?php esc_html_e('Skip', 'intentflow'); ?></button>
                        <button type="button" class="if-btn if-btn-primary" onclick="onbNext(2)"><?php esc_html_e('Next', 'intentflow'); ?> &rarr;</button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Ads -->
            <div class="onb-step" id="onb-step-2">
                <div class="if-section">
                    <h3 style="text-align:center">&#128176; <?php esc_html_e('Step 2: Set Up Ads', 'intentflow'); ?></h3>
                    <p style="color:#6B7280;font-size:13px;margin-bottom:16px;text-align:center"><?php esc_html_e('Enter your AdSense Publisher ID to start earning from ads.', 'intentflow'); ?></p>
                    <div class="if-field">
                        <label><?php esc_html_e('AdSense Publisher ID', 'intentflow'); ?></label>
                        <input type="text" name="onboard_publisher_id" placeholder="ca-pub-1234567890123456" value="<?php echo esc_attr(get_theme_mod('intentflow_adsense_publisher_id', '')); ?>">
                    </div>
                    <div class="if-toggle">
                        <input type="checkbox" name="onboard_auto_ads" value="1">
                        <label><?php esc_html_e('Enable Google Auto Ads', 'intentflow'); ?></label>
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
                        <button type="button" class="if-btn" onclick="onbNext(1)">&larr; <?php esc_html_e('Back', 'intentflow'); ?></button>
                        <button type="button" class="if-btn" onclick="onbSkip(3)"><?php esc_html_e('Skip', 'intentflow'); ?></button>
                        <button type="button" class="if-btn if-btn-primary" onclick="onbNext(3)"><?php esc_html_e('Next', 'intentflow'); ?> &rarr;</button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Create Content -->
            <div class="onb-step" id="onb-step-3">
                <div class="if-section" style="text-align:center">
                    <h3>&#127881; <?php esc_html_e('Step 3: You\'re Ready!', 'intentflow'); ?></h3>
                    <p style="color:#6B7280;font-size:13px;margin-bottom:20px"><?php esc_html_e('Your theme is configured. Start creating content or explore settings.', 'intentflow'); ?></p>
                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:20px">
                        <a href="<?php echo esc_url(admin_url('post-new.php')); ?>" class="if-btn if-btn-primary"><?php esc_html_e('Create First Post', 'intentflow'); ?></a>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=safelink')); ?>" class="if-btn if-btn-green"><?php esc_html_e('Create Safelink', 'intentflow'); ?></a>
                        <a href="<?php echo esc_url(admin_url('tools.php?page=intentflow-ai-generator')); ?>" class="if-btn"><?php esc_html_e('AI Generator', 'intentflow'); ?></a>
                    </div>
                    <button type="submit" name="intentflow_complete_onboarding" class="if-btn if-btn-primary" style="width:100%;margin-top:8px">
                        <?php esc_html_e('Complete Setup & Open Dashboard', 'intentflow'); ?>
                    </button>
                    <button type="button" class="if-btn" onclick="onbNext(2)" style="margin-top:8px">&larr; <?php esc_html_e('Back', 'intentflow'); ?></button>
                </div>
            </div>

            </form>
        </div>
    </div>
    <script>
    function onbNext(step) {
        document.querySelectorAll('.onb-step').forEach(function(s){s.classList.remove('active')});
        document.getElementById('onb-step-'+step).classList.add('active');
        for(var i=1;i<=3;i++){
            var d=document.getElementById('onb-dot-'+i);
            d.classList.remove('active','done');
            if(i<step) d.classList.add('done');
            if(i===step) d.classList.add('active');
        }
    }
    function onbSkip(step) { onbNext(step); }
    </script>
    <?php
}

function intentflow_settings_page() {
    // Handle form save
    if (isset($_POST['intentflow_save_settings']) && check_admin_referer('intentflow_settings_nonce')) {
        intentflow_save_dashboard_settings();
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'intentflow') . '</p></div>';
    }

    // Handle queue add
    if (isset($_POST['intentflow_add_queue']) && check_admin_referer('intentflow_settings_nonce')) {
        $keywords     = sanitize_textarea_field($_POST['queue_keywords'] ?? '');
        $content_type = sanitize_text_field($_POST['queue_content_type'] ?? 'guide');
        $category     = sanitize_text_field($_POST['queue_category'] ?? '');
        if (!empty($keywords)) {
            $count = intentflow_add_to_queue($keywords, $content_type, $category);
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__('%d keywords added to queue.', 'intentflow'), $count) . '</p></div>';
        }
    }

    // Handle run now
    if (isset($_POST['intentflow_run_now']) && check_admin_referer('intentflow_settings_nonce')) {
        intentflow_daily_auto_publish();
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Auto-publish triggered. Check the log below.', 'intentflow') . '</p></div>';
    }

    // Handle export
    if (isset($_POST['intentflow_export'])) {
        // Export handled via JS
    }

    // Handle import
    if (isset($_POST['intentflow_import']) && check_admin_referer('intentflow_settings_nonce')) {
        $json = sanitize_textarea_field($_POST['intentflow_import_data'] ?? '');
        if (!empty($json)) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    set_theme_mod($key, $value);
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings imported successfully.', 'intentflow') . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid JSON.', 'intentflow') . '</p></div>';
            }
        }
    }

    // Handle onboarding completion
    if (isset($_POST['intentflow_complete_onboarding']) && check_admin_referer('intentflow_settings_nonce')) {
        update_option('intentflow_onboarding_complete', true);
        // Save API key and publisher ID from onboarding
        if (!empty($_POST['onboard_api_key'])) {
            set_theme_mod('intentflow_gemini_api_key', sanitize_text_field($_POST['onboard_api_key']));
        }
        if (!empty($_POST['onboard_publisher_id'])) {
            set_theme_mod('intentflow_adsense_publisher_id', sanitize_text_field($_POST['onboard_publisher_id']));
        }
        if (!empty($_POST['onboard_auto_ads'])) {
            set_theme_mod('intentflow_adsense_auto_ads', true);
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Setup complete! You\'re ready to go.', 'intentflow') . '</p></div>';
    }

    // Show onboarding wizard on first visit
    if (!get_option('intentflow_onboarding_complete')) {
        intentflow_render_onboarding();
        return; // Don't show full dashboard yet
    }

    // Gather stats
    $ai_usage     = (int) get_option('intentflow_ai_usage_count', 0);
    $api_key      = get_theme_mod('intentflow_gemini_api_key', '');
    $adsense_id   = get_theme_mod('intentflow_adsense_publisher_id', '');
    $model        = get_theme_mod('intentflow_ai_model', 'gemini-2.5-flash');
    $safelink_count = wp_count_posts('safelink');
    $total_safelinks = ($safelink_count->publish ?? 0) + ($safelink_count->draft ?? 0);
    $total_posts  = wp_count_posts()->publish;

    // Safelink analytics — single SQL query instead of N+1
    $safelink_stats = get_transient('intentflow_safelink_stats');
    if (false === $safelink_stats) {
        global $wpdb;
        $safelink_stats = $wpdb->get_row("
            SELECT
                COALESCE(SUM(CASE WHEN pm.meta_key = '_safelink_impressions' THEN CAST(pm.meta_value AS UNSIGNED) END), 0) AS total_impressions,
                COALESCE(SUM(CASE WHEN pm.meta_key = '_safelink_clicks' THEN CAST(pm.meta_value AS UNSIGNED) END), 0) AS total_clicks
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE p.post_type = 'safelink'
              AND pm.meta_key IN ('_safelink_impressions', '_safelink_clicks')
        ");
        if (!$safelink_stats) $safelink_stats = (object) array('total_impressions' => 0, 'total_clicks' => 0);
        set_transient('intentflow_safelink_stats', $safelink_stats, 300); // 5 min cache
    }
    $total_impressions = (int) $safelink_stats->total_impressions;
    $total_clicks      = (int) $safelink_stats->total_clicks;
    $overall_ctr = $total_impressions > 0 ? round(($total_clicks / $total_impressions) * 100, 1) : 0;

    ?>
    <div class="if-wrap">

        <!-- Header -->
        <div class="if-header">
            <h1>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                IntentFlow
                <span class="if-version">v<?php echo esc_html(INTENTFLOW_VERSION); ?></span>
            </h1>
            <a href="<?php echo esc_url(admin_url('customize.php')); ?>" class="if-btn">
                <?php esc_html_e('Open Customizer', 'intentflow'); ?>
            </a>
        </div>

        <form method="post" id="intentflow-settings-form">
        <?php wp_nonce_field('intentflow_settings_nonce'); ?>
        <input type="hidden" name="intentflow_active_tab" id="intentflow_active_tab" value="<?php echo esc_attr($_POST['intentflow_active_tab'] ?? 'overview'); ?>">

        <!-- Tabs (inside form, explicit type=button to prevent form submit) -->
        <div class="if-tabs">
            <button type="button" class="if-tab active" data-tab="overview"><?php esc_html_e('Overview', 'intentflow'); ?></button>
            <button type="button" class="if-tab" data-tab="ads"><?php esc_html_e('Ads', 'intentflow'); ?></button>
            <button type="button" class="if-tab" data-tab="ai"><?php esc_html_e('AI', 'intentflow'); ?></button>
            <button type="button" class="if-tab" data-tab="safelinks"><?php esc_html_e('Safelinks', 'intentflow'); ?></button>
            <button type="button" class="if-tab" data-tab="automation"><?php esc_html_e('Automation', 'intentflow'); ?></button>
            <button type="button" class="if-tab" data-tab="advanced"><?php esc_html_e('Advanced', 'intentflow'); ?></button>
        </div>

        <!-- ==================== OVERVIEW TAB ==================== -->
        <div class="if-panel active" id="tab-overview">

            <!-- Stats Cards -->
            <div class="if-cards">
                <div class="if-card if-card-stat">
                    <div class="num blue"><?php echo number_format($total_posts); ?></div>
                    <div class="label"><?php esc_html_e('Published Posts', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num green"><?php echo number_format($total_safelinks); ?></div>
                    <div class="label"><?php esc_html_e('Safelinks', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num orange"><?php echo number_format($total_impressions); ?></div>
                    <div class="label"><?php esc_html_e('Safelink Views', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num <?php echo $overall_ctr > 5 ? 'green' : 'red'; ?>"><?php echo $overall_ctr; ?>%</div>
                    <div class="label"><?php esc_html_e('Overall CTR', 'intentflow'); ?></div>
                </div>
            </div>

            <!-- Health Check -->
            <div class="if-section">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <?php esc_html_e('Health Check', 'intentflow'); ?>
                </h3>

                <div class="if-status-row">
                    <span><?php esc_html_e('Gemini AI API', 'intentflow'); ?></span>
                    <?php if (!empty($api_key)) : ?>
                        <span class="if-badge if-badge-ok"><?php esc_html_e('Connected', 'intentflow'); ?></span>
                    <?php else : ?>
                        <span class="if-badge if-badge-err"><?php esc_html_e('Not Configured', 'intentflow'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="if-status-row">
                    <span><?php esc_html_e('AdSense', 'intentflow'); ?></span>
                    <?php if (!empty($adsense_id)) : ?>
                        <span class="if-badge if-badge-ok"><?php echo esc_html($adsense_id); ?></span>
                    <?php else : ?>
                        <span class="if-badge if-badge-warn"><?php esc_html_e('Not Set', 'intentflow'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="if-status-row">
                    <span><?php esc_html_e('Primary Menu', 'intentflow'); ?></span>
                    <?php if (has_nav_menu('primary')) : ?>
                        <span class="if-badge if-badge-ok"><?php esc_html_e('Assigned', 'intentflow'); ?></span>
                    <?php else : ?>
                        <span class="if-badge if-badge-warn"><?php esc_html_e('Not Assigned', 'intentflow'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="if-status-row">
                    <span><?php esc_html_e('Custom Logo', 'intentflow'); ?></span>
                    <?php if (has_custom_logo()) : ?>
                        <span class="if-badge if-badge-ok"><?php esc_html_e('Set', 'intentflow'); ?></span>
                    <?php else : ?>
                        <span class="if-badge if-badge-warn"><?php esc_html_e('Using Text', 'intentflow'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="if-status-row">
                    <span><?php esc_html_e('AI Model', 'intentflow'); ?></span>
                    <span class="if-badge if-badge-ok"><?php echo esc_html($model); ?></span>
                </div>

                <div class="if-status-row">
                    <span><?php esc_html_e('AI Generations Used', 'intentflow'); ?></span>
                    <span><strong><?php echo number_format($ai_usage); ?></strong></span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="if-section">
                <h3><?php esc_html_e('Quick Actions', 'intentflow'); ?></h3>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <a href="<?php echo esc_url(admin_url('post-new.php')); ?>" class="if-btn if-btn-primary"><?php esc_html_e('New Post', 'intentflow'); ?></a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=safelink')); ?>" class="if-btn if-btn-green"><?php esc_html_e('New Safelink', 'intentflow'); ?></a>
                    <a href="<?php echo esc_url(admin_url('tools.php?page=intentflow-ai-generator')); ?>" class="if-btn"><?php esc_html_e('AI Post Generator', 'intentflow'); ?></a>
                    <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="if-btn"><?php esc_html_e('Menus', 'intentflow'); ?></a>
                    <a href="<?php echo esc_url(admin_url('widgets.php')); ?>" class="if-btn"><?php esc_html_e('Widgets', 'intentflow'); ?></a>
                </div>
            </div>
        </div>

        <!-- ==================== ADS TAB ==================== -->
        <div class="if-panel" id="tab-ads">

            <div class="if-section">
                <h3><?php esc_html_e('AdSense Integration', 'intentflow'); ?></h3>
                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('Publisher ID', 'intentflow'); ?></label>
                        <input type="text" name="intentflow_adsense_publisher_id"
                               value="<?php echo esc_attr(get_theme_mod('intentflow_adsense_publisher_id', '')); ?>"
                               placeholder="ca-pub-1234567890123456">
                    </div>
                    <div class="if-field">
                        <div class="if-toggle">
                            <input type="checkbox" name="intentflow_adsense_auto_ads" value="1"
                                   <?php checked(get_theme_mod('intentflow_adsense_auto_ads', false)); ?>>
                            <label><?php esc_html_e('Enable Auto Ads', 'intentflow'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('ads.txt Content', 'intentflow'); ?></label>
                    <textarea name="intentflow_ads_txt_content" placeholder="google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0"><?php echo esc_textarea(get_theme_mod('intentflow_ads_txt_content', '')); ?></textarea>
                    <small><?php esc_html_e('Served at yourdomain.com/ads.txt', 'intentflow'); ?></small>
                </div>
            </div>

            <div class="if-section">
                <h3><?php esc_html_e('Ad Placements', 'intentflow'); ?></h3>
                <div class="if-ad-grid">
                    <?php
                    $positions = array(
                        'header_banner'  => array('Header Banner', '728x90, shown on desktop'),
                        'article_top'    => array('Article Top', 'Above article content'),
                        'article_mid'    => array('Article Mid', 'Inserted after paragraph'),
                        'article_bottom' => array('Article Bottom', 'Below article content'),
                        'sidebar'        => array('Sidebar', '300x250, desktop only'),
                        'mobile_bottom'  => array('Mobile Bottom', 'Sticky bar, mobile only'),
                        'safelink'       => array('Safelink Page', 'On safelink pages'),
                    );
                    foreach ($positions as $key => $info) :
                    ?>
                        <div class="if-card">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                <strong style="font-size:13px"><?php echo esc_html($info[0]); ?></strong>
                                <div class="if-toggle" style="margin:0">
                                    <input type="checkbox" name="contentflow_ad_<?php echo esc_attr($key); ?>_enabled" value="1"
                                           <?php checked(get_theme_mod("contentflow_ad_{$key}_enabled", true)); ?>>
                                </div>
                            </div>
                            <small style="color:#6B7280;display:block;margin-bottom:6px"><?php echo esc_html($info[1]); ?></small>
                            <textarea name="contentflow_ad_<?php echo esc_attr($key); ?>_code" rows="3"
                                      style="font-size:11px;min-height:60px"
                                      placeholder="Paste ad code here..."><?php echo esc_textarea(get_theme_mod("contentflow_ad_{$key}_code", '')); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="if-field" style="margin-top:16px">
                    <label><?php esc_html_e('Mid-Article Ad: Insert After Paragraph #', 'intentflow'); ?></label>
                    <input type="number" name="intentflow_ad_mid_paragraph" min="2" max="10" style="width:80px"
                           value="<?php echo esc_attr(get_theme_mod('intentflow_ad_mid_paragraph', 3)); ?>">
                    <small><?php esc_html_e('Only shows if article has 300+ words', 'intentflow'); ?></small>
                </div>
            </div>

            <button type="submit" name="intentflow_save_settings" class="if-btn if-btn-primary"><?php esc_html_e('Save Ad Settings', 'intentflow'); ?></button>
        </div>

        <!-- ==================== AI TAB ==================== -->
        <div class="if-panel" id="tab-ai">

            <div class="if-cards">
                <div class="if-card if-card-stat">
                    <div class="num blue"><?php echo number_format($ai_usage); ?></div>
                    <div class="label"><?php esc_html_e('Total Generations', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num green" style="font-size:16px"><?php echo esc_html(ucfirst(get_theme_mod('intentflow_ai_provider', 'gemini')) . ': ' . $model); ?></div>
                    <div class="label"><?php esc_html_e('Active Provider', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num <?php echo !empty($api_key) ? 'green' : 'red'; ?>">
                        <?php echo !empty($api_key) ? '&#10003;' : '&#10007;'; ?>
                    </div>
                    <div class="label"><?php esc_html_e('API Status', 'intentflow'); ?></div>
                </div>
            </div>

            <div class="if-section">
                <h3><?php esc_html_e('AI Provider', 'intentflow'); ?></h3>

                <?php $provider = get_theme_mod('intentflow_ai_provider', 'gemini'); ?>

                <div class="if-field">
                    <label><?php esc_html_e('AI Provider', 'intentflow'); ?></label>
                    <select name="intentflow_ai_provider" id="if-ai-provider">
                        <option value="gemini" <?php selected($provider, 'gemini'); ?>>Google Gemini</option>
                        <option value="chatgpt" <?php selected($provider, 'chatgpt'); ?>>OpenAI ChatGPT</option>
                        <option value="grok" <?php selected($provider, 'grok'); ?>>xAI Grok</option>
                    </select>
                </div>

                <div class="if-field">
                    <label><?php esc_html_e('API Key', 'intentflow'); ?></label>
                    <input type="text" name="intentflow_gemini_api_key"
                           value="<?php echo esc_attr(get_theme_mod('intentflow_gemini_api_key', '')); ?>"
                           placeholder="<?php echo $provider === 'chatgpt' ? 'sk-...' : ($provider === 'grok' ? 'xai-...' : 'AIza...'); ?>">
                    <small>
                        <?php if ($provider === 'chatgpt') : ?>
                            <?php esc_html_e('Get from platform.openai.com/api-keys', 'intentflow'); ?>
                        <?php elseif ($provider === 'grok') : ?>
                            <?php esc_html_e('Get from console.x.ai', 'intentflow'); ?>
                        <?php else : ?>
                            <?php esc_html_e('Get from ai.google.dev', 'intentflow'); ?>
                        <?php endif; ?>
                    </small>
                </div>

                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('Model', 'intentflow'); ?></label>
                        <select name="intentflow_ai_model">
                            <?php $cur_model = get_theme_mod('intentflow_ai_model', 'gemini-2.5-flash'); ?>
                            <?php if ($provider === 'chatgpt') : ?>
                                <option value="gpt-4o" <?php selected($cur_model, 'gpt-4o'); ?>>GPT-4o (recommended)</option>
                                <option value="gpt-4o-mini" <?php selected($cur_model, 'gpt-4o-mini'); ?>>GPT-4o Mini (fast, cheap)</option>
                                <option value="gpt-4.1" <?php selected($cur_model, 'gpt-4.1'); ?>>GPT-4.1 (latest stable)</option>
                            <?php elseif ($provider === 'grok') : ?>
                                <option value="grok-3" <?php selected($cur_model, 'grok-3'); ?>>Grok 3 (recommended)</option>
                                <option value="grok-3-mini" <?php selected($cur_model, 'grok-3-mini'); ?>>Grok 3 Mini (fast)</option>
                            <?php else : ?>
                                <option value="gemini-2.5-flash" <?php selected($cur_model, 'gemini-2.5-flash'); ?>>Gemini 2.5 Flash (fast, recommended)</option>
                                <option value="gemini-2.5-pro" <?php selected($cur_model, 'gemini-2.5-pro'); ?>>Gemini 2.5 Pro (advanced)</option>
                                <option value="gemini-3.1-pro-preview" <?php selected($cur_model, 'gemini-3.1-pro-preview'); ?>>Gemini 3.1 Pro Preview (latest)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="if-field">
                        <label><?php esc_html_e('Language', 'intentflow'); ?></label>
                        <input type="text" name="intentflow_ai_language"
                               value="<?php echo esc_attr(get_theme_mod('intentflow_ai_language', 'English')); ?>">
                    </div>
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('Writing Tone', 'intentflow'); ?></label>
                    <select name="intentflow_ai_tone">
                        <option value="professional" <?php selected(get_theme_mod('intentflow_ai_tone', 'professional'), 'professional'); ?>><?php esc_html_e('Professional', 'intentflow'); ?></option>
                        <option value="casual" <?php selected(get_theme_mod('intentflow_ai_tone'), 'casual'); ?>><?php esc_html_e('Casual', 'intentflow'); ?></option>
                        <option value="technical" <?php selected(get_theme_mod('intentflow_ai_tone'), 'technical'); ?>><?php esc_html_e('Technical', 'intentflow'); ?></option>
                    </select>
                </div>
                <div class="if-toggle">
                    <input type="checkbox" name="intentflow_ai_auto_seo" value="1"
                           <?php checked(get_theme_mod('intentflow_ai_auto_seo', false)); ?>>
                    <label><?php esc_html_e('Auto-generate SEO on publish', 'intentflow'); ?></label>
                </div>
                <div class="if-toggle">
                    <input type="checkbox" name="intentflow_ai_auto_tags" value="1"
                           <?php checked(get_theme_mod('intentflow_ai_auto_tags', false)); ?>>
                    <label><?php esc_html_e('Auto-suggest tags on publish', 'intentflow'); ?></label>
                </div>
            </div>

            <div class="if-section">
                <h3><?php esc_html_e('REST API Endpoints (for n8n)', 'intentflow'); ?></h3>
                <div class="if-status-row"><code>POST <?php echo esc_html(rest_url('intentflow/v1/ai/generate')); ?></code><span style="color:#6B7280;font-size:12px"><?php esc_html_e('Generate post', 'intentflow'); ?></span></div>
                <div class="if-status-row"><code>POST <?php echo esc_html(rest_url('intentflow/v1/ai/seo')); ?></code><span style="color:#6B7280;font-size:12px"><?php esc_html_e('Generate SEO', 'intentflow'); ?></span></div>
                <div class="if-status-row"><code>GET <?php echo esc_html(rest_url('intentflow/v1/ai/status')); ?></code><span style="color:#6B7280;font-size:12px"><?php esc_html_e('API status', 'intentflow'); ?></span></div>
                <small style="color:#6B7280"><?php esc_html_e('Authenticate with WordPress Application Passwords (Settings > Security).', 'intentflow'); ?></small>
            </div>

            <button type="submit" name="intentflow_save_settings" class="if-btn if-btn-primary"><?php esc_html_e('Save AI Settings', 'intentflow'); ?></button>
        </div>

        <!-- ==================== SAFELINKS TAB ==================== -->
        <div class="if-panel" id="tab-safelinks">

            <div class="if-cards">
                <div class="if-card if-card-stat">
                    <div class="num blue"><?php echo number_format($total_safelinks); ?></div>
                    <div class="label"><?php esc_html_e('Total Safelinks', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num orange"><?php echo number_format($total_impressions); ?></div>
                    <div class="label"><?php esc_html_e('Total Views', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num green"><?php echo number_format($total_clicks); ?></div>
                    <div class="label"><?php esc_html_e('Total Clicks', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num <?php echo $overall_ctr > 5 ? 'green' : 'red'; ?>"><?php echo $overall_ctr; ?>%</div>
                    <div class="label"><?php esc_html_e('CTR', 'intentflow'); ?></div>
                </div>
            </div>

            <div class="if-section">
                <h3><?php esc_html_e('Global Safelink Settings', 'intentflow'); ?></h3>
                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('Default Timer (seconds)', 'intentflow'); ?></label>
                        <input type="number" name="contentflow_safelink_timer" min="3" max="60"
                               value="<?php echo esc_attr(get_theme_mod('contentflow_safelink_timer', 10)); ?>">
                    </div>
                    <div class="if-field">
                        <label><?php esc_html_e('Wait Duration (seconds)', 'intentflow'); ?></label>
                        <input type="number" name="intentflow_safelink_wait_duration" min="2" max="30"
                               value="<?php echo esc_attr(get_theme_mod('intentflow_safelink_wait_duration', 5)); ?>">
                    </div>
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('Wait Message', 'intentflow'); ?></label>
                    <input type="text" name="contentflow_safelink_text"
                           value="<?php echo esc_attr(get_theme_mod('contentflow_safelink_text', 'Please wait while we prepare your link...')); ?>">
                </div>
                <div class="if-toggle">
                    <input type="checkbox" name="intentflow_auto_safelink" value="1"
                           <?php checked(get_theme_mod('intentflow_auto_safelink', false)); ?>>
                    <label><?php esc_html_e('Auto-convert all outbound links to safelinks', 'intentflow'); ?></label>
                </div>
            </div>

            <!-- Top Performing Safelinks -->
            <?php
            $safelinks = get_posts(array('post_type' => 'safelink', 'posts_per_page' => 10, 'fields' => 'ids'));
            if (!empty($safelinks)) :
            ?>
            <div class="if-section">
                <h3><?php esc_html_e('Top Safelinks', 'intentflow'); ?></h3>
                <table class="widefat striped">
                    <thead><tr>
                        <th><?php esc_html_e('Title', 'intentflow'); ?></th>
                        <th style="width:80px"><?php esc_html_e('Views', 'intentflow'); ?></th>
                        <th style="width:80px"><?php esc_html_e('Clicks', 'intentflow'); ?></th>
                        <th style="width:60px"><?php esc_html_e('CTR', 'intentflow'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php
                    // Sort by impressions
                    usort($safelinks, function ($a, $b) {
                        return (int) get_post_meta($b, '_safelink_impressions', true) - (int) get_post_meta($a, '_safelink_impressions', true);
                    });
                    foreach (array_slice($safelinks, 0, 10) as $sl_id) :
                        $imp = (int) get_post_meta($sl_id, '_safelink_impressions', true);
                        $clk = (int) get_post_meta($sl_id, '_safelink_clicks', true);
                        $ctr = $imp > 0 ? round(($clk / $imp) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><a href="<?php echo esc_url(get_edit_post_link($sl_id)); ?>"><?php echo esc_html(get_the_title($sl_id)); ?></a></td>
                            <td><?php echo number_format($imp); ?></td>
                            <td><?php echo number_format($clk); ?></td>
                            <td><?php echo $ctr; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <button type="submit" name="intentflow_save_settings" class="if-btn if-btn-primary"><?php esc_html_e('Save Safelink Settings', 'intentflow'); ?></button>
        </div>

        <!-- ==================== AUTOMATION TAB ==================== -->
        <div class="if-panel" id="tab-automation">

            <?php
            $queue       = intentflow_get_queue();
            $q_pending   = count(array_filter($queue, function ($i) { return $i['status'] === 'pending'; }));
            $q_done      = count(array_filter($queue, function ($i) { return $i['status'] === 'done'; }));
            $q_failed    = count(array_filter($queue, function ($i) { return $i['status'] === 'failed'; }));
            $auto_on     = get_theme_mod('intentflow_auto_enabled', false);
            $next_run    = wp_next_scheduled('intentflow_daily_publish');
            $log_entries = intentflow_get_log();
            $nonce       = wp_create_nonce('intentflow_ai_nonce');
            ?>

            <!-- Stats -->
            <div class="if-cards">
                <div class="if-card if-card-stat">
                    <div class="num <?php echo $auto_on ? 'green' : 'red'; ?>"><?php echo $auto_on ? '&#10003;' : '&#10007;'; ?></div>
                    <div class="label"><?php esc_html_e('Auto-Publish', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num orange"><?php echo $q_pending; ?></div>
                    <div class="label"><?php esc_html_e('Queue Pending', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num green"><?php echo $q_done; ?></div>
                    <div class="label"><?php esc_html_e('Published', 'intentflow'); ?></div>
                </div>
                <div class="if-card if-card-stat">
                    <div class="num blue"><?php echo $next_run ? human_time_diff(time(), $next_run) : '—'; ?></div>
                    <div class="label"><?php esc_html_e('Next Run', 'intentflow'); ?></div>
                </div>
            </div>

            <!-- Settings -->
            <div class="if-section">
                <h3><?php esc_html_e('Automation Settings', 'intentflow'); ?></h3>
                <div class="if-toggle">
                    <input type="checkbox" name="intentflow_auto_enabled" value="1" <?php checked(get_theme_mod('intentflow_auto_enabled', false)); ?>>
                    <label><?php esc_html_e('Enable Daily Auto-Publish', 'intentflow'); ?></label>
                </div>
                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('Posts Per Run', 'intentflow'); ?></label>
                        <select name="intentflow_auto_posts_per_run">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php selected(get_theme_mod('intentflow_auto_posts_per_run', 2), $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="if-field">
                        <label><?php esc_html_e('Run Interval', 'intentflow'); ?></label>
                        <select name="intentflow_auto_interval">
                            <option value="6" <?php selected(get_theme_mod('intentflow_auto_interval', 8), 6); ?>>Every 6 hours (4x/day)</option>
                            <option value="8" <?php selected(get_theme_mod('intentflow_auto_interval', 8), 8); ?>>Every 8 hours (3x/day)</option>
                            <option value="12" <?php selected(get_theme_mod('intentflow_auto_interval', 8), 12); ?>>Every 12 hours (2x/day)</option>
                            <option value="24" <?php selected(get_theme_mod('intentflow_auto_interval', 8), 24); ?>>Every 24 hours (1x/day)</option>
                        </select>
                    </div>
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('Auto-Published Post Status', 'intentflow'); ?></label>
                    <select name="intentflow_auto_post_status">
                        <option value="draft" <?php selected(get_theme_mod('intentflow_auto_post_status', 'draft'), 'draft'); ?>><?php esc_html_e('Draft (review before publishing)', 'intentflow'); ?></option>
                        <option value="publish" <?php selected(get_theme_mod('intentflow_auto_post_status'), 'publish'); ?>><?php esc_html_e('Publish immediately', 'intentflow'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Keyword Queue -->
            <div class="if-section">
                <h3><?php esc_html_e('Keyword Queue', 'intentflow'); ?></h3>
                <p style="color:#6B7280;font-size:13px;margin-bottom:12px"><?php esc_html_e('Add keywords for automatic post generation. One keyword per line.', 'intentflow'); ?></p>

                <div class="if-field">
                    <textarea name="queue_keywords" rows="4" placeholder="best ai tools 2025&#10;how to edit videos with capcut&#10;wordpress speed optimization guide"></textarea>
                </div>
                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('Content Type', 'intentflow'); ?></label>
                        <select name="queue_content_type">
                            <option value="guide">Guide</option>
                            <option value="tutorial">Tutorial</option>
                            <option value="listicle">Listicle</option>
                            <option value="comparison">Comparison</option>
                            <option value="review">Review</option>
                            <option value="fix">Fix</option>
                        </select>
                    </div>
                    <div class="if-field">
                        <label><?php esc_html_e('Category', 'intentflow'); ?></label>
                        <select name="queue_category">
                            <option value="">— None —</option>
                            <?php foreach (get_categories(array('hide_empty' => false)) as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="submit" name="intentflow_add_queue" class="if-btn if-btn-primary"><?php esc_html_e('Add to Queue', 'intentflow'); ?></button>
                    <button type="submit" name="intentflow_run_now" class="if-btn if-btn-green"><?php esc_html_e('Run Now', 'intentflow'); ?></button>
                </div>
            </div>

            <!-- Queue Table -->
            <?php
            $recent_queue = array_slice(array_reverse($queue), 0, 15);
            if (!empty($recent_queue)) :
            ?>
            <div class="if-section">
                <details>
                <summary style="font-weight:700;cursor:pointer"><?php esc_html_e('Recent Queue Items', 'intentflow'); ?></summary>
                <?php foreach ($recent_queue as $qi) : ?>
                    <div class="if-queue-item">
                        <div>
                            <strong><?php echo esc_html($qi['keyword']); ?></strong>
                            <span style="color:#6B7280;font-size:11px;margin-left:8px"><?php echo esc_html($qi['content_type']); ?></span>
                        </div>
                        <div>
                            <?php if ($qi['status'] === 'pending') : ?>
                                <span class="if-queue-status if-queue-pending">Pending</span>
                            <?php elseif ($qi['status'] === 'done') : ?>
                                <span class="if-queue-status if-queue-done">Done</span>
                                <?php if (!empty($qi['post_id'])) : ?>
                                    <a href="<?php echo esc_url(get_edit_post_link($qi['post_id'])); ?>" style="font-size:11px;margin-left:4px">Edit</a>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="if-queue-status if-queue-failed">Failed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </details>
            </div>
            <?php endif; ?>

            <!-- Automation Log -->
            <?php if (!empty($log_entries)) : ?>
            <div class="if-section">
                <details>
                <summary style="font-weight:700;cursor:pointer"><?php esc_html_e('Automation Log', 'intentflow'); ?></summary>
                <div class="if-log" style="margin-top:12px">
                    <?php foreach (array_slice($log_entries, 0, 30) as $entry) : ?>
                        <div class="if-log-item <?php echo esc_attr($entry['type']); ?>">
                            <span class="time">[<?php echo esc_html($entry['time']); ?>]</span>
                            <?php echo esc_html($entry['message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                </details>
            </div>
            <?php endif; ?>

            <!-- n8n API Reference -->
            <div class="if-section">
                <h3><?php esc_html_e('n8n / API Integration', 'intentflow'); ?></h3>
                <p style="color:#6B7280;font-size:13px;margin-bottom:10px"><?php esc_html_e('Use these endpoints with n8n HTTP Request nodes. Auth with Application Passwords.', 'intentflow'); ?></p>
                <div class="if-status-row"><code style="font-size:11px">POST <?php echo esc_html(rest_url('intentflow/v1/queue/add')); ?></code><span style="font-size:11px;color:#6B7280">Add keywords</span></div>
                <div class="if-status-row"><code style="font-size:11px">GET <?php echo esc_html(rest_url('intentflow/v1/queue')); ?></code><span style="font-size:11px;color:#6B7280">Queue status</span></div>
                <div class="if-status-row"><code style="font-size:11px">POST <?php echo esc_html(rest_url('intentflow/v1/queue/run')); ?></code><span style="font-size:11px;color:#6B7280">Trigger run</span></div>
                <div class="if-status-row"><code style="font-size:11px">GET <?php echo esc_html(rest_url('intentflow/v1/automation/log')); ?></code><span style="font-size:11px;color:#6B7280">View log</span></div>
                <div class="if-status-row"><code style="font-size:11px">POST <?php echo esc_html(rest_url('intentflow/v1/ai/generate')); ?></code><span style="font-size:11px;color:#6B7280">Generate single post</span></div>
            </div>

            <button type="submit" name="intentflow_save_settings" class="if-btn if-btn-primary"><?php esc_html_e('Save Automation Settings', 'intentflow'); ?></button>
        </div>

        <!-- ==================== ADVANCED TAB ==================== -->
        <div class="if-panel" id="tab-advanced">

            <!-- CTA + Hero Settings -->
            <div class="if-section">
                <h3><?php esc_html_e('Homepage & CTA', 'intentflow'); ?></h3>
                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('Hero Title', 'intentflow'); ?></label>
                        <input type="text" name="intentflow_hero_title"
                               value="<?php echo esc_attr(get_theme_mod('intentflow_hero_title', 'Find the Best Tools for Your Work')); ?>">
                    </div>
                    <div class="if-field">
                        <label><?php esc_html_e('Hero Subtitle', 'intentflow'); ?></label>
                        <input type="text" name="intentflow_hero_subtitle"
                               value="<?php echo esc_attr(get_theme_mod('intentflow_hero_subtitle', 'Tutorials, comparisons, and fixes for the tools you use every day.')); ?>">
                    </div>
                </div>
                <div class="if-field-row">
                    <div class="if-field">
                        <label><?php esc_html_e('CTA Title', 'intentflow'); ?></label>
                        <input type="text" name="contentflow_cta_title"
                               value="<?php echo esc_attr(get_theme_mod('contentflow_cta_title', 'Download Now')); ?>">
                    </div>
                    <div class="if-field">
                        <label><?php esc_html_e('CTA Button Text', 'intentflow'); ?></label>
                        <input type="text" name="contentflow_cta_button_text"
                               value="<?php echo esc_attr(get_theme_mod('contentflow_cta_button_text', 'Download Free')); ?>">
                    </div>
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('CTA Description', 'intentflow'); ?></label>
                    <input type="text" name="contentflow_cta_description"
                           value="<?php echo esc_attr(get_theme_mod('contentflow_cta_description', 'Get the latest version of this tool for free.')); ?>">
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('CTA Button URL', 'intentflow'); ?></label>
                    <input type="url" name="contentflow_cta_button_url"
                           value="<?php echo esc_attr(get_theme_mod('contentflow_cta_button_url', '#')); ?>">
                </div>
                <div class="if-field">
                    <label><?php esc_html_e('Footer Text', 'intentflow'); ?></label>
                    <input type="text" name="contentflow_footer_text"
                           value="<?php echo esc_attr(get_theme_mod('contentflow_footer_text', '')); ?>">
                </div>
            </div>

            <!-- Social Links -->
            <div class="if-section">
                <h3><?php esc_html_e('Social Links', 'intentflow'); ?></h3>
                <div class="if-field-row">
                    <?php foreach (array('facebook', 'twitter', 'instagram', 'youtube') as $net) : ?>
                        <div class="if-field">
                            <label><?php echo esc_html(ucfirst($net)); ?></label>
                            <input type="url" name="contentflow_social_<?php echo esc_attr($net); ?>"
                                   value="<?php echo esc_attr(get_theme_mod("contentflow_social_{$net}", '')); ?>"
                                   placeholder="https://">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Export / Import -->
            <div class="if-section">
                <h3><?php esc_html_e('Export / Import Settings', 'intentflow'); ?></h3>
                <p style="color:#6B7280;font-size:13px"><?php esc_html_e('Backup all theme settings as JSON, or restore from a backup.', 'intentflow'); ?></p>

                <div class="if-export-box">
                    <strong style="font-size:13px"><?php esc_html_e('Export', 'intentflow'); ?></strong>
                    <textarea id="if-export-data" readonly><?php echo esc_textarea(wp_json_encode(get_theme_mods(), JSON_PRETTY_PRINT)); ?></textarea>
                    <button type="button" class="if-btn" style="margin-top:8px" onclick="navigator.clipboard.writeText(document.getElementById('if-export-data').value);this.textContent='Copied!';">
                        <?php esc_html_e('Copy to Clipboard', 'intentflow'); ?>
                    </button>
                </div>

                <div class="if-export-box" style="margin-top:12px">
                    <strong style="font-size:13px"><?php esc_html_e('Import', 'intentflow'); ?></strong>
                    <textarea name="intentflow_import_data" placeholder="Paste exported JSON here..."></textarea>
                    <button type="submit" name="intentflow_import" class="if-btn" style="margin-top:8px">
                        <?php esc_html_e('Import Settings', 'intentflow'); ?>
                    </button>
                </div>
            </div>

            <button type="submit" name="intentflow_save_settings" class="if-btn if-btn-primary"><?php esc_html_e('Save All Settings', 'intentflow'); ?></button>
        </div>

        </form>
    </div>

    <?php
}

// ============================================================
// SAVE SETTINGS
// ============================================================

function intentflow_save_dashboard_settings() {
    $settings = array(
        // AdSense
        'intentflow_adsense_publisher_id' => 'sanitize_text_field',
        'intentflow_ads_txt_content'      => 'sanitize_textarea_field',
        'intentflow_ad_mid_paragraph'     => 'absint',
        // AI
        'intentflow_ai_provider'          => 'sanitize_text_field',
        'intentflow_gemini_api_key'       => 'sanitize_text_field',
        'intentflow_ai_model'             => 'sanitize_text_field',
        'intentflow_ai_language'          => 'sanitize_text_field',
        'intentflow_ai_tone'              => 'sanitize_text_field',
        // Safelinks
        'contentflow_safelink_timer'          => 'absint',
        'intentflow_safelink_wait_duration'   => 'absint',
        'contentflow_safelink_text'           => 'sanitize_text_field',
        // Automation
        'intentflow_auto_posts_per_run'   => 'absint',
        'intentflow_auto_interval'        => 'absint',
        'intentflow_auto_post_status'     => 'sanitize_text_field',
        // Hero + CTA
        'intentflow_hero_title'           => 'sanitize_text_field',
        'intentflow_hero_subtitle'        => 'sanitize_text_field',
        'contentflow_cta_title'           => 'sanitize_text_field',
        'contentflow_cta_description'     => 'sanitize_text_field',
        'contentflow_cta_button_text'     => 'sanitize_text_field',
        'contentflow_cta_button_url'      => 'esc_url_raw',
        'contentflow_footer_text'         => 'wp_kses_post',
    );

    foreach ($settings as $key => $sanitize) {
        if (isset($_POST[$key])) {
            set_theme_mod($key, call_user_func($sanitize, $_POST[$key]));
        }
    }

    // Checkboxes (unchecked = not in $_POST)
    $checkboxes = array(
        'intentflow_adsense_auto_ads',
        'intentflow_ai_auto_seo',
        'intentflow_ai_auto_tags',
        'intentflow_auto_safelink',
        'intentflow_auto_enabled',
    );

    foreach ($checkboxes as $key) {
        set_theme_mod($key, !empty($_POST[$key]));
    }

    // Ad placements
    $ad_positions = array('header_banner', 'article_top', 'article_mid', 'article_bottom', 'sidebar', 'mobile_bottom', 'safelink');
    foreach ($ad_positions as $pos) {
        $code_key    = "contentflow_ad_{$pos}_code";
        $enabled_key = "contentflow_ad_{$pos}_enabled";

        if (isset($_POST[$code_key]) && current_user_can('manage_options')) {
            set_theme_mod($code_key, $_POST[$code_key]);
        }
        set_theme_mod($enabled_key, !empty($_POST[$enabled_key]));
    }

    // Social links
    foreach (array('facebook', 'twitter', 'instagram', 'youtube') as $net) {
        $key = "contentflow_social_{$net}";
        if (isset($_POST[$key])) {
            set_theme_mod($key, esc_url_raw($_POST[$key]));
        }
    }
}
