<?php
/**
 * Plugin Name: DockBloxx Product Percent Coupons v0.1.0
 * Plugin URI:  https://dockbloxx.com/
 * Description: Extends WooCommerce coupons to support per-product percentage discounts (100% = free product).
 * Version:     0.1.0
 * Author:      Tony Stark (The Moose)
 * Author URI:  https://dockbloxx.com/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dockbloxx-coupons
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * On plugin activation.
 */
function dockbloxx_coupons_activate() {
    // Just a marker for now
    error_log( 'DockBloxx Product Percent Coupons activated.' );
}
register_activation_hook( __FILE__, 'dockbloxx_coupons_activate' );

/**
 * Simple admin notice so we know plugin is running.
 */
function dockbloxx_coupons_admin_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'DockBloxx Product Percent Coupons plugin is active and ready.', 'dockbloxx-coupons' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'dockbloxx_coupons_admin_notice' );

/**
 * Add custom field to WooCommerce coupon edit screen.
 */
function dockbloxx_coupons_add_percentage_field( $coupon_id, $coupon ) {
    woocommerce_wp_text_input( array(
        'id'          => '_dockbloxx_discount_percent_per_product',
        'label'       => __( 'Discount Percentage (per product)', 'dockbloxx-coupons' ),
        'description' => __( 'Enter a percentage (0-100). 100% makes eligible products free. Leave empty to use fixed product discount instead.', 'dockbloxx-coupons' ),
        'desc_tip'    => true,
        'type'        => 'number',
        'custom_attributes' => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),
        'wrapper_class' => 'dockbloxx-percent-field', // 👈 wrapper CSS class
        'value'       => get_post_meta( $coupon_id, '_dockbloxx_discount_percent_per_product', true ),
    ) );
}
add_action( 'woocommerce_coupon_options', 'dockbloxx_coupons_add_percentage_field', 10, 2 );

/**
 * Save custom field value.
 */
function dockbloxx_coupons_save_percentage_field( $coupon_id ) {
    if ( isset( $_POST['_dockbloxx_discount_percent_per_product'] ) ) {
        $value = intval( $_POST['_dockbloxx_discount_percent_per_product'] );
        update_post_meta( $coupon_id, '_dockbloxx_discount_percent_per_product', $value );
    }
}
add_action( 'woocommerce_coupon_options_save', 'dockbloxx_coupons_save_percentage_field' );

/**
 * Enqueue admin JS to toggle percentage field.
 */
function dockbloxx_coupons_admin_scripts( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return; // Only load on post edit screens
    }

    global $post;
    if ( $post && 'shop_coupon' === $post->post_type ) {
        wp_add_inline_script( 'jquery-core', "
            jQuery(document).ready(function($) {
                function toggleDockbloxxField() {
                    var type = $('#discount_type').val();
                    if (type === 'fixed_product') {
                        $('.dockbloxx-percent-field').closest('p.form-field').show();
                    } else {
                        $('.dockbloxx-percent-field').closest('p.form-field').hide();
                    }
                }
                toggleDockbloxxField();
                $('#discount_type').on('change', toggleDockbloxxField);
            });
        " );
    }
}
add_action( 'admin_enqueue_scripts', 'dockbloxx_coupons_admin_scripts' );

/**
 * Add custom "Allowed Emails" field to WooCommerce coupon edit screen.
 */
function dockbloxx_coupons_add_allowed_emails_field( $coupon_id, $coupon ) {
    $saved = get_post_meta( $coupon_id, '_dockbloxx_allowed_emails', true );
    $value = is_array( $saved ) ? implode( "\n", $saved ) : $saved;

    woocommerce_wp_textarea_input( array(
        'id'          => '_dockbloxx_allowed_emails',
        'label'       => __( 'Allowed Emails', 'dockbloxx-coupons' ),
        'description' => __( 'Enter one email per line. Only these customers can use this coupon.', 'dockbloxx-coupons' ),
        'desc_tip'    => true,
        'wrapper_class' => 'dockbloxx-allowed-emails-field',
        'value'       => $value,
    ) );
}
add_action( 'woocommerce_coupon_options', 'dockbloxx_coupons_add_allowed_emails_field', 20, 2 );

/**
 * Save "Allowed Emails" field value as array.
 */
function dockbloxx_coupons_save_allowed_emails_field( $coupon_id ) {
    if ( isset( $_POST['_dockbloxx_allowed_emails'] ) ) {
        $raw = sanitize_textarea_field( $_POST['_dockbloxx_allowed_emails'] );
        $emails = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
        update_post_meta( $coupon_id, '_dockbloxx_allowed_emails', $emails );
    }
}
add_action( 'woocommerce_coupon_options_save', 'dockbloxx_coupons_save_allowed_emails_field' );



/**
 * Extend admin JS toggle to show/hide Allowed Emails field only for fixed_product coupons.
 */
function dockbloxx_coupons_admin_scripts_allowed_emails( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }

    global $post;
    if ( $post && 'shop_coupon' === $post->post_type ) {
        wp_add_inline_script( 'jquery-core', "
            jQuery(document).ready(function($) {
                function toggleDockbloxxFields() {
                    var type = $('#discount_type').val();
                    if (type === 'fixed_product') {
                        $('.dockbloxx-percent-field').closest('p.form-field').show();
                        $('.dockbloxx-allowed-emails-field').closest('p.form-field').show();
                    } else {
                        $('.dockbloxx-percent-field').closest('p.form-field').hide();
                        $('.dockbloxx-allowed-emails-field').closest('p.form-field').hide();
                    }
                }
                toggleDockbloxxFields();
                $('#discount_type').on('change', toggleDockbloxxFields);
            });
        " );
    }
}
add_action( 'admin_enqueue_scripts', 'dockbloxx_coupons_admin_scripts_allowed_emails' );
