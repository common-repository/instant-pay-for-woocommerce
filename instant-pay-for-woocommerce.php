<?php 

/**
 * Plugin Name: Instant Pay for Woocommerce
 * Plugin URI: https://instantpay.net.au
 * Description: Instant Pay for Woocommerce. Capture payments via bank transfer with API Banking in Australia.
 * Author: Instant Pay Pty Ltd
 * Version: 1.0.6
 * Tested up to: 5.8
 * License: GNU General Public License v3.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

add_action('plugins_loaded', 'advance_custom_payment_gateway_pro_setting_init', 11);
function advance_custom_payment_gateway_pro_setting_init() {
   require_once(plugin_basename('classes/advance_custom_payment_gateway_pro_settings.php'));
}

require_once(plugin_basename('includes/hooks.php'));      
require_once(plugin_basename('classes/advance_custom_payment_gateway_pro.php'));


//add_action( 'deactivated_plugin', 'wc_instant_pay_plugin', 10, 2 );
function wc_instant_pay_plugin( $plugin, $network_wide )
{
   $option_name = "woocommerce_advace_custom_payment_gateway_settings";
   $new_value = [];

   if ( get_option( $option_name ) !== false ) {
    // The option already exists, so update it.
     update_option( $option_name, $new_value );
  }
}

/************************************************/
