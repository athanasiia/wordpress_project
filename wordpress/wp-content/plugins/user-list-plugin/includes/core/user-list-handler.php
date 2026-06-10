<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_get_user_list_data(): array
{
    $source = get_option('ulp_data_source', 'local');
    $items_per_page = 5;

    $sort_field = isset($_GET['sort_field']) ? sanitize_text_field($_GET['sort_field']) : 'id';
    $sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'desc' ? 'desc' : 'asc';
    $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
    $filter_gender = isset($_GET['filter_gender']) ? sanitize_text_field($_GET['filter_gender']) : 'all';
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $page_number = isset($_GET['user_page']) ? (int)$_GET['user_page'] : 1;

    $filters = array(
        'status' => $filter_status !== 'all' ? $filter_status : null,
        'gender' => $filter_gender !== 'all' ? $filter_gender : null,
        'search' => !empty($search_term) ? $search_term : null,
        'sort' => $sort_field,
        'order' => $sort_order,
        'limit' => $items_per_page,
        'offset' => ($page_number - 1) * $items_per_page
    );

    $filters = array_filter($filters, function ($value) {
        return $value !== null;
    });

    if ($source === 'local') {
        $users = ulp_get_local_users($filters);
    } else {
        $users = ulp_get_gorest_users($filters);
    }

    $sort_params = array(
        'name' => array('sort_field' => 'name', 'sort_order' => $sort_field === 'name' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1),
        'email' => array('sort_field' => 'email', 'sort_order' => $sort_field === 'email' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1),
        'id' => array('sort_field' => 'id', 'sort_order' => $sort_field === 'id' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1)
    );

    return array(
        'users' => $users,
        'source' => $source,
        'items_per_page' => $items_per_page,
        'page_number' => $page_number,
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'filter_status' => $filter_status,
        'filter_gender' => $filter_gender,
        'search_term' => $search_term,
        'sort_params' => $sort_params
    );
}
