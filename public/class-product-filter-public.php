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

        if (empty($style)) {
            $style = isset($this->settings['filter_style']) ? $this->settings['filter_style'] : 'default';
        }

        // Use current page URL instead of shop page to prevent redirection
        $current_url = remove_query_arg(array('min_price', 'max_price', 'product_cat', 'product_tag', 'rating_filter', 'on_sale', 'stock_status'));
        $filter_html = '<form method="get" action="' . esc_url($current_url) . '" class="product-filter-form">';

        // Add a hidden input to preserve the current page context
        $filter_html .= '<input type="hidden" name="preserve_layout" value="1">';

        $filter_html .= '<div class="product-filter-container filter-style-' . esc_attr($style) . '">';

        // Mobile toggle button
        $filter_html .= '<button type="button" class="product-filter-toggle-mobile">' . __('Filter Products', 'product-filter-plugin') . '</button>';

        $filter_html .= '<div class="product-filter-wrapper">';

        // Add search filter at the top
        $current_search = isset($_GET['product_search']) ? sanitize_text_field($_GET['product_search']) : '';
        $filter_html .= '<div class="product-filter-section product-filter-search">';
        $filter_html .= '<h4>' . __('Search Products', 'product-filter-plugin') . '</h4>';
        $filter_html .= '<div class="search-input-container">';
        $filter_html .= '<input type="text" name="product_search" placeholder="' . esc_attr__('Search products...', 'product-filter-plugin') . '" value="' . esc_attr($current_search) . '" class="product-search-input" />';
        $filter_html .= '</div>';
        $filter_html .= '</div>';

        // Add reset button
        $filter_html .= '<div class="product-filter-reset">';
        $filter_html .= '<a href="' . esc_url(remove_query_arg(array('min_price', 'max_price', 'product_cat', 'product_tag', 'rating_filter', 'on_sale', 'stock_status', 'product_search'))) . '">' . __('Reset Filters', 'product-filter-plugin') . '</a>';
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

        // Add hidden submit button (for accessibility and non-JS fallback)
        $filter_html .= '<div class="product-filter-submit" style="display: none;">';
        $filter_html .= '<button type="submit" class="button">' . __('Apply Filters', 'product-filter-plugin') . '</button>';
        $filter_html .= '</div>';

        $filter_html .= '</form>'; // End form

        return $filter_html;
    }

    /**
     * Render price filter
     */
    private function render_price_filter() {
        global $wpdb;

        // Get min and max price from products
        $min_price = floor($wpdb->get_var(
            $wpdb->prepare(
                "SELECT min(meta_value + 0) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                '_price'
            )
        ));

        $max_price = ceil($wpdb->get_var(
            $wpdb->prepare(
                "SELECT max(meta_value + 0) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                '_price'
            )
        ));

        // Get current filter values
        $current_min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : $min_price;
        $current_max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : $max_price;

        $html = '<div class="product-filter-section product-filter-price">';
        $html .= '<h4>' . __('Price', 'product-filter-plugin') . '</h4>';
        $html .= '<div class="price-slider-container">';
        $html .= '<div class="price-slider" data-min="' . esc_attr($min_price) . '" data-max="' . esc_attr($max_price) . '" data-current-min="' . esc_attr($current_min_price) . '" data-current-max="' . esc_attr($current_max_price) . '"></div>';
        $html .= '<div class="price-slider-amount">';
        $html .= '<span class="price-label">' . __('Price:', 'product-filter-plugin') . ' </span>';
        $html .= '<span class="price-from">' . get_woocommerce_currency_symbol() . '<span class="from">' . esc_html($current_min_price) . '</span></span>';
        $html .= '<span class="price-separator"> - </span>';
        $html .= '<span class="price-to">' . get_woocommerce_currency_symbol() . '<span class="to">' . esc_html($current_max_price) . '</span></span>';
        $html .= '</div>';
        $html .= '<input type="hidden" name="min_price" value="' . esc_attr($current_min_price) . '" />';
        $html .= '<input type="hidden" name="max_price" value="' . esc_attr($current_max_price) . '" />';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render category filter
     */
    private function render_category_filter() {
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ));

        if (empty($product_categories) || is_wp_error($product_categories)) {
            return '';
        }

        $current_category = isset($_GET['product_cat']) ? sanitize_text_field($_GET['product_cat']) : '';

        $html = '<div class="product-filter-section product-filter-category">';
        $html .= '<h4>' . __('Categories', 'product-filter-plugin') . '</h4>';
        $html .= '<ul class="product-categories">';

        foreach ($product_categories as $category) {
            $selected = $current_category === $category->slug ? 'checked' : '';

            $html .= '<li>';
            $html .= '<label>';
            $html .= '<input type="checkbox" name="product_cat[]" value="' . esc_attr($category->slug) . '" ' . $selected . ' />';
            $html .= esc_html($category->name);
            $html .= '<span class="count">(' . esc_html($category->count) . ')</span>';
            $html .= '</label>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render attribute filter
     */
    private function render_attribute_filter() {
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if (empty($attribute_taxonomies)) {
            return '';
        }

        $html = '<div class="product-filter-section product-filter-attributes">';
        $html .= '<h4>' . __('Product Attributes', 'product-filter-plugin') . '</h4>';

        foreach ($attribute_taxonomies as $attribute) {
            $taxonomy = 'pa_' . $attribute->attribute_name;
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
            ));

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            $current_attribute = isset($_GET['filter_' . $attribute->attribute_name]) ? explode(',', sanitize_text_field($_GET['filter_' . $attribute->attribute_name])) : array();

            $html .= '<div class="attribute-filter">';
            $html .= '<h5>' . esc_html($attribute->attribute_label) . '</h5>';
            $html .= '<ul class="attribute-terms">';

            foreach ($terms as $term) {
                $selected = in_array($term->slug, $current_attribute) ? 'checked' : '';

                $html .= '<li>';
                $html .= '<label>';
                $html .= '<input type="checkbox" name="filter_' . esc_attr($attribute->attribute_name) . '[]" value="' . esc_attr($term->slug) . '" ' . $selected . ' />';
                $html .= esc_html($term->name);
                $html .= '<span class="count">(' . esc_html($term->count) . ')</span>';
                $html .= '</label>';
                $html .= '</li>';
            }

            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render tag filter
     */
    private function render_tag_filter() {
        $product_tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
        ));

        if (empty($product_tags) || is_wp_error($product_tags)) {
            return '';
        }

        $current_tag = isset($_GET['product_tag']) ? sanitize_text_field($_GET['product_tag']) : '';

        $html = '<div class="product-filter-section product-filter-tag">';
        $html .= '<h4>' . __('Tags', 'product-filter-plugin') . '</h4>';
        $html .= '<ul class="product-tags">';

        foreach ($product_tags as $tag) {
            $selected = $current_tag === $tag->slug ? 'checked' : '';

            $html .= '<li>';
            $html .= '<label>';
            $html .= '<input type="checkbox" name="product_tag[]" value="' . esc_attr($tag->slug) . '" ' . $selected . ' />';
            $html .= esc_html($tag->name);
            $html .= '<span class="count">(' . esc_html($tag->count) . ')</span>';
            $html .= '</label>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render rating filter
     */
    private function render_rating_filter() {
        $current_rating = isset($_GET['rating_filter']) ? absint($_GET['rating_filter']) : 0;

        $html = '<div class="product-filter-section product-filter-rating">';
        $html .= '<h4>' . __('Rating', 'product-filter-plugin') . '</h4>';
        $html .= '<ul class="product-ratings">';

        for ($rating = 5; $rating >= 1; $rating--) {
            $selected = $current_rating === $rating ? 'checked' : '';

            $html .= '<li>';
            $html .= '<label>';
            $html .= '<input type="radio" name="rating_filter" value="' . esc_attr($rating) . '" ' . $selected . ' />';

            // Display stars
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $rating) {
                    $html .= '<span class="star star-filled">&#9733;</span>';
                } else {
                    $html .= '<span class="star star-empty">&#9734;</span>';
                }
            }

            $html .= '<span class="rating-text">' . sprintf(__('%s and up', 'product-filter-plugin'), $rating) . '</span>';
            $html .= '</label>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render sale filter
     */
    private function render_sale_filter() {
        $on_sale = isset($_GET['on_sale']) && $_GET['on_sale'] === '1' ? 'checked' : '';

        $html = '<div class="product-filter-section product-filter-sale">';
        $html .= '<h4>' . __('Sale Status', 'product-filter-plugin') . '</h4>';
        $html .= '<label>';
        $html .= '<input type="checkbox" name="on_sale" value="1" ' . $on_sale . ' />';
        $html .= __('Show only products on sale', 'product-filter-plugin');
        $html .= '</label>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render stock filter
     */
    private function render_stock_filter() {
        $stock_status = isset($_GET['stock_status']) ? sanitize_text_field($_GET['stock_status']) : '';

        $html = '<div class="product-filter-section product-filter-stock">';
        $html .= '<h4>' . __('Stock Status', 'product-filter-plugin') . '</h4>';
        $html .= '<ul class="stock-statuses">';

        $statuses = array(
            'instock' => __('In Stock', 'product-filter-plugin'),
            'outofstock' => __('Out of Stock', 'product-filter-plugin'),
            'onbackorder' => __('On Backorder', 'product-filter-plugin'),
        );

        foreach ($statuses as $status => $label) {
            $selected = $stock_status === $status ? 'checked' : '';

            $html .= '<li>';
            $html .= '<label>';
            $html .= '<input type="radio" name="stock_status" value="' . esc_attr($status) . '" ' . $selected . ' />';
            $html .= esc_html($label);
            $html .= '</label>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Handle AJAX filter request
     */
    public function handle_ajax_filter() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'product_filter_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // Get filter parameters
        $filters = array();

        // Parse the form data if it's serialized
        if (isset($_POST['formData'])) {
            parse_str($_POST['formData'], $form_data);

            // Add preserve_layout parameter to maintain the current page context
            $filters['preserve_layout'] = '1';

            // Process form data
            if (isset($form_data['min_price'])) {
                $filters['min_price'] = floatval($form_data['min_price']);
            }

            if (isset($form_data['max_price'])) {
                $filters['max_price'] = floatval($form_data['max_price']);
            }

            if (isset($form_data['product_cat'])) {
                if (is_array($form_data['product_cat'])) {
                    $filters['product_cat'] = implode(',', array_map('sanitize_text_field', $form_data['product_cat']));
                } else {
                    $filters['product_cat'] = sanitize_text_field($form_data['product_cat']);
                }
            }

            if (isset($form_data['product_tag'])) {
                if (is_array($form_data['product_tag'])) {
                    $filters['product_tag'] = implode(',', array_map('sanitize_text_field', $form_data['product_tag']));
                } else {
                    $filters['product_tag'] = sanitize_text_field($form_data['product_tag']);
                }
            }

            // Process search parameter
            if (isset($form_data['product_search']) && !empty($form_data['product_search'])) {
                $filters['product_search'] = sanitize_text_field($form_data['product_search']);
            }

            if (isset($form_data['rating_filter'])) {
                $filters['rating_filter'] = absint($form_data['rating_filter']);
            }

            if (isset($form_data['on_sale'])) {
                $filters['on_sale'] = sanitize_text_field($form_data['on_sale']);
            }

            if (isset($form_data['stock_status'])) {
                $filters['stock_status'] = sanitize_text_field($form_data['stock_status']);
            }

            // Handle attribute filters
            foreach ($form_data as $key => $value) {
                if (strpos($key, 'filter_') === 0) {
                    if (is_array($value)) {
                        $filters[$key] = implode(',', array_map('sanitize_text_field', $value));
                    } else {
                        $filters[$key] = sanitize_text_field($value);
                    }
                }
            }
        } else {
            // Fallback to direct POST data if formData is not provided
            if (isset($_POST['min_price'])) {
                $filters['min_price'] = floatval($_POST['min_price']);
            }

            if (isset($_POST['max_price'])) {
                $filters['max_price'] = floatval($_POST['max_price']);
            }

            if (isset($_POST['product_cat'])) {
                $filters['product_cat'] = sanitize_text_field($_POST['product_cat']);
            }

            if (isset($_POST['product_tag'])) {
                $filters['product_tag'] = sanitize_text_field($_POST['product_tag']);
            }

            if (isset($_POST['rating_filter'])) {
                $filters['rating_filter'] = absint($_POST['rating_filter']);
            }

            if (isset($_POST['on_sale'])) {
                $filters['on_sale'] = sanitize_text_field($_POST['on_sale']);
            }

            if (isset($_POST['stock_status'])) {
                $filters['stock_status'] = sanitize_text_field($_POST['stock_status']);
            }

            // Handle attribute filters
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'filter_') === 0) {
                    $filters[$key] = sanitize_text_field($value);
                }
            }
        }

        // Build query URL - use current page URL if preserve_layout is set
        $current_url = isset($_POST['current_url']) ? esc_url_raw($_POST['current_url']) : get_permalink(wc_get_page_id('shop'));
        $filter_url = add_query_arg($filters, $current_url);

        // Get filtered products HTML
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
        );

        // Apply filters to query
        $this->apply_filters_to_query($args, $filters);

        $products_query = new WP_Query($args);

        ob_start();

        if ($products_query->have_posts()) {
            woocommerce_product_loop_start();

            while ($products_query->have_posts()) {
                $products_query->the_post();
                wc_get_template_part('content', 'product');
            }

            woocommerce_product_loop_end();
        } else {
            echo '<p class="woocommerce-info">' . __('No products found matching your selection.', 'product-filter-plugin') . '</p>';
        }

        wp_reset_postdata();

        $products_html = ob_get_clean();

        wp_send_json_success(array(
            'products_html' => $products_html,
            'filter_url' => $filter_url,
        ));
    }

    /**
     * Apply filters to WooCommerce product query
     */
    public function filter_product_query($query) {
        // Only modify the main shop query, not custom product widgets
        if (isset($_GET['preserve_layout']) && $_GET['preserve_layout'] === '1') {
            // If preserve_layout is set, we're on a custom page with product widgets
            // Don't modify the main query to avoid affecting the layout
            return;
        }

        // Apply filters to the main shop query
        $this->apply_filters_to_query($query->query_vars, $_GET);
    }

    /**
     * Enhance product search to include SKU
     *
     * @param string $search SQL for search query
     * @param WP_Query $wp_query The WP_Query instance
     * @return string Modified search SQL
     */
    public function product_search_by_sku($search, $wp_query) {
        global $wpdb;

        if (!$wp_query->is_search || !$wp_query->is_main_query() || !is_search()) {
            return $search;
        }

        if (!isset($wp_query->query_vars['s']) || empty($wp_query->query_vars['s'])) {
            return $search;
        }

        $search_term = $wp_query->query_vars['s'];

        // Remove existing search filter to prevent infinite loop
        remove_filter('posts_search', array($this, 'product_search_by_sku'), 10);

        // Search in SKU
        $sku_search = $wpdb->prepare(
            "ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE %s)",
            '%' . $wpdb->esc_like($search_term) . '%'
        );

        // If there's already a search clause, add our SKU search with OR
        if (!empty($search)) {
            $search = preg_replace('/^\s*AND\s*/', '', $search);
            $search = " AND ((" . $search . ") OR (" . $sku_search . "))";
        } else {
            $search = " AND (" . $sku_search . ")";
        }

        return $search;
    }

    /**
     * Apply filters to query args
     */
    private function apply_filters_to_query(&$args, $filters) {
        // Search filter
        if (isset($filters['product_search']) && !empty($filters['product_search'])) {
            $search_term = sanitize_text_field($filters['product_search']);
            $args['s'] = $search_term;

            // Enhance search to include product SKU, title, and content
            add_filter('posts_search', array($this, 'product_search_by_sku'), 10, 2);
        }

        // Price filter
        if (isset($filters['min_price']) && isset($filters['max_price'])) {
            $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();

            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => array(floatval($filters['min_price']), floatval($filters['max_price'])),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }

        // Category filter
        if (isset($filters['product_cat'])) {
            $args['tax_query'] = isset($args['tax_query']) ? $args['tax_query'] : array();

            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => explode(',', $filters['product_cat']),
                'operator' => 'IN',
            );
        }

        // Tag filter
        if (isset($filters['product_tag'])) {
            $args['tax_query'] = isset($args['tax_query']) ? $args['tax_query'] : array();

            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => explode(',', $filters['product_tag']),
                'operator' => 'IN',
            );
        }

        // Rating filter
        if (isset($filters['rating_filter']) && $filters['rating_filter'] > 0) {
            $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();

            $args['meta_query'][] = array(
                'key' => '_wc_average_rating',
                'value' => absint($filters['rating_filter']),
                'type' => 'NUMERIC',
                'compare' => '>=',
            );
        }

        // Sale filter
        if (isset($filters['on_sale']) && $filters['on_sale'] === '1') {
            $args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
        }

        // Stock status filter
        if (isset($filters['stock_status']) && !empty($filters['stock_status'])) {
            $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();

            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => sanitize_text_field($filters['stock_status']),
                'compare' => '=',
            );
        }

        // Attribute filters
        foreach ($filters as $key => $value) {
            if (strpos($key, 'filter_') === 0) {
                $attribute = str_replace('filter_', '', $key);
                $taxonomy = 'pa_' . $attribute;

                $args['tax_query'] = isset($args['tax_query']) ? $args['tax_query'] : array();

                $args['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => explode(',', $value),
                    'operator' => 'IN',
                );
            }
        }

        // Set relation for multiple tax queries
        if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
    }
}