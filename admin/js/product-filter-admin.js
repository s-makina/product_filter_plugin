/**
 * Admin JavaScript for the Product Filter Plugin
 */
(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initColorPickers();
        initTabs();
        initToggleFields();
    });

    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        // Initialize WordPress color picker on color inputs
        if ($.fn.wpColorPicker) {
            $('.product-filter-color-picker').wpColorPicker();
        }
    }

    /**
     * Initialize settings tabs
     */
    function initTabs() {
        // Handle tab clicks
        $('.product-filter-tabs a').on('click', function(e) {
            e.preventDefault();
            
            // Get target section
            var target = $(this).attr('href');
            
            // Update active tab
            $('.product-filter-tabs a').removeClass('active');
            $(this).addClass('active');
            
            // Show target section, hide others
            $('.product-filter-section').hide();
            $(target).show();
            
            // Save active tab in localStorage
            if (window.localStorage) {
                localStorage.setItem('product_filter_active_tab', target);
            }
        });
        
        // Check for saved tab in localStorage
        if (window.localStorage && localStorage.getItem('product_filter_active_tab')) {
            var savedTab = localStorage.getItem('product_filter_active_tab');
            $('.product-filter-tabs a[href="' + savedTab + '"]').trigger('click');
        } else {
            // Default to first tab
            $('.product-filter-tabs a:first').trigger('click');
        }
    }

    /**
     * Initialize toggle fields
     */
    function initToggleFields() {
        // Handle dependency fields
        function updateDependentFields() {
            // Price filter dependencies
            var priceEnabled = $('#enable_price_filter').is(':checked');
            $('.price-filter-options').toggle(priceEnabled);
            
            // AJAX dependencies
            var ajaxEnabled = $('#use_ajax').is(':checked');
            $('.ajax-options').toggle(ajaxEnabled);
        }
        
        // Run on page load
        updateDependentFields();
        
        // Run when checkboxes change
        $('#enable_price_filter, #use_ajax').on('change', updateDependentFields);
    }

})(jQuery);
