<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_user_table(array $data): string
{
    $users = $data['users'];
    $source = $data['source'];
    $show_admin_columns = $data['context'] === 'admin';

    ob_start();
    ?>
    <table class="ulp-users-table">
        <thead>
        <tr>
            <?php if($show_admin_columns): ?>
                <th></th>
                <th><?php esc_html_e('Delete', 'user-list-plugin'); ?></th>
            <?php endif; ?>
            <th><?php esc_html_e('ID', 'user-list-plugin'); ?></th>
            <th><?php esc_html_e('Email', 'user-list-plugin'); ?></th>
            <th><?php esc_html_e('Name', 'user-list-plugin'); ?></th>
            <?php if($source === 'local'): ?>
                <th><?php esc_html_e('City', 'user-list-plugin'); ?></th>
                <th><?php esc_html_e('Country', 'user-list-plugin'); ?></th>
            <?php endif; ?>
            <th><?php esc_html_e('Gender', 'user-list-plugin'); ?></th>
            <th><?php esc_html_e('Status', 'user-list-plugin'); ?></th>
            <?php if($show_admin_columns && $data['source'] === 'local'): ?>
                <th><?php esc_html_e('Created', 'user-list-plugin'); ?></th>
                <th><?php esc_html_e('Updated', 'user-list-plugin'); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach($users as $user): ?>
            <tr>
                <?php if($show_admin_columns): ?>
                    <td>
                        <button class="ulp-table-button" data-id="<?php echo esc_attr($user['id']); ?>">
                            <?php esc_html_e('Edit', 'user-list-plugin'); ?>
                        </button>
                    </td>
                    <td>
                        <input type="checkbox" class="ulp-user-checkbox" data-id="<?php echo esc_attr($user['id']); ?>" />
                    </td>
                <?php endif; ?>
                <td><?php echo esc_html($user['id']); ?></td>
                <td><?php echo esc_html($user['email']); ?></td>
                <td><?php echo esc_html($user['name']); ?></td>
                <?php if($source === 'local'): ?>
                    <td><?php echo isset($user['city']) ? esc_html($user['city']) : ''; ?></td>
                    <td><?php echo isset($user['country']) ? esc_html($user['country']) : ''; ?></td>
                <?php endif; ?>
                <td><?php echo esc_html($user['gender']); ?></td>
                <td><?php echo ulp_add_icons($user['status']); ?></td>
                <?php if($show_admin_columns && $data['source'] === 'local'): ?>
                    <td><?php echo isset($user['created']) ? esc_html($user['created']) : ''; ?></td>
                    <td><?php echo isset($user['updated']) ? esc_html($user['updated']) : ''; ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}