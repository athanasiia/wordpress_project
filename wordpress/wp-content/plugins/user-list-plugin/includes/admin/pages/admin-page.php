<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_add_admin_menu(): void
{
    add_menu_page(
            __('Manage Users', 'user-list-plugin'),
            __('User List', 'user-list-plugin'),
            'manage_options',
            'ulp-users',
            __NAMESPACE__ . '\\ulp_render_admin_page'
    );
}

function ulp_get_admin_page_data(): array
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
            'status' => $filter_status,
            'gender' => $filter_gender,
            'search' => !empty($search_term) ? $search_term : '',
            'sort' => $sort_field,
            'order' => $sort_order,
            'limit' => $items_per_page,
            'offset' => ($page_number - 1) * $items_per_page
    );

    $filters = array_filter($filters, function($value) {
        return $value !== null;
    });

    if ($source === 'local') {
        $users = ulp_get_local_users($filters);
        $inactive_users = get_option('ulp_inactive_users_list');
    } else {
        $users = ulp_get_gorest_users($filters);
    }

    $name_sort_params = array('sort_field' => 'name', 'sort_order' => $sort_field === 'name' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1);
    $email_sort_params = array('sort_field' => 'email', 'sort_order' => $sort_field === 'email' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1);
    $id_sort_params = array('sort_field' => 'id', 'sort_order' => $sort_field === 'id' && $sort_order === 'asc' ? 'desc' : 'asc', 'user_page' => 1);

    return array(
            'result' => ulp_handle_delete(),
            'source' => $source,
            'users' => $users,
            'inactive_users' => $inactive_users ?? null,
            'sort_field' => $sort_field,
            'sort_order' => $sort_order,
            'filter_status' => $filter_status,
            'filter_gender' => $filter_gender,
            'search_term' => $search_term,
            'page_number' => $page_number,
            'items_per_page' => $items_per_page,
            'sort_params' => array(
                    'name' => $name_sort_params,
                    'email' => $email_sort_params,
                    'id' => $id_sort_params
            ),
            'base_url' => admin_url('admin.php?page=ulp-users')
    );
}

