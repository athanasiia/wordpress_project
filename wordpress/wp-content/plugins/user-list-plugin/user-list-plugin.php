<?php

/**
 * Plugin Name: User List Plugin
 * Version: 1.0
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

wp_enqueue_style('user-list-plugin', plugin_dir_url(__FILE__) . '../../themes/twentytwentyfive-child/style.css');
wp_enqueue_script('user-list-plugin', plugin_dir_url(__FILE__) . 'assets/js/user-table.js');