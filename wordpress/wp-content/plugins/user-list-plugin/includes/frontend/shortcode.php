<?php

/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('user_list', 'ulp_render_user_list_shortcode');

function ulp_render_user_list_shortcode($atts): false | string
{
    $source = get_option('ulp_data_source', 'local');
    $items_per_page = 5;

    $sort_field = isset($_GET['sort_field']) ? sanitize_text_field($_GET['sort_field']) : 'id';
    $sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'desc' ? 'desc' : 'asc';
    $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
    $filter_gender = isset($_GET['filter_gender']) ? sanitize_text_field($_GET['filter_gender']) : 'all';
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $page_number = isset($_GET['user_page']) ? intval($_GET['user_page']) : 1;

    $filters = array(
        'status' => $filter_status ?? 'all',
        'gender' => $filter_gender ?? 'all',
        'search' => !empty($search_term) ? $search_term : null,
        'sort' => $sort_field,
        'order' => $sort_order,
        'limit' => $items_per_page,
        'offset' => ($page_number - 1) * $items_per_page
    );

    $filters = array_filter($filters, function($value) {
        return $value !== null;
    });

    if ($source == 'local') {
        $users = ulp_get_local_users($filters);
    } else {
        $users = ulp_get_gorest_users($filters);
    }

    $base_url = get_permalink();
    $current_url = home_url(wp_unslash( $_SERVER['REQUEST_URI']));

    ob_start();
    ?>

    <div class="ulp-users-container">
        <div class="ulp-filters-panel">
            <div class="ulp-search-box">
                <form method="get" action="<?php echo esc_url($base_url); ?>">
                    <input type="text" placeholder="Search by name..." name="search" value="<?php echo esc_attr($search_term); ?>" class="ulp-search-input" />
                    <?php foreach($_GET as $key => $value): ?>
                        <?php if($key !== 'search' && $key !== 'user_page'): ?>
                            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" />
                        <?php endif; ?>
                    <?php endforeach; ?>
                </form>
            </div>

            <div class="ulp-filter-group">
                <label>Status:</label>
                <select class="ulp-filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="ulp-filter-group">
                <label>Gender:</label>
                <select class="ulp-filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="all" <?php echo $filter_gender === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="male" <?php echo $filter_gender === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo $filter_gender === 'female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>

            <div class="ulp-sort-buttons">
                <span>Sort by:</span>
                <a href="<?php echo esc_url(add_query_arg(array('sort_field' => 'name', 'sort_order' => $sort_field === 'name' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1), $base_url)); ?>" class="ulp-sort-button">
                    Name <?php echo $sort_field === 'name' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(array('sort_field' => 'email', 'sort_order' => $sort_field === 'email' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1), $base_url)); ?>" class="ulp-sort-button">
                    Email <?php echo $sort_field === 'email' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(array('sort_field' => 'id', 'sort_order' => $sort_field === 'id' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1), $base_url)); ?>" class="ulp-sort-button">
                    ID <?php echo $sort_field === 'id' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
                </a>
            </div>
        </div>

        <form id="filterForm" method="get" action="<?php echo esc_url($base_url); ?>">
            <?php foreach($_GET as $key => $value): ?>
                <?php if($key !== 'filter_status' && $key !== 'filter_gender' && $key !== 'user_page'): ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" />
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="filter_status" id="status_input" value="<?php echo esc_attr($filter_status); ?>" />
            <input type="hidden" name="filter_gender" id="gender_input" value="<?php echo esc_attr($filter_gender); ?>" />
        </form>

        <div>
            <table class="ulp-users-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <?php if($source === 'local'): ?>
                        <th>City</th>
                        <th>Country</th>
                    <?php endif; ?>
                    <th>Gender</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo esc_html($user['id']); ?></td>
                        <td><?php echo esc_html($user['email']); ?></td>
                        <td><?php echo esc_html($user['name']); ?></td>
                        <?php if($source === 'local'): ?>
                            <td><?php echo isset($user['city']) ? esc_html($user['city']) : ''; ?></td>
                            <td><?php echo isset($user['country']) ? esc_html($user['country']) : ''; ?></td>
                        <?php endif; ?>
                        <td><?php echo esc_html($user['gender']); ?></td>
                        <td><?php echo esc_html($user['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="ulp-table-pages">
                <?php if($page_number > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg('user_page', $page_number - 1, $current_url)); ?>" class="ulp-table-button">&#10094;</a>
                <?php endif; ?>

                <p><?php echo esc_html($page_number); ?></p>

                <?php if(count($users) === $items_per_page): ?>
                    <a href="<?php echo esc_url(add_query_arg('user_page', $page_number + 1, $current_url)); ?>" class="ulp-table-button">&#10095;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}