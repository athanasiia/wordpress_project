<?php

// ISSUE [CRITICAL]: Missing ABSPATH guard. Every PHP file in a WordPress installation
// should prevent direct HTTP access. Without this, the file can be loaded outside the
// WordPress bootstrap, potentially exposing path information or executing code without
// the full WP environment. Add as the very first statement after <?php:
// if ( ! defined( 'ABSPATH' ) ) { exit; }

// ISSUE [MEDIUM]: Function name 'my_child_theme_enqueue_styles' has no meaningful prefix
// tied to this theme. Another child theme or plugin using the same generic name will cause
// a fatal "function already declared" PHP error. Use a unique prefix (e.g. ttfc_).
function my_child_theme_enqueue_styles(): void
{
    $parent_style = 'parent-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    // ISSUE [LOW]: wp_get_theme()->get('Version') instantiates a WP_Theme object on
    // every page load just to read the version string. Store the version in a constant
    // or a simple string variable defined once at the top of this file.
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array($parent_style), wp_get_theme()->get('Version'));
}

add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles');

// ISSUE [HIGH - Architectural coupling]: The ulp_add_icons filter belongs to the plugin,
// but its only implementation lives here in the child theme. Switching or deactivating
// this theme silently removes the plugin feature with no warning or fallback. The plugin
// must define a working default inside itself; the theme may override it, but must never
// be the sole place this logic exists.
add_filter('ulp_add_icons', 'ulp_add_icons');

function ulp_add_icons(string $status): string
{
    // ISSUE [HIGH - XSS]: $status is interpolated into the returned HTML string without
    // esc_html(). The caller in admin-page.php echoes the filter result without escaping
    // either (see CRITICAL-02 in plugin audit). A crafted status value from the API or
    // a compromised DB row would execute as HTML/JavaScript in the admin context.
    // Fix: return esc_html( $status ) . ( $status === 'active' ? ' &#10687;' : ' &#10686;' );

    // ISSUE [HIGH - Malformed HTML entities]: Both &#10687 and &#10686 are missing their
    // closing semicolons. Without the semicolon the entity is invalid HTML. Most browsers
    // recover, but the markup fails validation and will break in XML/XHTML contexts.
    // Fix: use &#10687; and &#10686; (note the semicolons).
    return $status === 'active' ? "$status &#10687" : "$status &#10686";
}
