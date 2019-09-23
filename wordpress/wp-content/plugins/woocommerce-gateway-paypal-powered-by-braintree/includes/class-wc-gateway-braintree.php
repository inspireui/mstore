<?php
/**
 * WooCommerce Braintree Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@woocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Braintree Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Braintree Gateway for your
 * needs please refer to http://docs.woocommerce.com/document/braintree/
 *
 * @package   WC-Braintree/Gateway
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree Base Gateway Class
 *
 * Handles common functionality among the Credit Card/PayPal gateways
 *
 * @since 2.0.0
 */
class WC_Gateway_Braintree extends WC_Braintree_Framework\SV_WC_Payment_Gateway_Direct {


	/** sandbox environment ID */
	const ENVIRONMENT_SANDBOX = 'sandbox';

	/** @var string the Braintree Auth access token */
	protected $auth_access_token;

	/** @var bool whether the gateway is connected manually */
	protected $connect_manually;

	/** @var string production merchant ID */
	protected $merchant_id;

	/** @var string production public key */
	protected $public_key;

	/** @var string production private key */
	protected $private_key;

	/** @var string sandbox merchant ID */
	protected $sandbox_merchant_id;

	/** @var string sandbox public key */
	protected $sandbox_public_key;

	/** @var string sandbox private key */
	protected $sandbox_private_key;

	/** @var string name dynamic descriptor */
	protected $name_dynamic_descriptor;

	/** @var string phone dynamic descriptor */
	protected $phone_dynamic_descriptor;

	/** @var string url dynamic descriptor */
	protected $url_dynamic_descriptor;

	/** @var \WC_Braintree_API instance */
	protected $api;

	/** @var array shared settings names */
	protected $shared_settings_names = array( 'public_key', 'private_key', 'merchant_id', 'sandbox_public_key', 'sandbox_private_key', 'sandbox_merchant_id', 'name_dynamic_descriptor' );


	/**
	 * Loads the plugin configuration settings
	 *
	 * @since 2.0.0
	 */
	public function load_settings() {

		parent::load_settings();

		$this->auth_access_token = get_option( 'wc_braintree_auth_access_token', '' );
		$this->auth_environment  = get_option( 'wc_braintree_auth_environment', self::ENVIRONMENT_PRODUCTION );
	}


	/**
	 * Enqueue the Braintree.js library prior to enqueueing gateway scripts
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::enqueue_scripts()
	 * @return bool
	 */
	public function enqueue_gateway_assets() {

		if ( $this->is_available() ) {

			wp_enqueue_script( 'braintree-js-latinise', $this->get_plugin()->get_plugin_url() . '/assets/js/frontend/latinise.min.js' );

			// braintree.js library
			wp_enqueue_script( 'braintree-js-client', 'https://js.braintreegateway.com/web/' . WC_Braintree::BRAINTREE_JS_SDK_VERSION . '/js/client.min.js', array(), WC_Braintree::VERSION, true );

			parent::enqueue_gateway_assets();
		}
	}


	/**
	 * Gets a client authorization token via AJAX.
	 *
	 * @internal
	 *
	 * @since 2.1.0
	 */
	public function ajax_get_client_token() {

		check_ajax_referer( 'wc_' . $this->get_id() . '_get_client_token', 'nonce' );

		try {

			$result = $this->get_api()->get_client_token( array( 'merchantAccountId' => $this->get_merchant_account_id() ) );

			wp_send_json_success( $result->get_client_token() );

		} catch ( WC_Braintree_Framework\SV_WC_Plugin_Exception $e ) {

			$this->add_debug_message( $e->getMessage(), 'error' );

			wp_send_json_error( array(
				'message' => $e->getMessage(),
			) );
		}
	}


	/**
	 * Validate the payment nonce exists
	 *
	 * @since 3.0.0
	 * @param $is_valid
	 * @return bool
	 */
	public function validate_payment_nonce( $is_valid ) {

		// nonce is required
		if ( ! WC_Braintree_Framework\SV_WC_Helper::get_post( 'wc_' . $this->get_id() . '_payment_nonce' ) ) {

			wc_add_notice( __( 'Oops, there was a temporary payment error. Please try another payment method or contact us to complete your transaction.', 'woocommerce-gateway-paypal-powered-by-braintree' ), 'error' );

			$is_valid = false;
		}

		return $is_valid;
	}


