<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_result_modal(array $result): string
{
    ob_start();
    ?>

    <div class="ulp-modal-overlay" id="resultModal">
        <div class="ulp-modal">
            <div class="ulp-modal-content">
                <h3><?php echo $result['success'] ? esc_html__('Success!', 'user-list-plugin') : esc_html__('Error', 'user-list-plugin'); ?></h3>
                <p class="ulp-modal-message"><?php echo $result['success'] ? esc_html($result['message']) : esc_html($result['error']); ?></p>

                <button class="ulp-modal-button" onclick="document.getElementById('resultModal').style.display='none'"><?php esc_html_e('Close', 'user-list-plugin'); ?></button>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}