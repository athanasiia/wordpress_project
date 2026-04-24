<?php
/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

function ulp_create_table(): void
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'ulp_users';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            country VARCHAR(2) NOT NULL,
            city VARCHAR(100) NOT NULL,
            gender ENUM('male', 'female') NOT NULL,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function ulp_get_local_users(array $filters): array
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';

    $sql = "SELECT * FROM $table_name WHERE 1=1";
    $params = array();

    if (!empty($filters['status']) && $filters['status'] != 'all') {
        $sql .= " AND status = %s";
        $params[] = $filters['status'];
    }

    if (!empty($filters['gender']) && $filters['gender'] != 'all') {
        $sql .= " AND gender = %s";
        $params[] = $filters['gender'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND name LIKE %s";
        $params[] = '%' . $filters['search'] . '%';
    }

    $allowed_fields = array('id', 'name', 'email');
    $sort_field = isset($filters['sort']) && in_array($filters['sort'], $allowed_fields) ? $filters['sort'] : 'id';
    $sort_order = isset($filters['order']) && strtoupper($filters['order']) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $sort_field $sort_order";

    $limit = $filters['limit'] ?? 5;
    $offset = $filters['offset'] ?? 0;
    $sql .= " LIMIT $limit OFFSET $offset";

    if (!empty($params)) {
        $sql = $wpdb->prepare($sql, $params);
    }

    $result = $wpdb->get_results($sql, ARRAY_A);

    return $result ?: array();
}

function ulp_get_local_user(int $id): array
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    $sql = "SELECT * FROM $table_name WHERE id = %d";
    $result = $wpdb->prepare($sql, $id);
    return $wpdb->get_row($result, ARRAY_A);
}

function ulp_create_local_user(array $data) : int | false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    return $wpdb->insert($table_name, $data);
}

function ulp_update_local_user(int $id, array $data) : int | false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    return $wpdb->update($table_name, $data, ['id' => $id]);
}

function ulp_delete_local_users(array $ids) : int | false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    $deleted_users = 0;
    foreach ($ids as $id) {
        $response = $wpdb->delete($table_name, ['id' => (int)$id]);
        if ($response) {
            $deleted_users++;
        }
    }
    return $deleted_users > 0 ? $deleted_users : false;
}