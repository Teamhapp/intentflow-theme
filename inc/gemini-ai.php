<?php
/**
 * IntentFlow Gemini AI — Core API Client & Generation Engine
 *
 * Functions:
 * - intentflow_gemini_request()     — Raw API call
 * - intentflow_ai_generate_post()   — Generate full article from keyword
 * - intentflow_ai_generate_seo()    — Generate SEO meta for existing post
 * - intentflow_ai_enhance_content() — Rewrite/improve content
 * - intentflow_ai_suggest_related() — Suggest related topics
 *
 * Endpoints:
 * - AJAX: intentflow_ai_generate, _seo, _enhance, _bulk
 * - REST: /wp-json/intentflow/v1/ai/generate, /seo, /status
 */

// ============================================================
// API CLIENT
// ============================================================

/**
 * Send a prompt to the configured AI provider (Gemini, ChatGPT, or Grok)
 *
 * @param string $prompt  The prompt text
 * @param array  $options Optional overrides (model, temperature, max_tokens)
 * @return string|WP_Error Response text or error
 */
function intentflow_gemini_request($prompt, $options = array()) {
    $provider = get_theme_mod('intentflow_ai_provider', 'gemini');
    $api_key  = get_theme_mod('intentflow_gemini_api_key', '');

    if (empty($api_key)) {
        return new WP_Error('no_api_key', sprintf(
            __('%s API key not configured. Set it in IntentFlow Settings > AI.', 'intentflow'),
            ucfirst($provider)
        ));
    }

    // Global rate limiting: max 15 requests per minute
    $global_key   = 'intentflow_ai_rate_global';
    $global_count = (int) get_transient($global_key);
    if ($global_count >= 15) {
        return new WP_Error('rate_limit', __('Rate limit reached. Please wait a minute.', 'intentflow'));
    }
    set_transient($global_key, $global_count + 1, 60);

    // Per-user rate limiting: max 10 per minute
    $rate_key   = 'intentflow_ai_rate_' . get_current_user_id();
    $rate_count = (int) get_transient($rate_key);
    if ($rate_count >= 10) {
        return new WP_Error('rate_limit', __('Rate limit reached. Please wait a minute.', 'intentflow'));
    }
    set_transient($rate_key, $rate_count + 1, 60);

    // Route to the correct provider
    switch ($provider) {
        case 'chatgpt':
            $result = intentflow_request_openai($prompt, $api_key, $options);
            break;
        case 'grok':
            $result = intentflow_request_grok($prompt, $api_key, $options);
            break;
        case 'gemini':
        default:
            $result = intentflow_request_gemini($prompt, $api_key, $options);
            break;
    }

    if (!is_wp_error($result)) {
        $usage = (int) get_option('intentflow_ai_usage_count', 0);
        update_option('intentflow_ai_usage_count', $usage + 1);
    }

    return $result;
}

// ============================================================
// PROVIDER: Google Gemini
// ============================================================

function intentflow_request_gemini($prompt, $api_key, $options = array()) {
    $model = isset($options['model']) ? $options['model'] : get_theme_mod('intentflow_ai_model', 'gemini-2.5-flash');

    $url = sprintf(
        'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
        $model,
        $api_key
    );

    $body = array(
        'contents' => array(
            array('parts' => array(array('text' => $prompt))),
        ),
        'generationConfig' => array(
            'temperature'     => isset($options['temperature']) ? $options['temperature'] : 0.7,
            'maxOutputTokens' => isset($options['max_tokens']) ? $options['max_tokens'] : 8192,
        ),
    );

    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => wp_json_encode($body),
        'timeout' => 60,
    ));

    if (is_wp_error($response)) return $response;

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        return new WP_Error('api_error', isset($data['error']['message']) ? $data['error']['message'] : 'Gemini API error (HTTP ' . $code . ')');
    }

    if (empty($data['candidates'][0]['content']['parts'][0]['text'])) {
        return new WP_Error('empty_response', __('Gemini returned an empty response.', 'intentflow'));
    }

    return $data['candidates'][0]['content']['parts'][0]['text'];
}

// ============================================================
// PROVIDER: OpenAI ChatGPT (GPT-4o, GPT-4.1)
// ============================================================

