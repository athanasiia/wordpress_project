<?php
/**
 * @package UserListPlugin
 */

function ulp_render_inactive_users_block(array $inactive_users) : string
{
    ob_start();
    ?>

    <div class="notice notice-success">
        <p>Users with the following IDs haven't been updated for the past <?php echo get_option('ulp_update_interval'); ?> days:</p>
        <p>
            <?php foreach($inactive_users as $user)
            echo $user . ' ';
            ?>
        </p>
    </div>

    <?php
    return ob_get_clean();
}