<?php

// PSR-12: declare(strict_types=1) should be added right after <?php
// PSR-12: All return types use ' : type' — PSR-12 requires no space before the colon: 'func(): type'
// PSR-1: File mixes side-effect calls (add_action) with function declarations.
//        PSR-1 says a file should either declare symbols OR cause side-effects, not both.

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'ulp_add_settings_page');

function ulp_add_settings_page() : void
{
    add_options_page(
        __('User List Plugin Settings', 'user-list-plugin'),
        __('User List Plugin', 'user-list-plugin'),
        'manage_options',
        'ulp-settings',
        'ulp_render_settings_page'
    );
}

function ulp_render_settings_page() : void
{
    if (isset($_POST['ulp_save_settings']) && check_admin_referer('ulp_settings_nonce')) {
        $source = sanitize_text_field($_POST['ulp_data_source']);
        if (!in_array($source, ['local', 'gorest'], true)) {
            $source = 'local';
        }

        $token = sanitize_text_field($_POST['ulp_gorest_token']);

        $days = absint($_POST['ulp_update_interval']);

        if (!is_numeric($days)) {
            echo '<div class="notice notice-error">' . esc_html__('Incorrect number of days', 'user-list-plugin') . '</div>';
        } else {
            update_option('ulp_data_source', $source);

            if (!empty($token) && !ulp_encrypt_and_save_token($token)) {
                echo '<div class="notice notice-error">' . esc_html__('Could not update token', 'user-list-plugin') . '</div>';
            }

            update_option('ulp_update_interval', $days);

            echo '<div class="notice notice-success">' . esc_html__('Saved', 'user-list-plugin') . '</div>';
        }
    }

    $current_source = get_option('ulp_data_source', 'local');
    ?>

    <div class="wrap">
        <h1><?php esc_html_e('User List Plugin Settings', 'user-list-plugin'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('ulp_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Data Source:', 'user-list-plugin'); ?></th>
                    <td>
                        <select name="ulp_data_source">
                            <option value="local" <?php selected($current_source, 'local'); ?>><?php esc_html_e('Local Database', 'user-list-plugin'); ?></option>
                            <option value="gorest" <?php selected($current_source, 'gorest'); ?>><?php esc_html_e('REST API GoREST', 'user-list-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('GoREST Token:', 'user-list-plugin'); ?></th>
                    <td>
                        <input type="password" name="ulp_gorest_token" class="regular-text" value="" placeholder="<?php esc_attr_e('Enter your GoREST API token', 'user-list-plugin'); ?>"/>
                        <?php if (ulp_get_decrypted_token()): ?>
                            <p class="description"><?php esc_html_e('Token is currently set. Enter new token to replace it.', 'user-list-plugin'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Days since last user update:', 'user-list-plugin'); ?></th>
                    <td>
                        <input type="text" name="ulp_update_interval" class="regular-text" value="<?php echo esc_attr(get_option('ulp_update_interval')); ?>"/>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="ulp_save_settings" class="button-primary">
                    <?php esc_html_e('Save settings', 'user-list-plugin'); ?>
                </button>
            </p>
        </form>
    </div>

    <?php
}