function intentflow_request_openai($prompt, $api_key, $options = array()) {
    $model = isset($options['model']) ? $options['model'] : get_theme_mod('intentflow_ai_model', 'gpt-4o');

    $body = array(
        'model'       => $model,
        'messages'    => array(
            array('role' => 'user', 'content' => $prompt),
        ),
        'temperature' => isset($options['temperature']) ? $options['temperature'] : 0.7,
        'max_tokens'  => isset($options['max_tokens']) ? $options['max_tokens'] : 8192,
    );

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body'    => wp_json_encode($body),
        'timeout' => 60,
    ));

    if (is_wp_error($response)) return $response;

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        return new WP_Error('api_error', isset($data['error']['message']) ? $data['error']['message'] : 'ChatGPT API error (HTTP ' . $code . ')');
    }

    if (empty($data['choices'][0]['message']['content'])) {
        return new WP_Error('empty_response', __('ChatGPT returned an empty response.', 'intentflow'));
    }

    return $data['choices'][0]['message']['content'];
}

// ============================================================
// PROVIDER: xAI Grok (Grok-3, Grok-3-mini)
// ============================================================

function intentflow_request_grok($prompt, $api_key, $options = array()) {
    $model = isset($options['model']) ? $options['model'] : get_theme_mod('intentflow_ai_model', 'grok-3-mini');

    // Grok uses OpenAI-compatible API format
    $body = array(
        'model'       => $model,
        'messages'    => array(
            array('role' => 'user', 'content' => $prompt),
        ),
        'temperature' => isset($options['temperature']) ? $options['temperature'] : 0.7,
        'max_tokens'  => isset($options['max_tokens']) ? $options['max_tokens'] : 8192,
    );

    $response = wp_remote_post('https://api.x.ai/v1/chat/completions', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body'    => wp_json_encode($body),
        'timeout' => 60,
    ));

    if (is_wp_error($response)) return $response;

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        return new WP_Error('api_error', isset($data['error']['message']) ? $data['error']['message'] : 'Grok API error (HTTP ' . $code . ')');
    }

    if (empty($data['choices'][0]['message']['content'])) {
        return new WP_Error('empty_response', __('Grok returned an empty response.', 'intentflow'));
    }

    return $data['choices'][0]['message']['content'];
}

    return $data['candidates'][0]['content']['parts'][0]['text'];
}

// ============================================================
// GENERATION FUNCTIONS
// ============================================================

/**
 * Generate a full article from a keyword
 *
 * @param string $keyword      Target keyword
 * @param string $content_type Content type (tutorial, comparison, fix, etc.)
 * @param array  $options      Optional: category, status, generate_seo
 * @return int|WP_Error Post ID or error
 */
function intentflow_ai_generate_post($keyword, $content_type = 'guide', $options = array()) {
    $language = get_theme_mod('intentflow_ai_language', 'English');
    $tone     = get_theme_mod('intentflow_ai_tone', 'professional');

    $prompt = sprintf(
        'Write a comprehensive %s article about "%s" in %s.
Tone: %s.
Format: Use HTML with H2 and H3 headings, bullet points, short paragraphs, and bold key terms.
Include: an engaging introduction, 3-5 detailed sections, and a brief conclusion.
Also provide a compelling title for this article.

Output format:
TITLE: [your title here]
---
[article HTML content here]',
        $content_type,
        $keyword,
        $language,
        $tone
    );

    $result = intentflow_gemini_request($prompt, array('max_tokens' => 8192));

    if (is_wp_error($result)) {
        return $result;
    }

    // Parse title and content
    $parts   = explode('---', $result, 2);
    $title   = '';
    $content = '';

    if (count($parts) === 2) {
        $title_line = trim($parts[0]);
        $title      = preg_replace('/^TITLE:\s*/i', '', $title_line);
        $content    = trim($parts[1]);
    } else {
        // Fallback: use keyword as title
        $title   = ucwords($keyword);
        $content = trim($result);
    }

    // Clean up markdown code fences (handle all variations)
    $content = preg_replace('/^```\s*html?\s*/im', '', $content);
    $content = preg_replace('/\s*```\s*$/m', '', $content);
    $content = trim($content);
    // Convert remaining markdown bold/italic to HTML
    $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
    $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);
    // Convert markdown headers to HTML if Gemini used them
    $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
    $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);

    // Create post
    $post_data = array(
        'post_title'   => sanitize_text_field($title),
        'post_content' => wp_kses_post($content),
        'post_status'  => isset($options['status']) ? $options['status'] : 'draft',
        'post_type'    => 'post',
        'post_author'  => get_current_user_id(),
    );

    $post_id = wp_insert_post($post_data, true);

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Set category
    if (!empty($options['category'])) {
        $cat = get_category_by_slug($options['category']);
        if ($cat) {
            wp_set_post_categories($post_id, array($cat->term_id));
        }
    }

    // Set content type taxonomy
    if (!empty($content_type)) {
        wp_set_object_terms($post_id, $content_type, 'content_type');
    }

    // Mark as AI-generated
    update_post_meta($post_id, '_intentflow_ai_generated', true);
    update_post_meta($post_id, '_intentflow_ai_keyword', $keyword);

    // Auto-generate SEO if requested
    if (!empty($options['generate_seo'])) {
        intentflow_ai_generate_seo($post_id);
    }

    // Auto-generate featured image
    intentflow_ai_generate_thumbnail($post_id);

    return $post_id;
}

