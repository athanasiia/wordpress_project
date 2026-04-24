<?php
/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

function ulp_gorest_request(string $method, string $endpoint = '', array $data = null) : array | WP_Error
{
    $url = 'https://gorest.in/public/v2/users' . $endpoint;

    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    $token = get_option('ulp_gorest_token');
    if (!empty($token)) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    $args = [
        'method' => $method,
        'headers' => $headers,
    ];

    if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($response_code < 200 || $response_code >= 300) {
        $error_message = isset($data->error->message) ? $data->message : 'Unknown API error';
        return new WP_Error('api_error', $error_message, ['status' => $response_code]);
    }

    return $data ?? [];
}

function ulp_get_gorest_users(array $filters) : array
{
    $endpoint = '?per_page=19';
    $endpoint .= apply_request_filters($filters);

    $response = ulp_gorest_request('GET', $endpoint);

    if (is_wp_error($response)) {
        return [];
    }

    $sorted_users = apply_user_sorting($response, $filters['sort'], $filters['order']);
    $paginated_users = array_slice($sorted_users, $filters['offset'], $filters['limit']);

    return array_map(function($user) {
        $user = (array)$user;

        return [
            'id' => (int)$user['id'],
            'email' => (string)$user['email'],
            'name' => (string)$user['name'],
            'gender' => (string)$user['gender'],
            'status' => (string)$user['status']
        ];
    }, $paginated_users);
}

function ulp_get_gorest_user(int $id) : array
{
    $response = ulp_gorest_request('GET', '/' . $id);

    if (is_wp_error($response)) {
        return [];
    }

    return $response;
}

function ulp_create_gorest_user(array $data) : int | false | WP_Error
{
    if (empty(get_option('ulp_gorest_token'))) {
        return new WP_Error('no_token', 'Missing API token');
    }

    $response = ulp_gorest_request('POST', '', $data);

    if (is_wp_error($response)) {
        return $response;
    }

    return $response['id'] ?? false;
}

function ulp_update_gorest_user(int $id, array $data) : int | false | WP_Error
{
    if (empty(get_option('ulp_gorest_token'))) {
        return new WP_Error('no_token', 'Missing API token');
    }

    $response = ulp_gorest_request('PUT', '/' . $id, $data);
    if (is_wp_error($response)) {
        return $response;
    }

    return $response['id'] ?? false;
}

function ulp_delete_gorest_users(array $ids) : int | false | WP_Error
{
    if (empty(get_option('ulp_gorest_token'))) {
        return new WP_Error('no_token', 'Missing API token');
    }

    $deleted_users = 0;
    foreach($ids as $id) {

        $response = ulp_gorest_request('DELETE', '/' . $id);
        if (!is_wp_error($response)) {
            $deleted_users++;
        }
    }

    return $deleted_users > 0 ? $deleted_users : false;
}

function apply_request_filters(array $filters) : string
{
    $endpoint = '';
    if ($filters['status'] && $filters['status'] !== 'all') {
        $endpoint .= '&status=' . $filters['status'];
    }

    if ($filters['gender'] && $filters['gender'] !== 'all') {
        $endpoint .= '&gender=' . $filters['gender'];
    }

    if ($filters['search']) {
        $endpoint .= '&name=' . urlencode($filters['search']);
    }

    return $endpoint;
}

function apply_user_sorting(array $users, string $sort, string $order) : array
{
    if (empty($users)) {
        return $users;
    }

    $column = array_column($users, $sort);

    if ($sort === 'id') {
        $column = array_map('intval', $column);
    } else {
        $column = array_map('strval', $column);
    }

    $order_flag = ($order === 'asc') ? SORT_ASC : SORT_DESC;

    array_multisort($column, $order_flag, $users);

    return $users;
}