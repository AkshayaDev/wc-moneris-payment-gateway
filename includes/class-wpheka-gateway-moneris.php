<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPHEKA_Gateway_Moneris class.
 *
 * @extends WC_Payment_Gateway_CC
 */
class WPHEKA_Gateway_Moneris extends WC_Payment_Gateway_CC {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'moneris';
		$this->method_title       = __( 'Moneris', 'wpheka-gateway-moneris' );
		$this->method_description = __( 'Allows payments by Moneris.', 'wpheka-gateway-moneris' );
		$this->new_method_label   = __( 'Use a new card', 'wpheka-gateway-moneris' );
		$this->has_fields         = true;
		$this->supports           = array(
			'products',
			'default_credit_card_form',
			'refunds',
			'pre-orders',
		);
		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = WPHEKA_MONERIS_PLUGIN_ICON;
		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->enabled         = $this->get_option( 'enabled' );
		$this->sandbox         = $this->get_option( 'sandbox' );
		$this->store_id        = $this->get_option( 'store_id' );
		$this->api_token       = $this->get_option( 'api_token' );
		$this->country_code    = $this->get_option( 'country_code' );
		$this->crypt_type      = $this->get_option( 'crypt_type' );
		$this->preferred_cards = $this->get_option( 'preferred_cards' );

