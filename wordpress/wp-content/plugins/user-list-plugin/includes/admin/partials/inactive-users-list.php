<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_inactive_users_block(array $inactive_users): string
{
    ob_start();
    ?>

    <div class="notice notice-success">
        <p><?php echo esc_html(sprintf(__("Users with the following IDs haven't been updated for the past %d days:", 'user-list-plugin'), get_option('ulp_update_interval'))); ?></p>
        <p>
            <?php
            foreach($inactive_users as $user)
            {
                echo esc_html($user) . ' ';
            }
            ?>
        </p>
    </div>

    <?php
    return ob_get_clean();
}