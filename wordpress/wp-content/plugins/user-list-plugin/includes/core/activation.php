<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_activate_plugin(): void
{
    ulp_create_table();
    update_option('ulp_db_version', ULP_DB_VERSION, false);
    ulp_activate_cron();
}

function ulp_check_db_version(): void
{
    $current_version = get_option('ulp_db_version', '1.0.0');

    if (version_compare($current_version, ULP_DB_VERSION, '<')) {
        ulp_create_table();
        update_option('ulp_db_version', ULP_DB_VERSION, false);
    }
}