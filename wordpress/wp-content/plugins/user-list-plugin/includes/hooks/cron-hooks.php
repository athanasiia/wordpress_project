<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

add_action('ulp_check_inactive_users', __NAMESPACE__ . '\\ulp_check_inactive_users');