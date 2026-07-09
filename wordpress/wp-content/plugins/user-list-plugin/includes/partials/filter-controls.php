<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_filter_controls(array $data): string
{
    ob_start();
    ?>
    <div class="ulp-filter-group" data-filter-type="status">
        <label for="ulp_filter_status"><?php esc_html_e('Status:', 'user-list-plugin'); ?></label>
        <select class="ulp-filter-select" id="ulp_filter_status" onchange="this.form.submit()" form="filterForm">
            <option value="all" <?php echo $data['filter_status'] === 'all' ? 'selected' : ''; ?>><?php esc_html_e('All', 'user-list-plugin'); ?></option>
            <option value="active" <?php echo $data['filter_status'] === 'active' ? 'selected' : ''; ?>><?php esc_html_e('Active', 'user-list-plugin'); ?></option>
            <option value="inactive" <?php echo $data['filter_status'] === 'inactive' ? 'selected' : ''; ?>><?php esc_html_e('Inactive', 'user-list-plugin'); ?></option>
        </select>
    </div>

    <div class="ulp-filter-group" data-filter-type="gender">
        <label for="ulp_filter_gender"><?php esc_html_e('Gender:', 'user-list-plugin'); ?></label>
        <select class="ulp-filter-select" id="ulp_filter_gender" onchange="this.form.submit()" form="filterForm">
            <option value="all" <?php echo $data['filter_gender'] === 'all' ? 'selected' : ''; ?>><?php esc_html_e('All', 'user-list-plugin'); ?></option>
            <option value="male" <?php echo $data['filter_gender'] === 'male' ? 'selected' : ''; ?>><?php esc_html_e('Male', 'user-list-plugin'); ?></option>
            <option value="female" <?php echo $data['filter_gender'] === 'female' ? 'selected' : ''; ?>><?php esc_html_e('Female', 'user-list-plugin'); ?></option>
        </select>
    </div>
    <?php
    return ob_get_clean();
}