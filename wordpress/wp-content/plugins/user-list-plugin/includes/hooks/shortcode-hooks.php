<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../frontend/shortcode.php';

add_shortcode('user_list', __NAMESPACE__ . '\\ulp_render_user_list_shortcode');

// FIX: moved from user-list-plugin.php — enqueue hooks belong in the hooks layer, not the plugin root.
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\ulp_enqueue_frontend_assets');

function ulp_enqueue_frontend_assets(): void
{
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'user_list')) {
        wp_enqueue_style('ulp-table-style', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/css/user-table.css', [], '1.0');
        wp_enqueue_script('ulp-script', plugin_dir_url(ULP_PLUGIN_FILE) . 'assets/js/user-table.js', [], '1.0', true);
    }
}