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
 * @package   WC-Braintree/Gateway/API/Response
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Abstract Response Class
 *
 * Provides functionality common to all responses
 *
 * @since 3.0.0
 */
abstract class WC_Braintree_API_Response implements WC_Braintree_Framework\SV_WC_API_Response {


	/** @var mixed raw response from the Braintree SDK */
	protected $response;


	/**
	 * Setup the response
	 *
	 * @since 3.0.0
	 * @param mixed $response response data from Braintree SDK
	 * @param string $response_type indicates whether the response is from a credit card or PayPal request
	 */
	public function __construct( $response, $response_type ) {

		$this->response = $response;
		$this->response_type = $response_type;
	}


	/**
	 * Checks if the transaction was successful. Braintree's "success" attribute
	 * indicates _both_ that the request was successful *and* the transaction
	 * (if the request was a transaction) was successful. If a request/transaction
	 * isn't successful, it's due to one or more of the following 4 things:
	 *
	 * 1) Validation failure - invalid request data or the request itself was invalid
	 * 2) Gateway Rejection - the gateway rejected the transaction (duplicate check, AVS, CVV, fraud, 3dsecure)
	 * 3) Processor Declined - the merchant processor declined the transaction (soft/hard decline, depends on error code)
	 * 4) Exception - invalid API credentials, Braintree's servers are down or undergoing maintenance
	 *
	 * Note that exceptions are handled prior to response "parsing" so there's no
	 * handling for them here.
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::transaction_approved()
	 * @return bool true if approved, false otherwise
	 */
	public function transaction_approved() {

		return $this->response->success;
	}


	/**
	 * Braintree does not support the concept of held requests/transactions, so this
	 * doesn not apply
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function transaction_held() {
		return false;
	}


	/**
	 * Gets the transaction status code
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::get_status_code()
	 * @return string status code
	 */
	public function get_status_code() {

		return $this->transaction_approved() ? $this->get_success_status_info( 'code' ) : $this->get_failure_status_info( 'code' );
	}


	/**
	 * Gets the transaction status message
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::get_status_message()
	 * @return string status message
	 */
	public function get_status_message() {

		return $this->transaction_approved() ? $this->get_success_status_info( 'message' ) : $this->get_failure_status_info( 'message' );
	}


	/**
	 * Get the success status info for the given parameter, either code or message
	 *
	 * @since 3.0.0
	 * @param string $type status info type, either `code` or `message`
	 * @return string
	 */
	public function get_success_status_info( $type ) {

		// determine which type of response, transaction or credit card verification from adding customer/payment method
		$transaction = ! empty( $this->response->transaction ) ? $this->response->transaction : $this->response->creditCardVerification;

		if ( isset( $transaction->processorSettlementResponseCode ) ) {

			// submitting a previously authorized charge for settlement
			$status = array(
				'code'    => $transaction->processorSettlementResponseCode,
				'message' => $transaction->processorSettlementResponseText,
			);

		} else {

			// regular transactions
			$status = array(
				'code'    => $transaction->processorResponseCode,
				'message' => $transaction->processorResponseText,
			);
		}

		return isset( $status[ $type ] ) ? $status[ $type ] : null;
	}


	/**
	 * Get the failure status info for the given parameter, either code or message
	 *
	 * @since 3.0.0
	 * @param string $type status info type, either `code` or `message`
	 * @return string
	 */
	public function get_failure_status_info( $type ) {

		// check for validation errors first
		if ( $this->has_validation_errors() ) {

			$errors = $this->get_validation_errors();

			return implode( ', ', ( 'code' === $type ? array_keys( $errors ) : array_values( $errors ) ) );
		}

		// determine which type of response, transaction or credit card verification from adding customer/payment method
		$transaction = ! empty( $this->response->transaction ) ? $this->response->transaction : $this->response->creditCardVerification;

		// see https://developers.braintreepayments.com/reference/response/transaction/php#unsuccessful-result
		switch ( $transaction->status ) {

			// gateway rejections are due to CVV, AVS, fraud, etc
			case 'gateway_rejected':

				$status = array(
					'code'    => $transaction->gatewayRejectionReason,
					'message' => $this->response->message,
				);
				break;

			// soft/hard decline directly from merchant processor
			case 'processor_declined':

				$status = array(
					'code'    => $transaction->processorResponseCode,
					'message' => $transaction->processorResponseText . ( ! empty( $transaction->additionalProcessorResponse ) ? ' (' . $transaction->additionalProcessorResponse . ')' : '' ),
				);
				break;

			// only can occur when attempting to settle a previously authorized charge
			case 'settlement_declined':

				$status = array(
					'code' => $transaction->processorSettlementResponseCode,
					'message' => $transaction->processorSettlementResponseText,
				);
				break;

			// this path shouldn't execute, but for posterity
			default:
				$status = array(
					'code'    => $transaction->status,
					'message' => isset( $this->response->message ) ? $this->response->message : '',
				);
		}

		return isset( $status[ $type] ) ? $status[ $type ] : null;
	}


	/**
	 * Returns true if the response contains validation errors (API call
	 * cannot be processed because the request was invalid)
	 *
	 * @link https://developers.braintreepayments.com/reference/general/validation-errors/overview/php
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_validation_errors() {

		return isset( $this->response->errors ) && $this->response->errors->deepSize();
	}


	/**
	 * Get an associative array of validation codes => messages
	 *
	 * @link https://developers.braintreepayments.com/reference/general/validation-errors/all/php
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_validation_errors() {

		$errors = array();

		if ( $this->has_validation_errors() ) {

			foreach ( $this->response->errors->deepAll() as $error ) {

				$errors[ $error->code ] = $error->message;
			}
		}

		return $errors;
	}


	/**
	 * Get the error message suitable for displaying to the customer. This should
	 * provide enough information to be helpful for correcting customer-solvable
	 * issues (e.g. invalid CVV) but not enough to help nefarious folks phishing
	 * for data
	 *
	 * @since 3.0.0
	 */
	public function get_user_message() {

		$helper = new WC_Braintree_API_Response_Message_Helper( $this );

		return $helper->get_message();
	}


	/**
	 * Return the string representation of the response
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function to_string() {

		// TODO: print this nicer and with less irrelevant information (e.g. subscription attributes, etc) @MR 2015-11-05
		return print_r( $this->response, true );
	}


	/**
	 * Return the string representation of the response, stripped of any
	 * confidential info
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function to_string_safe() {

		// no idea yet
		return $this->to_string();
	}


	/**
	 * Return the response type, either `credit-card` or `paypal`
	 *
	 * @since 3.0.0
	 * @return string
	 */
	protected function get_response_type() {

		return $this->response_type;
	}


	/**
	 * Return the payment type for the response, either `credit-card` or `paypal`
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_payment_type() {

		return $this->get_response_type();
	}


	/**
	 * Return true if this response is from a credit card request
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	protected function is_credit_card_response() {
		return 'credit-card' === $this->get_response_type();
	}


}
