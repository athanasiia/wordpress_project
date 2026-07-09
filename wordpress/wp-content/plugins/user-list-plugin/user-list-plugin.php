<?php declare(strict_types=1);

/**
 * Plugin Name: User List Plugin
 * Version: 1.0.0
 * Description: Plugin to manage users
 * Author: athanasiia
 * Text Domain: user-list-plugin
 * Domain Path: /languages
 * Requires at least: 8.3
 * Requires PHP: 8.3
 * License: GPL v2 or later
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

define('ULP_DB_VERSION', '1.0.0');
define('ULP_VERSION', '1.0.0');
define('ULP_PLUGIN_FILE', __FILE__);

global $ulp_page_hooks;
$ulp_page_hooks = [];

require_once plugin_dir_path(__FILE__) . 'includes/bootstrap.php';