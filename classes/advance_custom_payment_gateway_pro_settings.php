<?php
/**
 * Advance Custom Payment Gateway Pro Settings
 *
 * @class   WC_Advance_Custom_Payment_Gateway_Pro_Settings
 * @extends	WC_Payment_Gateway
 */

class WC_Advance_Custom_Payment_Gateway_Pro_Settings extends WC_Payment_Gateway
{    
    public function __construct()
    {
        $this->domain               =     'wc_advace_custom_payment_gateway_pro';       
        $this->id                   =     'advace_custom_payment_gateway';
        $this->icon                 =     apply_filters('woocommerce_offline_icon', '');
        $this->has_fields           =     false;
        $this->method_title         =     __('Instant Pay for Woocommerce', $this->domain);
        $this->method_description   =     __('Take payments via Instant Pay and its similar as direct bank transfer', $this->domain);

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->enabled               =   $this->get_option( 'enabled' );
        $this->title                 =   $this->get_option('title');
        $this->description           =   $this->get_option('description');
        $this->gateway_icon          =   $this->get_option( 'gateway_icon' );
        $this->customer_note         =   $this->get_option( 'customer_note' );
        $this->order_status          =   $this->get_option( 'order_status' );
        $this->transaction_fee       =   $this->get_option( 'transaction_fee' );

        // Advance Options
        $this->enable_api            =   $this->get_option( 'enable_api' );
        $this->api_url               =   apply_filters( 'custom_payment_gateways_api_url', $this->get_option( 'api_url' ) );

        $this->api_request_headers   =   $this->get_option( 'api_request_headers' );
        $this->api_atts              =   $this->get_option( 'api_atts' );

        // Actions for process admin option
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = apply_filters('wc_offline_form_fields', array(

            'enabled'       =>      array(
                'title'     =>      __('Enable/Disable', $this->domain),
                'type'      =>      'checkbox',
                'label'     =>      __('Enable Payment', $this->domain),
                'default'   =>      'yes'
            ),

            'title'           =>    array(
                'title'       =>    __('Title', $this->domain),
                'type'        =>       'text',
                'description' =>    __('This controls the title for the payment method the customer sees during checkout.', $this->domain),
                'default'     =>    __('Pay With Instant Pay', $this->domain),
            ),

            'gateway_icon'    =>    array(
                'title'       =>    __( 'Gateway Icon', $this->domain),
                'type'        =>    'text',
                'description' =>    __( 'Icon URL for the gateway that will show to the user on the checkout page.', $this->domain ),
                'default'     =>    __( 'http://', $this->domain),
            ),

            'description'     =>    array(
                'title'       =>    __('Customer Message', $this->domain),
                'type'        =>    'textarea',
                'css'         =>    'width:50%;',
                'default'     =>    '',
                'description' =>    __('Payment method description that the customer will see on your checkout.', $this->domain),
            ),   

            'customer_note'   =>    array(
                'title'       =>     __( 'Customer Note', $this->domain ),
                'type'        =>    'textarea',
                'css'         =>    'width:50%;',
                'default'     =>    '',
                'description' =>    __( 'A note for the customer after the Checkout process.', $this->domain ),
            ), 

            'order_status'    =>    array(
                'title'       =>     __( 'Order Status After The Checkout', $this->domain ),
                'type'        =>    'select',
                'options'     =>    wc_get_order_statuses(),
                'default'     =>    'wc-pending',
                'description' =>    __( 'The default order status if this gateway used in payment.', $this->domain ),
            ),    

            'transaction_fee'           =>    array(
                'title'       =>    __('Transaction Fee', $this->domain),
                'type'        =>       'text',
                'description' =>    __('The transaction fee will be added to the total amount of the order.', $this->domain),
                'default'     =>    '',
            ),

            'advanced'        =>    array(
                'title'       =>    __( 'Advanced Options<hr>', $this->domain ),
                'type'        =>    'title',
                'description' =>    '',
            ),

            'enable_api'      =>    array(
                'title'       =>    __( 'Enable API', $this->domain),
                'type'        =>    'checkbox',
                'label'       =>    __( 'Enable the gateway to request an API URL after the checkout process.', $this->domain),
                'default'     =>    'no'
            ),

            'api_url'         =>    array(
                'title'       =>    __( 'API URL', $this->domain),
                'type'        =>    'text',
                'description' =>     __( 'The gateway will send the payment data to this URL after placing the order.', $this->domain),
                'default'     =>    '',
                'placeholder' =>    'http://'
            ),

            'api_request_headers' => [
                'type'            => 'api_request_headers',
            ],

            'api_atts'            => [
                'type'            => 'api_atts',
            ],
        ));
    }

    public function get_icon() {

        if ( trim( $this->gateway_icon ) === 'http://' ) {
            return '';
        }

        if ( trim( $this->gateway_icon ) != '' ) {
            return '<img class="customized_payment_icon" src="' . esc_attr( $this->gateway_icon ) . '" />';
        }

        return '';
    }

    public function validate_api_request_headers_field( $k ) {
        $attributes = [];
        if ( ! isset( $_POST['header_keys'] ) ) {
            return '';
        }
        if ( ! isset( $_POST['header_values'] ) ) {
            return '';
        }
        foreach ( $_POST['header_keys'] as $key => $value ) {
            $attributes[ $value ] = sanitize_text_field($_POST['header_values'][ $key ]);
        }

        return $attributes;
    }
    public function validate_api_atts_field( $k ) {
        $attributes = [];
        if ( ! isset( $_POST['extra_keys'] ) ) {
            return '';
        }
        if ( ! isset( $_POST['extra_values'] ) ) {
            return '';
        }
        foreach ( $_POST['extra_keys'] as $key => $value ) {
            $attributes[ $value ] = sanitize_text_field($_POST['extra_values'][ $key ]);
        }

        return $attributes;
    }

    public function generate_api_request_headers_html() {
        ob_start();
        include_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/views/api_request_headers_html.php' );
        return ob_get_clean();
    }
    public function generate_api_atts_html() {
        ob_start();
        include_once( plugin_dir_path( dirname( __FILE__ ) ) . '/includes/views/api_atts_html.php' );
        return ob_get_clean();
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id )
    {
        global $woocommerce;
        $order = new WC_Order( $order_id );       
        
        update_post_meta( (int) $order_id, 'woocommerce_customized_customer_note', $this->customer_note );

        // Update order status
        $order->update_status( $this->order_status );       

        // Reduce stock levels
        if ( function_exists( 'wc_reduce_stock_levels' ) ) {
            wc_reduce_stock_levels( $order_id );
        } else {
            $order->reduce_order_stock();
        }

        // Remove cart
        $woocommerce->cart->empty_cart();

        if ( trim( $this->customer_note ) != '' ) {
            $order->add_order_note( $this->customer_note, 1 );
        }

        // Return thank you redirect
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order )
        ];
    }
    
}