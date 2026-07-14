<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_add_edit_submenu(): void
{
    global $ulp_page_hooks;

    $ulp_page_hooks['edit_page'] = add_submenu_page(
        null,
        __('Edit User', 'user-list-plugin'),
        __('Edit User', 'user-list-plugin'),
        'manage_options',
        'ulp-user-edit',
        __NAMESPACE__ . '\\ulp_render_edit_page'
    );
}

function ulp_get_edit_page_data(): array
{
    $source = get_option('ulp_data_source', 'local');
    $id = (int)wp_unslash($_GET['id'] ?? 0);

    $data = [
            'source' => $source,
            'id' => $id,
            'error' => '',
            'user' => null,
            'result' => null
    ];

    if (empty($id)) {
        $data['error'] = __('No user ID provided', 'user-list-plugin');
        return $data;
    }

    $data['result'] = ulp_handle_edit($id);
    $data['error'] = $data['result']['error'] ?? '';

    if ($source === 'local') {
        $data['user'] = ulp_get_local_user($id);
    } else {
        $data['user'] = ulp_get_gorest_user($id);
    }

    if (empty($data['user'])) {
        $data['error'] = __('Could not get user', 'user-list-plugin');
    }

    return $data;
}

function ulp_render_edit_page(): void
{
    $data = ulp_get_edit_page_data();

    if ($data['error'] && empty($data['user'])) {
        echo '<div>' . esc_html($data['error']) . '</div>';
        return;
    }
    ?>
    <div class="wrap">
        <form method="post" class="ulp-user-form">
            <?php wp_nonce_field('ulp_edit_nonce'); ?>
            <?php echo ulp_render_user_form($data['source'], __('Edit User', 'user-list-plugin'), $data['error'], $data['user']); ?>
            <button class="ulp-submit-button" type="submit" name="ulp_edit_submit"><?php esc_html_e('Submit', 'user-list-plugin'); ?></button>
        </form>
    </div>
    <?php

    if (isset($data['result']['success'])) {
        echo ulp_render_result_modal($data['result']);
    }
}