	/**
	 * Add Braintree-specific data to the order prior to processing, currently:
	 *
	 * $order->payment->nonce - payment method nonce
	 * $order->payment->tokenize - true to tokenize payment method, false otherwise
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::get_order()
	 * @param int $order order ID being processed
	 * @return \WC_Order object with payment and transaction information attached
	 */
	public function get_order( $order ) {

		$order = parent::get_order( $order );

		// nonce may be previously populated by Apple Pay
		if ( empty( $order->payment->nonce ) ) {
			$order->payment->nonce = WC_Braintree_Framework\SV_WC_Helper::get_post( 'wc_'. $this->get_id() . '_payment_nonce' );
		}

		$order->payment->tokenize = $this->get_payment_tokens_handler()->should_tokenize();

		// billing address ID if using existing payment token
		if ( ! empty( $order->payment->token ) && $this->get_payment_tokens_handler()->user_has_token( $order->get_user_id(), $order->payment->token ) ) {

			$token = $this->get_payment_tokens_handler()->get_token( $order->get_user_id(), $order->payment->token );

			if ( $billing_address_id = $token->get_billing_address_id() ) {
				$order->payment->billing_address_id = $billing_address_id;
			}
		}

		// fraud tool data as a JSON string, unslashed as WP slashes $_POST data which breaks the JSON
		$order->payment->device_data = wp_unslash( WC_Braintree_Framework\SV_WC_Helper::get_post( 'device_data' ) );

		// merchant account ID
		if ( $merchant_account_id = $this->get_merchant_account_id( WC_Braintree_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' ) ) ) {
			$order->payment->merchant_account_id = $merchant_account_id;
		}

		// dynamic descriptors
		$order->payment->dynamic_descriptors = new stdClass();

		// only set the name descriptor if it is valid
		if ( $this->get_name_dynamic_descriptor() && $this->is_name_dynamic_descriptor_valid() ) {
			$order->payment->dynamic_descriptors->name = $this->get_name_dynamic_descriptor();
		}

		// only set the phone descriptor if it is valid
		if ( $this->get_phone_dynamic_descriptor() && $this->is_phone_dynamic_descriptor_valid() ) {
			$order->payment->dynamic_descriptors->phone = $this->get_phone_dynamic_descriptor();
		}

		// the URL descriptor doesn't have any specific validation, so just truncate it if needed
		$order->payment->dynamic_descriptors->url = WC_Braintree_Framework\SV_WC_Helper::str_truncate( $this->get_url_dynamic_descriptor(), 13, '' );

		// add the recurring flag to Subscriptions renewal orders
		if ( $this->get_plugin()->is_subscriptions_active() && wcs_order_contains_subscription( $order, 'any' ) ) {

			$order->payment->subscription = new \stdClass();
			$order->payment->subscription->is_renewal = false;

			if ( wcs_order_contains_renewal( $order ) ) {

				$order->payment->recurring                = true;
				$order->payment->subscription->is_renewal = true;
			}
		}

		// test amount when in sandbox mode
		if ( $this->is_test_environment() && ( $test_amount = WC_Braintree_Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-test-amount' ) ) ) {
			$order->payment_total = WC_Braintree_Framework\SV_WC_Helper::number_format( $test_amount );
		}

		return $order;
	}


	/**
	 * Gets the order object with data added to process a refund.
	 *
	 * Overridden to add the transaction ID to legacy orders since the v1.x
	 * plugin didn't set its own transaction ID meta.
	 *
	 * @see \SV_WC_Payment_Gateway::get_order_for_refund()
	 * @since 2.0.0
	 * @param \WC_Order $order the order object
	 * @param float $amount the refund amount
	 * @param string $reason the refund reason
	 * @return \WC_Order
	 */
	public function get_order_for_refund( $order, $amount, $reason ) {

		$order = parent::get_order_for_refund( $order, $amount, $reason );

		if ( empty( $order->refund->trans_id ) ) {

			$order->refund->trans_id = WC_Braintree_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'transaction_id' );
		}

		return $order;
	}


	/**
	 * Gets the capture handler.
	 *
	 * @since 2.2.0
	 *
	 * @return \WC_Braintree\Capture
	 */
	public function get_capture_handler() {

		require_once( $this->get_plugin()->get_plugin_path() . '/includes/class-wc-braintree-capture.php' );

		return new \WC_Braintree\Capture( $this );
	}


	/** Tokenization methods **************************************************/


	/**
	 * Braintree tokenizes payment methods during the transaction (if successful)
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function tokenize_with_sale() {
		return true;
	}


	/**
	 * Return the custom Braintree payment tokens handler class
	 *
	 * @since 3.2.0
	 * @return \WC_Braintree_Payment_Method_Handler
	 */
	protected function build_payment_tokens_handler() {

		return new WC_Braintree_Payment_Method_Handler( $this );
	}


	/** Admin settings methods ************************************************/


	/**
	 * Returns an array of form fields specific for this method
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array of form fields
	 */
	protected function get_method_form_fields() {

		return array(

			// production
			'merchant_id' => array(
				'title'    => __( 'Merchant ID', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'The Merchant ID for your Braintree account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			'public_key' => array(
				'title'    => __( 'Public Key', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'The Public Key for your Braintree account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			'private_key' => array(
				'title'    => __( 'Private Key', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'password',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'The Private Key for your Braintree account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			// sandbox
			'sandbox_merchant_id' => array(
				'title'    => __( 'Sandbox Merchant ID', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'The Merchant ID for your Braintree sandbox account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			'sandbox_public_key' => array(
				'title'    => __( 'Sandbox Public Key', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'The Public Key for your Braintree sandbox account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			'sandbox_private_key' => array(
				'title'    => __( 'Sandbox Private Key', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'password',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'The Private Key for your Braintree sandbox account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			),

			// merchant account ID per currency feature
			'merchant_account_id_title' => array(
				'title'       => __( 'Merchant Account IDs', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'        => 'title',
				'description' => sprintf(
					esc_html__( 'Enter additional merchant account IDs if you do not want to use your Braintree account default. %1$sLearn more about merchant account IDs%2$s', 'woocommerce-gateway-paypal-powered-by-braintree' ),
					'<a href="' . esc_url( wc_braintree()->get_documentation_url() ). '#merchant-account-ids' . '">', '&nbsp;&rarr;</a>'
				),
			),

			'merchant_account_id_fields' => array( 'type' => 'merchant_account_ids' ),

			// dynamic descriptors
			'dynamic_descriptor_title' => array(
				'title'       => __( 'Dynamic Descriptors', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'        => 'title',
				/* translators: Placeholders: %1$s - <p> tag, %2$s - </p> tag, %3$s - <a> tag, %4$s - </a> tag */
				'description' => sprintf( esc_html__( 'Dynamic descriptors define what will appear on your customers\' credit card statements for a specific purchase. Contact Braintree to enable these for your account.%1$sPlease ensure that you have %3$sread the documentation on dynamic descriptors%4$s and are using an accepted format.%2$s', 'woocommerce-gateway-paypal-powered-by-braintree' ), '<p style="font-weight: bold;">', '</p>', '<a target="_blank" href="https://docs.woocommerce.com/document/braintree/#dynamic-descriptor-setup">', '</a>' ),
			),

			'name_dynamic_descriptor' => array(
				'title'    => __( 'Name', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type'     => 'text',
				'class'    => 'js-dynamic-descriptor-name',
				'desc_tip' => __( 'The value in the business name field of a customer\'s statement. Company name/DBA section must be either 3, 7 or 12 characters and the product descriptor can be up to 18, 14, or 9 characters respectively (with an * in between for a total descriptor name of 22 characters).', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'custom_attributes' => array( 'maxlength' => 22 ),
			),

			'phone_dynamic_descriptor' => array(
				'title' => __( 'Phone', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type' => 'text',
				'class' => 'js-dynamic-descriptor-phone',
				'desc_tip' => __( 'The value in the phone number field of a customer\'s statement. Phone must be exactly 10 characters and can only contain numbers, dashes, parentheses and periods.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'custom_attributes' => array( 'maxlength' => 14 ),
			),

			'url_dynamic_descriptor' => array(
				'title' => __( 'URL', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'type' => 'text',
				'class' => 'js-dynamic-descriptor-url',
				'desc_tip' => __( 'The value in the URL/web address field of a customer\'s statement. The URL must be 13 characters or less.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'custom_attributes' => array( 'maxlength' => 13 ),
			),
		);
	}


	/**
	 * Adds the shared settings form fields.
	 *
	 * @since 2.0.0
	 * @param array $form_fields
	 * @return array
	 */
	protected function add_shared_settings_form_fields( $form_fields ) {

		$form_fields = parent::add_shared_settings_form_fields( $form_fields );

		$this->load_settings();

		// if this gateway can't connect to Braintree Auth, no need to continue
		if ( ! $this->can_connect() ) {
			return $form_fields;
		}

		// used to move the environment field below
		$environment_field = $form_fields['environment'];
		unset( $form_fields['environment'] );

		$form_fields = WC_Braintree_Framework\SV_WC_Helper::array_insert_after( $form_fields, 'connection_settings', array(
			'braintree_auth'   => array( 'type' => 'braintree_auth' ),
			'connect_manually' => array(
				'type'    => 'checkbox',
				'label'   => __( 'Enter connection credentials manually', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				'default' => ( ! $this->get_option( 'connect_manually' ) && $this->is_connected_manually() ) ? 'yes' : 'no',
			),
			'environment' => $environment_field,
		) );

		// get any sibling gateways
		$other_gateway_ids                  = array_diff( $this->get_plugin()->get_gateway_ids(), array( $this->get_id() ) );
		$connect_manually_other_gateway_ids = array();

		// determine if any sibling gateways have configured manual connection
		foreach ( $other_gateway_ids as $other_gateway_id ) {

			$other_gateway_settings = $this->get_plugin()->get_gateway_settings( $other_gateway_id );

			// add the gateway to the list if manual connection is set
			if ( isset( $other_gateway_settings['connect_manually'] ) && 'yes' === $other_gateway_settings['connect_manually'] ) {
				$connect_manually_other_gateway_ids[] = $other_gateway_id;
			}
		}

		// if no sibling gateways are manually connected, disable the inherit settings checkbox
		if ( empty( $connect_manually_other_gateway_ids ) ) {
			$form_fields['inherit_settings']['disabled']    = true;
			$form_fields['inherit_settings']['description'] = __( 'Disabled because the other gateway is not connected manually.', 'woocommerce-gateway' );
		}

		return $form_fields;
	}


	/**
	 * Generates the Braintree Auth connection HTML.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function generate_braintree_auth_html() {

		if ( $this->is_connected() ) {

			$help_tip = sprintf(
				'%s<br><br>%s<br><br>%s',
				__( 'You just connected your Braintree account to WooCommerce. You can start taking payments now.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				__( 'Once you have processed a payment, PayPal will review your application for final approval. Before you ship any goods make sure you have received a final approval for your Braintree account.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
				__( 'Questions? We are a phone call away: 1-855-489-0345.', 'woocommerce-gateway-paypal-powered-by-braintree' )
			);

		} else {

			$help_tip = __( 'Click button to create an account with Braintree and start transacting.', 'woocommerce-gateway-paypal-powered-by-braintree' );
		}

		$button_image_url    = $this->get_plugin()->get_plugin_url() . '/assets/images/button/connect-braintree.png';
		$is_connected        = $this->is_connected();
		$connect_url         = $this->get_connect_url();
		$sandbox_connect_url = $this->get_connect_url( self::ENVIRONMENT_SANDBOX );
		$disconnect_url      = $this->get_disconnect_url();

		ob_start();

		include( $this->get_plugin()->get_plugin_path() . '/includes/views/admin-html-braintree-auth.php' );

		return ob_get_clean();
	}


	/**
	 * Gets the Braintree Auth connect URL.
	 *
	 * Although the Partner API expects an array, the WooCommerce Connect
	 * middleware presently wants things flattened. So instead of passing a user
	 * array and a business array, we pass selected fields with `user_` and
	 * `business_` prepended.
	 *
	 * @since 2.0.0
	 * @param string $environment the desired environment, either 'production' or 'sandbox'
	 * @return string
	 */
	protected function get_connect_url( $environment = self::ENVIRONMENT_PRODUCTION ) {

		$production_connect_url = 'https://connect.woocommerce.com/login/braintree';
		$sandbox_connect_url    = 'https://connect.woocommerce.com/login/braintreesandbox';

		$redirect_url = wp_nonce_url( $this->get_plugin()->get_payment_gateway_configuration_url( $this->get_id() ), 'connect_paypal_braintree', 'wc_paypal_braintree_admin_nonce' );

		$current_user = wp_get_current_user();

		// Note:  We doubly urlencode the redirect url to avoid Braintree's server
		// decoding it which would cause loss of query params on the final redirect
		$query_args = array(
			'user_email'        => $current_user->user_email,
			'business_currency' => get_woocommerce_currency(),
			'business_website'  => get_bloginfo( 'url' ),
			'redirect'          => urlencode( urlencode( $redirect_url ) ),
			'scopes'            => 'read_write',
		);

		if ( ! empty( $current_user->user_firstname ) ) {
			$query_args[ 'user_firstName' ] = $current_user->user_firstname;
		}

		if ( ! empty( $current_user->user_lastname ) ) {
			$query_args[ 'user_lastName' ] = $current_user->user_lastname;
		}

		// Let's go ahead and assume the user and business are in the same region and country,
		// because they probably are.  If not, they can edit these anyways
		$base_location = wc_get_base_location();

		if ( ! empty( $base_location['country'] ) ) {
			$query_args['business_country'] = $query_args['user_country'] = $base_location['country'];
		}

		if ( ! empty( $base_location['state'] ) ) {
			$query_args['business_region'] = $query_args['user_region'] = $base_location['state'];
		}

		if ( $site_name = get_bloginfo( 'name' ) ) {
			$query_args[ 'business_name' ] = $site_name;
		}

		if ( $site_description = get_bloginfo( 'description' ) ) {
			$query_args[ 'business_description' ] = $site_description;
		}

		if ( self::ENVIRONMENT_SANDBOX === $environment ) {
			$connect_url = 'https://connect.woocommerce.com/login/braintreesandbox';
		} else {
			$connect_url = 'https://connect.woocommerce.com/login/braintree';
		}

		return add_query_arg( $query_args, $connect_url );
	}


	/**
	 * Gets the Braintree Auth disconnect URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_disconnect_url() {

		$url = add_query_arg( 'disconnect_paypal_braintree', 1, $this->get_plugin()->get_payment_gateway_configuration_url( $this->get_id() ) );

		return wp_nonce_url( $url, 'disconnect_paypal_braintree', 'wc_paypal_braintree_admin_nonce' );
	}


	/** Merchant account ID (multi-currency) feature **************************/


	/**
	 * Generate the merchant account ID section HTML, including the currency
	 * selector and any existing merchant account IDs that have been entered
	 * by the admin
	 *
	 * @since 3.0.0
	 * @return string HTML
	 */
	protected function generate_merchant_account_ids_html() {

		$base_currency = get_woocommerce_currency();

		$button_text = sprintf( __( 'Add merchant account ID for %s', 'woocommerce-gateway-paypal-powered-by-braintree' ), $base_currency );

		// currency selector
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<select id="wc_braintree_merchant_account_id_currency" class="wc-enhanced-select">
					<?php foreach ( get_woocommerce_currencies() as $code => $name ) : ?>
						<option <?php selected( $code, $base_currency ); ?> value="<?php echo esc_attr( $code ); ?>">
							<?php echo esc_html( sprintf( '%s (%s)', $name, get_woocommerce_currency_symbol( $code ) ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</th>
			<td class="forminp">
				<a href="#" class="button js-add-merchant-account-id"><?php echo esc_html( $button_text ); ?></a>
			</td>
		</tr>
		<?php

		$html = ob_get_clean();
		// generate HTML for saved merchant account IDs
		foreach ( array_keys( $this->settings ) as $key ) {
			if ( preg_match( '/merchant_account_id_[a-z]{3}$/', $key ) ) {

				$currency = substr( $key, -3 );

				$html .= $this->generate_merchant_account_id_html( $currency );
			}
		}

		return $html;
	}


	/**
	 * Display the settings page with some additional CSS/JS to support the
	 * merchant account IDs feature
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::admin_options()
	 */
	public function admin_options() {

		parent::admin_options();
		?>
		<style type="text/css">

			.js-remove-merchant-account-id .dashicons-trash { margin-top: 5px; opacity: .4; } .js-remove-merchant-account-id { text-decoration: none; }
			input.js-dynamic-descriptor-valid { border-color: #7ad03a; } input.js-dynamic-descriptor-invalid { border-color: #a00; }

			.wc-braintree-auth.disabled {
				opacity: 0.25;
			}
			.wc-braintree-auth.disabled .wc-braintree-connect-button {
				cursor: default;
			}

		</style>
		<?php

		ob_start();
		?>

		$( document.body ).on( 'click', '.wc-braintree-auth.disabled .wc-braintree-connect-button', function( e ) {
			e.preventDefault();
		} );

		<?php // hide the "manually connect" toggle if already connected via Braintree Auth
		if ( $this->is_connected() ) : ?>
			$( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_connect_manually' ).closest( 'tr' ).hide();
		<?php endif; ?>

		$( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_connect_manually' ).change( function() {

			var $environment = $( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_environment' ).val();

			var $environmentFields = $( '.' + $environment + '-field' );

			if ( $( this ).is( ':checked' ) ) {

				$( 'tr.wc-braintree-auth' ).addClass( 'disabled' );

				$( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_environment' ).closest( 'tr' ).show();
				$( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_inherit_settings' ).closest( 'tr' ).show();

				if ( ! $( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_inherit_settings' ).is( ':checked' ) ) {
					$environmentFields.closest( 'tr' ).show();
				}

			} else {

				$( 'tr.wc-braintree-auth' ).removeClass( 'disabled' );

				$( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_environment' ).closest( 'tr' ).hide();
				$( '#woocommerce_<?php echo esc_js( $this->get_id() ); ?>_inherit_settings' ).closest( 'tr' ).hide();

				$environmentFields.closest( 'tr' ).hide();
			}

		} ).change();

		// sync add merchant account ID button text to selected currency
		$( 'select#wc_braintree_merchant_account_id_currency' ).change( function() {
			$( '.js-add-merchant-account-id' ).text( '<?php esc_html_e( 'Add merchant account ID for ', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?>' + $( this ).val() )
		} );

		// add new merchant account ID field
		$( '.js-add-merchant-account-id' ).click( function( e ) {
			e.preventDefault();

			var row_fragment = '<?php echo $this->generate_merchant_account_id_html(); ?>',
				currency     = $( 'select#wc_braintree_merchant_account_id_currency' ).val();

			// replace currency placeholders with selected currency
			row_fragment = row_fragment.replace( /{{currency_display}}/g, currency ).replace( /{{currency_code}}/g, currency.toLowerCase() );

			// prevent adding more than 1 merchant account ID for the same currency
			if ( $( 'input[name="' + $( row_fragment ).find( '.js-merchant-account-id-input' ).attr( 'name' ) + '"]' ).length ) {
				return;
			}

			// inject field HTML
			if ( $( '.js-merchant-account-id-input' ).length ) {
				$( '.js-merchant-account-id-input' ).closest( 'tr' ).last().after( row_fragment );
			} else {
				$( this ).closest( 'tr' ).after( row_fragment );
			}
		} );

		// delete existing merchant account ID
		$( '.form-table' ).on( 'click', '.js-remove-merchant-account-id', function( e ) {
			e.preventDefault();

			$( this ).closest( 'tr' ).delay( 50 ).fadeOut( 400, function() {
				$( this ).remove();
			} );
		} );

		$( '#woocommerce_braintree_credit_card_name_dynamic_descriptor' ).after( '<span style="margin-top:4px;" class="dashicons dashicons-yes js-dynamic-descriptor-icon"></span>' );

		// company name/DBA dynamic descriptor validation
		$( '#woocommerce_braintree_credit_card_name_dynamic_descriptor' ).on( 'change paste keyup', function () {

			var descriptor = $( this ).val();
			var $icon      = $( '.js-dynamic-descriptor-icon' );

			// not using descriptors
			if ( '' === descriptor ) {
				return;
			}

			// missing asterisk
			if ( -1 === descriptor.indexOf( '*' ) ) {
				$icon.addClass( 'dashicons-no-alt' ).removeClass( 'dashicons-yes' );
				$( this ).addClass( 'js-dynamic-descriptor-invalid' ).removeClass( 'js-dynamic-descriptor-valid' );
				return;
			}

			descriptor = descriptor.split( '*', 2 );
			name       = descriptor[0];
			product    = descriptor[1];

			// company name must be 3, 7, or 12 characters
			if ( 3 !== name.length && 7 !== name.length && 12 !== name.length ) {
				$icon.addClass( 'dashicons-no-alt' ).removeClass( 'dashicons-yes' );
				$( this ).addClass( 'js-dynamic-descriptor-invalid' ).removeClass( 'js-dynamic-descriptor-valid' );
				return;
			}

			$icon.removeClass( 'dashicons-no-alt' ).addClass( 'dashicons-yes' );
			$( this ).addClass( 'js-dynamic-descriptor-valid' ).removeClass( 'js-dynamic-descriptor-invalid' );
		} ).change();
		<?php

		wc_enqueue_js( ob_get_clean() );
	}


	/**
	 * Generate HTML for an individual merchant account ID field
	 *
	 * @since 3.0.0
	 * @param string|null $currency_code 3 character currency code for the merchant account ID
	 * @return string HTML
	 */
	protected function generate_merchant_account_id_html( $currency_code = null ) {

		if ( is_null( $currency_code ) ) {

			// set placeholders to be replaced by JS for new account account IDs
			$currency_display = '{{currency_display}}';
			$currency_code = '{{currency_code}}';

		} else {

			// used passed in currency code
			$currency_display = strtoupper( $currency_code );
			$currency_code = strtolower( $currency_code );
		}

		$id    = sprintf( 'woocommerce_%s_merchant_account_id_%s', $this->get_id(), $currency_code );
		$title = sprintf( __( 'Merchant Account ID (%s)', 'woocommerce-gateway-paypal-powered-by-braintree' ), $currency_display );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ) ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo esc_html( $title ) ?></span></legend>
					<input class="input-text regular-input js-merchant-account-id-input" type="text" name="<?php printf( 'woocommerce_%s_merchant_account_id[%s]', esc_attr( $this->get_id() ), esc_attr( $currency_code ) ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $this->get_option( "merchant_account_id_{$currency_code}" ) ); ?>" placeholder="<?php esc_attr_e( 'Enter merchant account ID', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?>" />
					<a href="#" title="<?php esc_attr_e( 'Remove this merchant account ID', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?>" class="js-remove-merchant-account-id"><span class="dashicons dashicons-trash"></span></a>
				</fieldset>
			</td>
		</tr>
		<?php

		// newlines break JS when this HTML is used as a fragment
		return trim( preg_replace( "/[\n\r\t]/",'', ob_get_clean() ) );
	}


	/**
	 * Filter admin options before saving to dynamically inject valid merchant
	 * account IDs so they're persisted to settings
	 *
	 * @since 3.3.0
	 * @param array $sanitized_fields
	 * @return array
	 */
	public function filter_admin_options( $sanitized_fields ) {

		// remove fields used only for display
		unset( $sanitized_fields['braintree_auth'] );
		unset( $sanitized_fields['merchant_account_id_title'] );
		unset( $sanitized_fields['merchant_account_ids'] );
		unset( $sanitized_fields['dynamic_descriptor_title'] );

		// first unset all merchant account IDs from settings so they can be freshly set
		foreach ( array_keys( $sanitized_fields ) as $name ) {

			if ( WC_Braintree_Framework\SV_WC_Helper::str_starts_with( $name, 'merchant_account_id_' ) ) {
				unset( $sanitized_fields[ $name ] );
				unset( $this->settings[ $name ] );
			}
		}

		$merchant_account_id_field_key = sprintf( 'woocommerce_%s_merchant_account_id', $this->get_id() );

		// add merchant account IDs
		if ( ! empty( $_POST[ $merchant_account_id_field_key ] ) ) {

			$currency_codes = array_keys( get_woocommerce_currencies() );

			foreach ( $_POST[ $merchant_account_id_field_key ] as $currency => $merchant_account_id ) {

				// sanity check for valid currency
				if ( ! in_array( strtoupper( $currency ), $currency_codes ) ) {
					continue;
				}

				$merchant_account_key = 'merchant_account_id_' . strtolower( $currency );

				// add to persisted fields
				$sanitized_fields[ $merchant_account_key ] = wp_kses_post( trim( stripslashes( $merchant_account_id ) ) );
				$this->settings[ $merchant_account_key ]   = $sanitized_fields[ $merchant_account_key ];
			}
		}

		return $sanitized_fields;
	}


	/** Getters ***************************************************************/


	/**
	 * Gets order meta.
	 *
	 * Overridden to account for some straggling meta that may be leftover from
	 * the v1 in certain cases when WC was updated to 3.0 before Subscriptions.
	 *
	 * @since 2.0.2
	 *
	 * @param \WC_Order|int $order order object or ID
	 * @param string $key meta key to get
	 * @return mixed meta value
	 */
	public function get_order_meta( $order, $key ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return false;
		}

		$order_id = WC_Braintree_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'id' );

		// update a legacy payment token if it exists
		if ( 'payment_token' === $key && metadata_exists( 'post', $order_id, '_wc_paypal_braintree_payment_method_token' ) && ! get_post_meta( $order_id, $this->get_order_meta_prefix() . $key, true ) && $this->get_id() === get_post_meta( $order_id, '_payment_method', true ) ) {

			$legacy_token = get_post_meta( $order_id, '_wc_paypal_braintree_payment_method_token', true );

			update_post_meta( $order_id, $this->get_order_meta_prefix() . $key, $legacy_token );

			delete_post_meta( $order_id, '_wc_paypal_braintree_payment_method_token' );

			return $legacy_token;
		}

		// update a legacy customer ID if it exists
		if ( 'customer_id' === $key && metadata_exists( 'post', $order_id, '_wc_paypal_braintree_customer_id' ) && ! get_post_meta( $order_id, $this->get_order_meta_prefix() . $key, true ) && $this->get_id() === get_post_meta( $order_id, '_payment_method', true ) ) {

			$legacy_customer_id = get_post_meta( $order_id, '_wc_paypal_braintree_customer_id', true );

			update_post_meta( $order_id, $this->get_order_meta_prefix() . $key, $legacy_customer_id );

			delete_post_meta( $order_id, '_wc_paypal_braintree_customer_id' );

			return $legacy_customer_id;
		}

		return parent::get_order_meta( $order, $key );
	}


	/**
	 * Returns the customer ID for the given user ID. Braintree provides a customer
	 * ID after creation.
	 *
	 * This is overridden to account for merchants that switched to v1 from the
	 * SkyVerge plugin, then updated old subscriptions and/or processed new
	 * subscriptions while waiting for v2.
	 *
	 * @since 2.0.1
	 * @see SV_WC_Payment_Gateway::get_customer_id()
	 * @param int $user_id WP user ID
	 * @param array $args optional additional arguments which can include: environment_id, autocreate (true/false), and order
	 * @return string payment gateway customer id
	 */
	public function get_customer_id( $user_id, $args = array() ) {

		$defaults = array(
			'environment_id' => $this->get_environment(),
			'autocreate'     => false,
			'order'          => null,
		);

		$args = array_merge( $defaults, $args );

		$customer_ids = get_user_meta( $user_id, $this->get_customer_id_user_meta_name( $args['environment_id'] ) );

		// if there is more than one customer ID, grab the latest and use it
		if ( is_array( $customer_ids ) && count( $customer_ids ) > 1 ) {

			$customer_id = end( $customer_ids );

			if ( $customer_id ) {

				$this->remove_customer_id( $user_id, $args['environment_id'] );

				$this->update_customer_id( $user_id, $customer_id, $args['environment_id'] );
			}
		}

		return parent::get_customer_id( $user_id, $args );
	}


	/**
	 * Ensure a customer ID is created in Braintree for guest customers
	 *
	 * A customer ID must exist in Braintree before it can be used so a guest
	 * customer ID cannot be generated on the fly. This ensures a customer is
	 * created when a payment method is tokenized for transactions such as a
	 * pre-order guest purchase.
	 *
	 * @since 3.1.1
	 * @see SV_WC_Payment_Gateway::get_guest_customer_id()
	 * @param WC_Order $order
	 * @return bool false
	 */
	public function get_guest_customer_id( WC_Order $order ) {

		// is there a customer id already tied to this order?
		if ( $customer_id = $this->get_order_meta( $order, 'customer_id' ) ) {
			return $customer_id;
		}

		// default to false as a customer must be created first
		return false;
	}



	/**
	 * Returns the merchant account transaction URL for the given order
	 *
	 * @since 3.0.0
	 * @see WC_Payment_Gateway::get_transaction_url()
	 * @param \WC_Order $order the order object
	 * @return string transaction URL
	 */
	public function get_transaction_url( $order ) {

		$merchant_id    = $this->get_merchant_id();
		$transaction_id = $this->get_order_meta( $order, 'trans_id' );
		$environment    = $this->get_order_meta( $order, 'environment' );

		if ( $merchant_id && $transaction_id ) {

			$this->view_transaction_url = sprintf( 'https://%s.braintreegateway.com/merchants/%s/transactions/%s',
				$this->is_test_environment( $environment ) ? 'sandbox' : 'www',
				$merchant_id,
				$transaction_id
			);
		}

		return parent::get_transaction_url( $order );
	}


	/**
	 * Returns true if the gateway is properly configured to perform transactions
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::is_configured()
	 * @return boolean true if the gateway is properly configured
	 */
	protected function is_configured() {

		$is_configured = parent::is_configured();

		if ( $this->is_connected() && ! $this->is_connected_manually() ) {
			$is_configured = true;
		} elseif ( ! $this->get_merchant_id() || ! $this->get_public_key() || ! $this->get_private_key() ) {
			$is_configured = false;
		}

		return $is_configured;
	}


	/**
	 * Determines if the gateway is connected via Braintree Auth.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_connected() {

		$token = $this->get_auth_access_token();

		return ! empty( $token );
	}


	/**
	 * Determines if the merchant can use Braintree Auth.
	 *
	 * Right now this checks that the shop is US-based and transacting in USD.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function can_connect() {

		return 'US' === WC()->countries->get_base_country() && 'USD' === get_woocommerce_currency();
	}


	/**
	 * Determines if the API is connected via standard credentials.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_connected_manually() {

		return 'yes' === $this->connect_manually || ! $this->can_connect();
	}


	/**
	 * Returns true if the current page contains a payment form
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function is_payment_form_page() {

		return ( is_checkout() && ! is_order_received_page() ) || is_checkout_pay_page() || is_add_payment_method_page();
	}


	/**
	 * Get the API object
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_api()
	 * @return \WC_Braintree_API instance
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		$includes_path = $this->get_plugin()->get_plugin_path() . '/includes';

		// main API class
		require_once( $includes_path . '/api/class-wc-braintree-api.php' );

		// response message helper
		require_once( $includes_path . '/api/class-wc-braintree-api-response-message-helper.php' );

		// requests
		require_once( $includes_path . '/api/requests/abstract-wc-braintree-api-request.php' );
		require_once( $includes_path . '/api/requests/class-wc-braintree-api-client-token-request.php' );
		require_once( $includes_path . '/api/requests/class-wc-braintree-api-transaction-request.php' );
		require_once( $includes_path . '/api/requests/abstract-wc-braintree-api-vault-request.php' );
		require_once( $includes_path . '/api/requests/class-wc-braintree-api-customer-request.php' );
		require_once( $includes_path . '/api/requests/class-wc-braintree-api-payment-method-request.php' );
		require_once( $includes_path . '/api/requests/class-wc-braintree-api-payment-method-nonce-request.php' );

		// responses
		require_once( $includes_path . '/api/responses/abstract-wc-braintree-api-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-client-token-response.php' );
		require_once( $includes_path . '/api/responses/abstract-wc-braintree-api-transaction-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-credit-card-transaction-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-paypal-transaction-response.php' );
		require_once( $includes_path . '/api/responses/abstract-wc-braintree-api-vault-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-customer-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-payment-method-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-payment-method-nonce-response.php' );
		require_once( $includes_path . '/api/responses/class-wc-braintree-api-merchant-configuration-response.php' );

		return $this->api = new WC_Braintree_API( $this );
	}


	/**
	 * Returns true if the current gateway environment is configured to 'sandbox'
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::is_test_environment()
	 * @param string $environment_id optional environment id to check, otherwise defaults to the gateway current environment
	 * @return boolean true if $environment_id (if non-null) or otherwise the current environment is test
	 */
	public function is_test_environment( $environment_id = null ) {

		// if an environment is passed in, check that
		if ( ! is_null( $environment_id ) ) {
			return self::ENVIRONMENT_SANDBOX === $environment_id;
		}

		// otherwise default to checking the current environment
		return $this->is_environment( self::ENVIRONMENT_SANDBOX );
	}


	/**
	 * Gets configured environment.
	 *
	 * If connected to Braintree Auth, the environment was explicitly set at
	 * the time of authentication. Otherwise, use the standard setting.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_environment() {

		if ( $this->is_connected() && ! $this->is_connected_manually() ) {
			$environment = $this->get_auth_environment();
		} else {
			$environment = parent::get_environment();
		}

		return $environment;
	}


	/**
	 * Returns true if the gateway is PayPal
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_paypal_gateway() {

		return WC_Gateway_Braintree_PayPal::PAYMENT_TYPE_PAYPAL === $this->get_payment_type();
	}


	/**
	 * Determines if this is a gateway that supports charging virtual-only orders.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function supports_credit_card_charge_virtual() {
		return $this->supports( self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL );
	}


	/**
	 * Returns the merchant ID based on the current environment
	 *
	 * @since 3.0.0
	 * @param string $environment_id optional one of 'sandbox' or 'production', defaults to current configured environment
	 * @return string merchant ID
	 */
	public function get_merchant_id( $environment_id = null ) {

		if ( $this->is_connected() && ! $this->is_connected_manually() ) {
			return $this->get_auth_merchant_id();
		}

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->merchant_id : $this->sandbox_merchant_id;
	}


	/**
	 * Gets the Braintree Auth access token.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_auth_access_token() {

		return $this->auth_access_token;
	}


	/**
	 * Gets the Braintree Auth merchant ID.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_auth_environment() {

		return get_option( 'wc_braintree_auth_environment', self::ENVIRONMENT_PRODUCTION );
	}


	/**
	 * Gets the Braintree Auth merchant ID.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_auth_merchant_id() {

		return get_option( 'wc_braintree_auth_merchant_id', '' );
	}


	/**
	 * Returns the public key based on the current environment
	 *
	 * @since 3.0.0
	 * @param string $environment_id optional one of 'sandbox' or 'production', defaults to current configured environment
	 * @return string public key
	 */
	public function get_public_key( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->public_key : $this->sandbox_public_key;
	}


	/**
	 * Returns the private key based on the current environment
	 *
	 * @since 3.0.0
	 * @param string $environment_id optional one of 'sandbox' or 'production', defaults to current configured environment
	 * @return string private key
	 */
	public function get_private_key( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return self::ENVIRONMENT_PRODUCTION === $environment_id ? $this->private_key : $this->sandbox_private_key;
	}


	/**
	 * Return the merchant account ID for the given currency and environment
	 *
	 * @since 3.0.0
	 * @param string|null $currency optional currency code, defaults to base WC currency
	 * @return string|null
	 */
	public function get_merchant_account_id( $currency = null ) {

		if ( is_null( $currency ) ) {
			$currency = get_woocommerce_currency();
		}

		$key = 'merchant_account_id_' . strtolower( $currency );

		return isset( $this->$key ) ? $this->$key : null;
	}


	/**
	 * Return an array of valid Braintree environments
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_braintree_environments() {

		return array( self::ENVIRONMENT_PRODUCTION => __( 'Production', 'woocommerce-gateway-paypal-powered-by-braintree' ), self::ENVIRONMENT_SANDBOX => __( 'Sandbox', 'woocommerce-gateway-paypal-powered-by-braintree' ) );
	}


	/**
	 * Determines if a dynamic descriptor name value is valid.
	 *
	 * @since 2.1.0
	 *
	 * @param string $value name to check. Defaults to the gateway's configured setting
	 * @return bool
	 */
	public function is_name_dynamic_descriptor_valid( $value = '' ) {

		if ( ! $value ) {
			$value = $this->get_name_dynamic_descriptor();
		}

		// missing asterisk
		if ( false === strpos( $value, '*' ) ) {
			return false;
		}

		$parts = explode( '*', $value );

		$company = $parts[0];
		$product = $parts[1];

		switch ( strlen( $company ) ) {

			case 3:  $product_length = 18; break;
			case 7:  $product_length = 14; break;
			case 12: $product_length = 9;  break;

			// if any other length, bail
			default: return false;
		}

		if ( $product > $product_length ) {
			return false;
		}

		return true;
	}


	/**
	 * Return the name dynamic descriptor
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#descriptor.name
	 * @since 3.0.0
	 * @return string
	 */
	public function get_name_dynamic_descriptor() {

		return $this->name_dynamic_descriptor;
	}


	/**
	 * Determines if a phone dynamic descriptor value is valid.
	 *
	 * The value must be 14 characters or less, have exactly 10 digits, and
	 * otherwise contain only numbers, dashes, parentheses, or periods.
	 *
	 * @since 2.1.0
	 *
	 * @param string $value value to check. Defaults to the gateway's configured setting
	 * @return bool
	 */
	public function is_phone_dynamic_descriptor_valid( $value = '' ) {

		if ( ! $value ) {
			$value = $this->get_phone_dynamic_descriptor();
		}

		// max 14 total characters
		if ( strlen( $value ) > 14 ) {
			return false;
		}

		// check for invalid characters
		if ( $invalid_characters = preg_replace( '/[\d-().]/', '', $value ) ) {
			return false;
		}

		// must have exactly 10 numbers
		if ( strlen( preg_replace( '/[^0-9]/', '', $value ) ) !== 10 ) {
			return false;
		}

		return true;
	}


	/**
	 * Return the phone dynamic descriptor
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#descriptor.phone
	 * @since 3.0.0
	 * @return string
	 */
	public function get_phone_dynamic_descriptor() {
		return $this->phone_dynamic_descriptor;
	}


	/**
	 * Return the URL dynamic descriptor
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#descriptor.url
	 * @since 3.0.0
	 * @return string
	 */
	public function get_url_dynamic_descriptor() {
		return $this->url_dynamic_descriptor;
	}


}
