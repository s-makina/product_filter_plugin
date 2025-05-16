# Advanced Product Filter for WooCommerce

A comprehensive product filter plugin for WooCommerce with a clean UI and extensive configuration options.

## Description

Advanced Product Filter for WooCommerce is a powerful and flexible plugin that enhances your WooCommerce store by providing advanced filtering capabilities. It allows customers to easily find products based on various criteria such as price, categories, attributes, tags, ratings, sale status, and stock availability.

### Key Features

- **Multiple Filter Types**: Filter products by price, categories, attributes, tags, ratings, sale status, and stock availability
- **AJAX Filtering**: Apply filters without page reload for a seamless user experience
- **Responsive Design**: Works perfectly on all devices with special mobile layouts
- **Customizable**: Multiple display styles and configuration options
- **Shortcode Support**: Easily add filters anywhere on your site
- **Widget Support**: Add filters to any widget area
- **Developer Friendly**: Clean code with hooks and filters for customization
- **Search Input Filter**: Includes a product search input for text-based filtering
- **Auto-Apply Filters**: Filters apply automatically when changed without requiring a button click
- **Layout Preservation**: Apply filters without changing the product grid layout or redirecting to the shop page

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0.0 or higher
- PHP 7.0 or higher

## Installation

1. Upload the `product-filter-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and activated
4. Configure the plugin settings under WooCommerce > Product Filter

## Usage

### Shortcode

You can display the product filter using the shortcode:

```
[product_filter]
```

### Shortcode Parameters

- `filters`: Specify which filters to display (default: 'all')
  - Options: 'all', 'price', 'category', 'attribute', 'tag', 'rating', 'sale', 'stock'
  - You can combine multiple filters with commas: 'price,category,attribute'
- `style`: Override the default style (default: uses the style set in settings)
- `preserve_layout`: Whether to preserve the current page layout (default: 'yes')
- `target_url`: Custom target URL for the form (default: current page)

### Examples

Display only price and category filters:
```
[product_filter filters="price,category"]
```

Display all filters with a custom style:
```
[product_filter style="minimal"]
```

### PHP Function

You can also display the filter in your theme files using the PHP function:

```php
<?php 
if (function_exists('product_filter_display')) {
    product_filter_display(array(
        'filters' => 'price,category,attribute',
        'style' => 'default',
        'preserve_layout' => 'yes',
        'target_url' => ''
    ));
}
?>
```

### Widget

The plugin also provides a widget that you can add to any widget area:

1. Go to Appearance > Widgets
2. Find the "Product Filter" widget
3. Drag it to your desired widget area
4. Configure the widget settings

## Configuration

### General Settings

- **Enable/Disable Filter Types**: Choose which filter types to enable
- **Filter Position**: Choose where to display the filter on the shop page
- **AJAX Filtering**: Enable/disable AJAX filtering
- **Mobile Display**: Choose how filters are displayed on mobile devices
- **Filter Style**: Choose the visual style of the filters
- **Disable CSS**: Option to disable the plugin's CSS for custom styling

## Filter Types

### Price Filter

A slider that allows customers to filter products by price range.

### Category Filter

Displays product categories as a hierarchical list or dropdown.

### Attribute Filter

Shows product attributes (like color, size, etc.) as checkboxes or dropdowns.

### Tag Filter

Displays product tags as a list of checkboxes.

### Rating Filter

Allows filtering products by their rating.

### Sale Filter

Option to show only products that are on sale.

### Stock Filter

Filter products by stock status (in stock, out of stock, on backorder).

### Search Filter

A text input field for searching products by name or description.

## Advanced Usage

### Custom Styling

You can customize the appearance of the filters by:

1. Disabling the plugin's CSS in the settings
2. Adding your own CSS to your theme

### Developer Hooks

The plugin provides several action and filter hooks for developers to extend its functionality.

## Troubleshooting

### Common Issues

- **Filters Not Working**: Make sure WooCommerce is activated and up to date
- **AJAX Not Working**: Check for JavaScript errors in your browser console
- **Styling Issues**: Try disabling other plugins that might conflict with the CSS

## Support

For support, please contact us at support@example.com or visit our website at https://example.com/support.

## Changelog

### 1.0.0
- Initial release

## Credits

Developed by Salvation Makina

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```
