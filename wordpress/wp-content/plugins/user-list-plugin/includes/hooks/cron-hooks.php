<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../core/cron-jobs.php';

add_action('ulp_check_inactive_users', __NAMESPACE__ . '\\ulp_check_inactive_users');