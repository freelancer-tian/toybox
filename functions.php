<?php

/**
 * Theme Functions – Toybox
 * @package toybox
 */

if (! defined('ABSPATH')) exit; // No direct access.

// --------------------------------------------------
// 1) CONSTANTS & HELPERS
// --------------------------------------------------
if (! defined('TOYBOX_VERSION')) {
    $theme      = wp_get_theme();
    $theme_ver  = $theme && $theme->get('Version') ? $theme->get('Version') : '1.0.0';
    define('TOYBOX_VERSION', $theme_ver);
}
define('TOYBOX_DIR', get_template_directory());
define('TOYBOX_URI', get_template_directory_uri());

/**
 * Quick helper to version assets by file mtime if present (fallback to theme version).
 */
function toybox_asset_ver($relative_path)
{
    $file = trailingslashit(TOYBOX_DIR) . ltrim($relative_path, '/');
    return file_exists($file) ? filemtime($file) : TOYBOX_VERSION;
}

// --------------------------------------------------
// 2) THEME SETUP
// --------------------------------------------------
function toybox_setup()
{
    // Make theme available for translation.
    load_theme_textdomain('toybox', TOYBOX_DIR . '/languages');

    // Core supports.
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('custom-logo', ['height' => 60, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor.css');

    // WooCommerce (if active).
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Image sizes (adjust to taste).
    add_image_size('toybox-card', 600, 600, true);

    // Menus.
    register_nav_menus([
        'primary' => __('Primary Menu', 'toybox'),
        'footer'  => __('Footer Menu', 'toybox'),
    ]);

    // Editor color palette (friendly, playful but clean).
    add_theme_support('editor-color-palette', [
        ['name' => __('Primary', 'toybox'),   'slug' => 'primary',   'color' => '#ff6f61'],
        ['name' => __('Secondary', 'toybox'), 'slug' => 'secondary', 'color' => '#ffd166'],
        ['name' => __('Accent', 'toybox'),    'slug' => 'accent',    'color' => '#06d6a0'],
        ['name' => __('Dark', 'toybox'),      'slug' => 'dark',      'color' => '#073b4c'],
        ['name' => __('Light', 'toybox'),     'slug' => 'light',     'color' => '#f8f9fa'],
    ]);
}
add_action('after_setup_theme', 'toybox_setup');

// --------------------------------------------------
// 3) WIDGET AREAS
// --------------------------------------------------
function toybox_widgets_init()
{
    register_sidebar([
        'name'          => __('Sidebar', 'toybox'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'toybox'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Footer', 'toybox'),
        'id'            => 'footer-1',
        'description'   => __('Footer widgets.', 'toybox'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'toybox_widgets_init');

// --------------------------------------------------
// 4) ASSETS (CSS/JS)
// --------------------------------------------------
function toybox_enqueue_assets()
{
    // Google Fonts (Poppins). You can self-host later for perf/privacy.
    wp_enqueue_style(
        'toybox-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    // Bootstrap CSS (optional but handy for quick polish).
    wp_enqueue_style(
        'toybox-bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
    );

    // Theme stylesheet (style.css).
    wp_enqueue_style(
        'toybox-style',
        get_stylesheet_uri(),
        ['toybox-bootstrap', 'toybox-fonts'],
        TOYBOX_VERSION
    );

    // Your additional theme CSS (put custom styles in assets/css/main.css).
    $main_css = 'assets/css/main.css';
    if (file_exists(TOYBOX_DIR . '/' . $main_css)) {
        wp_enqueue_style(
            'toybox-main',
            TOYBOX_URI . '/' . $main_css,
            ['toybox-style'],
            toybox_asset_ver($main_css)
        );
    }

    // Bootstrap Bundle (with Popper) + theme JS.
    wp_enqueue_script(
        'toybox-bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        ['jquery'],
        '5.3.3',
        true
    );

    $main_js = 'assets/js/main.js';
    if (file_exists(TOYBOX_DIR . '/' . $main_js)) {
        wp_enqueue_script(
            'toybox-main',
            TOYBOX_URI . '/' . $main_js,
            ['jquery'],
            toybox_asset_ver($main_js),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'toybox_enqueue_assets');

// --------------------------------------------------
// 5) CLEANUPS & QoL
// --------------------------------------------------

// Remove emoji bloat (keeps the admin lighter).
function toybox_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'toybox_disable_emojis');

// Allow SVG uploads (basic mime check).
function toybox_allow_svg($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'toybox_allow_svg');

// Add "current-menu-item" class support for Bootstrap active states (optional enhancement).
function toybox_nav_menu_link_class($classes, $item)
{
    if (in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true)) {
        $classes[] = 'active';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'toybox_nav_menu_link_class', 10, 2);

// --------------------------------------------------
// 6) WOOCOMMERCE TWEAKS (safe if WC inactive)
// --------------------------------------------------
if (class_exists('WooCommerce')) {

    // Products per page (adjust to taste).
    function toybox_products_per_page($cols)
    {
        return 12;
    }
    add_filter('loop_shop_per_page', 'toybox_products_per_page', 20);

    // Wrap product thumbnails with a consistent ratio class (for neat grids).
    function toybox_product_thumbnail_wrapper_start()
    {
        echo '<div class="toybox-thumb ratio ratio-1x1 mb-2">';
    }
    function toybox_product_thumbnail_wrapper_end()
    {
        echo '</div>';
    }
    add_action('woocommerce_before_shop_loop_item_title', 'toybox_product_thumbnail_wrapper_start', 8);
    add_action('woocommerce_before_shop_loop_item_title', 'toybox_product_thumbnail_wrapper_end', 12);

    // Sale badge text.
    function toybox_custom_sale_flash($html, $post, $product)
    {
        return '<span class="badge bg-danger position-absolute top-0 start-0 m-2">' . esc_html__('Sale', 'toybox') . '</span>';
    }
    add_filter('woocommerce_sale_flash', 'toybox_custom_sale_flash', 10, 3);
}

// --------------------------------------------------
// 7) CUSTOMIZER HOOK (placeholder for later branding controls)
// --------------------------------------------------
function toybox_customize_register($wp_customize)
{
    // Example: a simple setting for header tagline toggle.
    $wp_customize->add_setting('toybox_show_tagline', ['default' => true, 'transport' => 'refresh']);
    $wp_customize->add_control('toybox_show_tagline', [
        'label'   => __('Show Header Tagline', 'toybox'),
        'section' => 'title_tagline',
        'type'    => 'checkbox',
    ]);
}
add_action('customize_register', 'toybox_customize_register');

// --------------------------------------------------
// 8) REQUIRED FILES (if you split code later; keep tidy).
// --------------------------------------------------
// require TOYBOX_DIR . '/inc/template-tags.php';
// require TOYBOX_DIR . '/inc/woocommerce.php';
