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