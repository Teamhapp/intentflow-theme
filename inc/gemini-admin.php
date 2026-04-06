<?php
/**
 * IntentFlow Gemini AI — Admin UI
 *
 * - Post editor meta box with AI buttons
 * - Bulk post generator admin page
 */

// ============================================================
// POST EDITOR META BOX
// ============================================================

function intentflow_ai_meta_boxes() {
    $api_key = get_theme_mod('intentflow_gemini_api_key', '');
    if (empty($api_key)) return;
    if (!current_user_can('manage_options')) return; // Match AJAX handler capability

    add_meta_box(
        'intentflow_ai',
        __('IntentFlow AI', 'intentflow'),
        'intentflow_ai_meta_box_html',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'intentflow_ai_meta_boxes');

function intentflow_ai_meta_box_html($post) {
    $meta_desc = get_post_meta($post->ID, '_intentflow_meta_description', true);
    $seo_title = get_post_meta($post->ID, '_intentflow_seo_title', true);
    $ai_gen    = get_post_meta($post->ID, '_intentflow_ai_generated', true);
    $nonce     = wp_create_nonce('intentflow_ai_nonce');
    ?>
    <div id="intentflow-ai-box" data-post-id="<?php echo esc_attr($post->ID); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">

        <!-- Status -->
        <div style="margin-bottom:12px">
            <?php if ($ai_gen) : ?>
                <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:#dbeafe;color:#2563EB">
                    AI Generated
                </span>
            <?php endif; ?>
            <span id="ai-status" style="font-size:12px;color:#6B7280"></span>
        </div>

        <!-- Generate SEO -->
        <div style="margin-bottom:10px">
            <button type="button" class="button button-primary" id="ai-btn-seo" style="width:100%">
                <?php esc_html_e('Generate SEO', 'intentflow'); ?>
            </button>
        </div>

        <!-- SEO Results -->
        <div id="ai-seo-results" style="display:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:10px;font-size:12px">
            <div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:start">
                <div><strong><?php esc_html_e('SEO Title:', 'intentflow'); ?></strong><div id="ai-seo-title" style="color:#111827"></div></div>
                <button type="button" class="button button-small ai-copy-btn" data-target="ai-seo-title" title="Copy"><?php esc_html_e('Copy', 'intentflow'); ?></button>
            </div>
            <div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:start">
                <div><strong><?php esc_html_e('Meta Description:', 'intentflow'); ?></strong><div id="ai-meta-desc" style="color:#111827"></div></div>
                <button type="button" class="button button-small ai-copy-btn" data-target="ai-meta-desc" title="Copy"><?php esc_html_e('Copy', 'intentflow'); ?></button>
            </div>
            <div style="margin-bottom:8px;display:flex;justify-content:space-between;align-items:start">
                <div><strong><?php esc_html_e('Tags:', 'intentflow'); ?></strong><div id="ai-tags" style="color:#111827"></div></div>
                <button type="button" class="button button-small ai-copy-btn" data-target="ai-tags" title="Copy"><?php esc_html_e('Copy', 'intentflow'); ?></button>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:start">
                <div><strong><?php esc_html_e('Excerpt:', 'intentflow'); ?></strong><div id="ai-excerpt" style="color:#111827"></div></div>
                <button type="button" class="button button-small ai-copy-btn" data-target="ai-excerpt" title="Copy"><?php esc_html_e('Copy', 'intentflow'); ?></button>
            </div>
        </div>

        <!-- Existing SEO data -->
        <?php if ($meta_desc || $seo_title) : ?>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:8px;margin-bottom:10px;font-size:11px">
                <?php if ($seo_title) : ?>
                    <div><strong>SEO:</strong> <?php echo esc_html($seo_title); ?></div>
                <?php endif; ?>
                <?php if ($meta_desc) : ?>
                    <div style="margin-top:4px"><strong>Meta:</strong> <?php echo esc_html($meta_desc); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <hr style="border:0;border-top:1px solid #e5e7eb;margin:12px 0">

        <!-- Generate Thumbnail -->
        <div style="margin-bottom:10px">
            <button type="button" class="button" id="ai-btn-thumbnail" style="width:100%">
                <?php esc_html_e('Generate Featured Image', 'intentflow'); ?>
            </button>
            <div id="ai-thumbnail-result" style="display:none;margin-top:8px;text-align:center">
                <img id="ai-thumbnail-preview" src="" alt="" style="max-width:100%;border-radius:6px;border:1px solid #e5e7eb">
            </div>
        </div>

        <hr style="border:0;border-top:1px solid #e5e7eb;margin:12px 0">

        <!-- Enhance Content -->
        <div style="margin-bottom:8px">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">
                <?php esc_html_e('Enhance Content', 'intentflow'); ?>
            </label>
            <select id="ai-enhance-action" style="width:100%;margin-bottom:6px">
                <option value="improve"><?php esc_html_e('Improve & Polish — Fix grammar, add transitions', 'intentflow'); ?></option>
                <option value="simplify"><?php esc_html_e('Simplify — Make easier to read', 'intentflow'); ?></option>
                <option value="expand"><?php esc_html_e('Expand — Add more detail and examples', 'intentflow'); ?></option>
                <option value="summarize"><?php esc_html_e('Summarize — Condense to key points', 'intentflow'); ?></option>
            </select>
            <button type="button" class="button" id="ai-btn-enhance" style="width:100%">
                <?php esc_html_e('Enhance', 'intentflow'); ?>
            </button>
        </div>

        <hr style="border:0;border-top:1px solid #e5e7eb;margin:12px 0">

        <!-- Suggest Related -->
        <div>
            <button type="button" class="button" id="ai-btn-related" style="width:100%">
                <?php esc_html_e('Suggest Related Articles', 'intentflow'); ?>
            </button>
            <div id="ai-related-results" style="display:none;margin-top:8px;font-size:12px"></div>
        </div>

        <!-- Loading overlay -->
        <div id="ai-loading" style="display:none;text-align:center;padding:16px 0">
            <span class="spinner is-active" style="float:none;margin:0 auto"></span>
            <div style="margin-top:8px;font-size:12px;color:#6B7280" id="ai-loading-text">
                <?php esc_html_e('Generating with Gemini AI...', 'intentflow'); ?>
            </div>
            <button type="button" class="button" id="ai-cancel-btn" style="margin-top:8px">
                <?php esc_html_e('Cancel', 'intentflow'); ?>
            </button>
        </div>

    </div>
    <?php
}

// ============================================================
// BULK POST GENERATOR ADMIN PAGE
// ============================================================

function intentflow_ai_admin_menu() {
    $api_key = get_theme_mod('intentflow_gemini_api_key', '');
    if (empty($api_key)) return;

    add_submenu_page(
        'tools.php',
        __('AI Post Generator', 'intentflow'),
        __('AI Post Generator', 'intentflow'),
        'manage_options',
        'intentflow-ai-generator',
        'intentflow_ai_generator_page'
    );
}
add_action('admin_menu', 'intentflow_ai_admin_menu');

function intentflow_ai_generator_page() {
    $nonce      = wp_create_nonce('intentflow_ai_nonce');
    $categories = get_categories(array('hide_empty' => false));
    $usage      = (int) get_option('intentflow_ai_usage_count', 0);
    $model      = get_theme_mod('intentflow_ai_model', 'gemini-2.5-flash');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('AI Post Generator', 'intentflow'); ?></h1>
        <p><?php esc_html_e('Generate articles from keywords using Google Gemini AI.', 'intentflow'); ?></p>

        <!-- Stats bar -->
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:20px;display:flex;gap:24px">
            <div>
                <span style="font-size:12px;color:#6B7280;text-transform:uppercase"><?php esc_html_e('Model', 'intentflow'); ?></span>
                <div style="font-size:16px;font-weight:600;color:#111827"><?php echo esc_html($model); ?></div>
            </div>
            <div>
                <span style="font-size:12px;color:#6B7280;text-transform:uppercase"><?php esc_html_e('Total Generations', 'intentflow'); ?></span>
                <div style="font-size:16px;font-weight:600;color:#2563EB"><?php echo number_format($usage); ?></div>
            </div>
            <div>
                <span style="font-size:12px;color:#6B7280;text-transform:uppercase"><?php esc_html_e('Status', 'intentflow'); ?></span>
                <div style="font-size:16px;font-weight:600;color:#22C55E"><?php esc_html_e('Connected', 'intentflow'); ?></div>
            </div>
        </div>

        <div style="display:flex;gap:24px">
            <!-- Generator Form -->
            <div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px">
                <h2 style="margin-top:0"><?php esc_html_e('Bulk Generate', 'intentflow'); ?></h2>

                <div id="ai-generator-form" data-nonce="<?php echo esc_attr($nonce); ?>">
                    <div style="margin-bottom:16px">
                        <label style="display:block;font-weight:600;margin-bottom:4px">
                            <?php esc_html_e('Keywords (one per line)', 'intentflow'); ?>
                        </label>
                        <textarea id="bulk-keywords" rows="8" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:14px"
                                  placeholder="best video editing tools 2025&#10;how to start a blog&#10;capcut vs filmora comparison"></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">
                                <?php esc_html_e('Content Type', 'intentflow'); ?>
                            </label>
                            <select id="bulk-type" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:6px">
                                <option value="guide"><?php esc_html_e('Guide', 'intentflow'); ?></option>
                                <option value="tutorial"><?php esc_html_e('Tutorial', 'intentflow'); ?></option>
                                <option value="comparison"><?php esc_html_e('Comparison', 'intentflow'); ?></option>
                                <option value="review"><?php esc_html_e('Review', 'intentflow'); ?></option>
                                <option value="listicle"><?php esc_html_e('Listicle / Best Of', 'intentflow'); ?></option>
                                <option value="fix"><?php esc_html_e('Fix / Troubleshoot', 'intentflow'); ?></option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">
                                <?php esc_html_e('Category', 'intentflow'); ?>
                            </label>
                            <select id="bulk-category" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:6px">
                                <option value=""><?php esc_html_e('— None —', 'intentflow'); ?></option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?php echo esc_attr($cat->slug); ?>">
                                        <?php echo esc_html($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">
                            <?php esc_html_e('Post Status', 'intentflow'); ?>
                        </label>
                        <select id="bulk-status" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:6px">
                            <option value="draft"><?php esc_html_e('Draft (review before publishing)', 'intentflow'); ?></option>
                            <option value="publish"><?php esc_html_e('Publish immediately', 'intentflow'); ?></option>
                        </select>
                    </div>

                    <button type="button" id="bulk-generate-btn" class="button button-primary button-hero" style="width:100%">
                        <?php esc_html_e('Generate All Posts', 'intentflow'); ?>
                    </button>
                </div>

                <!-- Progress -->
                <div id="bulk-progress" style="display:none;margin-top:16px">
                    <div style="background:#f3f4f6;border-radius:8px;overflow:hidden;height:8px;margin-bottom:8px">
                        <div id="bulk-progress-bar" style="height:100%;background:linear-gradient(90deg,#2563EB,#22C55E);width:0%;transition:width .3s;border-radius:8px"></div>
                    </div>
                    <div id="bulk-progress-text" style="font-size:13px;color:#6B7280;text-align:center"></div>
                </div>
            </div>

            <!-- Results -->
            <div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px">
                <h2 style="margin-top:0"><?php esc_html_e('Generated Posts', 'intentflow'); ?></h2>
                <table class="widefat" id="bulk-results" style="display:none">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'intentflow'); ?></th>
                            <th style="width:80px"><?php esc_html_e('Status', 'intentflow'); ?></th>
                            <th style="width:80px"><?php esc_html_e('Actions', 'intentflow'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="bulk-results-body"></tbody>
                </table>
                <div id="bulk-empty" style="text-align:center;padding:40px 0;color:#9ca3af">
                    <?php esc_html_e('Generated posts will appear here.', 'intentflow'); ?>
                </div>
            </div>
        </div>

        <!-- n8n Integration Info -->
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-top:20px">
            <h2 style="margin-top:0"><?php esc_html_e('API / n8n Integration', 'intentflow'); ?></h2>
            <p style="color:#6B7280;font-size:13px"><?php esc_html_e('Use these REST API endpoints with n8n or any automation tool. Authenticate with WordPress Application Passwords.', 'intentflow'); ?></p>
            <table class="widefat" style="margin-top:12px">
                <thead><tr><th>Endpoint</th><th>Method</th><th>Description</th></tr></thead>
                <tbody>
                    <tr>
                        <td><code><?php echo esc_html(rest_url('intentflow/v1/ai/generate')); ?></code></td>
                        <td>POST</td>
                        <td><?php esc_html_e('Generate a post from keyword', 'intentflow'); ?></td>
                    </tr>
                    <tr>
                        <td><code><?php echo esc_html(rest_url('intentflow/v1/ai/seo')); ?></code></td>
                        <td>POST</td>
                        <td><?php esc_html_e('Generate SEO for a post', 'intentflow'); ?></td>
                    </tr>
                    <tr>
                        <td><code><?php echo esc_html(rest_url('intentflow/v1/ai/status')); ?></code></td>
                        <td>GET</td>
                        <td><?php esc_html_e('Check API status & usage', 'intentflow'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
