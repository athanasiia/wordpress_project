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
    global $ulp_page_hooks;

    $ulp_page_hooks['admin_page'] = add_menu_page(
            __('Manage Users', 'user-list-plugin'),
            __('User List', 'user-list-plugin'),
            'manage_options',
            'ulp-users',
            __NAMESPACE__ . '\\ulp_render_admin_page'
    );
}

function ulp_render_admin_page(): void
{
    $delete_result = ulp_handle_delete();
    $data = ulp_get_user_list_data(true);
    $current_url = add_query_arg(wp_unslash($_GET), admin_url('admin.php?page=ulp-users'));

    if ($data['inactive_users']) {
        echo ulp_render_inactive_users_block($data['inactive_users']);
    }

    ?>

    <div class="ulp-users-container">

        <?php echo ulp_render_action_panel(); ?>

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

        <?php echo '<form id="deleteUsersForm" method="post" action="">';
            wp_nonce_field('ulp_delete_nonce');
            echo '<input type="hidden" name="ulp_delete_submit" value="1" />';
            echo '<input type="hidden" name="delete_ids" id="deleteIdsInput" value="" />';
            echo '</form>';
            echo '<div id="confirmModalContainer" style="display: none;"></div>';
            if (!empty($delete_result)) {
                echo ulp_render_result_modal($delete_result);
            }
        ?>
    </div>
    <?php
}