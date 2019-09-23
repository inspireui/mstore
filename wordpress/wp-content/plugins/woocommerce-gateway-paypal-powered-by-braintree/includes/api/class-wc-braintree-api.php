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
 * @package   WC-Braintree/Gateway/API
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Class
 *
 * This is a pseudo-wrapper around the Braintree PHP SDK
 *
 * @link https://github.com/braintree/braintree_php
 * @link https://developers.braintreepayments.com/javascript+php/reference/overview
 *
 * @since 3.0.0
 */
class WC_Braintree_API extends WC_Braintree_Framework\SV_WC_API_Base implements WC_Braintree_Framework\SV_WC_Payment_Gateway_API {


	/** Braintree Partner ID for transactions using Braintree Auth */
	const BT_AUTH_CHANNEL = 'woothemes_bt';

	/** Braintree Partner ID for transactions using API keys */
	const API_CHANNEL = 'woocommerce_bt';


	/** @var \WC_Gateway_Braintree class instance */
	protected $gateway;

	/** @var \WC_Order order associated with the request, if any */
	protected $order;


	/**
	 * Constructor - setup request object and set endpoint
	 *
	 * @since 3.0.0
	 * @param \WC_Gateway_Braintree $gateway class instance
	 */
	public function __construct( $gateway ) {

		$this->gateway = $gateway;
	}


	/** API Methods ***********************************************************/


	/**
	 * Gets the merchant account configuration.
	 *
	 * @since 2.2.0
	 *
	 * @return WC_Braintree_API_Merchant_Configuration_Response
	 * @throws WC_Braintree_Framework\SV_WC_API_Exception
	 */
	public function get_merchant_configuration() {

		$response = $this->get_client_token( [ 'merchantAccountId' => '' ] );

		$data = base64_decode( $response->get_client_token() );

		// sanity check that the client key has valid JSON to decode
		if ( ! json_decode( $data ) ) {
			throw new WC_Braintree_Framework\SV_WC_API_Exception( 'The client key contained invalid JSON.', 500 );
		}

		return new WC_Braintree_API_Merchant_Configuration_Response( $data );
	}


	/**
	 * Get a client token for initializing the hosted fields or PayPal forms
	 *
	 * @since 3.0.0
	 * @param array $args
	 * @return \WC_Braintree_API_Client_Token_Response
	 * @throws \SV_WC_Payment_Gateway_Exception
	 */
	public function get_client_token( Array $args = array() ) {

		$request = $this->get_new_request( array(
			'type' => 'client-token',
		) );

		$request->get_token( $args );

		return $this->perform_request( $request );
	}


	/**
	 * Create a new credit card charge transaction
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_charge()
	 * @param \WC_Order $order order
	 * @return \WC_Braintree_API_Credit_Card_Transaction_Response|\WC_Braintree_API_PayPal_Transaction_Response
	 * @throws \SV_WC_Payment_Gateway_Exception
	 */
	public function credit_card_charge( WC_Order $order ) {

		// pre-verify CSC
		if ( $this->get_gateway()->is_credit_card_gateway() && $this->get_gateway()->is_csc_required() ) {
			$this->verify_csc( $order );
		}

		$request = $this->get_new_request( array(
			'type'  => 'transaction',
			'order' => $order,
		) );

		$request->create_credit_card_charge();

		return $this->perform_request( $request );
	}


	/**
	 * Create a new credit card auth transaction
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_authorization()
	 * @param \WC_Order $order order
	 * @return \WC_Braintree_API_Credit_Card_Transaction_Response|\WC_Braintree_API_PayPal_Transaction_Response
	 * @throws \SV_WC_Payment_Gateway_Exception
	 */
	public function credit_card_authorization( WC_Order $order ) {

		// pre-verify CSC
		if ( $this->get_gateway()->is_credit_card_gateway() && $this->get_gateway()->is_csc_required() ) {
			$this->verify_csc( $order );
		}

		$request = $this->get_new_request( array(
			'type'  => 'transaction',
			'order' => $order,
		) );

		$request->create_credit_card_auth();

		return $this->perform_request( $request );
	}