/**
 * Generate SEO metadata for a post
 *
 * @param int $post_id
 * @return array|WP_Error SEO data or error
 */
function intentflow_ai_generate_seo($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('invalid_post', __('Post not found.', 'intentflow'));
    }

    $content_preview = wp_trim_words(strip_tags($post->post_content), 200);

    $prompt = sprintf(
        'For the article titled "%s" with this content:

%s

Generate SEO metadata. Output ONLY valid JSON with these exact keys:
{"meta_description":"(max 155 characters)","excerpt":"(2 concise sentences)","tags":"(5 relevant tags, comma separated)","seo_title":"(max 60 characters, SEO optimized)"}',
        $post->post_title,
        $content_preview
    );

    $result = intentflow_gemini_request($prompt, array('temperature' => 0.3));

    if (is_wp_error($result)) {
        return $result;
    }

    // Extract JSON from response (handle markdown fences and nested braces)
    $json_str = $result;
    // Strip code fences
    $json_str = preg_replace('/^```\s*json?\s*/im', '', $json_str);
    $json_str = preg_replace('/\s*```\s*$/m', '', $json_str);
    // Find the JSON object (supports nested braces)
    if (preg_match('/\{(?:[^{}]|(?:\{[^{}]*\}))*\}/s', $json_str, $matches)) {
        $json_str = $matches[0];
    }

    $seo = json_decode(trim($json_str), true);

    if (!$seo || json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('parse_error', sprintf(
            __('Could not parse SEO response. JSON error: %s', 'intentflow'),
            json_last_error_msg()
        ));
    }

    // Save SEO data
    update_post_meta($post_id, '_intentflow_meta_description', sanitize_text_field($seo['meta_description']));
    update_post_meta($post_id, '_intentflow_seo_title', sanitize_text_field($seo['seo_title']));

    // Update excerpt if empty
    if (empty($post->post_excerpt) && !empty($seo['excerpt'])) {
        wp_update_post(array(
            'ID'           => $post_id,
            'post_excerpt' => sanitize_text_field($seo['excerpt']),
        ));
    }

    // Set tags
    if (!empty($seo['tags'])) {
        $tags = array_map('trim', explode(',', $seo['tags']));
        wp_set_post_tags($post_id, $tags, true);
    }

    return $seo;
}

/**
 * Enhance/rewrite content
 *
 * @param string $content Original content
 * @param string $action  'improve', 'simplify', 'expand', 'summarize'
 * @return string|WP_Error Enhanced content or error
 */
function intentflow_ai_enhance_content($content, $action = 'improve') {
    $action_prompts = array(
        'improve'   => 'Improve and polish',
        'simplify'  => 'Simplify for a general audience',
        'expand'    => 'Expand with more detail and examples',
        'summarize' => 'Summarize into key points',
    );

    $action_text = isset($action_prompts[$action]) ? $action_prompts[$action] : $action_prompts['improve'];

    $prompt = sprintf(
        '%s the following content. Keep the same topic and key information. Improve readability, add transitions, fix grammar. Output only the improved HTML content, no explanations.

Content:
%s',
        $action_text,
        $content
    );

    $result = intentflow_gemini_request($prompt);

    if (is_wp_error($result)) {
        return $result;
    }

    // Clean markdown fences
    $result = preg_replace('/^```html?\s*/i', '', trim($result));
    $result = preg_replace('/```\s*$/', '', $result);

    return $result;
}

