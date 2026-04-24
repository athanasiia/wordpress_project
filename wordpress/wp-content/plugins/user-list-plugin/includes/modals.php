<?php
/**
 * @package UserListPlugin
 */

function ulp_render_result_modal(array $result) : string
{
    ob_start();
    ?>

    <div class="ulp-modal-overlay" id="resultModal">
        <div class="ulp-modal">
            <div class="ulp-modal-content">
                <h3><?php echo $result['success'] ? 'Success!' :  'Error'; ?></h3>
                <p><?php echo $result['success'] ? $result['message'] : $result['error']; ?></p>

                <button class="ulp-modal-button" onclick="document.getElementById('resultModal').style.display='none'"> Close </button>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}