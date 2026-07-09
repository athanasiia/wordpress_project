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
    $current_url = home_url(wp_unslash($_SERVER['REQUEST_URI']));
    $data = ulp_get_user_list_data(false);

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
            <?php echo ulp_render_pagination($data, $current_url); ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}