<?php
/**
 * IntentFlow Daily Automation
 *
 * - Keyword queue (add/manage keywords for auto-publishing)
 * - WP Cron auto-publisher (3-5 posts/day from queue)
 * - Automation log (history of generated posts)
 * - n8n-compatible REST endpoints
 */

// ============================================================
// KEYWORD QUEUE (stored as option)
// ============================================================

/**
 * Get the keyword queue
 * @return array Array of keyword objects {keyword, content_type, category, status, added}
 */
function intentflow_get_queue() {
    return get_option('intentflow_keyword_queue', array());
}

function intentflow_save_queue($queue) {
    update_option('intentflow_keyword_queue', $queue);
}

/**
 * Add keyword(s) to queue
 */
function intentflow_add_to_queue($keywords, $content_type = 'guide', $category = '') {
    $queue = intentflow_get_queue();

    if (is_string($keywords)) {
        $keywords = array_filter(array_map('trim', explode("\n", $keywords)));
    }

    foreach ($keywords as $kw) {
        $queue[] = array(
            'keyword'      => sanitize_text_field($kw),
            'content_type' => sanitize_text_field($content_type),
            'category'     => sanitize_text_field($category),
            'status'       => 'pending',
            'added'        => current_time('mysql'),
        );
    }

    intentflow_save_queue($queue);
    return count($keywords);
}

/**
 * Get next pending keywords from queue
 */
function intentflow_get_next_keywords($count = 3) {
    $queue   = intentflow_get_queue();
    $pending = array();

    foreach ($queue as $i => $item) {
        // Recover stuck "processing" items — use processing_started if available, else added
        if ($item['status'] === 'processing') {
            $ts = isset($item['processing_started']) ? $item['processing_started'] : (isset($item['added']) ? $item['added'] : '');
            if (!empty($ts) && (time() - strtotime($ts)) > 900) {
                $queue[$i]['status'] = 'pending';
                unset($queue[$i]['processing_started']);
            }
        }

        if ($item['status'] === 'pending') {
            $pending[] = array('index' => $i, 'item' => $item);
            if (count($pending) >= $count) break;
        }
    }

    // Save recovered items
    intentflow_save_queue($queue);

    return $pending;
}

/**
 * Clean up old completed/failed queue items (keep last 50)
 */
function intentflow_cleanup_queue() {
    $queue = intentflow_get_queue();
    if (count($queue) <= 50) return;

    $pending = array_filter($queue, function ($i) { return $i['status'] === 'pending' || $i['status'] === 'processing'; });
    $done    = array_filter($queue, function ($i) { return $i['status'] === 'done' || $i['status'] === 'failed'; });

    // Keep all pending + last 20 completed
    $done = array_slice($done, -20, null, true);
    $queue = array_values(array_merge($pending, $done));

    intentflow_save_queue($queue);
}
add_action('intentflow_daily_publish', 'intentflow_cleanup_queue', 5);

/**
 * Mark queue items as processed
 */
function intentflow_mark_queue_done($index, $post_id = 0) {
    $queue = intentflow_get_queue();
    if (isset($queue[$index])) {
        $queue[$index]['status']    = 'done';
        $queue[$index]['post_id']   = $post_id;
        $queue[$index]['completed'] = current_time('mysql');
    }
    intentflow_save_queue($queue);
}

function intentflow_mark_queue_failed($index, $error = '') {
    $queue = intentflow_get_queue();
    if (isset($queue[$index])) {
        $queue[$index]['status'] = 'failed';
        $queue[$index]['error']  = $error;
    }
    intentflow_save_queue($queue);
}

// ============================================================
// AUTOMATION LOG
// ============================================================

function intentflow_log_automation($message, $type = 'info') {
    // Use autoload=no to prevent loading log on every page request
    $log   = get_option('intentflow_automation_log', array());
    if (!is_array($log)) $log = array();

    $log[] = array(
        'time'    => current_time('mysql'),
        'message' => $message,
        'type'    => $type,
    );

    // Keep last 50 entries (reduced from 100 to minimize DB bloat)
    if (count($log) > 50) {
        $log = array_slice($log, -50);
    }

    // autoload=no prevents loading this on every page request
    if (get_option('intentflow_automation_log') === false) {
        add_option('intentflow_automation_log', $log, '', 'no');
    } else {
        update_option('intentflow_automation_log', $log, false);
    }
}