		// Hooks.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Admin Panel Options
	 *
	 * @since 1.7
	 * @return void
	 */
	public function admin_options() {
		?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<?php parent::admin_options(); ?>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<div class="postbox ">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><i class="dashicons dashicons-editor-help"></i>&nbsp;&nbsp;Plugin Support</span></h3>
							<div class="inside">
								<div class="support-widget">
									<p>
									<img style="width: 70%;margin: 0 auto;position: relative;display: inherit;" src="<?php echo WPHEKA_MONERIS_PLUGIN_LOGO; ?>">
									<br/>
									Got a Question, Idea, Problem or Praise?</p>
									<ul>
										<li>» Please leave us a <a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/wc-moneris-payment-gateway?filter=5#postform">★★★★★</a> rating.</li>
										<li>» <a href="https://www.wpheka.com/submit-ticket/" target="_blank">Support Request</a></li>
										<li>» <a href="https://www.wpheka.com/product/wc-moneris-payment-gateway/" target="_blank">Documentation and Common issues.</a></li>
										<li>» <a href="https://www.wpheka.com/plugins/" target="_blank">Our Plugins Shop</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'wpheka-gateway-moneris' ),
				'label'       => __( 'Enable Moneris Gateway', 'wpheka-gateway-moneris' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'wpheka-gateway-moneris' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wpheka-gateway-moneris' ),
				'default'     => __( 'Moneris', 'wpheka-gateway-moneris' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'wpheka-gateway-moneris' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wpheka-gateway-moneris' ),
				'default'     => 'Pay with your credit card via moneris.',
				'desc_tip'    => true,
			),
			'store_id' => array(
				'title'     => __( 'Store Id', 'wpheka-gateway-moneris' ),
				'type'      => 'text',
				'desc_tip'  => __( 'Enter your Moneris account store id here.', 'wpheka-gateway-moneris' ),
			),
			'api_token' => array(
				'title'     => __( 'API Token', 'wpheka-gateway-moneris' ),
				'type'      => 'text',
				'desc_tip'  => __( 'Enter your Moneris API Token here.', 'wpheka-gateway-moneris' ),
			),
			'country_code' => array(
				'title'     => __( 'Integration Country', 'wpheka-gateway-moneris' ),
				'type'      => 'select',
				'class'     => 'wc-enhanced-select',
				'desc_tip' => __( 'Is your Moneris account based in the US or Canada?', 'wpheka-gateway-moneris' ),
				'default'  => 'CA',
				'options' => array(
					'CA' => __( 'Canada', 'wpheka-gateway-moneris' ),
					'US' => __( 'United States', 'wpheka-gateway-moneris' ),
				),
			),
			'crypt_type' => array(
				'title'     => __( 'E-Commerce indicator', 'wpheka-gateway-moneris' ),
				'type'      => 'select',
				'class'     => 'wc-enhanced-select',
				'desc_tip'  => __( 'Select your E-Commerce indicator.', 'wpheka-gateway-moneris' ),
				'default'     => '7',
				'options' => array(
					'1' => __( 'Mail Order / Telephone Order—Single', 'wpheka-gateway-moneris' ),
					'2' => __( 'Mail Order / Telephone Order—Recurring', 'wpheka-gateway-moneris' ),
					'3' => __( 'Mail Order / Telephone Order—Instalment', 'wpheka-gateway-moneris' ),
					'4' => __( 'Mail Order / Telephone Order—Unknown classification', 'wpheka-gateway-moneris' ),
					'5' => __( 'Authenticated e-commerce transaction (VBV)', 'wpheka-gateway-moneris' ),
					'6' => __( 'Non-authenticated e-commerce transaction (VBV)', 'wpheka-gateway-moneris' ),
					'7' => __( 'SSL-enabled merchant', 'wpheka-gateway-moneris' ),
					'8' => __( 'Non-secure transaction (web- or email-based)', 'wpheka-gateway-moneris' ),
					'9' => __( 'SET non-authenticated transaction', 'wpheka-gateway-moneris' ),
				),
			),
			'preferred_cards' => array(
				'title'     => __( 'Preferred Cards', 'wpheka-gateway-moneris' ),
				'type'      => 'multiselect',
				'class'     => 'wc-enhanced-select',
				'desc_tip'  => __( 'Select your desired cards from the multiple-select box. The logo of the selected card(s) will be displayed on the checkout page.', 'wpheka-gateway-moneris' ),
				'default'     => 'visa',
				'options' => array(
					'visa' => __( 'Visa', 'wpheka-gateway-moneris' ),
					'mastercard' => __( 'MasterCard', 'wpheka-gateway-moneris' ),
					'discover' => __( 'Discover', 'wpheka-gateway-moneris' ),
					'amex' => __( 'American Express', 'wpheka-gateway-moneris' ),
					'jcb' => __( 'JCB', 'wpheka-gateway-moneris' ),
				),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select your desired cards', 'wpheka-gateway-moneris' ),
				),
			),
			'sandbox' => array(
				'title'       => __( 'Sandbox', 'wpheka-gateway-moneris' ),
				'label'       => __( 'Enable sandbox mode', 'wpheka-gateway-moneris' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in sandbox mode.', 'wpheka-gateway-moneris' ),
				'default'     => 'yes',
			),
		);
	}

	/**
	 * Payment form on checkout page.
	 */
	public function payment_fields() {
		$description = $this->get_description();

		if ( 'yes' == $this->sandbox ) {
			$description .= ' ' . sprintf( __( 'TEST MODE ENABLED. Use a test card: %s', 'woocommerce' ), '<a href="https://developer.moneris.com/More/Testing/Testing%20a%20Solution" target="_blank">https://developer.moneris.com/More/Testing/Testing a Solution</a>' );
		}

		if ( $description ) {
			echo wpautop( wptexturize( trim( $description ) ) );
		}

		if ( $this->supports( 'default_credit_card_form' ) ) {
			parent::payment_fields();
		}
	}

	/**
	 * Check transaction response and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return bool
	 */
	private function transaction_success( $response ) {

		if ( ( $response->getResponseCode() != 'null' ) && ( $response->getResponseCode() < 50 ) && $response->getComplete() ) {
			return true;
		}

		return false;
	}

	/**
	 * Validate frontend fields.
	 *
	 * Validate payment fields on the frontend.
	 *
	 * @return bool
	 */
	public function validate_fields() {

		$posted_data = $_POST;

		if ( empty( $posted_data[ $this->id . '-card-number' ] ) ) {
			wc_add_notice( __( 'Please enter your card number.', 'wpheka-gateway-moneris' ), 'error' );
			WC_Stripe_Logger::log( 'Please enter your card number.' );
			return false;
		}

		if ( empty( $posted_data[ $this->id . '-card-expiry' ] ) ) {
			wc_add_notice( __( 'Please enter your card expiry.', 'wpheka-gateway-moneris' ), 'error' );
			WC_Stripe_Logger::log( 'Please enter your card expiry.' );
			return false;
		}

		if ( empty( $posted_data[ $this->id . '-card-cvc' ] ) ) {
			wc_add_notice( __( 'Please enter your card cvd code.', 'wpheka-gateway-moneris' ), 'error' );
			WC_Stripe_Logger::log( 'Please enter your card cvd code.' );
			return false;
		}

		return true;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		global $woocommerce;
		$customer_order = new WC_Order( $order_id );

		/************************ Request Variables */
		$store_id = $this->store_id;
		$api_token = $this->api_token;

		if ( empty( $store_id ) || empty( $api_token ) ) {
			WPHEKA_Moneris_Logger::log( 'Please update your Moneris credentials in payment settings.' );
			throw new Exception( __( 'Please update your Moneris credentials in payment settings.', 'wpheka-gateway-moneris' ) );
		}

		/********************* Transactional Variables */

		$type = 'purchase';
		$cust_id = $customer_order->get_user_id();
		$amount = $customer_order->order_total;
		$pan = str_replace( array( ' ', '-' ), '', wc_clean( wp_unslash( $_POST[ $this->id . '-card-number' ] ) ) );
		$expiry_date = wc_clean( wp_unslash( $_POST[ $this->id . '-card-expiry' ] ) );
		if ( ! empty( $expiry_date ) ) {
			$expiry_date = explode( '/', $expiry_date );
			list($cardmonth, $cardyear) = $expiry_date;
			$expiry_date = $cardyear . $cardmonth;
		}
		$crypt = $this->crypt_type;
		$dynamic_descriptor = isset( $_POST[ $this->id . '-card-cvc' ] ) ? wc_clean( wp_unslash( $_POST[ $this->id . '-card-cvc' ] ) ) : '';

		/******************* Customer Information Variables */

		$first_name = $customer_order->get_billing_first_name();
		$last_name = $customer_order->get_billing_last_name();
		$company_name = $customer_order->get_billing_company();
		$address = $customer_order->get_billing_address_1() . ' ' . $customer_order->get_billing_address_2();
		$city = $customer_order->get_billing_city();
		$province = $customer_order->get_billing_state();
		$postal_code = $customer_order->get_billing_postcode();
		$country = $customer_order->get_billing_country();
		$phone_number = $customer_order->get_billing_phone();
		$fax = '';
		$tax1 = '';
		$tax2 = '';
		$tax3 = '';
		$shipping_cost = $customer_order->get_total_shipping();
		if ( ! empty( $shipping_cost ) ) {
			$shipping_cost = number_format( $customer_order->get_total_shipping(), 2, '.', '' );
		} else {
			$shipping_cost = '';
		}

		$email = $customer_order->get_billing_email();
		$instructions = $customer_order->get_customer_note();

		/******************** Customer Information Object */

		$mpgCustInfo = new mpgCustInfo();

		/********************** Set Customer Information */

		$billing = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'company_name' => $company_name,
			'address' => $address,
			'city' => $city,
			'province' => $province,
			'postal_code' => $postal_code,
			'country' => $country,
			'phone_number' => $phone_number,
			'fax' => $fax,
			'tax1' => $tax1,
			'tax2' => $tax2,
			'tax3' => $tax3,
			'shipping_cost' => $shipping_cost,
		);

		$mpgCustInfo->setBilling( $billing );

		$shipping_addr1 = empty( $customer_order->get_shipping_address_1() ) ? $customer_order->get_billing_address_1() : $customer_order->get_shipping_address_1();

		$shipping_addr2 = empty( $customer_order->get_shipping_address_2() ) ? $customer_order->get_billing_address_2() : $customer_order->get_shipping_address_2();

		$shipping_addr = $shipping_addr1 . ' ' . $shipping_addr2;

		$shipping = array(
			'first_name' => empty( $customer_order->get_shipping_first_name() ) ? $customer_order->get_billing_first_name() : $customer_order->get_shipping_first_name(),
			'last_name' => empty( $customer_order->get_shipping_last_name() ) ? $customer_order->get_billing_last_name() : $customer_order->get_shipping_last_name(),
			'company_name' => empty( $customer_order->get_shipping_company() ) ? $customer_order->get_billing_company() : $customer_order->get_shipping_company(),
			'address' => $shipping_addr,
			'city' => empty( $customer_order->get_shipping_city() ) ? $customer_order->get_billing_city() : $customer_order->get_shipping_city(),
			'province' => empty( $customer_order->get_shipping_state() ) ? $customer_order->get_billing_state() : $customer_order->get_shipping_state(),
			'postal_code' => empty( $customer_order->get_shipping_postcode() ) ? $customer_order->get_billing_postcode() : $customer_order->get_shipping_postcode(),
			'country' => empty( $customer_order->get_shipping_country() ) ? $customer_order->get_billing_country() : $customer_order->get_shipping_country(),
			'phone_number' => $phone_number,
			'fax' => $fax,
			'tax1' => $tax1,
			'tax2' => $tax2,
			'tax3' => $tax3,
			'shipping_cost' => $shipping_cost,
		);

		$mpgCustInfo->setShipping( $shipping );

		$mpgCustInfo->setEmail( $email );
		$mpgCustInfo->setInstructions( $instructions );

		/*********************** Set Line Item Information */

		$i = 0;
		$items = $customer_order->get_items();

		foreach ( $items as $item ) {
			$itemsArray = array();
			$product_id = ( $item['variation_id'] > 0 ) ? $item['variation_id'] : $item['product_id'];
			$itemsArray[ $i ] = array(
				'name' => get_the_title( $item['product_id'] ),
				'quantity' => $item['qty'],
				'product_code' => $product_id,
				'extended_amount' => $item['line_total'],
			);
			$mpgCustInfo->setItems( $itemsArray[ $i ] );
			$i++;
		}

		/************************** CVD Variables */

		$cvd_indicator = '1';
		$cvd_value = isset( $_POST[ $this->id . '-card-cvc' ] ) ? wc_clean( wp_unslash( $_POST[ $this->id . '-card-cvc' ] ) ) : '';

		/********************** CVD Associative Array */

		$cvdTemplate = array(
			'cvd_indicator' => $cvd_indicator,
			'cvd_value' => $cvd_value,
		);

		/************************** CVD Object */

		$mpgCvdInfo = new mpgCvdInfo( $cvdTemplate );

		/***************** Transactional Associative Array */
		$final_order_id = $order_id;
		if ( $this->sandbox == 'yes' ) {
			if ( ! empty( get_option( 'timezone_string' ) ) ) {
				date_default_timezone_set( get_option( 'timezone_string' ) );
			}
			$final_order_id = 'wc-order-' . date( 'dmy-G:i:s' ); // Fix duplicate order issue
		}
		$txnArray = array(
			'type' => $type,
			'order_id' => strval( $final_order_id ),
			'cust_id' => strval( $cust_id ),
			'amount' => strval( $amount ),
			'pan' => $pan,
			'expdate' => $expiry_date,
			'crypt_type' => strval( $crypt ),
		);

		/********************** Transaction Object */

		$mpgTxn = new mpgTransaction( $txnArray );

		/******************** Set Customer Information */

		$mpgTxn->setCustInfo( $mpgCustInfo );

		/************************ Set CVD */

		$mpgTxn->setCvdInfo( $mpgCvdInfo );

		/************************* Request Object */

		$mpgRequest = new mpgRequest( $mpgTxn );
		$mpgRequest->setProcCountryCode( $this->country_code );
		if ( $this->sandbox == 'yes' ) {
			$mpgRequest->setTestMode( true );
		}

		/************************ HTTPS Post Object */

		$mpgHttpPost = new mpgHttpsPost( $store_id, $api_token, $mpgRequest );

		/*************************** Response */

		$mpgResponse = $mpgHttpPost->getMpgResponse();

		if ( $this->transaction_success( $mpgResponse ) ) {
			// Payment has been successful
			$customer_order->add_order_note( __( 'Moneris payment completed.', 'wpheka-gateway-moneris' ) );

			// Add Transaction details
			$_date = $mpgResponse->getTransDate() . ' ' . $mpgResponse->getTransTime();
			add_post_meta( $order_id, '_paid_date', $_date, true );
			add_post_meta( $order_id, '_transaction_id', $mpgResponse->getTxnNumber(), true );
			add_post_meta( $order_id, '_completed_date', $_date, true );
			add_post_meta( $order_id, '_reference_no', $mpgResponse->getReferenceNum(), true );
			add_post_meta( $order_id, '_response_code', $mpgResponse->getResponseCode(), true );
			add_post_meta( $order_id, '_iso_code', $mpgResponse->getISO(), true );
			add_post_meta( $order_id, '_authorization_code', $mpgResponse->getAuthCode(), true );
			add_post_meta( $order_id, '_transaction_type', $mpgResponse->getTransType(), true );
			add_post_meta( $order_id, '_card_type', $mpgResponse->getCardType(), true );
			add_post_meta( $order_id, '_dynamic_descriptor', $dynamic_descriptor, true );
			add_post_meta( $order_id, '_card_cvd', $cvd_value, true );
			add_post_meta( $order_id, '_country_code', $this->country_code, true );
			if ( $this->sandbox == 'yes' ) {
				add_post_meta( $order_id, '_sandbox_order_id', $final_order_id, true );
			}

			// Mark order as Paid
			$customer_order->payment_complete();

			// Empty the cart (Very important step)
			$woocommerce->cart->empty_cart();

			// Redirect to thank you page
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $customer_order ),
			);
		} else {
			wc_add_notice( __( 'Payment error: ' . $mpgResponse->getMessage(), 'wpheka-gateway-moneris' ), 'error' );
			WPHEKA_Moneris_Logger::log( 'Payment error: ' . $mpgResponse->getMessage() );
			$customer_order->add_order_note( __( $mpgResponse->getMessage(), 'wpheka-gateway-moneris' ) );
			return;
		}
	}

	/**
	 * Check timestamps is on same day.
	 *
	 * @param  int $ts1 Timestamp1.
	 * @param  int $ts2 Timestamp2.
	 * @return bool
	 */
	private function isSameDay( $ts1, $ts2 = '' ) {
		if ( $ts2 == '' ) {
			$ts2 = time(); }
			$f = false;
		if ( date( 'z-Y', $ts1 ) == date( 'z-Y', $ts2 ) ) {
			$f = true; }
			return $f;
	}

	/**
	 * Process a refund if supported.
	 *
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		if ( $amount <= 0 ) {
			WPHEKA_Moneris_Logger::log( 'Refund failed.' );
			return new WP_Error( 'error', __( 'Refund failed.', 'woocommerce' ) );
		}

		$store_id = $this->store_id;
		$api_token = $this->api_token;

		$txnnumber = get_post_meta( $order_id, '_transaction_id', true );
		$customer_order = new WC_Order( $order_id );
		$order_country_code = get_post_meta( $order_id, '_country_code', true );

		if ( ! empty( get_option( 'timezone_string' ) ) ) {
			date_default_timezone_set( get_option( 'timezone_string' ) );
		}
		$order_placed_datetime = get_post_meta( $order_id, '_paid_date', true );

		if ( ! empty( $order_placed_datetime ) ) {
			$order_placed_timestamp = strtotime( $order_placed_datetime );

			$is_order_placed_same_day = $this->isSameDay( $order_placed_timestamp, time() );

			if ( $is_order_placed_same_day ) {
				WPHEKA_Moneris_Logger::log( 'Same day refund feature is not available. Please contact plugin author for professional version of this plugin.' );
				return new WP_Error( 'error', __( 'Same day refund feature is not available. Please contact plugin author for professional version of this plugin.', 'woocommerce' ) );
			}
		}

		if ( $this->sandbox == 'yes' ) {
			$order_id = get_post_meta( $order_id, '_sandbox_order_id', true );
		}
		// Refund transaction object mandatory values.
		// step 1) create transaction array.
		$txnArray = array(
			'type' => 'refund',
			'txn_number' => $txnnumber,
			'order_id' => $order_id,
			'amount' => $amount,
			'crypt_type' => $this->crypt_type,
			'cust_id' => $customer_order->get_user_id(),
			'dynamic_descriptor' => isset( $reason ) ? $reason : 'refund',
		);

		// step 2) create a transaction  object passing the array created in step 1.
		$mpgTxn = new mpgTransaction( $txnArray );

		// step 3) create a mpgRequest object passing the transaction object created in step 2.
		$mpgRequest = new mpgRequest( $mpgTxn );
		$mpgRequest->setProcCountryCode( $order_country_code );
		if ( $this->sandbox == 'yes' ) {
			$mpgRequest->setTestMode( true );
		}
		// step 4) create mpgHttpsPost object which does an https post ##.
		$mpgHttpPost = new mpgHttpsPost( $store_id, $api_token, $mpgRequest );

		// step 5) get an mpgResponse object ##.
		$mpgResponse = $mpgHttpPost->getMpgResponse();
		if ( $this->transaction_success( $mpgResponse ) ) {
			$customer_order->add_order_note( __( 'Amount Refunded: ' . $amount, 'wpheka-gateway-moneris' ) );
			return true;
		} else {
			$customer_order->add_order_note( __( $mpgResponse->getMessage(), 'wpheka-gateway-moneris' ) );
			return false;
		}
	}

	/**
	 * Get gateway icon.
	 *
	 * @access public
	 * @return string
	 */
	public function get_icon() {

		$visa = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/visa.svg' ) . '" alt="Visa" width="32" />';
		$mastercard = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard.svg' ) . '" alt="MasterCard" width="32" />';
		$discover = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover.svg' ) . '" alt="Discover" width="32" />';
		$amex = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex.svg' ) . '" alt="Amex" width="32" />';
		$jcb = '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/jcb.svg' ) . '" alt="JCB" width="32" />';

		$icon = '';
		if ( ! empty( $this->preferred_cards ) ) {
			foreach ( $this->preferred_cards as $card ) {
				$icon .= $$card;
			}
		}

		return apply_filters( 'wpheka_gateway_icon', $icon, $this->id );
	}
}