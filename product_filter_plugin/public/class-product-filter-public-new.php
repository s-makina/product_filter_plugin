<?php
/**
 * The public-facing functionality of the plugin.
 */
class Product_Filter_Public {

    /**
     * The plugin settings
     */
    private $settings;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->settings = get_option('product_filter_settings');
        
        // Register shortcode
        add_shortcode('product_filter', array($this, 'product_filter_shortcode'));
        
        // Register widget
        add_action('widgets_init', array($this, 'register_widgets'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add filter to shop page
        add_action('woocommerce_before_shop_loop', array($this, 'add_filter_to_shop'), 30);
        
        // AJAX handlers
        add_action('wp_ajax_product_filter_ajax', array($this, 'handle_ajax_filter'));
        add_action('wp_ajax_nopriv_product_filter_ajax', array($this, 'handle_ajax_filter'));
        
        // Add filter parameters to WooCommerce query
        add_action('woocommerce_product_query', array($this, 'filter_product_query'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        // Check if CSS is disabled in settings
        if (isset($this->settings['disable_css']) && $this->settings['disable_css'] === 'yes') {
            // Only enqueue jQuery UI styles for the slider
            wp_enqueue_style(
                'jquery-ui-style',
                '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
                array(),
                '1.12.1',
                'all'
            );
            return;
        }
        
        // Enqueue plugin styles
        wp_enqueue_style(
            'product-filter-public',
            PRODUCT_FILTER_PLUGIN_URL . 'public/css/product-filter-public.css',
            array(),
            PRODUCT_FILTER_VERSION . '.' . time(), // Add timestamp to prevent caching
            'all'
        );
        
        // Add jQuery UI slider styles for price filter
        wp_enqueue_style(
            'jquery-ui-style',
            '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            array(),
            '1.12.1',
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        // Always enqueue scripts to ensure they're available
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-slider');
        
        wp_enqueue_script(
            'product-filter-public',
            PRODUCT_FILTER_PLUGIN_URL . 'public/js/product-filter-public.js',
            array('jquery', 'jquery-ui-slider'),
            PRODUCT_FILTER_VERSION . '.' . time(), // Add timestamp to prevent caching
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'product-filter-public',
            'product_filter_params',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('product_filter_nonce'),
                'use_ajax' => isset($this->settings['use_ajax']) && $this->settings['use_ajax'] === 'yes' ? 'yes' : 'no',
                'mobile_display' => isset($this->settings['mobile_display']) ? $this->settings['mobile_display'] : 'accordion',
                'filter_position' => isset($this->settings['filter_position']) ? $this->settings['filter_position'] : 'sidebar',
                'shop_page_url' => get_permalink(wc_get_page_id('shop')),
                'current_page_url' => get_permalink(),
            )
        );
    }

    /**
     * Register widgets
     */
    public function register_widgets() {
        // Include widget class first
        require_once PRODUCT_FILTER_PLUGIN_DIR . 'public/class-product-filter-widget.php';
        
        // Then register the widget
        register_widget('Product_Filter_Widget');
    }

    /**
     * Add filter to shop page
     */
    public function add_filter_to_shop() {
        $filter_position = isset($this->settings['filter_position']) ? $this->settings['filter_position'] : 'sidebar';
        
        if ($filter_position === 'above_content') {
            echo $this->render_filter();
        }
    }

    /**
     * Shortcode callback
     */
    public function product_filter_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'filters' => 'all', // all, price, category, attribute, tag, rating, sale, stock
                'style' => '', // Override default style
                'preserve_layout' => 'yes', // Whether to preserve the current page layout
                'target_url' => '', // Custom target URL for the form
            ),
            $atts,
            'product_filter'
        );
        
        return $this->render_filter($atts);
    }

