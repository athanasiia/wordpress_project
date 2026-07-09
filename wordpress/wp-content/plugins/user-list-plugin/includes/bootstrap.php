<?php declare(strict_types=1);

namespace UserListPlugin;

if (!defined('ABSPATH')) {
    exit;
}

define('ULP_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once ULP_PLUGIN_DIR . 'core/database.php';
require_once ULP_PLUGIN_DIR . 'core/gorest-api.php';
require_once ULP_PLUGIN_DIR . 'core/encryption.php';
require_once ULP_PLUGIN_DIR . 'core/user-handler.php';
require_once ULP_PLUGIN_DIR . 'core/user-list-handler.php';
require_once ULP_PLUGIN_DIR . 'core/cron-jobs.php';
require_once ULP_PLUGIN_DIR . 'core/activation.php';
require_once ULP_PLUGIN_DIR . 'core/deactivation.php';
require_once ULP_PLUGIN_DIR . 'core/filters.php';

require_once ULP_PLUGIN_DIR . 'partials/filter-controls.php';
require_once ULP_PLUGIN_DIR . 'partials/hidden-filter-form.php';
require_once ULP_PLUGIN_DIR . 'partials/search-form.php';
require_once ULP_PLUGIN_DIR . 'partials/sort-buttons.php';
require_once ULP_PLUGIN_DIR . 'partials/user-table.php';
require_once ULP_PLUGIN_DIR . 'partials/user-table-pagination.php';

require_once ULP_PLUGIN_DIR . 'helpers/countries.php';

require_once ULP_PLUGIN_DIR . 'admin/partials/action-panel.php';
require_once ULP_PLUGIN_DIR . 'admin/partials/inactive-users-list.php';
require_once ULP_PLUGIN_DIR . 'admin/partials/modals.php';
require_once ULP_PLUGIN_DIR . 'admin/partials/user-form.php';

require_once ULP_PLUGIN_DIR . 'admin/pages/admin-create.php';
require_once ULP_PLUGIN_DIR . 'admin/pages/admin-edit.php';
require_once ULP_PLUGIN_DIR . 'admin/pages/admin-page.php';
require_once ULP_PLUGIN_DIR . 'admin/pages/settings-page.php';

require_once ULP_PLUGIN_DIR . 'frontend/shortcode.php';

require_once ULP_PLUGIN_DIR . 'hooks/cron-hooks.php';
require_once ULP_PLUGIN_DIR . 'hooks/admin-hooks.php';
require_once ULP_PLUGIN_DIR . 'hooks/shortcode-hooks.php';

require_once ULP_PLUGIN_DIR . 'hooks/main-plugin-hooks.php';