<?php declare(strict_types=1);

/**
 * @package UserListPlugin
 */

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

function ulp_render_user_form(string $source, string $title, string $errors = '', array $form_data = []): string
{
    $email = isset($form_data['email']) ? esc_attr($form_data['email']) : '';
    $name = isset($form_data['name']) ? esc_attr($form_data['name']) : '';
    $country = isset($form_data['country']) ? esc_attr($form_data['country']) : '';
    $city = isset($form_data['city']) ? esc_attr($form_data['city']) : '';
    $gender = isset($form_data['gender']) ? esc_attr($form_data['gender']) : '';
    $status = isset($form_data['status']) ? esc_attr($form_data['status']) : '';

    ob_start();
    ?>

    <h2><?php echo esc_html($title); ?></h2>

    <?php if (!empty($errors)): ?>
    <div class="ulp-errors"><?php echo esc_html($errors); ?></div>
    <?php endif; ?>

    <div>
        <label><?php esc_html_e('Email', 'user-list-plugin'); ?></label>
        <input
                type="email"
                name="email"
                value="<?php echo $email; ?>"
                placeholder="<?php esc_attr_e('example@mail.com', 'user-list-plugin'); ?>"
                required
        />
    </div>

    <div>
        <label><?php esc_html_e('Your first and last name', 'user-list-plugin'); ?></label>
        <input
                type="text"
                name="name"
                value="<?php echo $name; ?>"
                placeholder="<?php esc_attr_e('John Doe', 'user-list-plugin'); ?>"
                required
        />
    </div>

    <?php if ($source === 'local'): ?>
    <div>
        <label><?php esc_html_e('Country of residence', 'user-list-plugin'); ?></label>
        <?php echo ulp_render_countries_select($country); ?>
    </div>

    <div>
        <label><?php esc_html_e('City', 'user-list-plugin'); ?></label>
        <input
                type="text"
                name="city"
                value="<?php echo $city; ?>"
                placeholder="<?php esc_attr_e('New York', 'user-list-plugin'); ?>"
                required
        />
    </div>
    <?php endif; ?>

    <div>
        <label><?php esc_html_e('Gender', 'user-list-plugin'); ?></label>
        <select name="gender" required>
            <option value=""><?php esc_html_e('Select gender', 'user-list-plugin'); ?></option>
            <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>><?php esc_html_e('Male', 'user-list-plugin'); ?></option>
            <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>><?php esc_html_e('Female', 'user-list-plugin'); ?></option>
        </select>
    </div>

    <div>
        <label><?php esc_html_e('Status', 'user-list-plugin'); ?></label>
        <select name="status" required>
            <option value=""><?php esc_html_e('Select status', 'user-list-plugin'); ?></option>
            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>><?php esc_html_e('Active user', 'user-list-plugin'); ?></option>
            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>><?php esc_html_e('Inactive user', 'user-list-plugin'); ?></option>
        </select>
    </div>

    <?php
    return ob_get_clean();
}

function ulp_user_form_validation(array $data, string $source): string
{
    if ($source === 'local') {
        $required_fields = ['name', 'email', 'country', 'city', 'gender', 'status'];
    } else {
        $required_fields = ['name', 'email', 'gender', 'status'];
    }

    foreach ($required_fields as $field) {
        if (empty(trim($data[$field] ?? ''))) {
            return sprintf(__('Please fill in the %s field', 'user-list-plugin'), $field);
        }
    }

    if (!is_email($data['email'])) {
        return __('Please enter a valid email address', 'user-list-plugin');
    }

    if (!in_array($data['gender'], ['male', 'female'], true)) {
        return __('Gender must be either "male" or "female"', 'user-list-plugin');
    }

    if (!in_array($data['status'], ['active', 'inactive'], true)) {
        return __('Status must be either "active" or "inactive"', 'user-list-plugin');
    }

    return '';
}
