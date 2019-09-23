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
 * @package   WC-Braintree/Gateway/API/Responses/Transaction
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Abstract Transaction Response Class
 *
 * Provides common functionality to Credit Card & PayPal transaction response classes
 *
 * @link https://developers.braintreepayments.com/javascript+php/reference/response/transaction
 *
 * @since 3.0.0
 */
abstract class WC_Braintree_API_Transaction_Response extends WC_Braintree_API_Response implements WC_Braintree_Framework\SV_WC_Payment_Gateway_API_response, WC_Braintree_Framework\SV_WC_Payment_Gateway_API_Authorization_Response, WC_Braintree_Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response, WC_Braintree_Framework\SV_WC_Payment_Gateway_API_Customer_Response {


	/** Braintree's CSC match value */
	const CSC_MATCH = 'M';


	/**
	 * Gets the response transaction ID
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::get_transaction_id()
	 * @return string transaction id
	 */
	public function get_transaction_id() {

		return ! empty( $this->response->transaction->id ) ? $this->response->transaction->id : null;
	}


	/**
	 * Returns the result of the AVS check
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#avs_error_response_code
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::get_avs_result()
	 * @return string result of the AVS check, if any
	 */
	public function get_avs_result() {

		if ( ! empty( $this->response->transaction->avsErrorResponseCode ) ) {

			return 'error:' . $this->response->transaction->avsErrorResponseCode;

		} else {

			return $this->response->transaction->avsPostalCodeResponseCode . ':' . $this->response->transaction->avsStreetAddressResponseCode;
		}
	}


	/**
	 * Returns the result of the CSC check
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::get_csc_result()
	 * @return string result of CSC check
	 */
	public function get_csc_result() {

		return ( ! empty( $this->response->transaction->cvvResponseCode ) ) ? $this->response->transaction->cvvResponseCode : null;
	}


	/**
	 * Returns true if the CSC check was successful
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#cvv_response_code
	 *
	 * @since 3.0
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::csc_match()
	 * @return boolean true if the CSC check was successful
	 */
	public function csc_match() {

		return $this->get_csc_result() === self::CSC_MATCH;
	}


	/**
	 * Return the customer ID for the request
	 *
	 * @since 3.0.0
	 * @return string|null
	 */
	public function get_customer_id() {

		return ! empty( $this->response->transaction->customerDetails->id ) ? $this->response->transaction->customerDetails->id : null;
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

		return isset( $this->response->transaction->riskData );
	}


	/**
	 * Get the risk ID for this transaction
	 *
	 * @since 3.0.0
	 */
	public function get_risk_id() {

		return ! empty( $this->response->transaction->riskData->id ) ? $this->response->transaction->riskData->id : null;
	}


	/**
	 * Get the risk decision for this transaction, one of: 'not evaulated',
	 * 'approve', 'review', 'decline'
	 *
	 * @since 3.0.0
	 */
	public function get_risk_decision() {

		return ! empty( $this->response->transaction->riskData->decision ) ? $this->response->transaction->riskData->decision : null;
	}


}
