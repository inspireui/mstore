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
 * @package   WC-Braintree/Gateway/API/Responses/Payment-Method
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Payment Method Response Class
 *
 * Handles parsing payment method responses
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Payment_Method_Response extends WC_Braintree_API_Vault_Response implements WC_Braintree_Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response {


	/**
	 * Get the transaction ID, which is typically only present for create customer/
	 * payment method requests when verifying the associated credit card. PayPal
	 * requests (successful or unsuccessful) do not return a transaction ID
	 *
	 * @since 3.0.0
	 */
	public function get_transaction_id() {

		return $this->is_credit_card_response() && isset( $this->response->paymentMethod, $this->response->paymentMethod->verification ) ? $this->response->paymentMethod->verification->id : null;
	}


	/**
	 * Get the single payment token from a Braintree create payment method call
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method/php
	 *
	 * @since 3.0.0
	 * @return \WC_Braintree_Payment_Method
	 */
	public function get_payment_token() {

		return new WC_Braintree_Payment_Method( $this->response->paymentMethod->token, $this->get_payment_token_data( $this->response->paymentMethod ) );
	}


	/**
	 * Return true if the verification for this payment method has an AVS rejection from the gateway.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function has_avs_rejection() {

		return isset( $this->response->creditCardVerification ) && 'avs' === $this->response->creditCardVerification->gatewayRejectionReason;
	}


	/**
	 * Return true if the verification for this payment method has an CVV rejection from the gateway.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function has_cvv_rejection() {

		return isset( $this->response->creditCardVerification ) && 'cvv' === $this->response->creditCardVerification->gatewayRejectionReason;
	}


	/** Risk Data feature *****************************************************/


	/**
	 * Returns true if the transaction has risk data present. If this is not
	 * present, advanced fraud tools are not enabled (and set to "show") in
	 * the merchant's Braintree account and/or not enabled within plugin settings
	 *
	 * @since 3.0.0
	 */
	public function has_risk_data() {

		return isset( $this->response->paymentMethod->verification->riskData );
	}


	/**
	 * Get the risk ID for this transaction
	 *
	 * @since 3.0.0
	 */
	public function get_risk_id() {

		return $this->has_risk_data() ? $this->response->paymentMethod->verification->riskData->id : null;
	}


	/**
	 * Get the risk decision for this transaction, one of: 'not evaulated',
	 * 'approve', 'review', 'decline'
	 *
	 * @since 3.0.0
	 */
	public function get_risk_decision() {

		return $this->has_risk_data() ? $this->response->paymentMethod->verification->riskData->decision : null;
	}


}
