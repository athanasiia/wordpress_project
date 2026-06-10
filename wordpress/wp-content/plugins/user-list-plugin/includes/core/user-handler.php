<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_handle_create(): array
{
    if(!isset($_POST['ulp_create_submit'])) {
        return [];
    }

    if(!check_admin_referer('ulp_create_nonce')) {
        return ['success' => false, 'error' => __('Invalid WP Token', 'user-list-plugin')];
    }

    $source = get_option('ulp_data_source', 'local');

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $gender = sanitize_text_field($_POST['gender']);
    $status = sanitize_text_field($_POST['status']);

    $user = [];

    if ($source === 'local') {
        $country = sanitize_text_field($_POST['country']);
        $city = sanitize_text_field($_POST['city']);

        $user['country'] = $country;
        $user['city'] = $city;
    }

    $user['name'] = $name;
    $user['email'] = $email;
    $user['gender'] = $gender;
    $user['status'] = $status;

    $user['created'] = wp_date('Y-m-d');
    $user['updated'] = wp_date('Y-m-d');

    $validation_result = ulp_user_form_validation($user, $source);

    if (!empty($validation_result)) {
        return ['success' => false, 'error' => $validation_result];
    }

    if ($source === 'local') {
        $result = ulp_create_local_user($user);

        if ($result) {
            return ['success' => true, 'message' => __('Database user created', 'user-list-plugin')];
        }

        return ['success' => false, 'error' => __('Error creating database user', 'user-list-plugin')];
    }

    $result = ulp_create_gorest_user($user);

    if (is_wp_error($result)) {
        return ['success' => false, 'error' => __('Error creating GoREST user', 'user-list-plugin')];
    }

    return ['success' => true, 'message' => __('GoREST user created', 'user-list-plugin')];
}


function ulp_handle_edit(int $id): array
{
    if(!isset($_POST['ulp_edit_submit'])) {
        return [];
    }

    if(!check_admin_referer('ulp_edit_nonce')) {
        return ['success' => false, 'error' => __('Invalid WP Token', 'user-list-plugin')];
    }

    $source = get_option('ulp_data_source', 'local');

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $gender = sanitize_text_field($_POST['gender']);
    $status = sanitize_text_field($_POST['status']);

    $user = [];
    if ($source === 'local') {
        $country = sanitize_text_field($_POST['country']);
        $city = sanitize_text_field($_POST['city']);

        $user['country'] = $country;
        $user['city'] = $city;
    }

    $user['name'] = $name;
    $user['email'] = $email;
    $user['gender'] = $gender;
    $user['status'] = $status;

    $user['updated'] = wp_date("Y-m-d");

    $validation_result = ulp_user_form_validation($user, $source);

    if (!empty($validation_result)) {
        return ['success' => false, 'error' => $validation_result];
    }

    if ($source === 'local') {
        $result = ulp_update_local_user($id, $user);

        if ($result) {
            return ['success' => true, 'message' => __('Database user updated', 'user-list-plugin')];
        }

        return ['success' => false, 'error' => __('Error updating database user', 'user-list-plugin')];
    }

    $result = ulp_update_gorest_user($id, $user);

    if (is_wp_error($result)) {
        return ['success' => false, 'error' => __('Error updating GoREST user', 'user-list-plugin')];
    }

    return ['success' => true, 'message' => __('GoREST user updated', 'user-list-plugin')];
}

function ulp_handle_delete(): array
{
    if (!isset($_POST['ulp_delete_submit'])) {
        return [];
    }

    if (!check_admin_referer('ulp_delete_nonce')) {
        return ['success' => false, 'error' => __('Invalid WP Token', 'user-list-plugin')];
    }

    $ids_json = sanitize_text_field($_POST['delete_ids']);
    $ids = json_decode($ids_json, true);

    if (empty($ids) || !is_array($ids)) {
        return ['success' => false, 'error' => __('No users selected', 'user-list-plugin')];
    }

    $source = get_option('ulp_data_source', 'local');

    if ($source === 'local') {
        $result = ulp_delete_local_users($ids);

        if ($result) {
            return ['success' => true, 'message' => sprintf(__('Deleted %d database user(s)', 'user-list-plugin'), $result)];
        }

        return ['success' => false, 'error' => __('Error deleting database user(s)', 'user-list-plugin')];
    }

    $result = ulp_delete_gorest_users($ids);

    if (is_wp_error($result)) {
        return ['success' => false, 'error' => __('Error deleting GoREST user(s)', 'user-list-plugin')];
    }

    return ['success' => true, 'message' => sprintf(__('Deleted %d GoREST user(s)', 'user-list-plugin'), $result)];
}