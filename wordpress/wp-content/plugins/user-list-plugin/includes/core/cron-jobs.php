<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

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
    $current_date = date_create(wp_date("Y-m-d"));

    foreach ($users as $user) {
        $user_update_date = date_create($user['updated']);
        if (date_diff($user_update_date, $current_date)->format('%a') > $update_interval) {
            $no_updates_users[] = $user['id'];
        }
    }

    update_option('ulp_inactive_users_list', $no_updates_users, false);
}