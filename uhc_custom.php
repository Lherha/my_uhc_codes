<?php

/**
 * Plugin Name:       UHC Custom Plugin
 * Plugin URI:        https://uhcadoptiontree.com
 * Description:       Enhances WooCommerce and WordPress functionalities with customizations tailored for UHC Adoption Tree.
 * Version:           1.0.0
 * Author:            SGS TEAM
 * Author URI:        [author-website]
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       uhcadoptiontree
 * Domain Path:       /languages
 */

// Change "Place Order" text on WooCommerce checkout button
function uhc_custom_override_checkout_button_text() {
    return 'Pay Now';
}
add_filter('woocommerce_order_button_text', 'uhc_custom_override_checkout_button_text', 10);

// Custom shortcode for signup form display
function uhc_signup_shortcode() {
    if (!is_user_logged_in()) {
        echo do_shortcode('[elementor-template id="2422"]');
    } else {
        echo "<h3>You are already signed up/logged in</h3>";
    }
}
add_shortcode('signup_shortcode', 'uhc_signup_shortcode');

// Custom shortcode for forgot password link
function uhc_forgotpassword_shortcode() {
    if (!is_user_logged_in()) {
        return '<a class="elementor-lost-password" href="https://uhcadoptiontree.com/password-reset/">Lost your password?</a>';
    } else {
        echo "";
    }
}
add_shortcode('forgotpassword_ink', 'uhc_forgotpassword_shortcode');

// Custom shortcode for login form display
function uhc_login_shortcode() {
    if (!is_user_logged_in()) {
        echo do_shortcode('[elementor-template id="2463"]');
    } else {
        echo "";
    }
}
add_shortcode('login_shortcode', 'uhc_login_shortcode');

// Redirect users to login page if not logged in and trying to access "My Account" page
add_action('template_redirect', function(){
    if (!is_user_logged_in() && is_page('my-account')) {
        wp_redirect( site_url() . '/login');
        exit();
    }
});

// Increase product price on quantity increase
function enqueue_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery');
function uhc_increment_price_script() {
    if (class_exists('WooCommerce') && is_product()) {
        ?>
        <script>
            jQuery(function ($) {
                // Function to update price based on selected variation
                function updatePrice() {
                    // Get the selected variation data
                    var variationData = $('form.variations_form').data('product_variations');

                    // Find the currently selected variation
                    var selectedVariation = null;

                    $.each(variationData, function (index, variation) {
                        var isMatch = true;

                        $.each(variation.attributes, function (attribute, value) {
                            var select = 'select[name="attribute_' + attribute + '"]';
                            if ($(select).val() !== value) {
                                isMatch = false;
                                return false; // Exit the loop if not a match
                            }
                        });

                        if (isMatch) {
                            selectedVariation = variation;
                            return false; // Exit the loop if a match is found
                        }
                    });

                    if (selectedVariation) {
                        // Get the variation price
                        var variationPrice = parseFloat(selectedVariation.display_price);

                        // Update price based on variation price
                        var newPrice = variationPrice * parseInt($('input[name="quantity"]').val());

                        // Format the new price as currency
                        var formattedPrice = newPrice.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'NGN', // Change to your currency code
                        });

                        // Update the displayed price
                        $('.woocommerce-Price-amount').text(formattedPrice);
                    } else {
                        // If no variation is selected, fall back to the original price handling
                        var quantity = parseInt($('input[name="quantity"]').val());
                        var newPrice = originalPrice * quantity;

                        // Format the new price as currency
                        var formattedPrice = newPrice.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'NGN', // Change to your currency code
                        });

                        // Update the displayed price
                        $('.woocommerce-Price-amount').text(formattedPrice);
                    }
                }

                // Initial price (for variable products)
                var originalPrice = parseFloat($('form.variations_form').data('product_variations')[0].display_price);

                // Update price on quantity change
                $('input[name="quantity"]').on('input', updatePrice);

                // Update price when a variation is selected
                $('form.variations_form').on('change', 'select', updatePrice);
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'uhc_increment_price_script');

// Custom lost password form shortcode
function uhc_custom_lost_password_form( $atts ) {
    return wc_get_template( 'myaccount/form-lost-password.php', array( 'form' => 'lost_password' ) );
}
add_shortcode( 'uhc_lost_password_form', 'uhc_custom_lost_password_form' );

// Remove unnecessary billing fields in WooCommerce checkout
function customize_billing_fields($fields) {
    $fields = array(
        'billing_first_name' => $fields['billing_first_name'],
        'billing_last_name'  => $fields['billing_last_name'],
        'billing_email'      => $fields['billing_email'],
    );

    return $fields;
}
add_filter('woocommerce_billing_fields', 'customize_billing_fields', 10, 1);

// function uhc_custom_login_redirect() {
// $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// if ($current_url === 'https://uhcadoptiontree.com/wp-login.php') {
//         wp_redirect('https://uhcadoptiontree.com/login/');
//         exit();
//     }
// }
//add_action('init', 'uhc_custom_login_redirect');
/**
 * Remove quantity input from product page
 */

// add_filter( 'woocommerce_is_sold_individually','custom_remove_all_quantity_fields', 10, 2 );



// Remove WooCommerce breadcrumbs 
add_action( 'init', 'my_remove_breadcrumbs' );
function my_remove_breadcrumbs() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}