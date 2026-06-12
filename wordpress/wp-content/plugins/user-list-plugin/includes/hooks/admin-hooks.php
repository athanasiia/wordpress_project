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

// FIX: moved from user-list-plugin.php — enqueue hooks belong in the hooks layer, not the plugin root.
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\ulp_enqueue_admin_assets');

function ulp_enqueue_admin_assets(string $hook): void
{
    if ($hook === 'admin_page_ulp-user-edit' || $hook === 'user-list_page_ulp-user-create') {
        wp_enqueue_style('ulp-form-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-form.css', [], '1.0');
        wp_enqueue_style('ulp-modal-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/modal.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/js/user-table.js', [], '1.0', true);
    }

    if ($hook === 'toplevel_page_ulp-users') {
        wp_enqueue_style('ulp-table-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-table.css', [], '1.0');
        wp_enqueue_style('ulp-table-actions-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-table-actions.css', [], '1.0');
        wp_enqueue_style('ulp-modal-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/modal.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/js/user-table.js', [], '1.0', true);
    }
}