function ulp_render_admin_page(): void
{
    $data = ulp_get_admin_page_data();

    if ($data['inactive_users']) {
        echo ulp_render_inactive_users_block($data['inactive_users']);
    }
    ?>
    <div class="ulp-users-container">
        <div class="ulp-action-panel">
            <button class="ulp-table-button" id="createNewUserBtn">
                <?php esc_html_e('Create New User', 'user-list-plugin'); ?>
            </button>
            <div class="ulp-delete-panel">
                <button class="ulp-table-button ulp-delete-button" id="deleteSelectedBtn" disabled>
                    <?php esc_html_e('Delete Selected', 'user-list-plugin'); ?> (<span id="selectedCount">0</span>)
                </button>
                <div>
                    <label><?php esc_html_e('Check all', 'user-list-plugin'); ?></label>
                    <input type="checkbox" id="selectAllCheckbox" />
                </div>
            </div>
        </div>

        <div class="ulp-filters-panel">
            <div class="ulp-search-box">
                <form method="get" action="">
                    <input type="text" placeholder="<?php esc_attr_e('Search by name...', 'user-list-plugin'); ?>" name="search" value="<?php echo esc_attr($data['search_term']); ?>" class="ulp-search-input" />
                    <?php foreach($_GET as $key => $value): ?>
                        <?php if($key !== 'search' && $key !== 'user_page'): ?>
                            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" />
                        <?php endif; ?>
                    <?php endforeach; ?>
                </form>
            </div>

            <div class="ulp-filter-group">
                <label><?php esc_html_e('Status:', 'user-list-plugin'); ?></label>
                <select class="ulp-filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="all" <?php echo $data['filter_status'] === 'all' ? 'selected' : ''; ?>><?php esc_html_e('All', 'user-list-plugin'); ?></option>
                    <option value="active" <?php echo $data['filter_status'] === 'active' ? 'selected' : ''; ?>><?php esc_html_e('Active', 'user-list-plugin'); ?></option>
                    <option value="inactive" <?php echo $data['filter_status'] === 'inactive' ? 'selected' : ''; ?>><?php esc_html_e('Inactive', 'user-list-plugin'); ?></option>
                </select>
            </div>

            <div class="ulp-filter-group">
                <label><?php esc_html_e('Gender:', 'user-list-plugin'); ?></label>
                <select class="ulp-filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="all" <?php echo $data['filter_gender'] === 'all' ? 'selected' : ''; ?>><?php esc_html_e('All', 'user-list-plugin'); ?></option>
                    <option value="male" <?php echo $data['filter_gender'] === 'male' ? 'selected' : ''; ?>><?php esc_html_e('Male', 'user-list-plugin'); ?></option>
                    <option value="female" <?php echo $data['filter_gender'] === 'female' ? 'selected' : ''; ?>><?php esc_html_e('Female', 'user-list-plugin'); ?></option>
                </select>
            </div>

            <div class="ulp-sort-buttons">
                <span><?php esc_html_e('Sort by:', 'user-list-plugin'); ?></span>
                <a href="<?php echo esc_url(add_query_arg($data['sort_params']['name'], $data['base_url'])); ?>" class="ulp-sort-button">
                    <?php esc_html_e('Name', 'user-list-plugin'); ?> <?php echo $data['sort_field'] === 'name' ? ($data['sort_order'] === 'asc' ? '↑' : '↓') : '↕'; ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg($data['sort_params']['email'], $data['base_url'])); ?>" class="ulp-sort-button">
                    <?php esc_html_e('Email', 'user-list-plugin'); ?> <?php echo $data['sort_field'] === 'email' ? ($data['sort_order'] === 'asc' ? '↑' : '↓') : '↕'; ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg($data['sort_params']['id'], $data['base_url'])); ?>" class="ulp-sort-button">
                    <?php esc_html_e('ID', 'user-list-plugin'); ?> <?php echo $data['sort_field'] === 'id' ? ($data['sort_order'] === 'asc' ? '↑' : '↓') : '↕'; ?>
                </a>
            </div>
        </div>

        <form id="filterForm" method="get" action="">
            <?php foreach($_GET as $key => $value): ?>
                <?php if($key !== 'filter_status' && $key !== 'filter_gender' && $key !== 'user_page'): ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" />
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="filter_status" id="status_input" value="<?php echo esc_attr($data['filter_status']); ?>" />
            <input type="hidden" name="filter_gender" id="gender_input" value="<?php echo esc_attr($data['filter_gender']); ?>" />
        </form>

        <div>
            <table class="ulp-users-table">
                <thead>
                <tr>
                    <th></th>
                    <th><?php esc_html_e('Delete', 'user-list-plugin'); ?></th>
                    <th><?php esc_html_e('ID', 'user-list-plugin'); ?></th>
                    <th><?php esc_html_e('Email', 'user-list-plugin'); ?></th>
                    <th><?php esc_html_e('Name', 'user-list-plugin'); ?></th>
                    <?php if($data['source'] === 'local'): ?>
                        <th><?php esc_html_e('City', 'user-list-plugin'); ?></th>
                        <th><?php esc_html_e('Country', 'user-list-plugin'); ?></th>
                    <?php endif; ?>
                    <th><?php esc_html_e('Gender', 'user-list-plugin'); ?></th>
                    <th><?php esc_html_e('Status', 'user-list-plugin'); ?></th>
                    <?php if($data['source'] === 'local'): ?>
                        <th><?php esc_html_e('Created', 'user-list-plugin'); ?></th>
                        <th><?php esc_html_e('Updated', 'user-list-plugin'); ?></th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($data['users'] as $user): ?>
                    <tr>
                        <td>
                            <button class="ulp-table-button" data-id="<?php echo esc_attr($user['id']); ?>">
                                <?php esc_html_e('Edit', 'user-list-plugin'); ?>
                            </button>
                        </td>
                        <td>
                            <input type="checkbox" class="ulp-user-checkbox" data-id="<?php echo esc_attr($user['id']); ?>" />
                        </td>
                        <td><?php echo esc_html($user['id']); ?></td>
                        <td><?php echo esc_html($user['email']); ?></td>
                        <td><?php echo esc_html($user['name']); ?></td>
                        <?php if($data['source'] === 'local'): ?>
                            <td><?php echo isset($user['city']) ? esc_html($user['city']) : ''; ?></td>
                            <td><?php echo isset($user['country']) ? esc_html($user['country']) : ''; ?></td>
                        <?php endif; ?>
                        <td><?php echo esc_html($user['gender']); ?></td>
                        <td><?php echo wp_kses_post(apply_filters('ulp_add_icons', $user['status'])); ?></td>
                        <?php if($data['source'] === 'local'): ?>
                            <td><?php echo isset($user['created']) ? esc_html($user['created']) : ''; ?></td>
                            <td><?php echo isset($user['updated']) ? esc_html($user['updated']) : ''; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="ulp-table-pages">
                <?php if($data['page_number'] > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg('user_page', $data['page_number'] - 1, $data['base_url'])); ?>" class="ulp-table-button">&#10094;</a>
                <?php endif; ?>

                <span><?php echo esc_html($data['page_number']); ?></span>

                <?php if(count($data['users']) === $data['items_per_page']): ?>
                    <a href="<?php echo esc_url(add_query_arg('user_page', $data['page_number'] + 1, $data['base_url'])); ?>" class="ulp-table-button">&#10095;</a>
                <?php endif; ?>
            </div>
        </div>

        <?php echo '<form id="deleteUsersForm" method="post" action="">';
        wp_nonce_field('ulp_delete_nonce');
        echo '<input type="hidden" name="ulp_delete_submit" value="1" />';
        echo '<input type="hidden" name="delete_ids" id="deleteIdsInput" value="" />';
        echo '</form>';
        echo '<div id="confirmModalContainer" style="display: none;"></div>';?>

        <?php if (isset($data['result']['success'])) echo ulp_render_result_modal($data['result']); ?>
    </div>
    <?php
}