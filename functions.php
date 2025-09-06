<?php

/**
 * Toybox Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

function toybox_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce'); // important for shop
    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'toybox')
    ));
}
add_action('after_setup_theme', 'toybox_setup');

function toybox_enqueue_scripts() {
    wp_enqueue_style('toybox-style', get_stylesheet_uri());
    wp_enqueue_style('toybox-bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css");
    wp_enqueue_script('toybox-bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js", array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'toybox_enqueue_scripts');


add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('woocommerce');


    // Wide/thin content widths for blocks
    add_theme_support('custom-spacing');
});


add_action('enqueue_block_assets', function () {
    // Front + editor shared CSS could be enqueued here if needed
    // wp_enqueue_style('toybox-shared', get_template_directory_uri() . '/assets/css/shared.css', [], '1.0');
});


// Register pattern category
register_block_pattern_category('toybox', [
    'label' => __('Toybox', 'toybox')
]);


// Image sizes helpful for product cards
add_action('after_setup_theme', function () {
    add_image_size('toybox-card', 600, 600, true);
});


// Allow SVG upload for simple icons (optional & basic)
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});


// Include custom admin dashboard for Shop Managers
require_once get_template_directory() . '/inc/admin-dashboard.php';
require_once get_template_directory() . '/inc/helpers.php';