/**
 * Suggest related topics for content clusters
 *
 * @param int $post_id
 * @return array|WP_Error Array of suggestions or error
 */
function intentflow_ai_suggest_related($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('invalid_post', __('Post not found.', 'intentflow'));
    }

    $prompt = sprintf(
        'For the article "%s", suggest 6 related article ideas that would form a content cluster.
For each, provide a title and content type (tutorial, comparison, fix, guide, review, or listicle).

Output ONLY valid JSON array:
[{"title":"...","type":"tutorial"},{"title":"...","type":"comparison"}]',
        $post->post_title
    );

    $result = intentflow_gemini_request($prompt, array('temperature' => 0.5));

    if (is_wp_error($result)) {
        return $result;
    }

    // Extract JSON array
    if (preg_match('/\[.*\]/s', $result, $matches)) {
        $suggestions = json_decode($matches[0], true);
        if (is_array($suggestions)) {
            return $suggestions;
        }
    }

    return new WP_Error('parse_error', __('Could not parse suggestions from Gemini.', 'intentflow'));
}

// ============================================================
// THUMBNAIL / FEATURED IMAGE GENERATION
// ============================================================

/**
 * Generate a featured image for a post using Gemini's image generation
 * Falls back to creating an SVG placeholder with the post title
 *
 * @param int    $post_id Post ID
 * @param string $style   'gradient', 'minimal', 'photo'
 * @return int|WP_Error Attachment ID or error
 */
function intentflow_ai_generate_thumbnail($post_id, $style = 'gradient') {
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('invalid_post', __('Post not found.', 'intentflow'));
    }

    // Try Gemini image generation via Imagen model
    $api_key = get_theme_mod('intentflow_gemini_api_key', '');
    if (empty($api_key)) {
        return intentflow_create_svg_thumbnail($post_id, $style);
    }

    $prompt = sprintf(
        'Create a clean, modern blog header image for an article titled "%s". Style: professional, minimal, tech-focused. No text in the image. 16:9 aspect ratio. High quality.',
        $post->post_title
    );

    // Try Imagen 3 model for image generation
    $url = sprintf(
        'https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:predict?key=%s',
        $api_key
    );

    $body = array(
        'instances' => array(
            array('prompt' => $prompt),
        ),
        'parameters' => array(
            'sampleCount' => 1,
            'aspectRatio'  => '16:9',
        ),
    );

    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => wp_json_encode($body),
        'timeout' => 90,
    ));

    if (is_wp_error($response)) {
        return intentflow_create_svg_thumbnail($post_id, $style);
    }

    $code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    // If Imagen works, save the base64 image
    if ($code === 200 && !empty($data['predictions'][0]['bytesBase64Encoded'])) {
        $image_data = base64_decode($data['predictions'][0]['bytesBase64Encoded']);
        $filename   = 'ai-thumbnail-' . $post_id . '-' . time() . '.png';

        $upload = wp_upload_bits($filename, null, $image_data);

        if (!empty($upload['error'])) {
            return new WP_Error('upload_error', $upload['error']);
        }

        $attachment_id = wp_insert_attachment(array(
            'post_mime_type' => 'image/png',
            'post_title'     => sanitize_text_field($post->post_title) . ' - Featured Image',
            'post_status'    => 'inherit',
        ), $upload['file'], $post_id);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);
        set_post_thumbnail($post_id, $attachment_id);

        return $attachment_id;
    }

    // Fallback to SVG-based thumbnail
    return intentflow_create_svg_thumbnail($post_id, $style);
}

/**
 * Create an SVG-based gradient thumbnail with post title
 * Converts to PNG and sets as featured image
 */
