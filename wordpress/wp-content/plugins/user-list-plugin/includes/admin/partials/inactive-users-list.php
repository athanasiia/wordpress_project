<?php
/**
 * @package UserListPlugin
 */

// PSR-12: declare(strict_types=1) should be added right after <?php
// PSR-12: Return type uses ' : type' — should be ': type' (no space before colon)

function ulp_render_inactive_users_block(array $inactive_users) : string
{
    ob_start();
    ?>

    <div class="notice notice-success">
        <p><?php echo esc_html(sprintf(__("Users with the following IDs haven't been updated for the past %d days:", 'user-list-plugin'), get_option('ulp_update_interval'))); ?></p>
        <p>
            <?php
            foreach($inactive_users as $user)
            echo esc_html($user) . ' ';
            ?>
        </p>
    </div>

    <?php
    return ob_get_clean();
}