function intentflow_get_log() {
    return array_reverse(get_option('intentflow_automation_log', array()));
}

// ============================================================
// WP CRON AUTO-PUBLISHER
// ============================================================

/**
 * Schedule daily cron on theme activation
 */
function intentflow_schedule_cron() {
    if (!wp_next_scheduled('intentflow_daily_publish')) {
        wp_schedule_event(time(), 'intentflow_custom_interval', 'intentflow_daily_publish');
    }
}
add_action('after_switch_theme', 'intentflow_schedule_cron');

/**
 * Clear cron on theme deactivation
 */
function intentflow_clear_cron() {
    wp_clear_scheduled_hook('intentflow_daily_publish');
}
add_action('switch_theme', 'intentflow_clear_cron');

/**
 * Custom cron interval: every 8 hours (3x/day for 3-5 posts)
 */
function intentflow_cron_schedules($schedules) {
    $hours = (int) get_theme_mod('intentflow_auto_interval', 8);
    $schedules['intentflow_custom_interval'] = array(
        'interval' => $hours * 3600,
        'display'  => sprintf(__('Every %d hours', 'intentflow'), $hours),
    );
    return $schedules;
}
add_filter('cron_schedules', 'intentflow_cron_schedules');

/**
 * The daily auto-publish action
 */
function intentflow_daily_auto_publish() {
    // Check if automation is enabled
    if (!get_theme_mod('intentflow_auto_enabled', false)) return;

    // Concurrency lock — prevent overlapping runs
    $lock_key = 'intentflow_cron_lock';
    if (get_transient($lock_key)) {
        intentflow_log_automation('Skipped: Previous run still in progress.', 'info');
        return;
    }
    set_transient($lock_key, true, 600); // 10 min max lock

    $api_key = get_theme_mod('intentflow_gemini_api_key', '');
    if (empty($api_key)) {
        intentflow_log_automation('Skipped: No Gemini API key configured.', 'error');
        delete_transient($lock_key);
        return;
    }

    $posts_per_run = (int) get_theme_mod('intentflow_auto_posts_per_run', 2);
    $auto_status   = get_theme_mod('intentflow_auto_post_status', 'draft');

    $keywords = intentflow_get_next_keywords($posts_per_run);

    if (empty($keywords)) {
        intentflow_log_automation('No pending keywords in queue.', 'info');
        delete_transient($lock_key);
        return;
    }

    intentflow_log_automation(sprintf('Starting auto-publish: %d keywords', count($keywords)), 'info');

    // Set max execution time for cron batch (120 seconds)
    $start_time = time();
    $max_time   = 120;

    $success = 0;
    $failed  = 0;

    foreach ($keywords as $entry) {
        // Abort if approaching execution timeout
        if ((time() - $start_time) > $max_time) {
            intentflow_log_automation('Batch timeout reached. Remaining keywords deferred to next run.', 'info');
            break;
        }

        $item  = $entry['item'];
        $index = $entry['index'];

        // Mark as processing with timestamp for stuck-job detection
        $queue = intentflow_get_queue();
        if (isset($queue[$index])) {
            $queue[$index]['status'] = 'processing';
            $queue[$index]['processing_started'] = current_time('mysql');
            intentflow_save_queue($queue);
        }

        $post_id = intentflow_ai_generate_post(
            $item['keyword'],
            $item['content_type'],
            array(
                'category'     => $item['category'],
                'status'       => $auto_status,
                'generate_seo' => true,
            )
        );

        if (is_wp_error($post_id)) {
            $error = $post_id->get_error_message();
            intentflow_mark_queue_failed($index, $error);
            intentflow_log_automation(sprintf('Failed: "%s" — %s', $item['keyword'], $error), 'error');
            $failed++;
        } else {
            intentflow_mark_queue_done($index, $post_id);
            intentflow_log_automation(
                sprintf('Published: "%s" → Post #%d (%s)', $item['keyword'], $post_id, get_the_title($post_id)),
                'success'
            );
            $success++;
        }
    }

    intentflow_log_automation(sprintf('Batch complete: %d success, %d failed', $success, $failed), 'info');
    delete_transient($lock_key);
}
add_action('intentflow_daily_publish', 'intentflow_daily_auto_publish');

