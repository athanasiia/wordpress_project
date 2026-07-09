<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_pagination(array $data, string $current_url): string
{
    $page_number = $data['page_number'];
    $total_pages = $data['total_pages'];

    ob_start();
    ?>
    <div class="ulp-table-pages">
        <?php if($page_number > 1): ?>
            <a href="<?php echo esc_url(add_query_arg('user_page', $page_number - 1, $current_url)); ?>" class="ulp-table-button">&#10094;</a>
        <?php endif; ?>

        <p><?php echo esc_html($page_number); ?></p>

        <?php if($page_number < $total_pages): ?>
            <a href="<?php echo esc_url(add_query_arg('user_page', $page_number + 1, $current_url)); ?>" class="ulp-table-button">&#10095;</a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}