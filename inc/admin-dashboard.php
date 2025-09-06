<?php

/**
 * Lightweight store dashboard for Shop Managers (Shane)
 */
if (!defined('ABSPATH')) {
    exit;
}


add_action('admin_menu', function () {
    // Limit to users who can manage Woo (Shop Manager/Admin)
    if (!current_user_can('manage_woocommerce')) {
        return;
    }


    add_menu_page(
        __('Store Dashboard', 'toybox'),
        __('Store Dashboard', 'toybox'),
        'manage_woocommerce',
        'toybox-store-dashboard',
        'toybox_render_store_dashboard',
        'dashicons-store',
        3
    );
});


function toybox_render_store_dashboard()
{
    if (!class_exists('WC')) {
        echo '<div class="wrap"><h1>Store Dashboard</h1><p>WooCommerce is not active.</p></div>';
        return;
    }


    // Quick stats
    $orders = wc_get_orders([
        'limit' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => array_keys(wc_get_order_statuses())
    ]);


    $total_sales = wc_get_container()->get(\Automattic\WooCommerce\Admin\API\Reports\Products\Controller::class) ? null : null; // placeholder if later expanding


    echo '<div class="wrap"><h1>Store Dashboard</h1>';


    echo '<h2>Recent Orders</h2>';
    echo '<table class="widefat fixed striped"><thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody>';
    foreach ($orders as $o) {
        $order_id = $o->get_id();
        $name = $o->get_formatted_billing_full_name();
        $total = $o->get_formatted_order_total();
        $status = wc_get_order_status_name($o->get_status());
        $date = $o->get_date_created() ? $o->get_date_created()->date_i18n(get_option('date_format') . ' ' . get_option('time_format')) : '';
        echo '<tr>';
        echo '<td><a href="' . esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')) . '">#' . esc_html($order_id) . '</a></td>';
        echo '<td>' . esc_html($name) . '</td>';
        echo '<td>' . wp_kses_post($total) . '</td>';
        echo '<td>' . esc_html($status) . '</td>';
        echo '<td>' . esc_html($date) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';


    echo '<h2 style="margin-top:24px;">Low Stock</h2>';
    $low_stock = wc_get_products([
        'status' => 'publish',
        'stock_status' => 'instock',
        'stock_quantity' => 5,
        'orderby' => 'stock',
        'order' => 'ASC',
        'limit' => 20,
        'return' => 'objects'
    ]);


    if (empty($low_stock)) {
        echo '<p>No low stock items 🎉</p>';
    } else {
        echo '<table class="widefat fixed striped"><thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Price</th></tr></thead><tbody>';
        foreach ($low_stock as $p) {
            echo '<tr>';
            echo '<td><a href="' . get_edit_post_link($p->get_id()) . '">' . esc_html($p->get_name()) . '</a></td>';
            echo '<td>' . esc_html($p->get_sku()) . '</td>';
            echo '<td>' . esc_html($p->get_stock_quantity()) . '</td>';
            echo '<td>' . wp_kses_post($p->get_price_html()) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }


    echo '</div>';
}
