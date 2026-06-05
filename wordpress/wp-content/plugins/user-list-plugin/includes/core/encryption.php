<?php

/**
 * @package UserListPlugin
 */

// PSR-12: declare(strict_types=1) should be added right after <?php

if (!defined('ABSPATH')) {
    exit;
}

function ulp_encrypt_and_save_token(string $token): bool
{
    $key = '';
    if (defined('LOGGED_IN_KEY')) {
        $key = LOGGED_IN_KEY;
    }

    if (!$key) {
        return false;
    }

    $iv = substr($key, 0, 16);

    $encrypted = openssl_encrypt($token, 'aes-256-cbc', $key, 0, $iv);
    return update_option('ulp_gorest_token_encrypted', $encrypted);
}

function ulp_get_decrypted_token(): string
{
    $encrypted = get_option('ulp_gorest_token_encrypted', '');
    if (empty($encrypted)) {
        return '';
    }

    $key = '';
    if (defined('LOGGED_IN_KEY')) {
        $key = LOGGED_IN_KEY;
    }

    if (!$key) {
        return '';
    }

    $iv = substr($key, 0, 16);

    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    return $decrypted ?: '';
}