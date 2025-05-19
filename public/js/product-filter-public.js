/**
 * Public JavaScript for the Product Filter Plugin
 */
(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initPriceSlider();
        initMobileToggle();
        initAjaxFilter();
    });

    /**
     * Initialize price slider
     */
    function initPriceSlider() {
        // Find all price sliders
        $('.price-slider').each(function() {
            var $slider = $(this);
            var $form = $slider.closest('form');
            var $minInput = $form.find('input[name="min_price"]');
            var $maxInput = $form.find('input[name="max_price"]');
            var $fromLabel = $form.find('.price-from .from');
            var $toLabel = $form.find('.price-to .to');

            // Get values from data attributes
            var min = parseFloat($slider.data('min'));
            var max = parseFloat($slider.data('max'));
            var currentMin = parseFloat($slider.data('current-min'));
            var currentMax = parseFloat($slider.data('current-max'));

            // Initialize jQuery UI slider
            $slider.slider({
                range: true,
                min: min,
                max: max,
                values: [currentMin, currentMax],
                slide: function(event, ui) {
                    // Update labels and hidden inputs
                    $fromLabel.text(ui.values[0]);
                    $toLabel.text(ui.values[1]);
                    $minInput.val(ui.values[0]);
                    $maxInput.val(ui.values[1]);
                },
                change: function(event, ui) {
                    // Auto-apply filter when slider stops (only if triggered by user)
                    if (event.originalEvent) {
                        $form.trigger('submit');
                    }
                }
            });
        });
    }

    /**
     * Initialize mobile toggle
     */
    function initMobileToggle() {
        // Mobile toggle button
        $('.product-filter-toggle-mobile').on('click', function() {
            $(this).toggleClass('active');

            // Check mobile display type
            var mobileDisplay = product_filter_params.mobile_display;

            if (mobileDisplay === 'offcanvas') {
                // Off-canvas display
                var $wrapper = $(this).next('.product-filter-wrapper');
                $wrapper.toggleClass('active');

                // Add overlay if it doesn't exist
                if ($('.filter-overlay').length === 0) {
                    $('body').append('<div class="filter-overlay"></div>');
                }

                $('.filter-overlay').toggleClass('active');

                // Close button for off-canvas
                if ($wrapper.find('.filter-close').length === 0) {
                    $wrapper.prepend('<span class="filter-close">&times;</span>');
                }

                // Close off-canvas when clicking overlay or close button
                $('.filter-overlay, .filter-close').on('click', function() {
                    $('.product-filter-wrapper').removeClass('active');
                    $('.filter-overlay').removeClass('active');
                    $('.product-filter-toggle-mobile').removeClass('active');
                });
            } else if (mobileDisplay === 'accordion') {
                // Accordion display
                $('.product-filter-section h4').on('click', function() {
                    $(this).toggleClass('active');
                    $(this).next('.filter-content').slideToggle();
                });
            } else if (mobileDisplay === 'dropdown') {
                // Dropdown display
                $('.product-filter-section h4').on('click', function() {
                    $(this).toggleClass('active');
                    $(this).next('.filter-content').slideToggle();
                });
            }
        });
    }

    /**
     * Initialize AJAX filtering
     */
    function initAjaxFilter() {
        // Only initialize if AJAX is enabled
        if (product_filter_params.use_ajax !== 'yes') {
            return;
        }

        // Auto-apply filters when they change
        $('.product-filter-form input[type="checkbox"], .product-filter-form input[type="radio"], .product-filter-form select').on('change', function() {
            $(this).closest('form').trigger('submit');
        });

        // Add debounce for search input to avoid too many requests
        var searchTimer;
        $('.product-search-input').on('input', function() {
            clearTimeout(searchTimer);
            var $form = $(this).closest('form');

            searchTimer = setTimeout(function() {
                $form.trigger('submit');
            }, 500); // Wait 500ms after user stops typing
        });

        // Handle form submission
        $('.product-filter-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var formData = $form.serialize();
            var currentUrl = window.location.href;

            // Find the product container - could be a custom widget or the main products container
            var $productsContainer = $('.products');

            // Store the original container classes to preserve layout
            var originalClasses = $productsContainer.attr('class');

            // Add loading state
            $('body').addClass('filtering');

            // Show loading indicator
            if ($('.product-filter-loading').length === 0) {
                $productsContainer.before('<div class="product-filter-loading"><span class="spinner"></span><span class="text">Filtering products...</span></div>');
            } else {
                $('.product-filter-loading').show();
            }

            // Send AJAX request
            $.ajax({
                url: product_filter_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'product_filter_ajax',
                    nonce: product_filter_params.nonce,
                    formData: formData,
                    current_url: currentUrl
                },
                success: function(response) {
                    if (response.success) {
                        // Extract just the product elements from the response
                        var $responseProducts = $(response.data.products_html).find('li.product');

                        if ($responseProducts.length === 0) {
                            // If no products found in the expected format, use the full response
                            $productsContainer.html(response.data.products_html);
                        } else {
                            // Replace only the product items, preserving the container
                            $productsContainer.empty().append($responseProducts);

                            // Ensure the original classes are preserved to maintain layout
                            $productsContainer.attr('class', originalClasses);
                        }

                        // Update URL without redirecting
                        window.history.pushState({}, '', response.data.filter_url);

                        // Scroll to top of products
                        $('html, body').animate({
                            scrollTop: $productsContainer.offset().top - 100
                        }, 500);
                    } else {
                        console.error('Error filtering products');
                    }
                },
                error: function() {
                    console.error('AJAX error');
                },
                complete: function() {
                    // Remove loading state
                    $('body').removeClass('filtering');
                    $('.product-filter-loading').hide();
                }
            });
        });
    }

})(jQuery);
