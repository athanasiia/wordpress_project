<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_gorest_request(string $method, string $endpoint = '', ?array $data = null): array | WP_Error
{
    $url = 'https://gorest.in/public/v2/users' . $endpoint;

    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    $token = ulp_get_decrypted_token();
    if (!empty($token)) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    $args = [
        'method' => $method,
        'headers' => $headers,
        'timeout' => 10,
    ];

    if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_data = json_decode(wp_remote_retrieve_body($response), true);

    if ($response_code < 200 || $response_code >= 300) {
        $error_message = $response_data['message'] ?? __('Unknown API error', 'user-list-plugin');
        return new WP_Error('api_error', $error_message, ['status' => $response_code]);
    }

    return $response_data ?? [];
}

function ulp_get_gorest_users(array $filters): array
{
    $all_users = ulp_get_cached_gorest_users();

    if (empty($all_users)) {
        return [];
    }

    $filtered_users = ulp_apply_user_filters($all_users, $filters);
    $sorted_users = ulp_apply_user_sorting($filtered_users, $filters['sort'], $filters['order']);
    $paginated_users = array_slice($sorted_users, $filters['offset'], $filters['limit']);

    $total_pages = ceil(count($sorted_users) / $filters['limit']);

    return [
        'users' => array_map(function($user) {
            $user = (array)$user;
            return [
                'id' => (int)$user['id'],
                'email' => (string)$user['email'],
                'name' => (string)$user['name'],
                'gender' => (string)$user['gender'],
                'status' => (string)$user['status']
            ];
        }, $paginated_users),
        'total_pages' => $total_pages,
    ];
}

function ulp_get_gorest_user(int $id): array
{
    $response = ulp_gorest_request('GET', '/' . $id);

    if (is_wp_error($response)) {
        return [];
    }

    return $response;
}

function ulp_create_gorest_user(array $data): int | false | WP_Error
{
    if (empty(ulp_get_decrypted_token())) {
        return new WP_Error('no_token', __('Missing API token', 'user-list-plugin'));
    }

    $response = ulp_gorest_request('POST', '', $data);

    if (is_wp_error($response)) {
        return $response;
    }

    return $response['id'] ?? false;
}

function ulp_update_gorest_user(int $id, array $data): int | false | WP_Error
{
    if (empty(ulp_get_decrypted_token())) {
        return new WP_Error('no_token', __('Missing API token', 'user-list-plugin'));
    }

    $response = ulp_gorest_request('PUT', '/' . $id, $data);
    if (is_wp_error($response)) {
        return $response;
    }

    return $response['id'] ?? false;
}

function ulp_delete_gorest_users(array $ids): int | false | WP_Error
{
    if (empty(ulp_get_decrypted_token())) {
        return new WP_Error('no_token', __('Missing API token', 'user-list-plugin'));
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

function ulp_get_cached_gorest_users(): array
{
    $token = ulp_get_decrypted_token();
    $token_hash = !empty($token) ? md5($token) : 'no_token';
    $cache_key = 'ulp_gorest_users_' . $token_hash;

    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $all_users = ulp_fetch_all_gorest_users();

    if (!empty($all_users)) {
        set_transient($cache_key, $all_users, 5 * MINUTE_IN_SECONDS);
    }

    return $all_users;
}

function ulp_fetch_all_gorest_users(): array
{
    $endpoint = '?per_page=100';
    $response = ulp_gorest_request('GET', $endpoint);

    if (is_wp_error($response)) {
        return [];
    }

    return $response;
}

function ulp_apply_user_filters(array $users, array $filters): array
{
    return array_filter($users, function($user) use ($filters) {
        $user = (array)$user;

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($user['status'] !== $filters['status']) {
                return false;
            }
        }

        if (!empty($filters['gender']) && $filters['gender'] !== 'all') {
            if ($user['gender'] !== $filters['gender']) {
                return false;
            }
        }

        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $name = strtolower($user['name']);

            if (!str_contains($name, $search)) {
                return false;
            }
        }

        return true;
    });
}

function ulp_apply_user_sorting(array $users, string $sort, string $order): array
{
    if (empty($users)) {
        return $users;
    }

    $allowed_fields = ['id', 'name', 'email'];
    if (!in_array($sort, $allowed_fields, true)) {
        $sort = 'id';
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