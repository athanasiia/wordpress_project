<?php

/**
 * @package UserListPlugin
 */

// PSR-12: declare(strict_types=1) should be added right after <?php
// PSR-12: All return types use ' : type' — PSR-12 requires no space before the colon: 'func(): type'
// PSR-1: File mixes side-effect calls (add_action) with function declarations.
//        PSR-1 says a file should either declare symbols OR cause side-effects, not both.

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

    $user = [];
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

    $user['updated'] = wp_date("Y-m-d");

    $validation_result = ulp_user_form_validation($user, $source);

    if (!empty($validation_result)) {
        return ['success' => false, 'error' => $validation_result];
    }

    if ($source === 'local') {
        $result = ulp_update_local_user($id, $user);

        if ($result) {
            return ['success' => true, 'message' => 'Database user updated'];
        }

        return ['success' => false, 'error' => 'Error updating database user'];
    }

    $result = ulp_update_gorest_user($id, $user);

    if (is_wp_error($result)) {
        return ['success' => false, 'error' => 'Error updating GoREST user'];
    }

    return ['success' => true, 'message' => 'GoREST user updated'];
}

function ulp_render_edit_page() : void
{
    $source = get_option('ulp_data_source', 'local');
    $id = (int)$_GET['id'];

    if (empty($id)) {
        echo '<div>No user ID provided</div>';
        exit;
    }

    $result = ulp_handle_edit($id);
    $error = $result['error'] ?? '';

    if ($source === 'local') {
        $user = ulp_get_local_user($id);
    } else {
        $user = ulp_get_gorest_user($id);
    }

    if (is_null($user)) {
        $error = 'Could not get user';
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
