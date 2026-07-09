<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_create_table(): void
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'ulp_users';

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        name varchar(150) NOT NULL,
        country varchar(2) NOT NULL,
        city varchar(100) NOT NULL,
        gender varchar(6) NOT NULL,
        status varchar(8) NOT NULL DEFAULT 'active',
        created date NOT NULL,
        updated date NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email),
        KEY idx_gender (gender),
        KEY idx_status (status)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function ulp_get_local_users(array $filters): array
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';

    $where_conditions = [];
    $params = [];

    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $where_conditions[] = "status = %s";
        $params[] = $filters['status'];
    }

    if (!empty($filters['gender']) && $filters['gender'] !== 'all') {
        $where_conditions[] = "gender = %s";
        $params[] = $filters['gender'];
    }

    if (!empty($filters['search'])) {
        $where_conditions[] = "name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($filters['search']) . '%';
    }

    $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    $count_sql = "SELECT COUNT(*) FROM $table_name $where_sql";

    if (!empty($params)) {
        $count_sql = $wpdb->prepare($count_sql, $params);
    }

    $total_items = (int) $wpdb->get_var($count_sql);

    $allowed_fields = ['id', 'name', 'email'];
    $sort_field = isset($filters['sort']) && in_array($filters['sort'], $allowed_fields, true) ? $filters['sort'] : 'id';
    $sort_order = isset($filters['order']) && strtoupper($filters['order']) === 'DESC' ? 'DESC' : 'ASC';
    $limit = isset($filters['limit']) ? (int)$filters['limit'] : 5;
    $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;

    $sql = "SELECT * FROM $table_name $where_sql ORDER BY $sort_field $sort_order LIMIT %d OFFSET %d";
    $params[] = $limit;
    $params[] = $offset;

    if (!empty($params)) {
        $sql = $wpdb->prepare($sql, $params);
    }

    $result = $wpdb->get_results($sql, ARRAY_A);

    return [
        'users' => $result ?: [],
        'total_pages' => ceil($total_items / $limit),
    ];
}

function ulp_get_local_user(int $id): ?array
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    $sql = "SELECT * FROM $table_name WHERE id = %d";
    $result = $wpdb->prepare($sql, $id);
    return $wpdb->get_row($result, ARRAY_A);
}

function ulp_create_local_user(array $data): int | false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    return $wpdb->insert($table_name, $data);
}

function ulp_update_local_user(int $id, array $data): int | false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';
    return $wpdb->update($table_name, $data, ['id' => $id]);
}

function ulp_delete_local_users(array $ids): int | false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ulp_users';

    if (empty($ids)) {
        return false;
    }

    $ids = array_filter($ids, function($id) {
        return $id > 0;
    });

    if (empty($ids)) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '%d'));

    $sql = $wpdb->prepare(
        "DELETE FROM $table_name WHERE id IN ($placeholders)",
        $ids
    );

    $result = $wpdb->query($sql);

    return $result ?: false;
}