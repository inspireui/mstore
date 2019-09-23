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
 * @package   WC-Braintree/Gateway/API/Responses/Credit-Card-Transaction
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Credit Card Transaction Response Class
 *
 * Handles parsing credit card transaction responses
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction/php#credit_card_details
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Credit_Card_Transaction_Response extends WC_Braintree_API_Transaction_Response {


	/**
	 * Get the authorization code
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::get_authorization_code()
	 * @return string 6 character credit card authorization code
	 */
	public function get_authorization_code() {

		return ! empty( $this->response->transaction->processorAuthorizationCode ) ? $this->response->transaction->processorAuthorizationCode : null;
	}


	/**
	 * Get the credit card payment token created during this transaction
	 *
	 * @since 3.0.0
	 * @return \WC_Braintree_Payment_Method
	 * @throws \SV_WC_Payment_Gateway_Exception if token is missing
	 */
	public function get_payment_token() {

		if ( empty( $this->response->transaction->creditCardDetails->token ) ) {
			throw new WC_Braintree_Framework\SV_WC_Payment_Gateway_Exception( __( 'Required credit card token is missing or empty!', 'woocommerce-gateway-paypal-powered-by-braintree' ) );
		}

		$data = array(
			'default'            => false, // tokens created as part of a transaction can't be set as default
			'type'               => WC_Braintree_Payment_Method::CREDIT_CARD_TYPE,
			'last_four'          => $this->get_last_four(),
			'card_type'          => $this->get_card_type(),
			'exp_month'          => $this->get_exp_month(),
			'exp_year'           => $this->get_exp_year(),
			'billing_address_id' => $this->get_billing_address_id(),
		);

		return new WC_Braintree_Payment_Method( $this->response->transaction->creditCardDetails->token, $data );
	}


	/**
	 * Get the card type used for this transaction
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_card_type() {

		// note that creditCardDetails->cardType is not used here as it is already prettified (e.g. American Express instead of amex)
		return WC_Braintree_Framework\SV_WC_Payment_Gateway_Helper::card_type_from_account_number( $this->get_bin() );
	}


	/**
	 * Get the BIN (bank identification number), AKA the first 6 digits of the card
	 * number. Most useful for identifying the card type.
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#credit_card_details.bin
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_bin() {

		return ! empty( $this->response->transaction->creditCardDetails->bin ) ? $this->response->transaction->creditCardDetails->bin : null;
	}


	/**
	 * Get the masked card number, which is the first 6 digits followed by
	 * 6 asterisks then the last 4 digits. This complies with PCI security standards.
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#credit_card_details.masked_number
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_masked_number() {

		return ! empty( $this->response->transaction->creditCardDetails->maskedNumber ) ? $this->response->transaction->creditCardDetails->maskedNumber : null;
	}


	/**
	 * Get the last four digits of the card number used for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#credit_card_details.last_4
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_last_four() {

		return ! empty( $this->response->transaction->creditCardDetails->last4) ? $this->response->transaction->creditCardDetails->last4 : null;
	}


	/**
	 * Get the expiration month (MM) of the card number used for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#credit_card_details.expiration_month
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_exp_month() {

		return ! empty( $this->response->transaction->creditCardDetails->expirationMonth ) ? $this->response->transaction->creditCardDetails->expirationMonth : null;
	}


	/**
	 * Get the expiration year (YYYY) of the card number used for this transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#credit_card_details.expiration_year
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_exp_year() {

		return ! empty( $this->response->transaction->creditCardDetails->expirationYear ) ? $this->response->transaction->creditCardDetails->expirationYear : null;
	}


	/**
	 * Get the billing address ID associated with the credit card token added
	 * during the transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/transaction/php#billing_details.id
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_billing_address_id() {

		return ! empty( $this->response->transaction->billingDetails->id ) ? $this->response->transaction->billingDetails->id : null;
	}


	/** 3D Secure feature *****************************************************/


	/**
	 * Returns true if 3D Secure information is present for the transaction
	 *
	 * @since 3.0.0
	 */
	public function has_3d_secure_info() {

		return isset( $this->response->transaction->threeDSecureInfo ) && ! empty( $this->response->transaction->threeDSecureInfo );
	}


	/**
	 * Returns the 3D secure statuses
	 *
	 * @link https://developers.braintreepayments.com/guides/3d-secure/server-side/php#server-side-details
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_3d_secure_status() {

		return $this->has_3d_secure_info() ? $this->response->transaction->threeDSecureInfo->status : null;
	}


	/**
	 * Returns true if liability was shifted for the 3D secure transaction
	 *
	 * @link https://developers.braintreepayments.com/guides/3d-secure/server-side/php#server-side-details
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_liability_shifted() {

		return $this->has_3d_secure_info() ? $this->response->transaction->threeDSecureInfo->liabilityShifted : null;
	}


	/**
	 * Returns true if a liability shift was possible for the 3D secure transaction
	 *
	 * @link https://developers.braintreepayments.com/guides/3d-secure/server-side/php#server-side-details
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_liability_shift_possible() {

		return $this->has_3d_secure_info() ? $this->response->transaction->threeDSecureInfo->liabilityShiftPossible : null;
	}


	/**
	 * Returns true if the card was enrolled in a 3D secure program
	 *
	 * @link https://developers.braintreepayments.com/guides/3d-secure/server-side/php#server-side-details
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_enrollment() {

		return $this->has_3d_secure_info() && 'Y' === $this->response->transaction->threeDSecureInfo->enrolled;
	}


}