function intentflow_create_svg_thumbnail($post_id, $style = 'gradient') {
    $post  = get_post($post_id);
    $title = $post ? $post->post_title : 'Untitled';

    // Pick gradient based on category or random
    $gradients = array(
        array('#2563EB', '#06B6D4'),
        array('#22C55E', '#14B8A6'),
        array('#8B5CF6', '#EC4899'),
        array('#F59E0B', '#EF4444'),
        array('#3B82F6', '#1D4ED8'),
        array('#10B981', '#059669'),
        array('#6366F1', '#4F46E5'),
        array('#F43F5E', '#E11D48'),
    );

    $categories = get_the_category($post_id);
    $index = !empty($categories) ? $categories[0]->term_id % count($gradients) : array_rand($gradients);
    $grad  = $gradients[$index];

    // Get content type badge
    $content_types = wp_get_post_terms($post_id, 'content_type', array('fields' => 'names'));
    $badge = !empty($content_types) && !is_wp_error($content_types) ? $content_types[0] : '';

    // Word wrap title for SVG
    $words      = explode(' ', $title);
    $lines      = array();
    $current    = '';
    $max_chars  = 30;

    foreach ($words as $word) {
        if (strlen($current . ' ' . $word) > $max_chars && !empty($current)) {
            $lines[] = trim($current);
            $current = $word;
        } else {
            $current .= ' ' . $word;
        }
    }
    if (!empty(trim($current))) {
        $lines[] = trim($current);
    }
    $lines = array_slice($lines, 0, 3); // Max 3 lines

    // Build SVG
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675">';
    $svg .= '<defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">';
    $svg .= '<stop offset="0%" style="stop-color:' . $grad[0] . '"/>';
    $svg .= '<stop offset="100%" style="stop-color:' . $grad[1] . '"/>';
    $svg .= '</linearGradient></defs>';
    $svg .= '<rect width="1200" height="675" fill="url(#bg)"/>';

    // Decorative circles
    $svg .= '<circle cx="1050" cy="120" r="200" fill="rgba(255,255,255,0.08)"/>';
    $svg .= '<circle cx="150" cy="550" r="150" fill="rgba(255,255,255,0.05)"/>';

    // Badge
    if ($badge) {
        $svg .= '<rect x="80" y="220" width="' . (strlen($badge) * 12 + 30) . '" height="32" rx="16" fill="rgba(255,255,255,0.2)"/>';
        $svg .= '<text x="95" y="242" font-family="system-ui,sans-serif" font-size="14" font-weight="600" fill="#fff">' . htmlspecialchars($badge) . '</text>';
    }

    // Title text
    $y_start = $badge ? 300 : 280;
    foreach ($lines as $i => $line) {
        $svg .= '<text x="80" y="' . ($y_start + $i * 50) . '" font-family="system-ui,sans-serif" font-size="42" font-weight="700" fill="#fff">' . htmlspecialchars($line) . '</text>';
    }

    // IntentFlow branding
    $svg .= '<text x="80" y="620" font-family="system-ui,sans-serif" font-size="16" font-weight="600" fill="rgba(255,255,255,0.5)">IntentFlow</text>';

    $svg .= '</svg>';

    // Save SVG as file, then convert conceptually
    // WordPress can handle SVG uploads with proper mime type, but for compatibility we save as SVG
    $filename = 'thumbnail-' . $post_id . '-' . time() . '.svg';
    $upload   = wp_upload_bits($filename, null, $svg);

    if (!empty($upload['error'])) {
        return new WP_Error('upload_error', $upload['error']);
    }

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => 'image/svg+xml',
        'post_title'     => sanitize_text_field($title) . ' - Thumbnail',
        'post_status'    => 'inherit',
    ), $upload['file'], $post_id);

    if (!is_wp_error($attachment_id)) {
        set_post_thumbnail($post_id, $attachment_id);
    }

    return $attachment_id;
}

/**
 * Allow SVG uploads for thumbnails
 */
function intentflow_allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'intentflow_allow_svg_upload');

// ============================================================
// AUTO-SEO ON PUBLISH
// ============================================================

