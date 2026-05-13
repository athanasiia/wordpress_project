<?php
/**
 * @package UserListPlugin
 */

// ISSUE [MED-02 / general]: This file is missing the ABSPATH guard present in all
// other includes. Add: if ( ! defined( 'ABSPATH' ) ) { exit; }

function ulp_render_user_form(string $source, string $title, string $errors = '', array $form_data = []) : string
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
        <label>Email</label>
        <input
                type="email"
                name="email"
                value="<?php echo $email; ?>"
                placeholder="example@mail.com"
                required
        />
    </div>

    <div>
        <label>Your first and last name</label>
        <input
                type="text"
                name="name"
                value="<?php echo $name; ?>"
                placeholder="John Doe"
                required
        />
    </div>

    <?php if ($source === 'local'): ?>
    <div>
        <label>Country of residence</label>
        <?php echo ulp_render_countries_select($country); ?>
    </div>

    <div>
        <label>City</label>
        <input
                type="text"
                name="city"
                value="<?php echo $city; ?>"
                placeholder="New York"
                required
        />
    </div>
    <?php endif; ?>

    <div>
        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select gender</option>
            <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
        </select>
    </div>

    <div>
        <label>Status</label>
        <select name="status" required>
            <option value="">Select status</option>
            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active user</option>
            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive user</option>
        </select>
    </div>

    <?php
    return ob_get_clean();
}

function ulp_user_form_validation(array $data) : string
{
    foreach($data as $field => $value) {
        // ISSUE [CRITICAL-03]: empty($field) checks the array KEY (e.g. the string 'name'),
        // not the VALUE. A non-empty string key is never empty, so this condition is ALWAYS
        // false. Required-field validation never triggers. Empty name, city, gender, and
        // status all pass through silently into the database.
        // Fix: check empty($value) and validate $value against allowed enums for
        // gender ('male','female') and status ('active','inactive').
        if (empty($field)) {
            return 'Please fill in' . $field . ' field';
        }
    }

    if (!is_email($data['email'])) {
        return 'Please enter a valid email address';
    }

    return '';
}