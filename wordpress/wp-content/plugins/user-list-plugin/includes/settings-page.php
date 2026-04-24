<?php

add_action('admin_menu', 'ulp_add_settings_page');

function ulp_add_settings_page() : void
{
    add_options_page(
        'User List Plugin Settings',
        'User List Plugin',
        'manage_options',
        'ulp-settings',
        'ulp_render_settings_page'
    );
}

function ulp_render_settings_page() : void
{
    if (isset($_POST['ulp_save_settings']) && check_admin_referer('ulp_settings_nonce')) {
        $source = sanitize_text_field($_POST['ulp_data_source']);
        $token = sanitize_text_field($_POST['ulp_gorest_token']);

        update_option('ulp_data_source', $source);
        update_option('ulp_gorest_token', $token);

        echo '<div class="notice notice-success"> Saved </div>';
    }

    $current_source = get_option('ulp_data_source', 'local');
    ?>

    <div class="wrap">
        <h1>User List Plugin Settings</h1>
        <form method="post">
            <?php wp_nonce_field('ulp_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Data Source:</th>
                    <td>
                        <select name="ulp_data_source">
                            <option value="local" <?php selected($current_source, 'local'); ?>>Local Database</option>
                            <option value="gorest" <?php selected($current_source, 'gorest'); ?>>REST API GoREST</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">GoREST Token:</th>
                    <td>
                        <input type="text" name="ulp_gorest_token" class="regular-text"/>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="ulp_save_settings" class="button-primary">
                    Save settings
                </button>
            </p>
        </form>
    </div>

    <?php
}