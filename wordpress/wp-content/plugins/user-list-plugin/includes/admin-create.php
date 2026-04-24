<?php

/**
 * @package UserListPlugin
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'ulp_add_create_submenu');

function ulp_add_create_submenu() : void
{
    add_submenu_page(
        'ulp-users',
        'Create User',
        'Create User',
        'manage_options',
        'ulp-user-create',
        'ulp_render_create_page'
    );
}

function ulp_handle_create() : array
{
    if(!isset($_POST['ulp_create_submit'])) {
        return [];
    }

    if(!check_admin_referer('ulp_create_nonce')) {
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
        $result = ulp_create_local_user($user);

        if ($result) {
            return ['success' => true, 'message' => 'Database user created'];
        } else {
            return ['success' => false, 'error' => 'Error creating database user'];
        }
    } else {
        $result = ulp_create_gorest_user($user);

        if (is_wp_error($result)) {
            return ['success' => false, 'error' => 'Error creating GoREST user'];
        } else {
            return ['success' => true, 'message' => 'GoREST user created'];
        }
    }
}

function ulp_render_create_page() : void
{
    $source = get_option('ulp_data_source', 'local');
    $result = ulp_handle_create();
    $error = $result['error'] ?? '';
    ?>

    <div class="wrap">
        <form method="post" class="ulp-user-form">
            <?php wp_nonce_field('ulp_create_nonce'); ?>
            <?php echo ulp_render_user_form($source, 'Create User', $error); ?>
            <button class="ulp-submit-button" type="submit" name="ulp_create_submit"> Submit </button>
        </form>
    </div>

    <?php

    if (isset($result['success'])) {
        echo ulp_render_result_modal($result);
    }
}

