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
 * @package   WC-Braintree/Gateway/API/Responses/PayPal-Transaction
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API PayPal Transaction Response Class
 *
 * Handles parsing PayPal transaction responses
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details
 *
 * @since 3.0.0
 */
class WC_Braintree_API_PayPal_Transaction_Response extends WC_Braintree_API_Transaction_Response {


	/**
	 * Get the authorization code
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details.authorization_id
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::get_authorization_code()
	 * @return string 6 character credit card authorization code
	 */
	public function get_authorization_code() {

		return ! empty( $this->response->transaction->paypalDetails->authorizationId ) ? $this->response->transaction->paypalDetails->authorizationId : null;
	}


	/**
	 * Get the PayPal payment token created during this transaction
	 *
	 * @since 3.0.0
	 * @return \WC_Braintree_Payment_Method
	 * @throws \SV_WC_Payment_Gateway_Exception if token is missing
	 */
	public function get_payment_token() {

		if ( empty( $this->response->transaction->paypalDetails->token ) ) {
			throw new WC_Braintree_Framework\SV_WC_Payment_Gateway_Exception( __( 'Required PayPal token is missing or empty!', 'woocommerce-gateway-paypal-powered-by-braintree' ) );
		}

		$data = array(
			'default'     => false, // tokens created as part of a transaction can't be set as default
			'type'        => WC_Braintree_Payment_Method::PAYPAL_TYPE,
			'payer_email' => $this->get_payer_email(),
			'payer_id'    => $this->get_payer_id(),
		);

		return new WC_Braintree_Payment_Method( $this->response->transaction->paypalDetails->token, $data );
	}


	/**
	 * Get the email address associated with the PayPal account used for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details.payer_email
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_payer_email() {

		return ! empty( $this->response->transaction->paypalDetails->payerEmail ) ? $this->response->transaction->paypalDetails->payerEmail : null;
	}


	/**
	 * Get the payer ID associated with the PayPal account used for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details.payer_id
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_payer_id() {

		return ! empty( $this->response->transaction->paypalDetails->payerId ) ? $this->response->transaction->paypalDetails->payerId : null;
	}


	/**
	 * Get the payment ID for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details.payment_id
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_payment_id() {

		return ! empty( $this->response->transaction->paypalDetails->paymentId ) ? $this->response->transaction->paypalDetails->paymentId : null;
	}


	/**
	 * Get the debug ID for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_debug_id() {

		return ! empty( $this->response->transaction->paypalDetails->debugId ) ? $this->response->transaction->paypalDetails->debugId : null;
	}


	/**
	 * Get the refund ID for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#paypal_details.refund_id
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_refund_id() {

		return ! empty( $this->response->transaction->paypalDetails->refundId ) ? $this->response->transaction->paypalDetails->refundId : null;
	}


}
