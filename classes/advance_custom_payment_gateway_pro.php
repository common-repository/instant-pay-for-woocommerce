<?php
class AdvanceCustomPaymentGatewayPro 
{
	public $api;
	public $clientId;
	public $basePath;
	public $currentDate;
	
	public function __construct()
	{	
		$options = get_option("woocommerce_advace_custom_payment_gateway_settings", true);

		$this->api  = !empty($options['api_url'])?$options['api_url']:"";		
		$this->clientId = !empty($options['api_atts']['clientId'])?$options['api_atts']['clientId']:"";
		$this->accessToken = !empty($options['api_request_headers']['Authorization'])?$options['api_request_headers']['Authorization']:"";

		$this->basePath = $_SERVER['DOCUMENT_ROOT'].'/kostababy/inst-errors.log';
		$date = new DateTime();
		$this->currentDate = $date->format('Y-m-d H:i:s');

		add_action('woocommerce_thankyou', [$this, 'call_instantpay_api'], 10, 1);
		add_action('woocommerce_thankyou', [$this, 'add_customer_note_to_thank_you_page']);
	}

	public function call_instantpay_api($order_id){
		if ( ! $order_id )	
			return;

		$selected_payment_method_id = get_post_meta( $order_id, '_payment_method', true );
		if($selected_payment_method_id == "advace_custom_payment_gateway"){
			if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {
        		// Get an instance of the WC_Order object
				$order = wc_get_order( $order_id );
				
				if(empty($this->clientId)){
					$message = sprintf( '[%s] %s', $this->currentDate, "Client Id is missing in Instant pay api url. Please check Instant Pay for Woocommerce plugin settings." );	
					$this->capture_error($message);
				} 

				$paymentDetails = [           
					"orderId" => $order_id,
					"totalCost" => $order->get_total(),
					"email" => $order->get_billing_email(),
					"phone" => $order->get_billing_phone(),
					"customerId" => $order->get_customer_id(),
					'clientId' => $this->clientId,
				]; 
				// Get payment details after successfull payment confirmation from Instany Pay API
				$details = $this->send_payment_details($paymentDetails);					

				$html = "";
				if(isset($details)){
					if( $details['status'] == "ok" ){	
							// Set Update Instruction note after payment process.
						$instruction_note = "Please pay $".$details['total_amount'] ." via direct bank transfer into following bank account. Bank Account Name: ". $details['bank_account_name'] .", Bank Account Number: ". $details['bank_account_number'] .", Bank Account BSB: ". $details['bank_account_bsb'] .". Enter unique reference ". $details['unique_reference'] ." as the payment description.";

						$html .= "<div class='payment_wrapper'>";

						$html .= "<h2 class='woocommerce-order-details__title'>Instant Bank Transfer Payment Details";
						$html .= "<img class='instant_pay_logo' src='https://instantpay.net.au/svg/poweredByInstantPayPurple_logo.svg'></h2>";
						$html .= "<h5><strong>Amount</strong>: $". esc_html( $details['total_amount'] ) ."</h5>";

						$html .= "<div class='details'><p><strong>Account Name</strong>: ". esc_html( $details['bank_account_name'] ) ."</p>";
						$html .= "<button class='btn btn-sm float-right copy-button popup' data-variable='". esc_html( $details['bank_account_name'] ) ."'><img class='copy-icon' src='https://instantpay.net.au/svg/copy_Button.svg'><span class='popuptext hide'>Copied!</span></button></div>";

						$html .= "<div class='details'><p><strong>Account BSB</strong>: ". esc_html( $details['bank_account_bsb'] ) ."</p>";
						$html .= "<button class='btn btn-sm float-right copy-button popup' data-variable='". esc_html( $details['bank_account_bsb'] ) ."'><img class='copy-icon' src='https://instantpay.net.au/svg/copy_Button.svg'><span class='popuptext hide'>Copied!</span></button></div>";

						$html .= "<div class='details'><p><strong>Account Number</strong>: ". esc_html( $details['bank_account_number'] ) ."</p>";
						$html .= "<button class='btn btn-sm float-right copy-button popup' data-variable='". esc_html( $details['bank_account_number'] ) ."'><img class='copy-icon' src='https://instantpay.net.au/svg/copy_Button.svg'><span class='popuptext hide'>Copied!</span></button></div>";

						$html .= "<div class='details'><p><strong>Unique Reference</strong>: ". esc_html( $details['unique_reference'] ) ."</p>";
						$html .= "<button class='btn btn-sm float-right copy-button popup' data-variable='". esc_html( $details['unique_reference'] ) ."'><img class='copy-icon' src='https://instantpay.net.au/svg/copy_Button.svg'><span class='popuptext hide'>Copied!</span></button></div>";

						$html .= "</div>";

					} else{
						$html .= "<p class='payment_error_block'>Unauthorized. Please check the Instant Pay payment setting.</h3>";
						$instruction_note = '';
					}	
				} else{
					$html .= "<p class='payment_error_block'>Unauthorized. Please check the Instant Pay payment setting.</h3>";
					$instruction_note = '';
				} 
				echo $html;

				// Update Instruction note after payment process.
				update_post_meta( $order_id, 'instruction_note', sanitize_text_field( $instruction_note ) );				
			}
		}
	}

	public function add_customer_note_to_thank_you_page($order_id){
		$customer_note = get_post_meta((int)$order_id, 'woocommerce_customized_customer_note', true);
		if($customer_note){
			echo '<p class="customer_note">' . esc_html( $customer_note ) . '</p>';
		}
	}

	public function send_payment_details($data){
		$endpoint = $this->api;		
		$options = [
			'body'        => json_encode($data),
			'headers'     => [
				'Content-Type' => 'application/json',
				'Authorization' => $this->accessToken,
			],
		];

		$request = wp_remote_post( $endpoint, $options );	
		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			error_log( print_r( $request, true ) );		
	    	//Write in inst-error log file after getting error from api.
			$message = sprintf( '[%s] %s', $this->currentDate, print_r( $request, true ) );		
			$this->capture_error($message);
		}

		if(wp_remote_retrieve_response_code( $request ) == 200){			
			$response = wp_remote_retrieve_body( $request );
		} else {
			$response = json_encode(['status' => wp_remote_retrieve_response_code( $request )]);
		}
		return json_decode($response, true); 
	}

	public function capture_error($message){
		file_put_contents( $this->basePath, $message.PHP_EOL, FILE_APPEND ); 
	}
}

$unused = new AdvanceCustomPaymentGatewayPro();