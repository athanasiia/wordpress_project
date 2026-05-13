<?php
/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('ulp_check_inactive_users', 'ulp_check_inactive_users');

function ulp_activate_cron(): void
{
    if (!wp_next_scheduled('ulp_check_inactive_users')) {
        wp_schedule_event( time(), 'daily', 'ulp_check_inactive_users' );
    }
}

function ulp_deactivate_cron(): void
{
    $timestamp = wp_next_scheduled('ulp_check_inactive_users');
    if ($timestamp) {
        wp_unschedule_event( $timestamp, 'ulp_check_inactive_users' );
    }
}

function ulp_check_inactive_users(): void
{
    $source = get_option('ulp_data_source');
    $filters = array(
        'status' => 'all',
        'gender' => 'all',
        'search' => '',
        'sort' => 'id',
        'order' => 'asc',
        'limit' => 100,
        'offset' => 0
    );

    if ($source !== 'local') {
        return;
    }

    $users = ulp_get_local_users($filters);

    $update_interval = get_option('ulp_update_interval');
    $no_updates_users = array();
    // ISSUE [MED-03]: date() uses the PHP server timezone, not the WordPress site timezone.
    // Use wp_date('Y-m-d') so the date respects the timezone set in Settings > General.
    $current_date = date_create(date("Y-m-d"));


    foreach ($users as $user) {
        // ISSUE [MED-03]: $user['updated'] is NULL for every existing row because the
        // 'updated' column does not exist in the schema (see HIGH-03 in database.php).
        // date_create(null) returns a DateTime set to the current date, so date_diff()
        // returns 0 days, meaning NO user ever appears inactive. Fix the schema first.
        $user_update_date = date_create($user['updated']);
        if (date_diff($user_update_date, $current_date)->format('%a') > $update_interval) {
            $no_updates_users[] = $user['id'];
        }
    }

    // ISSUE [MED-13]: update_option() defaults to autoload = true. This option may hold
    // a large array of user IDs and will be loaded on every single WordPress page request,
    // including frontend pages that have nothing to do with this plugin.
    update_option('ulp_inactive_users_list', $no_updates_users);
}