<?php
/**
 * Admin page to check if the plugin is loaded
 */

// Add admin menu
add_action('admin_menu', 'product_filter_plugin_admin_menu');

function product_filter_plugin_admin_menu() {
    add_management_page(
        'Product Filter Plugin Status',
        'Product Filter Status',
        'manage_options',
        'product-filter-status',
        'product_filter_plugin_status_page'
    );
}

function product_filter_plugin_status_page() {
    ?>
    <div class="wrap">
        <h1>Product Filter Plugin Status</h1>
        <p>This page shows the status of the Product Filter Plugin.</p>

        <h2>Plugin Files</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Main plugin file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/product-filter-plugin.php') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Admin class file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/admin/class-product-filter-admin.php') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Public class file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/public/class-product-filter-public.php') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Widget class file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/public/class-product-filter-widget.php') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Admin CSS file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/admin/css/product-filter-admin.css') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Public CSS file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/public/css/product-filter-public.css') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Admin JS file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/admin/js/product-filter-admin.js') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Public JS file</td>
                    <td><?php echo file_exists(plugin_dir_path(__FILE__) . 'product_filter_plugin/public/js/product-filter-public.js') ? 'Exists' : 'Missing'; ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Plugin Classes</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Product_Filter_Plugin</td>
                    <td><?php echo class_exists('Product_Filter_Plugin') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Product_Filter_Admin</td>
                    <td><?php echo class_exists('Product_Filter_Admin') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Product_Filter_Public</td>
                    <td><?php echo class_exists('Product_Filter_Public') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>Product_Filter_Widget</td>
                    <td><?php echo class_exists('Product_Filter_Widget') ? 'Exists' : 'Missing'; ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Plugin Functions</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Function</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>product_filter_plugin</td>
                    <td><?php echo function_exists('product_filter_plugin') ? 'Exists' : 'Missing'; ?></td>
                </tr>
                <tr>
                    <td>product_filter_display</td>
                    <td><?php echo function_exists('product_filter_display') ? 'Exists' : 'Missing'; ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Plugin Initialization</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Check</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Plugin initialized</td>
                    <td><?php echo isset($GLOBALS['product_filter_plugin']) && $GLOBALS['product_filter_plugin'] !== null ? 'Yes' : 'No'; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}
