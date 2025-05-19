<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Product_Filter_Admin {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles($hook) {
        if ('woocommerce_page_product-filter-settings' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'product-filter-admin',
            PRODUCT_FILTER_PLUGIN_URL . 'admin/css/product-filter-admin.css',
            array(),
            PRODUCT_FILTER_VERSION,
            'all'
        );

        // Add WordPress color picker
        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts($hook) {
        if ('woocommerce_page_product-filter-settings' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'product-filter-admin',
            PRODUCT_FILTER_PLUGIN_URL . 'admin/js/product-filter-admin.js',
            array('jquery', 'wp-color-picker'),
            PRODUCT_FILTER_VERSION,
            false
        );
    }

    /**
     * Add options page to the admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Product Filter Settings', 'product-filter-plugin'),
            __('Product Filter', 'product-filter-plugin'),
            'manage_options',
            'product-filter-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'product_filter_settings_group',
            'product_filter_settings',
            array($this, 'sanitize_settings')
        );

        // General Settings Section
        add_settings_section(
            'product_filter_general_section',
            __('General Settings', 'product-filter-plugin'),
            array($this, 'general_section_callback'),
            'product-filter-settings'
        );

        // Filter Types Section
        add_settings_section(
            'product_filter_types_section',
            __('Filter Types', 'product-filter-plugin'),
            array($this, 'filter_types_section_callback'),
            'product-filter-settings'
        );

        // Display Settings Section
        add_settings_section(
            'product_filter_display_section',
            __('Display Settings', 'product-filter-plugin'),
            array($this, 'display_section_callback'),
            'product-filter-settings'
        );

        // Advanced Settings Section
        add_settings_section(
            'product_filter_advanced_section',
            __('Advanced Settings', 'product-filter-plugin'),
            array($this, 'advanced_section_callback'),
            'product-filter-settings'
        );

        // General Settings Fields
        add_settings_field(
            'use_ajax',
            __('AJAX Filtering', 'product-filter-plugin'),
            array($this, 'use_ajax_callback'),
            'product-filter-settings',
            'product_filter_general_section'
        );

        add_settings_field(
            'filter_position',
            __('Filter Position', 'product-filter-plugin'),
            array($this, 'filter_position_callback'),
            'product-filter-settings',
            'product_filter_general_section'
        );

        // Filter Types Fields
        add_settings_field(
            'enable_price_filter',
            __('Price Filter', 'product-filter-plugin'),
            array($this, 'enable_price_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        add_settings_field(
            'enable_category_filter',
            __('Category Filter', 'product-filter-plugin'),
            array($this, 'enable_category_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        add_settings_field(
            'enable_attribute_filter',
            __('Attribute Filter', 'product-filter-plugin'),
            array($this, 'enable_attribute_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        add_settings_field(
            'enable_tag_filter',
            __('Tag Filter', 'product-filter-plugin'),
            array($this, 'enable_tag_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        add_settings_field(
            'enable_rating_filter',
            __('Rating Filter', 'product-filter-plugin'),
            array($this, 'enable_rating_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        add_settings_field(
            'enable_sale_filter',
            __('On Sale Filter', 'product-filter-plugin'),
            array($this, 'enable_sale_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        add_settings_field(
            'enable_stock_filter',
            __('Stock Status Filter', 'product-filter-plugin'),
            array($this, 'enable_stock_filter_callback'),
            'product-filter-settings',
            'product_filter_types_section'
        );

        // Display Settings Fields
        add_settings_field(
            'mobile_display',
            __('Mobile Display', 'product-filter-plugin'),
            array($this, 'mobile_display_callback'),
            'product-filter-settings',
            'product_filter_display_section'
        );

        add_settings_field(
            'filter_style',
            __('Filter Style', 'product-filter-plugin'),
            array($this, 'filter_style_callback'),
            'product-filter-settings',
            'product_filter_display_section'
        );

        // Advanced Settings Fields
        add_settings_field(
            'disable_css',
            __('Disable Plugin CSS', 'product-filter-plugin'),
            array($this, 'disable_css_callback'),
            'product-filter-settings',
            'product_filter_advanced_section'
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();

        // Sanitize each setting field
        $sanitized_input['use_ajax'] = isset($input['use_ajax']) ? 'yes' : 'no';
        $sanitized_input['filter_position'] = sanitize_text_field($input['filter_position']);
        $sanitized_input['enable_price_filter'] = isset($input['enable_price_filter']) ? 'yes' : 'no';
        $sanitized_input['enable_category_filter'] = isset($input['enable_category_filter']) ? 'yes' : 'no';
        $sanitized_input['enable_attribute_filter'] = isset($input['enable_attribute_filter']) ? 'yes' : 'no';
        $sanitized_input['enable_tag_filter'] = isset($input['enable_tag_filter']) ? 'yes' : 'no';
        $sanitized_input['enable_rating_filter'] = isset($input['enable_rating_filter']) ? 'yes' : 'no';
        $sanitized_input['enable_sale_filter'] = isset($input['enable_sale_filter']) ? 'yes' : 'no';
        $sanitized_input['enable_stock_filter'] = isset($input['enable_stock_filter']) ? 'yes' : 'no';
        $sanitized_input['mobile_display'] = sanitize_text_field($input['mobile_display']);
        $sanitized_input['filter_style'] = sanitize_text_field($input['filter_style']);
        $sanitized_input['disable_css'] = isset($input['disable_css']) ? 'yes' : 'no';

        return $sanitized_input;
    }

    /**
     * Display the settings page
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('product_filter_settings_group');
                do_settings_sections('product-filter-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Section callbacks
    public function general_section_callback() {
        echo '<p>' . __('Configure general settings for the product filter.', 'product-filter-plugin') . '</p>';
    }

    public function filter_types_section_callback() {
        echo '<p>' . __('Enable or disable specific filter types.', 'product-filter-plugin') . '</p>';
    }

    public function display_section_callback() {
        echo '<p>' . __('Configure how filters are displayed on your site.', 'product-filter-plugin') . '</p>';
    }

    public function advanced_section_callback() {
        echo '<p>' . __('Advanced settings for developers and customization.', 'product-filter-plugin') . '</p>';
    }

    // Field callbacks
    public function use_ajax_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['use_ajax']) && $settings['use_ajax'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="use_ajax" name="product_filter_settings[use_ajax]" ' . $checked . ' />';
        echo '<label for="use_ajax">' . __('Enable AJAX filtering (update products without page reload)', 'product-filter-plugin') . '</label>';
    }

    public function filter_position_callback() {
        $settings = get_option('product_filter_settings');
        $position = isset($settings['filter_position']) ? $settings['filter_position'] : 'sidebar';
        ?>
        <select id="filter_position" name="product_filter_settings[filter_position]">
            <option value="sidebar" <?php selected($position, 'sidebar'); ?>><?php _e('Sidebar', 'product-filter-plugin'); ?></option>
            <option value="above_content" <?php selected($position, 'above_content'); ?>><?php _e('Above Content', 'product-filter-plugin'); ?></option>
            <option value="offcanvas" <?php selected($position, 'offcanvas'); ?>><?php _e('Off-Canvas', 'product-filter-plugin'); ?></option>
        </select>
        <?php
    }

    // Filter type callbacks
    public function enable_price_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_price_filter']) && $settings['enable_price_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_price_filter" name="product_filter_settings[enable_price_filter]" ' . $checked . ' />';
    }

    public function enable_category_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_category_filter']) && $settings['enable_category_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_category_filter" name="product_filter_settings[enable_category_filter]" ' . $checked . ' />';
    }

    public function enable_attribute_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_attribute_filter']) && $settings['enable_attribute_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_attribute_filter" name="product_filter_settings[enable_attribute_filter]" ' . $checked . ' />';
    }

    public function enable_tag_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_tag_filter']) && $settings['enable_tag_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_tag_filter" name="product_filter_settings[enable_tag_filter]" ' . $checked . ' />';
    }

    public function enable_rating_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_rating_filter']) && $settings['enable_rating_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_rating_filter" name="product_filter_settings[enable_rating_filter]" ' . $checked . ' />';
    }

    public function enable_sale_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_sale_filter']) && $settings['enable_sale_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_sale_filter" name="product_filter_settings[enable_sale_filter]" ' . $checked . ' />';
    }

    public function enable_stock_filter_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['enable_stock_filter']) && $settings['enable_stock_filter'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="enable_stock_filter" name="product_filter_settings[enable_stock_filter]" ' . $checked . ' />';
    }

    public function mobile_display_callback() {
        $settings = get_option('product_filter_settings');
        $display = isset($settings['mobile_display']) ? $settings['mobile_display'] : 'accordion';
        ?>
        <select id="mobile_display" name="product_filter_settings[mobile_display]">
            <option value="accordion" <?php selected($display, 'accordion'); ?>><?php _e('Accordion', 'product-filter-plugin'); ?></option>
            <option value="offcanvas" <?php selected($display, 'offcanvas'); ?>><?php _e('Off-Canvas', 'product-filter-plugin'); ?></option>
            <option value="dropdown" <?php selected($display, 'dropdown'); ?>><?php _e('Dropdown', 'product-filter-plugin'); ?></option>
        </select>
        <?php
    }

    public function filter_style_callback() {
        $settings = get_option('product_filter_settings');
        $style = isset($settings['filter_style']) ? $settings['filter_style'] : 'default';
        ?>
        <select id="filter_style" name="product_filter_settings[filter_style]">
            <option value="default" <?php selected($style, 'default'); ?>><?php _e('Default', 'product-filter-plugin'); ?></option>
            <option value="minimal" <?php selected($style, 'minimal'); ?>><?php _e('Minimal', 'product-filter-plugin'); ?></option>
            <option value="boxed" <?php selected($style, 'boxed'); ?>><?php _e('Boxed', 'product-filter-plugin'); ?></option>
        </select>
        <?php
    }

    public function disable_css_callback() {
        $settings = get_option('product_filter_settings');
        $checked = isset($settings['disable_css']) && $settings['disable_css'] === 'yes' ? 'checked' : '';
        echo '<input type="checkbox" id="disable_css" name="product_filter_settings[disable_css]" ' . $checked . ' />';
        echo '<label for="disable_css">' . __('Disable the plugin\'s CSS (use your theme\'s styling instead)', 'product-filter-plugin') . '</label>';
        echo '<p class="description">' . __('Check this if you want to completely disable the plugin\'s CSS and style the filter using your own CSS.', 'product-filter-plugin') . '</p>';
    }
}
