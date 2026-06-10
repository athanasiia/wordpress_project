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

add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\ulp_enqueue_admin_assets');
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\ulp_enqueue_frontend_assets');

function ulp_enqueue_admin_assets($hook): void
{
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

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'user_list')) {
        wp_enqueue_style('ulp-table-style', plugin_dir_url(__FILE__) . 'assets/css/user-table.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(__FILE__) . 'assets/js/user-table.js', [], '1.0', true);
    }
}