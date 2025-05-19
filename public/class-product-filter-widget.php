<?php
/**
 * The Product Filter Widget
 */
class Product_Filter_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'product_filter_widget', // Base ID
            __('Product Filter', 'product-filter-plugin'), // Name
            array('description' => __('Display product filters in your sidebar', 'product-filter-plugin')) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        // Only show on WooCommerce pages
        if (!is_woocommerce() && !is_shop() && !is_product_category() && !is_product_tag()) {
            return;
        }
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        // Get filter types to display
        $filters = !empty($instance['filters']) ? explode(',', $instance['filters']) : array('all');
        $style = !empty($instance['style']) ? $instance['style'] : '';
        
        // Get plugin instance
        if (isset($GLOBALS['product_filter_plugin']) && $GLOBALS['product_filter_plugin'] !== null) {
            echo $GLOBALS['product_filter_plugin']->public->product_filter_shortcode(array(
                'filters' => implode(',', $filters),
                'style' => $style,
            ));
        }
        
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Filter Products', 'product-filter-plugin');
        $filters = !empty($instance['filters']) ? $instance['filters'] : 'all';
        $style = !empty($instance['style']) ? $instance['style'] : '';
        
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'product-filter-plugin'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('filters')); ?>"><?php _e('Filters to Display:', 'product-filter-plugin'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('filters')); ?>" name="<?php echo esc_attr($this->get_field_name('filters')); ?>">
                <option value="all" <?php selected($filters, 'all'); ?>><?php _e('All Filters', 'product-filter-plugin'); ?></option>
                <option value="price" <?php selected($filters, 'price'); ?>><?php _e('Price Only', 'product-filter-plugin'); ?></option>
                <option value="category" <?php selected($filters, 'category'); ?>><?php _e('Categories Only', 'product-filter-plugin'); ?></option>
                <option value="attribute" <?php selected($filters, 'attribute'); ?>><?php _e('Attributes Only', 'product-filter-plugin'); ?></option>
                <option value="price,category" <?php selected($filters, 'price,category'); ?>><?php _e('Price + Categories', 'product-filter-plugin'); ?></option>
                <option value="price,attribute" <?php selected($filters, 'price,attribute'); ?>><?php _e('Price + Attributes', 'product-filter-plugin'); ?></option>
                <option value="category,attribute" <?php selected($filters, 'category,attribute'); ?>><?php _e('Categories + Attributes', 'product-filter-plugin'); ?></option>
            </select>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>"><?php _e('Style:', 'product-filter-plugin'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>" name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <option value="" <?php selected($style, ''); ?>><?php _e('Default (from settings)', 'product-filter-plugin'); ?></option>
                <option value="default" <?php selected($style, 'default'); ?>><?php _e('Default Style', 'product-filter-plugin'); ?></option>
                <option value="minimal" <?php selected($style, 'minimal'); ?>><?php _e('Minimal Style', 'product-filter-plugin'); ?></option>
                <option value="boxed" <?php selected($style, 'boxed'); ?>><?php _e('Boxed Style', 'product-filter-plugin'); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['filters'] = (!empty($new_instance['filters'])) ? sanitize_text_field($new_instance['filters']) : 'all';
        $instance['style'] = (!empty($new_instance['style'])) ? sanitize_text_field($new_instance['style']) : '';
        
        return $instance;
    }
}
