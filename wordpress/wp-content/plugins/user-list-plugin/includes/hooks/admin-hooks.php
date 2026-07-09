<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_admin_menu');
add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_edit_submenu');
add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_create_submenu');
add_action('admin_menu', __NAMESPACE__ . '\\ulp_add_settings_page');
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\ulp_enqueue_admin_assets');

function ulp_enqueue_admin_assets(string $hook): void
{
    global $ulp_page_hooks;

    if ($hook === $ulp_page_hooks['edit_page'] || $hook === $ulp_page_hooks['create_page']) {
        wp_enqueue_style('ulp-form-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-form.css', [], ULP_VERSION);
        wp_enqueue_style('ulp-modal-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/modal.css', [], ULP_VERSION);
        wp_enqueue_script('ulp-script', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/js/user-table.js', [], ULP_VERSION, true);
    }

    if ($hook === $ulp_page_hooks['admin_page']) {
        wp_enqueue_style('ulp-table-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-table.css', [], ULP_VERSION);
        wp_enqueue_style('ulp-table-actions-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-table-actions.css', [], ULP_VERSION);
        wp_enqueue_style('ulp-modal-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/modal.css', [], ULP_VERSION);
        wp_enqueue_script('ulp-script', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/js/user-table.js', [], ULP_VERSION, true);

        wp_localize_script('ulp-script', 'ulp_i18n', [
            'confirmTitle'   => __('Confirm Selection', 'user-list-plugin'),
            'cancelBtn'      => __('Cancel', 'user-list-plugin'),
            'deleteBtn'      => __('Delete', 'user-list-plugin'),
            'confirmMessage' => __('Are you sure you want to delete %d user(s)?', 'user-list-plugin')
        ]);
    }
}


