<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_action_panel(): string
{
    ob_start();
    ?>
    <div class="ulp-action-panel">
        <button class="ulp-table-button" id="createNewUserBtn">
            <?php esc_html_e('Create New User', 'user-list-plugin'); ?>
        </button>

        <div class="ulp-delete-panel">
            <button class="ulp-table-button ulp-delete-button" id="deleteSelectedBtn" disabled>
                <?php esc_html_e('Delete Selected', 'user-list-plugin'); ?>
                (<span id="selectedCount">0</span>)
            </button>
            <div>
                <label for="selectAllCheckbox"><?php esc_html_e('Check all', 'user-list-plugin'); ?></label>
                <input type="checkbox" id="selectAllCheckbox" />
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}