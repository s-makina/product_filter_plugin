<?php
/**
 * Plugin Name: Advanced Product Filter for WooCommerce
 * Plugin URI: https://example.com/product-filter-plugin
 * Description: A comprehensive product filter plugin for WooCommerce with a clean UI and extensive configuration options.
 * Version: 1.0.0
 * Author: Salvation Makina
 * Author URI: https://example.com
 * Text Domain: product-filter-plugin
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 8.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('PRODUCT_FILTER_VERSION', '1.0.0');
define('PRODUCT_FILTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PRODUCT_FILTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PRODUCT_FILTER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function product_filter_check_woocommerce() {
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', 'product_filter_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * Admin notice for missing WooCommerce
 */
function product_filter_woocommerce_missing_notice() {
    ?>
    <div class="error">
        <p><?php _e('Advanced Product Filter requires WooCommerce to be installed and active.', 'product-filter-plugin'); ?></p>
    </div>
    <?php
}

/**
 * The core plugin class
 */
class Product_Filter_Plugin {

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Admin class instance
     */
    public $admin = null;

    /**
     * Public class instance
     */
    public $public = null;

    /**
     * Main Plugin Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();

        // Initialize admin and public classes
        $this->admin = new Product_Filter_Admin();
        $this->public = new Product_Filter_Public();
    }

    /**
     * Include required files
     */
    private function includes() {
        // Admin
        require_once PRODUCT_FILTER_PLUGIN_DIR . 'admin/class-product-filter-admin.php';

        // Public
        require_once PRODUCT_FILTER_PLUGIN_DIR . 'public/class-product-filter-public.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Activate the plugin
     */
    public function activate() {
        // Create default settings
        $default_settings = array(
            'enable_price_filter' => 'yes',
            'enable_category_filter' => 'yes',
            'enable_attribute_filter' => 'yes',
            'enable_tag_filter' => 'yes',
            'enable_rating_filter' => 'yes',
            'enable_sale_filter' => 'yes',
            'enable_stock_filter' => 'yes',
            'filter_position' => 'sidebar',
            'use_ajax' => 'yes',
            'mobile_display' => 'accordion',
            'filter_style' => 'default',
            'disable_css' => 'no'
        );

        if (!get_option('product_filter_settings')) {
            add_option('product_filter_settings', $default_settings);
        }
    }

    /**
     * Deactivate the plugin
     */
    public function deactivate() {
        // Cleanup if needed
    }

    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'product-filter-plugin',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
}

/**
 * Debug function to log messages
 */
function product_filter_debug($message) {
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

/**
 * Initialize the plugin
 */
function product_filter_plugin() {
    // Check if WooCommerce is active
    if (product_filter_check_woocommerce()) {
        product_filter_debug('Product Filter Plugin initialized');
        return Product_Filter_Plugin::instance();
    }
    return null;
}

// Start the plugin
$GLOBALS['product_filter_plugin'] = product_filter_plugin();

/**
 * Template function to display the product filter
 *
 * @param array $args Filter arguments
 * @return void
 */
function product_filter_display($args = array()) {
    if (isset($GLOBALS['product_filter_plugin']) && $GLOBALS['product_filter_plugin'] !== null) {
        echo $GLOBALS['product_filter_plugin']->public->product_filter_shortcode($args);
    }
}
