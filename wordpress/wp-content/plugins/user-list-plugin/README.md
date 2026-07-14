# User List Plugin

A WordPress admin and frontend plugin for managing a custom user directory. Users can be stored in a local database table or synchronized with the public [GoREST](https://gorest.co.in/) Users API. The plugin provides CRUD operations, filtering, sorting, pagination, optional inactive-user monitoring, and a frontend shortcode for read-only display.

**Version:** 1.0  
**Author:** athanasiia  
**License:** GPL v2 or later

---

## Overview

User List Plugin is not tied to WordPress core users (`wp_users`). It maintains its own user records (either in `{prefix}ulp_users` or via GoREST) with fields such as email, name, gender, status, and—when using the local source—country and city.

Administrators with `manage_options` can list, create, edit, and bulk-delete users from the WordPress admin. Site visitors can view a paginated, filterable table on the frontend via the `[user_list]` shortcode (read-only; no create/edit/delete on the public side).

---

## Features

### Dual data sources

| Feature | Local database | GoREST API |
|--------|----------------|------------|
| Storage | Custom table `{prefix}ulp_users` | Remote API `https://gorest.in/public/v2/users` |
| Extra fields | Country (ISO 2-letter), city, `created`, `updated` | id, email, name, gender, status only |
| Create / update / delete | Yes | Yes (requires API Bearer token) |
| List caching | N/A (SQL queries) | 5-minute WordPress transient cache |
| Inactive-user cron | Yes | No (cron runs only for local source) |

Switch the active source under **Settings → User List Plugin**.

### Admin user management

- **User List** top-level menu (`ulp-users`): paginated table (5 users per page)
- **Create User** submenu: form to add a user
- **Edit User** hidden submenu: form loaded via `admin.php?page=ulp-user-edit&id={id}`
- **Bulk delete**: select checkboxes, confirm in a modal, delete via POST with nonce
- **Filters**: status (all / active / inactive), gender (all / male / female)
- **Search**: by name (partial match)
- **Sort**: by ID, name, or email (ascending/descending)
- **Status display**: `ulp_add_icons()` escapes the status with `esc_html()` and appends HTML entities for icons (`&#10687;` active, `&#10686;` inactive)

### Frontend display

- Shortcode: `[user_list]`
- Same filters, search, sort, and pagination as admin (5 per page)
- Read-only table (no edit/delete/create)
- When data source is **local**, shows city and country columns; GoREST mode hides those columns

### Inactive user monitoring (local only)

- Daily WP-Cron event `ulp_check_inactive_users`
- Compares each user’s `updated` date to today; if older than **Days since last user update** (settings), user ID is added to `ulp_inactive_users_list`
- Admin list page shows a notice banner listing those IDs (not full user rows)

### Security

- GoREST API token stored encrypted with AES-256-CBC using WordPress `LOGGED_IN_KEY` as the encryption key; a random 16-byte IV from `openssl_random_pseudo_bytes()` is stored alongside the ciphertext as `base64(iv):base64(ciphertext)` in `ulp_gorest_token_encrypted`
- Admin forms protected with WordPress nonces (`ulp_create_nonce`, `ulp_edit_nonce`, `ulp_delete_nonce`, `ulp_settings_nonce`)
- Input sanitization: `sanitize_text_field`, `sanitize_email`, etc.
- Output escaped in templates (`esc_html`, `esc_attr`, `esc_url`)

---

## Requirements

- WordPress (version not specified in plugin header)
- PHP with OpenSSL extension (for token encryption)
- For **GoREST** mode: valid Bearer token from [gorest.co.in](https://gorest.co.in/) (create/update/delete require authentication)
- `LOGGED_IN_KEY` defined in `wp-config.php` (standard in WordPress installs) for token encryption

---

## Installation

1. Copy the `user-list-plugin` folder to `wp-content/plugins/`.
2. Activate **User List Plugin** in **Plugins**.
3. On activation:
    - Creates/updates the `{prefix}ulp_users` table via `dbDelta`
    - Sets option `ulp_db_version` to `1.0.0`
    - Schedules the daily inactive-user cron job
4. Go to **Settings → User List Plugin** and configure data source, optional GoREST token, and inactive threshold (days).

On deactivation, the daily cron is unscheduled (table and options remain).

---

## Configuration

**Settings → User List Plugin** (`ulp-settings`)

| Setting | Option key | Description |
|---------|------------|-------------|
| Data Source | `ulp_data_source` | `local` (default) or `gorest` |
| GoREST Token | `ulp_gorest_token_encrypted` | Encrypted Bearer token; leave password field empty to keep existing token |
| Days since last user update | `ulp_update_interval` | Integer; used by cron to flag stale local users |

---

## Data model (local database)

Table: `{wpdb->prefix}ulp_users`

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT, PK, auto-increment | |
| `email` | VARCHAR(255), UNIQUE, NOT NULL | |
| `name` | VARCHAR(150), NOT NULL | |
| `country` | VARCHAR(2), NOT NULL | ISO country code from helper list |
| `city` | VARCHAR(100), NOT NULL | |
| `gender` | ENUM(`male`, `female`) | |
| `status` | ENUM(`active`, `inactive`), default `active` | |
| `created` | DATE, NOT NULL | |
| `updated` | DATE, NOT NULL | Refreshed on edit (`wp_date("Y-m-d")`) |

Indexes: `gender`, `status`.

---

## GoREST API integration

- Base URL: `https://gorest.in/public/v2/users`
- Methods: GET (list with `?per_page=100`, single user), POST, PUT, DELETE
- Authorization: `Bearer {token}` when token is configured
- List endpoint results are cached in transient `ulp_gorest_users_{md5(token)}` (or `ulp_gorest_users_no_token` when no token is set) for 5 minutes
- Client-side filtering, sorting, and pagination applied in PHP after fetch
- Create/update/delete return `WP_Error` if token is missing or API returns non-2xx

---

## Usage

### Admin

1. **User List** — browse, filter, sort, paginate; click **Edit** or **Create New User**; bulk **Delete Selected**.
2. **User List → Create User** — submit validated form.
3. **Settings → User List Plugin** — choose data source and maintenance options.

### Frontend

Add to any post or page:

```
[user_list]
```

Query parameters on the page URL control behavior (same as admin): `search`, `filter_status`, `filter_gender`, `sort_field`, `sort_order`, `user_page`.

---

## Plugin architecture

### Directory layout

```text
user-list-plugin/
├── user-list-plugin.php
├── includes/
│   ├── core/
│   │   ├── database.php
│   │   ├── gorest-api.php
│   │   ├── encryption.php
│   │   └── cron-jobs.php
│   ├── admin/
│   │   ├── pages/
│   │   │   ├── admin-page.php
│   │   │   ├── admin-create.php
│   │   │   ├── admin-edit.php
│   │   │   └── settings-page.php
│   │   └── partials/
│   │       ├── user-form.php
│   │       ├── modals.php
│   │       └── inactive-users-list.php
│   ├── frontend/
│   │   └── shortcode.php
│   └── helpers/
│       └── countries.php
└── assets/
    ├── css/
    └── js/
        └── user-table.js
```

### File roles

| Path | Role |
|------|------|
| `user-list-plugin.php` | Bootstrap, hooks, asset loading |
| `includes/core/database.php` | Table creation and local CRUD |
| `includes/core/gorest-api.php` | GoREST HTTP client, cache, filter/sort |
| `includes/core/encryption.php` | Encrypt/decrypt API token |
| `includes/core/cron-jobs.php` | Daily inactive-user check |
| `includes/admin/pages/admin-page.php` | Main list and bulk delete |
| `includes/admin/pages/admin-create.php` | Create user page |
| `includes/admin/pages/admin-edit.php` | Edit user page |
| `includes/admin/pages/settings-page.php` | Plugin settings |
| `includes/admin/partials/user-form.php` | Shared form and validation |
| `includes/admin/partials/modals.php` | Success/error modal |
| `includes/admin/partials/inactive-users-list.php` | Stale-user admin notice |
| `includes/frontend/shortcode.php` | `[user_list]` shortcode |
| `includes/helpers/countries.php` | ISO country list and select markup |
| `assets/css/*.css` | Admin and frontend styles |
| `assets/js/user-table.js` | Admin table UX (filters, modals, delete) |

### Key hooks

| Hook | Purpose |
|------|---------|
| `register_activation_hook` | Create table, set DB version, schedule cron |
| `register_deactivation_hook` | Unschedule cron |
| `plugins_loaded` → `ulp_check_db_version` | Migrate table if `ulp_db_version` is behind |
| `admin_menu` | Register admin pages |
| `admin_enqueue_scripts` / `wp_enqueue_scripts` | Load CSS/JS on relevant screens |
| `ulp_check_inactive_users` (cron) | Rebuild inactive user ID list |

### Validation rules (`ulp_user_form_validation`)

- Required fields depend on source (local includes `country`, `city`)
- Valid email via `is_email()`
- `gender` must be `male` or `female`
- `status` must be `active` or `inactive`

---

## User interface behavior (JavaScript)

`assets/js/user-table.js` (admin pages with table/form):

- **Create New User** → navigates to create submenu
- Filter dropdowns sync hidden inputs and submit `#filterForm`
- Search submits on Enter
- **Edit** buttons → edit page with user ID
- Checkbox selection, select-all, delete confirmation modal, JSON-encoded IDs submitted to delete form

Frontend shortcode loads table styles and the same script where applicable (filters work; create/delete UI elements are admin-only).

---

## Limitations and notes

- GoREST list is capped at 100 users per API fetch (`per_page=100`).
- Inactive-user cron only runs when `ulp_data_source` is `local`.
- Frontend shortcode does not expose admin actions.
- Plugin header lists minimal `Requires at least` / `Requires PHP`; ensure your environment meets WordPress and OpenSSL needs for production.

---

## Summary

The plugin is a **dual-backend user directory**: WordPress custom table **or** GoREST API, with full admin CRUD, settings for source/token/stale threshold, daily cron for “not updated in N days” (local only), and a **`[user_list]`** shortcode for public browsing.