<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test file to check if the plugin is being loaded
echo "Testing Product Filter Plugin\n";

// Check if the required files exist
echo "Checking if files exist:\n";
echo "Main plugin file: " . (file_exists('product-filter-plugin.php') ? "Yes" : "No") . "\n";
echo "Admin class file: " . (file_exists('admin/class-product-filter-admin.php') ? "Yes" : "No") . "\n";
echo "Public class file: " . (file_exists('public/class-product-filter-public.php') ? "Yes" : "No") . "\n";
echo "Widget class file: " . (file_exists('public/class-product-filter-widget.php') ? "Yes" : "No") . "\n";
echo "Admin CSS file: " . (file_exists('admin/css/product-filter-admin.css') ? "Yes" : "No") . "\n";
echo "Public CSS file: " . (file_exists('public/css/product-filter-public.css') ? "Yes" : "No") . "\n";
echo "Admin JS file: " . (file_exists('admin/js/product-filter-admin.js') ? "Yes" : "No") . "\n";
echo "Public JS file: " . (file_exists('public/js/product-filter-public.js') ? "Yes" : "No") . "\n";

// Define WordPress constants for testing
if (!defined('WPINC')) {
    define('WPINC', true);
}

// Include the plugin file
try {
    include_once 'product-filter-plugin.php';
    echo "Plugin file included successfully\n";
} catch (Exception $e) {
    echo "Error including plugin file: " . $e->getMessage() . "\n";
}

// Check if the plugin class exists
if (class_exists('Product_Filter_Plugin')) {
    echo "Product_Filter_Plugin class exists\n";
} else {
    echo "Product_Filter_Plugin class does not exist\n";
}

// Check if the admin class exists
if (class_exists('Product_Filter_Admin')) {
    echo "Product_Filter_Admin class exists\n";
} else {
    echo "Product_Filter_Admin class does not exist\n";
}

// Check if the public class exists
if (class_exists('Product_Filter_Public')) {
    echo "Product_Filter_Public class exists\n";
} else {
    echo "Product_Filter_Public class does not exist\n";
}

// Check if the widget class exists
if (class_exists('Product_Filter_Widget')) {
    echo "Product_Filter_Widget class exists\n";
} else {
    echo "Product_Filter_Widget class does not exist\n";
}

// Check if the plugin function exists
if (function_exists('product_filter_plugin')) {
    echo "product_filter_plugin function exists\n";
} else {
    echo "product_filter_plugin function does not exist\n";
}

// Check if the plugin is initialized
if (isset($GLOBALS['product_filter_plugin']) && $GLOBALS['product_filter_plugin'] !== null) {
    echo "Plugin is initialized\n";
} else {
    echo "Plugin is not initialized\n";
}
