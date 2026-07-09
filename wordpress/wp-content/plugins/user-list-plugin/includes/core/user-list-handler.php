<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_get_user_list_data(bool $include_inactive): array
{
    $source = get_option('ulp_data_source', 'local');
    $items_per_page = 5;

    $sort_field = isset($_GET['sort_field']) ? sanitize_text_field(wp_unslash($_GET['sort_field'])) : 'id';
    $sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'desc' ? 'desc' : 'asc';
    $filter_status = isset($_GET['filter_status']) ? sanitize_text_field(wp_unslash($_GET['filter_status'])) : 'all';
    $filter_gender = isset($_GET['filter_gender']) ? sanitize_text_field(wp_unslash($_GET['filter_gender'])) : 'all';
    $search_term = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
    $page_number = isset($_GET['user_page']) ? (int)wp_unslash($_GET['user_page']) : 1;

    $filters = [
        'status' => $filter_status !== 'all' ? $filter_status : null,
        'gender' => $filter_gender !== 'all' ? $filter_gender : null,
        'search' => !empty($search_term) ? $search_term : null,
        'sort' => $sort_field,
        'order' => $sort_order,
        'limit' => $items_per_page,
        'offset' => ($page_number - 1) * $items_per_page,
    ];

    $filters = array_filter($filters, function ($value) {
        return $value !== null;
    });

    if ($source === 'local') {
        $users_data = ulp_get_local_users($filters);
        $users = $users_data['users'];
        $total_pages = $users_data['total_pages'];
        $inactive_users = $include_inactive ? get_option('ulp_inactive_users_list') : null;
    } else {
        $users_data = ulp_get_gorest_users($filters);
        $users = $users_data['users'];
        $total_pages = $users_data['total_pages'];
    }

    $sort_params = [
        'name' => ['sort_field' => 'name', 'sort_order' => $sort_field === 'name' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1],
        'email' => ['sort_field' => 'email', 'sort_order' => $sort_field === 'email' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1],
        'id' => ['sort_field' => 'id', 'sort_order' => $sort_field === 'id' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1]
    ];

    $result = [
        'users' => $users,
        'source' => $source,
        'items_per_page' => $items_per_page,
        'page_number' => $page_number,
        'total_pages' => $total_pages,
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'filter_status' => $filter_status,
        'filter_gender' => $filter_gender,
        'search_term' => $search_term,
        'sort_params' => $sort_params,
        'context' => is_admin() ? 'admin' : 'frontend'
    ];

    if ($include_inactive) {
        $result['inactive_users'] = $inactive_users ?? null;
    }

    return $result;
}
