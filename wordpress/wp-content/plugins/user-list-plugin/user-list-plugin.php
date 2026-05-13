<?php

/**
 * Plugin Name: User List Plugin
 * Version: 1.0
 *
 * ISSUE [LOW-01]: Plugin header is missing required/recommended fields:
 * Description, Author, Text Domain, Domain Path,
 * Requires at least, Requires PHP, License.
 * Without Text Domain, the plugin cannot be translated (no i18n support).
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/gorest-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/modals.php';
require_once plugin_dir_path(__FILE__) . 'includes/countries.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/inactive-users-list.php';

require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-create.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-edit.php';

require_once plugin_dir_path(__FILE__) . 'includes/cron-jobs.php';

register_activation_hook(__FILE__, 'ulp_activate_plugin');

function ulp_activate_plugin(): void
{
    ulp_create_table();
    ulp_activate_cron();
}

register_deactivation_hook(__FILE__, 'ulp_deactivate_plugin');

function ulp_deactivate_plugin(): void
{
    ulp_deactivate_cron();
}

// ISSUE [CRITICAL-01]: wp_enqueue_style/script must never be called at file-include time.
// The WordPress enqueue API is not yet initialised when plugins are loaded.
// Use the 'wp_enqueue_scripts' hook (frontend) or 'admin_enqueue_scripts' hook (admin).
// Additionally, scripts/styles should only be loaded on pages that actually need them,
// not on every single request across the entire site.
wp_enqueue_style('user-list-plugin', plugin_dir_url(__FILE__) . '../../themes/twentytwentyfive-child/style.css');
// ISSUE [CRITICAL-01]: Path hardcodes a sibling directory pointing to a specific child theme.
// The plugin must ship its own stylesheet under assets/css/ and never depend on a theme's file.
// This path breaks on any installation that does not use exactly that child theme.
wp_enqueue_script('user-list-plugin', plugin_dir_url(__FILE__) . 'assets/js/user-table.js');
// ISSUE [LOW-03]: No version argument is passed to wp_enqueue_script.
// Without a version, browser/CDN caches will never be busted when the file changes.