	/**
	 * Verify the CSC for a transaction when using a saved payment toke and CSC
	 * is required. This must be done prior to processing the actual transaction.
	 *
	 * @since 3.0.0
	 * @param \WC_Order $ordero rder
	 * @throws \SV_WC_Payment_Gateway_Exception if CSC verification fails
	 */
	public function verify_csc( WC_Order $order ) {

		// don't verify the CSC for transactions that are already 3ds verified
		if ( ! empty( $order->payment->use_3ds_nonce ) ) {
			return;
		}

		if ( ! empty( $order->payment->nonce ) && ! empty( $order->payment->token ) ) {

			$request = $this->get_new_request( array(
				'type' => 'payment-method',
				'order' => $order,
			) );

			$request->verify_csc( $order->payment->token, $order->payment->nonce );

			$result = $this->perform_request( $request );

			if ( ! $result->transaction_approved() ) {

				if ( $result->has_avs_rejection() ) {

					$message = __( 'The billing address for this transaction does not match the cardholders.', 'woocommerce-gateway-paypal-powered-by-braintree' );

				} elseif ( $result->has_cvv_rejection() ) {

					$message = __( 'The CSC for the transaction was invalid or incorrect.', 'woocommerce-gateway-paypal-powered-by-braintree' );

				} else {

					$message = $result->get_user_message();
				}

				throw new WC_Braintree_Framework\SV_WC_Payment_Gateway_Exception( $message );
			}
		}
	}


	/**
	 * Capture funds for a credit card authorization
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_capture()
	 * @param \WC_Order $order order
	 * @return \WC_Braintree_API_Transaction_Response
	 * @throws \SV_WC_Payment_Gateway_Exception
	 */
	public function credit_card_capture( WC_Order $order ) {

		$request = $this->get_new_request( array(
			'type'  => 'transaction',
			'order' => $order,
		) );

		$request->create_credit_card_capture();

		return $this->perform_request( $request );
	}


	/**
	 * Check Debit - no-op
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order order
	 * @return null
	 */
	public function check_debit( WC_Order $order ) { }


	/**
	 * Perform a refund for the order
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order the order
	 * @return \WC_Braintree_API_Transaction_Response
	 * @throws \SV_WC_Payment_Gateway_Exception
	 */
	public function refund( WC_Order $order ) {

		$request = $this->get_new_request( array(
			'type'  => 'transaction',
			'order' => $order,
		) );

		$request->create_refund();

		return $this->perform_request( $request );
	}


	/**
	 * Perform a void for the order
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order the order
	 * @return \WC_Braintree_API_Transaction_Response
	 * @throws \SV_WC_Payment_Gateway_Exception
	 */
	public function void( WC_Order $order ) {

		$request = $this->get_new_request( array(
			'type'  => 'transaction',
			'order' => $order,
		) );

		$request->create_void();

		return $this->perform_request( $request );
	}


	/** API Tokenization methods **********************************************/


	/**
	 * Tokenize the payment method associated with the order
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::tokenize_payment_method()
	 * @param WC_Order $order the order with associated payment and customer info
	 * @return \WC_Braintree_API_Customer_Response|\WC_Braintree_API_Payment_Method_Response
	 */
	public function tokenize_payment_method( WC_Order $order ) {

		if ( $order->customer_id ) {

			// create a payment method for existing customer
			$request = $this->get_new_request( array(
				'type'  => 'payment-method',
				'order' => $order,
			) );

			$request->create_payment_method( $order );

		} else {

			// create both customer and payment method
			$request = $this->get_new_request( array(
				'type'  => 'customer',
				'order' => $order,
			) );

			$request->create_customer( $order );
		}

		return $this->perform_request( $request );
	}


	/**
	 * Get the tokenized payment methods for the customer
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::get_tokenized_payment_methods()
	 * @param string $customer_id unique
	 * @return \WC_Braintree_API_Customer_response
	 * @throws \SV_WC_API_Exception
	 */
	public function get_tokenized_payment_methods( $customer_id ) {

		$request = $this->get_new_request( array( 'type' => 'customer' ) );

		$request->get_payment_methods( $customer_id );

		return $this->perform_request( $request );
	}


	/**
	 * Update the tokenized payment method for given customer
	 *
	 * @since 3.0.0
	 * @param WC_Order $order
	 */
	public function update_tokenized_payment_method( WC_Order $order ) {

		// update payment method
		// https://developers.braintreepayments.com/javascript+php/reference/request/payment-method/update
	}


	/**
	 * Determines whether updating tokenized methods is supported.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function supports_update_tokenized_payment_method() {

		return false;
	}


	/**
	 * Remove the given tokenized payment method for the customer
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::remove_tokenized_payment_method()
	 * @param string $token the payment method token
	 * @param string $customer_id unique
	 * @return \WC_Authorize_Net_CIM_API_Payment_Profile_Response
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) {

		$request = $this->get_new_request( array( 'type' => 'payment-method' ) );

		$request->delete_payment_method( $token );

		return $this->perform_request( $request );
	}


	/**
	 * Braintree supports retrieving tokenized payment methods
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::supports_get_tokenized_payment_methods()
	 * @return boolean true
	 */
	public function supports_get_tokenized_payment_methods() {
		return true;
	}


	/**
	 * Braintree supports removing tokenized payment methods
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::supports_remove_tokenized_payment_method()
	 * @return boolean true
	 */
	public function supports_remove_tokenized_payment_method() {
		return true;
	}


