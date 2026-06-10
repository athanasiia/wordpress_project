<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_add_icons(string $status): string
{
    return esc_html($status) . ($status === 'active' ? ' &#10687;' : ' &#10686;');
}