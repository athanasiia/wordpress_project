<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_add_create_submenu(): void
{
    add_submenu_page(
        'ulp-users',
        __('Create User', 'user-list-plugin'),
        __('Create User', 'user-list-plugin'),
        'manage_options',
        'ulp-user-create',
        __NAMESPACE__ . '\\ulp_render_create_page'
    );
}

function ulp_render_create_page(): void
{
    $source = get_option('ulp_data_source', 'local');
    $result = ulp_handle_create();
    $error = $result['error'] ?? '';
    ?>

    <div class="wrap">
        <form method="post" class="ulp-user-form">
            <?php wp_nonce_field('ulp_create_nonce'); ?>
            <?php echo ulp_render_user_form($source, __('Create User', 'user-list-plugin'), $error); ?>
            <button class="ulp-submit-button" type="submit" name="ulp_create_submit"><?php esc_html_e('Submit', 'user-list-plugin'); ?></button>
        </form>
    </div>

    <?php

    if (isset($result['success'])) {
        echo ulp_render_result_modal($result);
    }
}