function intentflow_auto_seo_on_publish($new_status, $old_status, $post) {
    // Guard against infinite loop
    static $running = false;
    if ($running) return;
    $running = true;

    if ($new_status !== 'publish' || $old_status === 'publish') return;
    if ($post->post_type !== 'post') return;

    $auto_seo  = get_theme_mod('intentflow_ai_auto_seo', false);
    $auto_tags = get_theme_mod('intentflow_ai_auto_tags', false);
    $api_key   = get_theme_mod('intentflow_gemini_api_key', '');

    if (empty($api_key)) return;
    if (!$auto_seo && !$auto_tags) return;

    // Only generate if not already set
    $has_meta    = get_post_meta($post->ID, '_intentflow_meta_description', true);
    $has_excerpt = !empty($post->post_excerpt);
    $has_tags    = wp_get_post_tags($post->ID);

    if ($auto_seo && (empty($has_meta) || !$has_excerpt)) {
        intentflow_ai_generate_seo($post->ID);
    } elseif ($auto_tags && empty($has_tags)) {
        // Just generate tags
        intentflow_ai_generate_seo($post->ID);
    }
}
add_action('transition_post_status', 'intentflow_auto_seo_on_publish', 10, 3);

// ============================================================
// AJAX ENDPOINTS (Admin Only)
// ============================================================

/**
 * AJAX: Generate post from keyword
 */
function intentflow_ajax_ai_generate() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'intentflow')));
    }

    $keyword      = sanitize_text_field($_POST['keyword'] ?? '');
    $content_type = sanitize_text_field($_POST['content_type'] ?? 'guide');
    $category     = sanitize_text_field($_POST['category'] ?? '');
    $status       = sanitize_text_field($_POST['status'] ?? 'draft');

    if (empty($keyword)) {
        wp_send_json_error(array('message' => __('Keyword is required.', 'intentflow')));
    }

    $post_id = intentflow_ai_generate_post($keyword, $content_type, array(
        'category'     => $category,
        'status'       => $status,
        'generate_seo' => true,
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => $post_id->get_error_message()));
    }

    wp_send_json_success(array(
        'post_id'  => $post_id,
        'title'    => get_the_title($post_id),
        'edit_url' => get_edit_post_link($post_id, 'raw'),
        'view_url' => get_permalink($post_id),
    ));
}
add_action('wp_ajax_intentflow_ai_generate', 'intentflow_ajax_ai_generate');

/**
 * AJAX: Generate SEO for a post
 */
function intentflow_ajax_ai_seo() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'intentflow')));
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Post ID required.', 'intentflow')));
    }

    $seo = intentflow_ai_generate_seo($post_id);

    if (is_wp_error($seo)) {
        wp_send_json_error(array('message' => $seo->get_error_message()));
    }

    wp_send_json_success($seo);
}
add_action('wp_ajax_intentflow_ai_seo', 'intentflow_ajax_ai_seo');

/**
 * AJAX: Enhance content
 */
function intentflow_ajax_ai_enhance() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'intentflow')));
    }

    $content = wp_kses_post($_POST['content'] ?? '');
    $action  = sanitize_text_field($_POST['enhance_action'] ?? 'improve');

    if (empty($content)) {
        wp_send_json_error(array('message' => __('Content is required.', 'intentflow')));
    }

    $result = intentflow_ai_enhance_content($content, $action);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    wp_send_json_success(array('content' => $result));
}
add_action('wp_ajax_intentflow_ai_enhance', 'intentflow_ajax_ai_enhance');

/**
 * AJAX: Bulk generate posts
 */
function intentflow_ajax_ai_bulk() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'intentflow')));
    }

    $keyword      = sanitize_text_field($_POST['keyword'] ?? '');
    $content_type = sanitize_text_field($_POST['content_type'] ?? 'guide');
    $category     = sanitize_text_field($_POST['category'] ?? '');
    $status       = sanitize_text_field($_POST['status'] ?? 'draft');

    if (empty($keyword)) {
        wp_send_json_error(array('message' => __('Keyword is required.', 'intentflow')));
    }

    $post_id = intentflow_ai_generate_post($keyword, $content_type, array(
        'category'     => $category,
        'status'       => $status,
        'generate_seo' => true,
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => $post_id->get_error_message()));
    }

    wp_send_json_success(array(
        'post_id'  => $post_id,
        'title'    => get_the_title($post_id),
        'edit_url' => get_edit_post_link($post_id, 'raw'),
    ));
}
add_action('wp_ajax_intentflow_ai_bulk', 'intentflow_ajax_ai_bulk');

/**
 * AJAX: Suggest related articles
 */
