<?php 

function IPWC_wc_advace_custom_payment_gateway_pro_settings($gateways)
{
	$gateways[] = 'WC_Advance_Custom_Payment_Gateway_Pro_Settings';
	return $gateways;
}
add_filter('woocommerce_payment_gateways', 'IPWC_wc_advace_custom_payment_gateway_pro_settings');

add_action( 'woocommerce_cart_calculate_fees','IPWC_wc_advance_payment_gateway_additional_fee' );
function IPWC_wc_advance_payment_gateway_additional_fee() {
	global $woocommerce;

	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		return;

	$options = get_option("woocommerce_advace_custom_payment_gateway_settings", true);
	$transaction_fee = !empty($options['transaction_fee']) ? $options['transaction_fee'] : 0;

	$woocommerce->cart->add_fee( 'Transaction Fee', $transaction_fee, true, '' );
}

function IPWC_filter_woocommerce_endpoint_order_received_title( $title, $endpoint, $action ) {  
	global $wp;
    $order_id  = absint( $wp->query_vars['order-received'] );
    $selected_payment_method_id = get_post_meta( $order_id, '_payment_method', true );
	if($selected_payment_method_id == "advace_custom_payment_gateway"){
		$title = __( 'Complete your payment', 'woocommerce' );
		return $title;
	} else{
		return $title;
	}
}
add_filter( 'woocommerce_endpoint_order-received_title', 'IPWC_filter_woocommerce_endpoint_order_received_title', 10, 3 );

function IPWC_wc_thankyou_title_change( $thank_you_title, $order ){
    $selected_payment_method_id = get_post_meta( $order->id, '_payment_method', true );
	if($selected_payment_method_id == "advace_custom_payment_gateway"){
		if(IPWC_check_SSL() == 'false'){
			$text =  '<span class="text-danger">We\'ve detected this site is not secure. Please contact the business directly.</span><br/>';
		} else {
			$text = "";
		}
		
		wp_enqueue_style( 'style',  plugin_dir_url( __FILE__ ) . "css/style.css");
		return $text.'<span>Thank you. Your order is awaiting payment.</span>';
	} else {
		return $thank_you_title;
	}
}
add_filter( 'woocommerce_thankyou_order_received_text', 'IPWC_wc_thankyou_title_change', 20, 2 );

function IPWC_wc_checkout_scripts() {
	wp_enqueue_script( 'custom-script',  plugin_dir_url( __FILE__ ) . "js/common.js", array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'IPWC_wc_checkout_scripts' );

function IPWC_check_SSL(){
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
			return "true";
		} elseif ( '1' == $_SERVER['HTTPS'] ) {
			return "true";
		}
	}

	if ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] )  ) {
		return "true";
	}
	return "false";
}

add_action( 'woocommerce_after_order_notes', 'IPWC_custom_checkout_field' );
function IPWC_custom_checkout_field( $checkout ) {
	woocommerce_form_field( 'instruction_note', array(
		'type'          => 'hidden',
		'class'         => array('form-row-wide hidden'),
		'label'         => __(' '),
		'placeholder'   => __(' '),
	), 
	$checkout->get_value( 'instruction_note' ));

	$style = "<style>";
	$style .= "#instruction_note_field span.optional{ display:none; }";	
	$style .= "</style>";
	echo $style;
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', 'IPWC_instruction_note_field_display_admin_order_meta', 10, 1 );
function IPWC_instruction_note_field_display_admin_order_meta($order){	
	$instruction_note = get_post_meta( $order->id, 'instruction_note', true );
	if ( !empty($instruction_note)) {
		echo '<p><strong>'.__('Instructions to customer').':</strong> ' . esc_html($instruction_note) . '</p>';
	}
}

add_filter( 'woocommerce_order_details_after_order_table', 'IPWC_instruction_note_field_display_order_received_page', 10 , 1 );
function IPWC_instruction_note_field_display_order_received_page ( $order ) {
	$instruction_note = get_post_meta( $order->id, 'instruction_note', true );    
	if ( !empty($instruction_note)) {
		echo '<p style="font-size:18px;"><strong>' . __( 'Instructions to customer' ) . ':</strong> ' . esc_html($instruction_note);
	}
}