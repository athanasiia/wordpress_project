<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(ULP_PLUGIN_FILE, __NAMESPACE__ . '\\ulp_activate_plugin');
add_action('plugins_loaded', __NAMESPACE__ . '\\ulp_check_db_version');
register_deactivation_hook(ULP_PLUGIN_FILE, __NAMESPACE__ . '\\ulp_deactivate_plugin');
add_action('plugins_loaded', __NAMESPACE__ . '\\ulp_load_textdomain');
register_uninstall_hook(ULP_PLUGIN_FILE, __NAMESPACE__ . '\\ulp_delete_plugin');

function ulp_load_textdomain(): void
{
    load_plugin_textdomain(
        'user-list-plugin',
        false,
        dirname(plugin_basename(ULP_PLUGIN_FILE)) . '/languages'
    );
}

function ulp_delete_plugin(): void
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ulp_users';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    $options_to_delete = [
        'ulp_data_source',
        'ulp_inactive_users_list',
        'ulp_update_interval',
        'ulp_db_version',
        'ulp_gorest_token_encrypted'
    ];

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    wp_clear_scheduled_hook('ulp_check_inactive_users');
}