	/**
	 * Get payment method info from a client-side provided nonce, generally
	 * used for retrieving and verifying 3D secure information server-side
	 *
	 * @since 3.0.0
	 * @param string $nonce payment nonce
	 * @return \WC_Braintree_API_Payment_Method_Nonce_Response
	 * @throws WC_Braintree_Framework\SV_WC_Plugin_Exception
	 */
	public function get_payment_method_from_nonce( $nonce ) {

		$request = $this->get_new_request( array( 'type' => 'payment-method-nonce' ) );

		$request->get_payment_method( $nonce );

		return $this->perform_request( $request );
	}

	/**
	 * Get the payment nonce from a given payment token, generally used to
	 * provide a nonce for a previously vaulted payment method to the client-side
	 * 3D Secure verification script
	 *
	 * @since 3.0.0
	 * @param string $token payment method token ID
	 * @return \WC_Braintree_API_Payment_Method_Nonce_Response
	 * @throws WC_Braintree_Framework\SV_WC_Plugin_Exception
	 */
	public function get_nonce_from_payment_token( $token ) {

		$request = $this->get_new_request( array( 'type' => 'payment-method-nonce' ) );

		$request->create_nonce( $token );

		return $this->perform_request( $request );
	}


	/** Request/Response Methods **********************************************/


	/**
	 * Perform a remote request using the Braintree SDK. Overriddes the standard
	 * wp_remote_request() as the SDK already provides a cURL implementation
	 *
	 * @since 3.0.0
	 * @see SV_WC_API_Base::do_remote_request()
	 * @param string $callback SDK static callback, e.g. `Braintree_ClientToken::generate`
	 * @param array $callback_params parameters to pass to the static callback
	 * @return \Exception|mixed
	 */
	protected function do_remote_request( $callback, $callback_params ) {

		// configure
		if ( $this->is_braintree_auth() ) {

			// configure with access token
			$gateway_args = array(
				'accessToken' => $this->get_gateway()->get_auth_access_token(),
			);

		} else {

			$gateway_args = array(
				'environment' => $this->get_gateway()->get_environment(),
				'merchantId'  => $this->get_gateway()->get_merchant_id(),
				'publicKey'   => $this->get_gateway()->get_public_key(),
				'privateKey'  => $this->get_gateway()->get_private_key(),
			);
		}

		$sdk_gateway = new Braintree\Gateway( $gateway_args );

		$resource = $this->get_request()->get_resource();

		try {

			$response = call_user_func_array( array( $sdk_gateway->$resource(), $callback ), $callback_params );

		} catch ( Exception $e ) {

			$response = $e;
		}

		return $response;
	}


	/**
	 * Handle and parse the response
	 *
	 * @since 3.0.0
	 * @param mixed $response directly from Braintree SDK
	 * @return \WC_Braintree_API_Response
	 * @throws \SV_WC_API_Exception braintree errors
	 */
	protected function handle_response( $response ) {

		// check if Braintree response contains exception and convert to framework exception
		if ( $response instanceof Exception ) {
			throw new WC_Braintree_Framework\SV_WC_API_Exception( $this->get_braintree_exception_message( $response ), $response->getCode(), $response );
		}

		$handler_class = $this->get_response_handler();

		// parse the response body and tie it to the request
		$this->response = new $handler_class( $response, $this->get_gateway()->is_credit_card_gateway() ? 'credit-card' : 'paypal' );

		// broadcast request
		$this->broadcast_request();

		return $this->response;
	}



