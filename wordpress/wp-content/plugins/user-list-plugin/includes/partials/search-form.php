<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_search_form(array $data): string
{
    $base_url = get_permalink();
    $search_term = $data['search_term'];

    ob_start();
    ?>
    <div class="ulp-search-box">
        <form method="get" action="<?php echo esc_url($base_url); ?>">
            <input type="text" placeholder="<?php esc_attr_e('Search by name...', 'user-list-plugin'); ?>"
                   name="search" value="<?php echo esc_attr($search_term); ?>" class="ulp-search-input" />
            <?php echo ulp_render_hidden_fields_except(['search', 'user_page']); ?>
        </form>
    </div>
    <?php
    return ob_get_clean();
}