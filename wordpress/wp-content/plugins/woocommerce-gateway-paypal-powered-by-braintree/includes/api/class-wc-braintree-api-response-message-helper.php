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
 * @package   WC-Braintree/Gateway/API/Response-Message-Helper
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Response Message Helper
 *
 * Builds customer-friendly response messages by mapping the various Braintree
 * error codes to standardized messages
 *
 * @link https://developers.braintreepayments.com/reference/general/processor-responses/authorization-responses
 * @link https://developers.braintreepayments.com/reference/general/validation-errors/all/php
 *
 * @since 3.0.0
 * @see SV_WC_Payment_Gateway_API_Response_Message_Helper
 */
class WC_Braintree_API_Response_Message_Helper extends WC_Braintree_Framework\SV_WC_Payment_Gateway_API_Response_Message_Helper {


	/** @var \WC_Braintree_API_Response response */
	protected $response;

	/** @var array decline codes */
	protected $decline_codes = array(
		'cvv'  => 'csc_mismatch',
		'avs'  => 'avs_mismatch',
		'2000' => 'card_declined',
		'2001' => 'insufficient_funds',
		'2002' => 'credit_limit_reached',
		'2003' => 'card_declined',
		'2004' => 'card_expired',
		'2005' => 'card_number_invalid',
		'2006' => 'card_expiry_invalid',
		'2007' => 'card_type_invalid',
		'2008' => 'card_number_invalid',
		'2010' => 'csc_mismatch',
		'2012' => 'card_declined',
		'2013' => 'card_declined',
		'2014' => 'card_declined',
		'2016' => 'error',
		'2017' => 'card_declined',
		'2018' => 'card_declined',
		'2023' => 'card_type_not_accepted',
		'2024' => 'card_type_not_accepted',
		'2038' => 'card_declined',
		'2046' => 'card_declined',
		'2056' => 'credit_limit_reached',
		'2059' => 'avs_mismatch',
		'2060' => 'avs_mismatch',
		'2075' => 'paypal_closed',
	);


	/**
	 * Initialize the API response message handler
	 *
	 * @since 3.0.0
	 * @param \WC_Braintree_API_Response $response
	 */
	public function __construct( $response ) {

		$this->response = $response;
	}


	/**
	 * Get the user-facing error/decline message. Used in place of the get_user_message()
	 * method because this class is instantiated with the response class and handles
	 * generating the message ID internally
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_message() {

		// not handling specific validation error messages right now
		if ( $this->get_response()->has_validation_errors() ) {
			return $this->get_user_message( 'error' );
		}

		// note that $this->get_response()->response->message contains a Braintree-provided humanized error message, but it's generally
		// not appropriate for display to customers so it's not used here

		$response_code = $this->get_response()->get_failure_status_info( 'code' );

		// If the order was authorized for later capture, then there is no decline message
		if ( 'authorized' === $response_code ) {
			return '';
		}

		$message_id = isset( $this->decline_codes[ $response_code ] ) ? $this->decline_codes[ $response_code ] : 'decline';

		return $this->get_user_message( $message_id );
	}


	/**
	 * Returns a message appropriate for a frontend user.  This should be used
	 * to provide enough information to a user to allow them to resolve an
	 * issue on their own, but not enough to help nefarious folks fishing for
	 * info. Adds a few custom authorize.net-specific user error messages.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway_API_Response_Message_Helper::get_user_message()
	 * @param string $message_id identifies the message to return
	 * @return string a user message
	 */
	public function get_user_message( $message_id ) {

		switch ( $message_id ) {

			case 'paypal_closed':
				$message = __( 'Sorry, we cannot process your transaction. The PayPal account is either locked or closed. Please use a different account or a different payment method.', 'woocommerce-gateway-paypal-powered-by-braintree' );
				break;

			default:
				$message = parent::get_user_message( $message_id );
		}

		/**
		 * Braintree API Response User Message Filter.
		 *
		 * Allow actors to change the message displayed to customers as a result
		 * of a transaction error.
		 *
		 * @since 3.0.0
		 * @param string $message message displayed to customers
		 * @param string $message_id parsed message ID, e.g. 'decline'
		 * @param \WC_Braintree_API_Response_Message_Helper $this instance
		 */
		return apply_filters( 'wc_braintree_api_response_user_message', $message, $message_id, $this );
	}


	/**
	 * Return the response object for this user message
	 *
	 * @since 3.0.0
	 * @return \WC_Braintree_API_Response
	 */
	public function get_response() {

		return $this->response;
	}


}
