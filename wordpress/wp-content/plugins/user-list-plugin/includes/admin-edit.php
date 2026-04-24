<?php

/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'ulp_add_edit_submenu');

function ulp_add_edit_submenu() : void
{
    add_submenu_page(
        null,
        'Edit User',
        'Edit User',
        'manage_options',
        'ulp-user-edit',
        'ulp_render_edit_page'
    );
}

function ulp_handle_edit(int $id) : array
{
    if(!isset($_POST['ulp_edit_submit'])) {
        return [];
    }

    if(!check_admin_referer('ulp_edit_nonce')) {
        return ['success' => false, 'error' => 'Invalid WP Token'];
    }

    $source = get_option('ulp_data_source', 'local');

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $gender = sanitize_text_field($_POST['gender']);
    $status = sanitize_text_field($_POST['status']);

    if ($source === 'local') {
        $country = sanitize_text_field($_POST['country']);
        $city = sanitize_text_field($_POST['city']);

        $user['country'] = $country;
        $user['city'] = $city;
    }

    $user['name'] = $name;
    $user['email'] = $email;
    $user['gender'] = $gender;
    $user['status'] = $status;

    $validation_result = ulp_user_form_validation($user);

    if (!empty($validation_result)) {
        return ['success' => false, 'error' => $validation_result];
    }

    if ($source === 'local') {
        $result = ulp_update_local_user($id, $user);

        if ($result) {
            return ['success' => true, 'message' => 'Database user updated'];
        } else {
            return ['success' => false, 'error' => 'Error updating database user'];
        }
    } else {
        $result = ulp_update_gorest_user($id, $user);

        if (is_wp_error($result)) {
            return ['success' => false, 'error' => 'Error updating GoREST user'];
        } else {
            return ['success' => true, 'message' => 'GoREST user updated'];
        }
    }
}

function ulp_render_edit_page() : void
{
    $source = get_option('ulp_data_source', 'local');
    $id = intval($_GET['id']);

    if (empty($id)) {
        echo '<div>No user ID provided</div>';
    }

    $result = ulp_handle_edit($id);
    $error = $result['error'] ?? '';

    if ($source === 'local') {
        $user = ulp_get_local_user($id);
        var_dump($user);
    } else {
        $user = ulp_get_gorest_user($id);
    }

    ?>

    <div class="wrap">
        <form method="post" class="ulp-user-form">
            <?php wp_nonce_field('ulp_edit_nonce'); ?>
            <?php echo ulp_render_user_form($source, 'Edit User', $error, $user); ?>
            <button class="ulp-submit-button" type="submit" name="ulp_edit_submit"> Submit </button>
        </form>
    </div>

    <?php

    if (isset($result['success'])) {
        echo ulp_render_result_modal($result);
    }
}