/**
 * Re-schedule cron if not set (runs on admin_init)
 */
function intentflow_ensure_cron() {
    if (get_theme_mod('intentflow_auto_enabled', false) && !wp_next_scheduled('intentflow_daily_publish')) {
        intentflow_schedule_cron();
    }
}
add_action('admin_init', 'intentflow_ensure_cron');

// ============================================================
// AJAX: Queue Management
// ============================================================

function intentflow_ajax_add_queue() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error(array('message' => 'Permission denied'));

    $keywords     = sanitize_textarea_field($_POST['keywords'] ?? '');
    $content_type = sanitize_text_field($_POST['content_type'] ?? 'guide');
    $category     = sanitize_text_field($_POST['category'] ?? '');

    if (empty($keywords)) wp_send_json_error(array('message' => 'Keywords required'));

    $count = intentflow_add_to_queue($keywords, $content_type, $category);
    wp_send_json_success(array('added' => $count, 'queue_size' => count(intentflow_get_queue())));
}
add_action('wp_ajax_intentflow_add_queue', 'intentflow_ajax_add_queue');

function intentflow_ajax_clear_queue() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();

    // Only clear completed/failed, keep pending
    $queue = intentflow_get_queue();
    $queue = array_values(array_filter($queue, function ($item) {
        return $item['status'] === 'pending';
    }));
    intentflow_save_queue($queue);
    wp_send_json_success(array('remaining' => count($queue)));
}
add_action('wp_ajax_intentflow_clear_queue', 'intentflow_ajax_clear_queue');

function intentflow_ajax_run_now() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error(array('message' => 'Permission denied'));

    intentflow_daily_auto_publish();
    wp_send_json_success(array('log' => intentflow_get_log()));
}
add_action('wp_ajax_intentflow_run_now', 'intentflow_ajax_run_now');

// ============================================================
// REST API: Queue + Automation (for n8n)
// ============================================================

function intentflow_register_automation_routes() {
    $ns = 'intentflow/v1';

    // POST /queue/add — Add keywords to queue
    register_rest_route($ns, '/queue/add', array(
        'methods'             => 'POST',
        'callback'            => function ($request) {
            $keywords     = $request->get_param('keywords');
            $content_type = $request->get_param('content_type') ?: 'guide';
            $category     = $request->get_param('category') ?: '';

            if (empty($keywords)) {
                return new WP_REST_Response(array('error' => 'keywords required'), 400);
            }

            if (is_string($keywords)) $keywords = array($keywords);

            $count = intentflow_add_to_queue(implode("\n", $keywords), $content_type, $category);
            return new WP_REST_Response(array('added' => $count, 'queue_size' => count(intentflow_get_queue())), 200);
        },
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));

    // GET /queue — Get queue status
    register_rest_route($ns, '/queue', array(
        'methods'  => 'GET',
        'callback' => function () {
            $queue   = intentflow_get_queue();
            $pending = count(array_filter($queue, function ($i) { return $i['status'] === 'pending'; }));
            $done    = count(array_filter($queue, function ($i) { return $i['status'] === 'done'; }));
            $failed  = count(array_filter($queue, function ($i) { return $i['status'] === 'failed'; }));

            return new WP_REST_Response(array(
                'total'   => count($queue),
                'pending' => $pending,
                'done'    => $done,
                'failed'  => $failed,
                'items'   => array_slice($queue, -20), // last 20
            ), 200);
        },
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));

    // POST /queue/run — Trigger auto-publish now
    register_rest_route($ns, '/queue/run', array(
        'methods'  => 'POST',
        'callback' => function () {
            intentflow_daily_auto_publish();
            return new WP_REST_Response(array('success' => true, 'log' => array_slice(intentflow_get_log(), 0, 10)), 200);
        },
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));

    // GET /automation/log — Get automation log
    register_rest_route($ns, '/automation/log', array(
        'methods'  => 'GET',
        'callback' => function () {
            return new WP_REST_Response(array('log' => intentflow_get_log()), 200);
        },
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));
}
add_action('rest_api_init', 'intentflow_register_automation_routes');
