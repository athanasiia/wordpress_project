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
    $email = $form_data['email'] ?? '';
    $name = $form_data['name'] ?? '';
    $country = $form_data['country'] ?? '';
    $city = $form_data['city'] ?? '';
    $gender = $form_data['gender'] ?? '';
    $status = $form_data['status'] ?? '';

    ob_start();
    ?>

    <h2><?php echo esc_html($title); ?></h2>

    <?php if (!empty($errors)): ?>
    <div class="ulp-errors"><?php echo esc_html($errors); ?></div>
    <?php endif; ?>

    <div>
        <label for="ulp_email"><?php esc_html_e('Email', 'user-list-plugin'); ?></label>
        <input
                type="email"
                name="email"
                id="ulp_email"
                value="<?php echo esc_attr($email); ?>"
                placeholder="<?php esc_attr_e('example@mail.com', 'user-list-plugin'); ?>"
                required
        />
    </div>

    <div>
        <label for="ulp_name"><?php esc_html_e('Your first and last name', 'user-list-plugin'); ?></label>
        <input
                type="text"
                name="name"
                id="ulp_name"
                value="<?php echo esc_attr($name); ?>"
                placeholder="<?php esc_attr_e('John Doe', 'user-list-plugin'); ?>"
                required
        />
    </div>

    <?php if ($source === 'local'): ?>
    <div>
        <label for="ulp_country"><?php esc_html_e('Country of residence', 'user-list-plugin'); ?></label>
        <?php echo ulp_render_countries_select($country); ?>
    </div>

    <div>
        <label for="ulp_city"><?php esc_html_e('City', 'user-list-plugin'); ?></label>
        <input
                type="text"
                name="city"
                id="ulp_city"
                value="<?php echo esc_attr($city); ?>"
                placeholder="<?php esc_attr_e('New York', 'user-list-plugin'); ?>"
                required
        />
    </div>
    <?php endif; ?>

    <div>
        <label for="ulp_gender"><?php esc_html_e('Gender', 'user-list-plugin'); ?></label>
        <select name="gender" id="ulp_gender" required>
            <option value=""><?php esc_html_e('Select gender', 'user-list-plugin'); ?></option>
            <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>><?php esc_html_e('Male', 'user-list-plugin'); ?></option>
            <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>><?php esc_html_e('Female', 'user-list-plugin'); ?></option>
        </select>
    </div>

    <div>
        <label for="ulp_status"><?php esc_html_e('Status', 'user-list-plugin'); ?></label>
        <select name="status" id="ulp_status" required>
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
    $field_labels = [
            'name'    => __('Name', 'user-list-plugin'),
            'email'   => __('Email', 'user-list-plugin'),
            'country' => __('Country', 'user-list-plugin'),
            'city'    => __('City', 'user-list-plugin'),
            'gender'  => __('Gender', 'user-list-plugin'),
            'status'  => __('Status', 'user-list-plugin'),
    ];

    if ($source === 'local') {
        $required_fields = ['name', 'email', 'country', 'city', 'gender', 'status'];
    } else {
        $required_fields = ['name', 'email', 'gender', 'status'];
    }

    foreach ($required_fields as $field) {
        $value = $data[$field] ?? '';

        if (is_array($value) || empty(trim((string)$value))) {
            $label = $field_labels[$field] ?? $field;
            return sprintf(__('Please fill in the %s field', 'user-list-plugin'), $label);
        }
    }

    $email  = isset($data['email'])  && is_string($data['email'])  ? $data['email']  : '';
    $gender = isset($data['gender']) && is_string($data['gender']) ? $data['gender'] : '';
    $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : '';

    if (!is_email($email)) {
        return __('Please enter a valid email address', 'user-list-plugin');
    }

    if (!in_array($gender, ['male', 'female'], true)) {
        return __('Gender must be either "male" or "female"', 'user-list-plugin');
    }

    if (!in_array($status, ['active', 'inactive'], true)) {
        return __('Status must be either "active" or "inactive"', 'user-list-plugin');
    }

    return '';
}