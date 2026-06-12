<?php declare(strict_types=1);

/**
 * Plugin Name: User List Plugin
 * Version: 1.0
 * Description: Plugin to manage users
 * Author: athanasiia
 * Text Domain: user-list-plugin
 * Domain Path:
 * Requires at least:
 * Requires PHP:
 * License: GPL v2 or later
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

define('ULP_DB_VERSION', '1.0.0');
// FIX: Store the main plugin file path so register_activation_hook/register_deactivation_hook
// can reference the correct file from within subdirectory hook files.
define('ULP_PLUGIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'includes/core/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/gorest-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/encryption.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/user-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/user-list-handler.php';

require_once plugin_dir_path(__FILE__) . 'includes/hooks/cron-hooks.php';
require_once plugin_dir_path(__FILE__) . 'includes/hooks/admin-hooks.php';
require_once plugin_dir_path(__FILE__) . 'includes/hooks/shortcode-hooks.php';

require_once plugin_dir_path(__FILE__) . 'includes/admin/partials/inactive-users-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/partials/modals.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/partials/user-form.php';

require_once plugin_dir_path(__FILE__) . 'includes/helpers/countries.php';

require_once plugin_dir_path(__FILE__) . 'includes/hooks/main-plugin-hooks.php';

// FIX: enqueue functions moved to includes/hooks/admin-hooks.php and shortcode-hooks.php
// to keep the main plugin file free of function declarations (PSR-1).