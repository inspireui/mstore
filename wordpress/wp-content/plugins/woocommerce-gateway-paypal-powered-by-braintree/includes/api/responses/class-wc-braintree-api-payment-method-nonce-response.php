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
 * @package   WC-Braintree/Gateway/API/Responses/Payment-Nonce
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Payment Method Nonce Response Class
 *
 * Handles parsing payment method nonce responses
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Payment_Method_Nonce_Response extends WC_Braintree_API_Response {


	/**
	 * Get the payment method nonce
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php
	 *
	 * @since 3.0.0
	 * @return mixed
	 */
	public function get_nonce() {

		$payment_method = $this->get_payment_method();

		return ! empty( $payment_method ) ? $payment_method->nonce : null;
	}


	/**
	 * Returns true if the payment method has 3D Secure information present
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_3d_secure_info() {

		$payment_method = $this->get_payment_method();

		return ! empty( $payment_method ) && ! empty( $payment_method->threeDSecureInfo );
	}


	/**
	 * Returns the 3D secure statuses
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.status
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_3d_secure_status() {

		return $this->has_3d_secure_info() ? $this->get_payment_method()->threeDSecureInfo->status : null;
	}


	/**
	 * Returns true if liability was shifted for the 3D secure transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.liability_shifted
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_liability_shifted() {

		return $this->has_3d_secure_info() ? $this->get_payment_method()->threeDSecureInfo->liabilityShifted : null;
	}


	/**
	 * Returns true if a liability shift was possible for the 3D secure transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.liability_shift_possible
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_liability_shift_possible() {

		return $this->has_3d_secure_info() ? $this->get_payment_method()->threeDSecureInfo->liabilityShiftPossible : null;
	}


	/**
	 * Returns true if the card was enrolled in a 3D secure program
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.enrolled
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_enrollment() {

		return $this->has_3d_secure_info() && 'Y' === $this->get_payment_method()->threeDSecureInfo->enrolled;
	}


	/**
	 * Gets the payment method data.
	 *
	 * Some API requests will return the object directly, and others return it inside `paymentMethodNonce` so we need to
	 * check for that.
	 *
	 * @since 2.2.0
	 *
	 * @return object|null
	 */
	protected function get_payment_method() {

		return isset( $this->response->paymentMethodNonce ) ? $this->response->paymentMethodNonce : $this->response;
	}


}
