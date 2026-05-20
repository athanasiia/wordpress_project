<?php

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

if (!defined('ABSPATH')) {
    exit;
}

define('ULP_DB_VERSION', '1.0.0');

require_once plugin_dir_path(__FILE__) . 'includes/core/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/gorest-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/encryption.php';
require_once plugin_dir_path(__FILE__) . 'includes/core/cron-jobs.php';

require_once plugin_dir_path(__FILE__) . 'includes/admin/pages/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/pages/admin-create.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/pages/admin-edit.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/pages/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/partials/inactive-users-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/partials/modals.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/partials/user-form.php';

require_once plugin_dir_path(__FILE__) . 'includes/frontend/shortcode.php';

require_once plugin_dir_path(__FILE__) . 'includes/helpers/countries.php';

register_activation_hook(__FILE__, 'ulp_activate_plugin');

add_action('plugins_loaded', 'ulp_check_db_version');

function ulp_check_db_version(): void
{
    $current_version = get_option('ulp_db_version', '1.0.0');

    if (version_compare($current_version, ULP_DB_VERSION, '<')) {
        ulp_create_table();
        update_option('ulp_db_version', ULP_DB_VERSION);
    }
}

function ulp_activate_plugin(): void
{
    ulp_create_table();
    update_option('ulp_db_version', ULP_DB_VERSION);
    ulp_activate_cron();
}

register_deactivation_hook(__FILE__, 'ulp_deactivate_plugin');

function ulp_deactivate_plugin(): void
{
    ulp_deactivate_cron();
}

add_filter('ulp_add_icons', 'ulp_add_icons');

function ulp_add_icons(string $status): string
{
    return esc_html( $status ) . ( $status === 'active' ? ' &#10687;' : ' &#10686;' );
}

add_action('admin_enqueue_scripts', 'ulp_enqueue_admin_assets');
add_action('wp_enqueue_scripts', 'ulp_enqueue_frontend_assets');

function ulp_enqueue_admin_assets($hook): void
{
    wp_enqueue_style('ulp-common-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0');

    if ($hook === 'admin_page_ulp-user-edit' || $hook === 'user-list_page_ulp-user-create') {
        wp_enqueue_style('ulp-form-style', plugin_dir_url(__FILE__) . 'assets/css/user-form.css', [], '1.0');
        wp_enqueue_style('ulp-modal-style', plugin_dir_url(__FILE__) . 'assets/css/modal.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(__FILE__) . 'assets/js/user-table.js', [], '1.0', true);
    }

    if ($hook === 'toplevel_page_ulp-users') {
        wp_enqueue_style('ulp-table-style', plugin_dir_url(__FILE__) . 'assets/css/user-table.css', [], '1.0');
        wp_enqueue_style('ulp-table-actions-style', plugin_dir_url(__FILE__) . 'assets/css/user-table-actions.css', [], '1.0');
        wp_enqueue_style('ulp-modal-style', plugin_dir_url(__FILE__) . 'assets/css/modal.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(__FILE__) . 'assets/js/user-table.js', [], '1.0', true);
    }
}

function ulp_enqueue_frontend_assets(): void
{
    global $post;

    wp_enqueue_style('ulp-common-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0');

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'user_list')) {
        wp_enqueue_style('ulp-table-style', plugin_dir_url(__FILE__) . 'assets/css/user-table.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(__FILE__) . 'assets/js/user-table.js', [], '1.0', true);
    }
}