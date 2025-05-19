<?php
/**
 * Plugin Name: Product Filter Plugin Loader
 * Plugin URI: https://example.com/product-filter-plugin-loader
 * Description: Loads the Product Filter Plugin
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: product-filter-plugin-loader
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Load the Product Filter Plugin
add_action('plugins_loaded', 'load_product_filter_plugin');

function load_product_filter_plugin() {
    // Check if the plugin file exists
    $plugin_file = plugin_dir_path(__FILE__) . 'product-filter-plugin.php';
    if (file_exists($plugin_file)) {
        // Include the plugin file
        include_once $plugin_file;
    }
}

// Include the admin page
include_once plugin_dir_path(__FILE__) . 'admin-page.php';
