<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_hidden_filter_form(array $data): string
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

function ulp_render_hidden_fields_except(array $exclude_keys): string
{
    $output = '';
    foreach($_GET as $key => $value) {
        if(!is_array($key) && !is_array($value) && !in_array($key, $exclude_keys, true)) {
            $output .= '<input type="hidden" name="' . esc_attr(wp_unslash($key)) . '" value="' . esc_attr(wp_unslash($value)) . '" />';
        }
    }
    return $output;
}