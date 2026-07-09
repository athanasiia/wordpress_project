<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_sort_buttons(array $data): string
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
            <?php esc_html_e('Name', 'user-list-plugin'); ?> <?php echo $sort_field === 'name' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg($sort_params['email'], $base_url)); ?>" class="ulp-sort-button">
            <?php esc_html_e('Email', 'user-list-plugin'); ?> <?php echo $sort_field === 'email' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg($sort_params['id'], $base_url)); ?>" class="ulp-sort-button">
            <?php esc_html_e('ID', 'user-list-plugin'); ?> <?php echo $sort_field === 'id' ? ($sort_order === 'asc' ? '↑' : '↓') : '↕'; ?>
        </a>
    </div>
    <?php
    return ob_get_clean();
}
