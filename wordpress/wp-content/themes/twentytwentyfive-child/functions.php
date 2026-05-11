<?php

function my_child_theme_enqueue_styles(): void
{
    $parent_style = 'parent-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array($parent_style), wp_get_theme()->get('Version'));
}

add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles');

add_filter('ulp_add_icons', 'ulp_add_icons');

function ulp_add_icons(string $status): string
{
    return $status === 'active' ? "$status &#10687" : "$status &#10686";
}