function intentflow_ajax_ai_related() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'intentflow')));
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Post ID required.', 'intentflow')));
    }

    $suggestions = intentflow_ai_suggest_related($post_id);

    if (is_wp_error($suggestions)) {
        wp_send_json_error(array('message' => $suggestions->get_error_message()));
    }

    wp_send_json_success($suggestions);
}
add_action('wp_ajax_intentflow_ai_related', 'intentflow_ajax_ai_related');

/**
 * AJAX: Generate thumbnail for a post
 */
function intentflow_ajax_ai_thumbnail() {
    check_ajax_referer('intentflow_ai_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'intentflow')));
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    $style   = sanitize_text_field($_POST['style'] ?? 'gradient');

    if (!$post_id) {
        wp_send_json_error(array('message' => __('Post ID required.', 'intentflow')));
    }

    $attachment_id = intentflow_ai_generate_thumbnail($post_id, $style);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error(array('message' => $attachment_id->get_error_message()));
    }

    wp_send_json_success(array(
        'attachment_id' => $attachment_id,
        'url'           => wp_get_attachment_url($attachment_id),
    ));
}
add_action('wp_ajax_intentflow_ai_thumbnail', 'intentflow_ajax_ai_thumbnail');

// ============================================================
// REST API ENDPOINTS (for n8n / external automation)
// ============================================================

function intentflow_register_ai_rest_routes() {
    $namespace = 'intentflow/v1';

    // POST /ai/generate — Generate a post
    register_rest_route($namespace, '/ai/generate', array(
        'methods'             => 'POST',
        'callback'            => 'intentflow_rest_ai_generate',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'keyword'      => array('required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'),
            'content_type' => array('default' => 'guide', 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'),
            'category'     => array('default' => '', 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'),
            'status'       => array('default' => 'draft', 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'),
            'generate_seo' => array('default' => true, 'type' => 'boolean'),
        ),
    ));

    // POST /ai/seo — Generate SEO for a post
    register_rest_route($namespace, '/ai/seo', array(
        'methods'             => 'POST',
        'callback'            => 'intentflow_rest_ai_seo',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'post_id' => array('required' => true, 'type' => 'integer'),
        ),
    ));

    // GET /ai/status — API status + usage
    register_rest_route($namespace, '/ai/status', array(
        'methods'             => 'GET',
        'callback'            => 'intentflow_rest_ai_status',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));
}
add_action('rest_api_init', 'intentflow_register_ai_rest_routes');

function intentflow_rest_ai_generate($request) {
    $post_id = intentflow_ai_generate_post(
        $request['keyword'],
        $request['content_type'],
        array(
            'category'     => $request['category'],
            'status'       => $request['status'],
            'generate_seo' => $request['generate_seo'],
        )
    );

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(array('success' => false, 'error' => $post_id->get_error_message()), 400);
    }

    $post = get_post($post_id);
    $seo  = array(
        'meta_description' => get_post_meta($post_id, '_intentflow_meta_description', true),
        'seo_title'        => get_post_meta($post_id, '_intentflow_seo_title', true),
    );

    return new WP_REST_Response(array(
        'success' => true,
        'post_id' => $post_id,
        'title'   => $post->post_title,
        'url'     => get_permalink($post_id),
        'status'  => $post->post_status,
        'seo'     => $seo,
    ), 201);
}

function intentflow_rest_ai_seo($request) {
    $seo = intentflow_ai_generate_seo($request['post_id']);

    if (is_wp_error($seo)) {
        return new WP_REST_Response(array('success' => false, 'error' => $seo->get_error_message()), 400);
    }

    return new WP_REST_Response(array('success' => true, 'seo' => $seo), 200);
}

function intentflow_rest_ai_status($request) {
    $api_key = get_theme_mod('intentflow_gemini_api_key', '');
    $model   = get_theme_mod('intentflow_ai_model', 'gemini-2.5-flash');
    $usage   = (int) get_option('intentflow_ai_usage_count', 0);

    return new WP_REST_Response(array(
        'configured' => !empty($api_key),
        'model'      => $model,
        'usage'      => $usage,
        'auto_seo'   => get_theme_mod('intentflow_ai_auto_seo', false),
        'auto_tags'  => get_theme_mod('intentflow_ai_auto_tags', false),
    ), 200);
}
