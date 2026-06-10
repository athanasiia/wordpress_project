<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../admin/pages/admin-create.php';
require_once __DIR__ . '/../admin/pages/admin-edit.php';
require_once __DIR__ . '/../admin/pages/admin-page.php';
require_once __DIR__ . '/../admin/pages/settings-page.php';

add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_admin_menu');
add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_edit_submenu');
add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_create_submenu');
add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_settings_page');