	/**
	 * Get a human-friendly message from the Braintree exception object
	 *
	 * @link https://developers.braintreepayments.com/reference/general/exceptions/php
	 * @since 3.0.0
	 * @param \Exception $e
	 * @return string
	 */
	protected function get_braintree_exception_message( $e ) {

		switch ( get_class( $e ) ) {

			case 'Braintree\Exception\Authentication':
				$message = __( 'Invalid Credentials, please double-check your API credentials (Merchant ID, Public Key, Private Key, and Merchant Account ID) and try again.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			break;

			case 'Braintree\Exception\Authorization':
				$message = __( 'Authorization Failed, please verify the user for the API credentials provided can perform transactions and that the request data is correct.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			break;

			case 'Braintree\Exception\DownForMaintenance':
				$message = __( 'Braintree is currently down for maintenance, please try again later.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			break;

			case 'Braintree\Exception\NotFound':
				$message = __( 'The record cannot be found, please contact support.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			break;

			case 'Braintree\Exception\ServerError':
				$message = __( 'Braintree encountered an error when processing your request, please try again later or contact support.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			break;

			case 'Braintree\Exception\SSLCertificate':
				$message = __( 'Braintree cannot verify your server\'s SSL certificate. Please contact your hosting provider or try again later.', 'woocommerce-gateway-paypal-powered-by-braintree' );
			break;

			default:
				$message = $e->getMessage();
		}

		return $message;
	}


	/**
	 * Override the standard request URI with the static callback instead, since
	 * the Braintree SDK handles the actual remote request
	 *
	 * @since 3.0.0
	 * @see SV_WC_API_Base::get_request_uri()
	 * @return string
	 */
	protected function get_request_uri() {
		return $this->get_request()->get_callback();
	}


	/**
	 * Override the standard request args with the static callback params instead,
	 * since the Braintree SDK handles the actual remote request
	 *
	 * @since 3.0.0
	 * @see SV_WC_API_Base::get_request_args()
	 * @return array
	 */
	protected function get_request_args() {
		return $this->get_request()->get_callback_params();
	}


	/**
	 * Alert other actors that a request has been performed, primarily for
	 * request/response logging.
	 *
	 * @see SV_WC_API_Base::broadcast_request()
	 * @since 3.0.0
	 */
	protected function broadcast_request() {

		$request_data = array(
			'environment' => $this->get_gateway()->get_environment(),
			'uri'         => $this->get_request_uri(),
			'data'        => $this->get_request()->to_string_safe(),
			'duration'    => $this->get_request_duration() . 's', // seconds
		);

		$response_data = array(
			'data' => is_callable( array( $this->get_response(), 'to_string_safe' ) ) ? $this->get_response()->to_string_safe() : print_r( $this->get_response(), true ),
		);

		do_action( 'wc_' . $this->get_api_id() . '_api_request_performed', $request_data, $response_data, $this );
	}


	/**
	 * Builds and returns a new API request object
	 *
	 * @since 3.0.0
	 * @see SV_WC_API_Base::get_new_request()
	 * @param array $args
	 * @throws SV_WC_API_Exception for invalid request types
	 * @return \WC_Braintree_API_Client_Token_Request|\WC_Braintree_API_Transaction_Request|\WC_Braintree_API_Customer_Request|\WC_Braintree_API_Payment_Method_Request|\WC_Braintree_API_Payment_Method_Nonce_Request
	 */
	protected function get_new_request( $args = array() ) {

		$this->order = isset( $args['order'] ) && $args['order'] instanceof WC_Order ? $args['order'] : null;

		switch ( $args['type'] ) {

			case 'client-token':
				$this->set_response_handler( 'WC_Braintree_API_Client_Token_Response' );
				return new WC_Braintree_API_Client_Token_Request();
			break;

			case 'transaction':

				$channel = ( $this->is_braintree_auth() ) ? self::BT_AUTH_CHANNEL : self::API_CHANNEL;

				$this->set_response_handler( $this->get_gateway()->is_credit_card_gateway() ? 'WC_Braintree_API_Credit_Card_Transaction_Response' : 'WC_Braintree_API_PayPal_Transaction_Response' );
				return new WC_Braintree_API_Transaction_Request( $this->order, $channel );

			case 'customer':
				$this->set_response_handler( 'WC_Braintree_API_Customer_Response' );
				return new WC_Braintree_API_Customer_Request( $this->order );

			case 'payment-method':
				$this->set_response_handler( 'WC_Braintree_API_Payment_Method_Response' );
				return new WC_Braintree_API_Payment_Method_Request( $this->order );

			case 'payment-method-nonce':
				$this->set_response_handler( 'WC_Braintree_API_Payment_Method_Nonce_Response' );
				return new WC_Braintree_API_Payment_Method_Nonce_Request();

			default:
				throw new WC_Braintree_Framework\SV_WC_API_Exception( 'Invalid request type' );
		}
	}


	/** Helper methods ********************************************************/


	/**
	 * Determines if the gateway is configured with Braintree Auth or standard
	 * API keys.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function is_braintree_auth() {

		return $this->get_gateway()->is_connected() && ! $this->get_gateway()->is_connected_manually();
	}


	/**
	 * Return the order associated with the request, if any
	 *
	 * @since 3.0.0
	 * @return \WC_Order
	 */
	public function get_order() {

		return $this->order;
	}


	/**
	 * Get the ID for the API, used primarily to namespace the action name
	 * for broadcasting requests
	 *
	 * @since 3.0.0
	 * @return string
	 */
	protected function get_api_id() {

		return $this->get_gateway()->get_id();
	}


	/**
	 * Return the gateway plugin
	 *
	 * @since 3.0.0
	 * @return \SV_WC_Payment_Gateway_Plugin
	 */
	public function get_plugin() {

		return $this->get_gateway()->get_plugin();
	}


	/**
	 * Returns the gateway class associated with the request
	 *
	 * @since 3.0.0
	 * @return \WC_Gateway_Braintree class instance
	 */
	public function get_gateway() {

		return $this->gateway;
	}


}
