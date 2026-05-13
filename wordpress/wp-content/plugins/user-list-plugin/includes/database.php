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
    // ISSUE [HIGH-03]: Schema is missing 'created' and 'updated' columns.
    // admin-page.php renders those columns, admin-edit.php writes to 'updated',
    // and the cron job reads 'updated' to compute inactivity. Without these columns,
    // the edit save silently fails and the cron marks every user as inactive.
    // Also missing: indexes on 'status' and 'gender' which are used in WHERE clauses.
    // Also missing: schema version tracking — if the schema ever changes there is no
    // upgrade path because dbDelta is only called on activation.
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
    // ISSUE [MED-01]: $limit and $offset are interpolated directly into the SQL string
    // and are never passed through $wpdb->prepare(). Even when prepare() is called below,
    // it only substitutes the %s/%d placeholders — $limit and $offset are already embedded.
    // When $params is empty, prepare() is skipped entirely, leaving a raw SQL string.
    $sql .= " LIMIT $limit OFFSET $offset";

    // ISSUE [MED-01]: prepare() is only called when there are WHERE-clause params.
    // Queries with no filters (e.g., fetching all users) run as raw, unprepared SQL.
    if (!empty($params)) {
        $sql = $wpdb->prepare($sql, $params);
    }

    $result = $wpdb->get_results($sql, ARRAY_A);

    return $result ?: array();
}

// ISSUE [MED-02]: Return type declares 'array' but $wpdb->get_row() returns null when
// no matching row exists. Every caller that accesses keys on the return value (e.g.
// $user['email']) will trigger a fatal error on a missing user. Return type must be ?array
// and every call site must guard against null.
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

// ISSUE [HIGH-05]: N+1 query problem. One DELETE query is issued per user ID inside a loop.
// Deleting 50 users fires 50 separate queries. This should be a single
// DELETE FROM ... WHERE id IN (...) query built with $wpdb->prepare() and imploded placeholders.
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