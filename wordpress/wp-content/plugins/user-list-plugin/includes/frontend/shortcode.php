<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_user_list_shortcode($atts): false | string
{
    $data = ulp_get_user_list_data();

    ob_start();
    ?>

    <div class="ulp-users-container">
        <div class="ulp-filters-panel">
            <?php echo ulp_render_search_form($data); ?>
            <?php echo ulp_render_filter_controls($data); ?>
            <?php echo ulp_render_sort_buttons($data); ?>
        </div>

        <?php echo ulp_render_hidden_filter_form($data); ?>

        <div>
            <?php echo ulp_render_user_table($data); ?>
            <?php echo ulp_render_pagination($data); ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

function ulp_render_search_form($data): string
{
    $base_url = get_permalink();
    $search_term = $data['search_term'];

    ob_start();
    ?>
    <div class="ulp-search-box">
        <form method="get" action="<?php echo esc_url($base_url); ?>">
            <input type="text" placeholder="<?php esc_attr_e('Search by name...', 'user-list-plugin'); ?>"
                   name="search" value="<?php echo esc_attr($search_term); ?>" class="ulp-search-input" />
            <?php echo ulp_render_hidden_fields_except(['search', 'user_page']); ?>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function ulp_render_filter_controls($data): string
{
    ob_start();
    ?>
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
    <?php
    return ob_get_clean();
}

function ulp_render_sort_buttons($data): string
{
    $base_url = get_permalink();
    $sort_field = $data['sort_field'];
    $sort_order = $data['sort_order'];
    $sort_params = $data['sort_params'];

    ob_start();
    ?>
    <div class="ulp-sort-buttons">
        <span><?php esc_html_e('Sort by:', 'user-list-plugin'); ?></span>
        <a href="<?php echo esc_url(add_query_arg($sort_params['name'], $base_url)); ?>" class="ulp-sort-button">
            Name <?php echo $sort_field === 'name' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg($sort_params['email'], $base_url)); ?>" class="ulp-sort-button">
            Email <?php echo $sort_field === 'email' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg($sort_params['id'], $base_url)); ?>" class="ulp-sort-button">
            ID <?php echo $sort_field === 'id' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

function ulp_render_hidden_filter_form($data): string
{
    $base_url = get_permalink();
    $filter_status = $data['filter_status'];
    $filter_gender = $data['filter_gender'];

    ob_start();
    ?>
    <form id="filterForm" method="get" action="<?php echo esc_url($base_url); ?>">
        <?php echo ulp_render_hidden_fields_except(['filter_status', 'filter_gender', 'user_page']); ?>
        <input type="hidden" name="filter_status" id="status_input" value="<?php echo esc_attr($filter_status); ?>" />
        <input type="hidden" name="filter_gender" id="gender_input" value="<?php echo esc_attr($filter_gender); ?>" />
    </form>
    <?php
    return ob_get_clean();
}

function ulp_render_user_table($data): string
{
    $users = $data['users'];
    $source = $data['source'];

    ob_start();
    ?>
    <table class="ulp-users-table">
        <thead>
        <tr>
            <th><?php esc_html_e('ID', 'user-list-plugin'); ?></th>
            <th><?php esc_html_e('Email', 'user-list-plugin'); ?></th>
            <th><?php esc_html_e('Name', 'user-list-plugin'); ?></th>
            <?php if($source === 'local'): ?>
                <th><?php esc_html_e('City', 'user-list-plugin'); ?></th>
                <th><?php esc_html_e('Country', 'user-list-plugin'); ?></th>
            <?php endif; ?>
            <th><?php esc_html_e('Gender', 'user-list-plugin'); ?></th>
            <th><?php esc_html_e('Status', 'user-list-plugin'); ?></th>
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
    <?php
    return ob_get_clean();
}

function ulp_render_pagination($data): string
{
    $current_url = home_url(wp_unslash($_SERVER['REQUEST_URI']));
    $page_number = $data['page_number'];
    $items_per_page = $data['items_per_page'];
    $users = $data['users'];

    ob_start();
    ?>
    <div class="ulp-table-pages">
        <?php if($page_number > 1): ?>
            <a href="<?php echo esc_url(add_query_arg('user_page', $page_number - 1, $current_url)); ?>" class="ulp-table-button">&#10094;</a>
        <?php endif; ?>

        <p><?php echo esc_html($page_number); ?></p>

        <?php if(count($users) === $items_per_page): ?>
            <a href="<?php echo esc_url(add_query_arg('user_page', $page_number + 1, $current_url)); ?>" class="ulp-table-button">&#10095;</a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function ulp_render_hidden_fields_except($exclude_keys): string
{
    $output = '';
    foreach($_GET as $key => $value) {
        if(!in_array($key, $exclude_keys)) {
            $output .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
        }
    }
    return $output;
}