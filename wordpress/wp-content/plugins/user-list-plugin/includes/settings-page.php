<?php

// ISSUE [CRITICAL-04]: This file is missing the ABSPATH guard that every other include
// has. Without it, the file can be loaded directly via HTTP outside the WordPress
// bootstrap, potentially exposing error messages, path info, or executing code without
// the WP environment. Add: if ( ! defined( 'ABSPATH' ) ) { exit; }

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
        // ISSUE [MED-05]: sanitize_text_field() cleans the string but does not validate it
        // against the set of allowed values ('local', 'gorest'). Any arbitrary string can
        // be stored and will silently break the data-source logic across all other files.
        // Add: if ( ! in_array( $source, [ 'local', 'gorest' ], true ) ) { $source = 'local'; }
        $source = sanitize_text_field($_POST['ulp_data_source']);
        $token = sanitize_text_field($_POST['ulp_gorest_token']);
        // ISSUE [MED-05]: $days should use absint() / intval(), not sanitize_text_field(),
        // since it is a numeric field. sanitize_text_field() on '30abc' stores '30abc'.
        $days = sanitize_text_field($_POST['ulp_update_interval']);

        if (!is_numeric($days)) {
            echo '<div class="notice notice-error"> Incorrect number of days </div>';
        } else {
            update_option('ulp_data_source', $source);
            update_option('ulp_gorest_token', $token);
            update_option('ulp_update_interval', $days);

            echo '<div class="notice notice-success"> Saved </div>';
        }
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
                        <?php // ISSUE [HIGH-02]: get_option() output echoed directly without esc_attr().
                        // A stored token containing double-quotes or angle brackets would break
                        // out of the HTML attribute. Always wrap with esc_attr(). ?>
                        <input type="password" name="ulp_gorest_token" class="regular-text" value="<?php echo get_option('ulp_gorest_token'); ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Days since last user update:</th>
                    <td>
                        <?php // ISSUE [HIGH-02]: get_option() output echoed without esc_attr().
                        // Same attribute-breakout risk as the token field above. ?>
                        <input type="text" name="ulp_update_interval" class="regular-text" value="<?php echo get_option('ulp_update_interval'); ?>"/>
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