    /**
     * Render the filter HTML
     */
    public function render_filter($atts = array()) {
        $filters = isset($atts['filters']) ? explode(',', $atts['filters']) : array('all');
        $style = isset($atts['style']) ? $atts['style'] : '';
        $preserve_layout = isset($atts['preserve_layout']) ? $atts['preserve_layout'] : 'yes';
        $custom_target = isset($atts['target_url']) ? $atts['target_url'] : '';
        
        if (empty($style)) {
            $style = isset($this->settings['filter_style']) ? $this->settings['filter_style'] : 'default';
        }
        
        // Determine form action URL
        $form_action = '';
        if (!empty($custom_target)) {
            $form_action = esc_url($custom_target);
        } else {
            // Use current page URL if preserve_layout is yes, otherwise use shop page
            $form_action = ($preserve_layout === 'yes') ? esc_url(get_permalink()) : esc_url(wc_get_page_permalink('shop'));
        }
        
        // Start with a form to ensure proper submission
        $filter_html = '<form method="get" action="' . $form_action . '" class="product-filter-form">';
        
        // Add hidden input to preserve layout
        if ($preserve_layout === 'yes') {
            $filter_html .= '<input type="hidden" name="preserve_layout" value="yes" />';
        }
        
        // Add any existing query parameters that should be preserved
        foreach ($_GET as $key => $value) {
            if (!in_array($key, array('min_price', 'max_price', 'product_cat', 'product_tag', 'rating_filter', 'on_sale', 'stock_status', 'filter_'))) {
                $filter_html .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }
        }
        
        $filter_html .= '<div class="product-filter-container filter-style-' . esc_attr($style) . '">';
        
        // Mobile toggle button
        $filter_html .= '<button type="button" class="product-filter-toggle-mobile">' . __('Filter Products', 'product-filter-plugin') . '</button>';
        
        $filter_html .= '<div class="product-filter-wrapper">';
        
        // Add reset button
        $filter_html .= '<div class="product-filter-reset">';
        $filter_html .= '<a href="' . esc_url(remove_query_arg(array('min_price', 'max_price', 'product_cat', 'product_tag', 'rating_filter', 'on_sale', 'stock_status'))) . '">' . __('Reset Filters', 'product-filter-plugin') . '</a>';
        $filter_html .= '</div>';
        
        // Price filter
        if ((in_array('all', $filters) || in_array('price', $filters)) && isset($this->settings['enable_price_filter']) && $this->settings['enable_price_filter'] === 'yes') {
            $filter_html .= $this->render_price_filter();
        }
        
        // Category filter
        if ((in_array('all', $filters) || in_array('category', $filters)) && isset($this->settings['enable_category_filter']) && $this->settings['enable_category_filter'] === 'yes') {
            $filter_html .= $this->render_category_filter();
        }
        
        // Attribute filter
        if ((in_array('all', $filters) || in_array('attribute', $filters)) && isset($this->settings['enable_attribute_filter']) && $this->settings['enable_attribute_filter'] === 'yes') {
            $filter_html .= $this->render_attribute_filter();
        }
        
        // Tag filter
        if ((in_array('all', $filters) || in_array('tag', $filters)) && isset($this->settings['enable_tag_filter']) && $this->settings['enable_tag_filter'] === 'yes') {
            $filter_html .= $this->render_tag_filter();
        }
        
        // Rating filter
        if ((in_array('all', $filters) || in_array('rating', $filters)) && isset($this->settings['enable_rating_filter']) && $this->settings['enable_rating_filter'] === 'yes') {
            $filter_html .= $this->render_rating_filter();
        }
        
        // Sale filter
        if ((in_array('all', $filters) || in_array('sale', $filters)) && isset($this->settings['enable_sale_filter']) && $this->settings['enable_sale_filter'] === 'yes') {
            $filter_html .= $this->render_sale_filter();
        }
        
        // Stock filter
        if ((in_array('all', $filters) || in_array('stock', $filters)) && isset($this->settings['enable_stock_filter']) && $this->settings['enable_stock_filter'] === 'yes') {
            $filter_html .= $this->render_stock_filter();
        }
        
        $filter_html .= '</div>'; // End .product-filter-wrapper
        $filter_html .= '</div>'; // End .product-filter-container
        
        // Add submit button
        $filter_html .= '<div class="product-filter-submit">';
        $filter_html .= '<button type="submit" class="button">' . __('Apply Filters', 'product-filter-plugin') . '</button>';
        $filter_html .= '</div>';
        
        $filter_html .= '</form>'; // End form
        
        return $filter_html;
    }
}