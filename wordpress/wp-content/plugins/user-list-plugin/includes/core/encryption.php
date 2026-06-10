<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

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

    $iv = openssl_random_pseudo_bytes(16);
    if (!$iv) {
        return false;
    }

    $encrypted = openssl_encrypt($token, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if (!$encrypted) {
        return false;
    }

    $combined = base64_encode($iv) . ':' . base64_encode($encrypted);

    return update_option('ulp_gorest_token_encrypted', $combined);
}

function ulp_get_decrypted_token(): string
{
    $combined = get_option('ulp_gorest_token_encrypted', '');
    if (empty($combined)) {
        return '';
    }

    $key = '';
    if (defined('LOGGED_IN_KEY')) {
        $key = LOGGED_IN_KEY;
    }

    if (!$key) {
        return '';
    }

    $parts = explode(':', $combined, 2);
    if (count($parts) !== 2) {
        return '';
    }

    $iv = base64_decode($parts[0]);
    $encrypted = base64_decode($parts[1]);

    if (!$iv || !$encrypted || strlen($iv) !== 16) {
        return '';
    }

    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted ?: '';
}