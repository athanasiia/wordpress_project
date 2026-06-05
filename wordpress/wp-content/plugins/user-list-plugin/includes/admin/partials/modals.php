<?php
/**
 * @package UserListPlugin
 */

// ISSUE: Missing ABSPATH guard. Without it this file can be loaded directly
// over HTTP outside the WordPress bootstrap. Add:
//   if ( ! defined( 'ABSPATH' ) ) { exit; }

function ulp_render_result_modal(array $result) : string
{
    ob_start();
    ?>

    <div class="ulp-modal-overlay" id="resultModal">
        <div class="ulp-modal">
            <div class="ulp-modal-content">
                <h3><?php echo $result['success'] ? 'Success!' :  'Error'; ?></h3>
                <p class="ulp-modal-message"><?php echo $result['success'] ? esc_html($result['message']) : esc_html($result['error']); ?></p>

                <button class="ulp-modal-button" onclick="document.getElementById('resultModal').style.display='none'"> Close </button>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}