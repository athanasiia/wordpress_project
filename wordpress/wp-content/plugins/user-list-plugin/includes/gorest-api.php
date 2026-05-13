<?php
/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// ISSUE [MED-07]: The parameter is named $data (the request body payload). Further down,
// the decoded response body is also stored in a local variable named $data, silently
// overwriting the parameter. Rename one of them to avoid the shadow.
function ulp_gorest_request(string $method, string $endpoint = '', array $data = null) : array | WP_Error
{
    $url = 'https://gorest.in/public/v2/users' . $endpoint;

    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    // ISSUE [HIGH-07]: The token is retrieved from wp_options where it is stored as
    // plaintext. Anyone with database read access (shared hosting, DB breach, another
    // plugin calling get_option) can read the token. Prefer an environment variable or
    // an encrypted store, and never echo the raw option value into HTML.
    $token = get_option('ulp_gorest_token');
    if (!empty($token)) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    $args = [
        'method' => $method,
        'headers' => $headers,
        // ISSUE [MED-09]: No 'timeout' key is set. An unresponsive remote server will
        // block PHP execution for up to WordPress's default (5 s). On an admin page that
        // calls this function on every load, one slow API response stalls the entire page.
        // Add: 'timeout' => 10,
    ];

    if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    // ISSUE [MED-07]: $data here shadows the $data function parameter (the request body).
    $data = json_decode(wp_remote_retrieve_body($response), true);

    if ($response_code < 200 || $response_code >= 300) {
        // ISSUE [MED-06]: json_decode(..., true) returns an associative array, not an object.
        // '$data->error->message' always evaluates to false because you cannot use
        // object-access syntax on an array. The fallback 'Unknown API error' is returned
        // every time, discarding the actual API error message.
        // Additionally, the ternary checks $data->error->message but reads $data->message,
        // which are different keys. Fix: $data['message'] ?? $data['error']['message'] ?? '...'
        $error_message = isset($data->error->message) ? $data->message : 'Unknown API error';
        return new WP_Error('api_error', $error_message, ['status' => $response_code]);
    }

    return $data ?? [];
}

function ulp_get_gorest_users(array $filters) : array
{
    // ISSUE [MED-08]: per_page=19 is an arbitrary magic number that is too small for
    // meaningful pagination. All records are fetched into memory on every call, then sorted
    // and sliced in PHP. Requesting page 3 still downloads pages 1–3 from the API and
    // discards the first two pages worth of data. This does not scale.
    // Sorting should be delegated to the API, or results should be cached in a transient.
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

// ISSUE [HIGH-06]: Function name 'apply_request_filters' lacks the 'ulp_' prefix.
// All global functions in a plugin must be prefixed to prevent fatal "already declared"
// errors when another plugin or theme defines a function with the same name.
// The name also dangerously resembles WordPress's own 'apply_filters()'.
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

// ISSUE [HIGH-06]: Same missing 'ulp_' prefix problem as apply_request_filters above.
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