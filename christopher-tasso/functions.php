<?php
function tasso_setup() {
    // Enable featured images
    add_theme_support('post-thumbnails');

    // Enable title tag
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'tasso_setup');

// Enqueue the stylesheet
function tasso_styles() {
    wp_enqueue_style('tasso-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'tasso_styles');
?>
