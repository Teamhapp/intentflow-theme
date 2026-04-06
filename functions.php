<?php
/**
 * IntentFlow Theme Functions
 * A high-intent content + conversion theme
 */

define('INTENTFLOW_VERSION', '2.0.0');
define('INTENTFLOW_DIR', get_template_directory());
define('INTENTFLOW_URI', get_template_directory_uri());

// Core includes
require_once INTENTFLOW_DIR . '/inc/theme-setup.php';
require_once INTENTFLOW_DIR . '/inc/enqueue.php';
require_once INTENTFLOW_DIR . '/inc/customizer.php';
require_once INTENTFLOW_DIR . '/inc/ad-helpers.php';
require_once INTENTFLOW_DIR . '/inc/template-tags.php';
require_once INTENTFLOW_DIR . '/inc/class-safelink-cpt.php';

// IntentFlow new features
require_once INTENTFLOW_DIR . '/inc/content-clusters.php';
require_once INTENTFLOW_DIR . '/inc/next-step-engine.php';
require_once INTENTFLOW_DIR . '/inc/flow-blocks.php';
require_once INTENTFLOW_DIR . '/inc/seo.php';
require_once INTENTFLOW_DIR . '/inc/gemini-ai.php';
require_once INTENTFLOW_DIR . '/inc/gemini-admin.php';
require_once INTENTFLOW_DIR . '/inc/admin-dashboard.php';
require_once INTENTFLOW_DIR . '/inc/automation.php';
