<?php
/**
 * @package UserListPlugin
 */

function ulp_render_inactive_users_block(array $inactive_users) : string
{
    ob_start();
    ?>

    <div class="notice notice-success">
        <?php // ISSUE [CRITICAL-02]: get_option() echoed without esc_html(). If the stored
        // option value were ever modified to contain HTML it would render unescaped. ?>
        <p>Users with the following IDs haven't been updated for the past <?php echo get_option('ulp_update_interval'); ?> days:</p>
        <p>
            <?php
            // ISSUE [CRITICAL-02]: $user (a user ID from the stored option array) echoed
            // without any escaping or int cast. Always use esc_html() or (int) cast here.
            foreach($inactive_users as $user)
            echo $user . ' ';
            ?>
        </p>
    </div>

    <?php
    return ob_get_clean();
}