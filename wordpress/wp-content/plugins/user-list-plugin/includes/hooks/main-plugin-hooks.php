<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../core/activation.php';
require_once __DIR__ . '/../core/deactivation.php';
require_once __DIR__ . '/../core/filters.php';

register_activation_hook(__FILE__, __NAMESPACE__ . '\\ulp_activate_plugin');
add_action('plugins_loaded', __NAMESPACE__ . '\\ulp_check_db_version');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\ulp_deactivate_plugin');

add_filter('ulp_add_icons', __NAMESPACE__ . '\\ulp_add_icons');