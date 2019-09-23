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
 * @package   WC-Braintree/Gateway/API/Requests/Payment-Method
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Payment Method Request class
 *
 * Handles creating, updating, and deleting individual payment methods
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Payment_Method_Request extends WC_Braintree_API_Vault_Request {


	/**
	 * Create a new payment method for an existing customer
	 *
	 * @link https://developers.braintreepayments.com/reference/request/payment-method/create/php
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order
	 */
	public function create_payment_method( WC_Order $order ) {

		$this->order = $order;

		$this->set_resource( 'paymentMethod' );
		$this->set_callback( 'create' );

		$this->request_data = array(
			'customerId'         => $order->customer_id,
			'paymentMethodNonce' => $order->payment->nonce,
		);

		// add verification data for credit cards
		if ( 'credit_card' === $order->payment->type ) {
			$this->request_data['billingAddress'] = $this->get_billing_address();
			$this->request_data['cardholderName'] = $order->get_formatted_billing_full_name();
			$this->request_data['options']        = $this->get_credit_card_options();
		}

		// fraud data
		$this->add_device_data();
	}


	/**
	 * Delete a customer's payment method
	 *
	 * @link https://developers.braintreepayments.com/reference/request/payment-method/delete/php
	 *
	 * @since 3.0.0
	 * @param string $token Braintree payment method token
	 */
	public function delete_payment_method( $token ) {

		$this->set_resource( 'paymentMethod' );
		$this->set_callback( 'delete' );

		$this->request_data = $token;
	}


	/**
	 * Verify the CSC for an existing saved payment method using the provided
	 * nonce
	 *
	 * @since 3.0.0
	 * @param string $token existing payment method token
	 * @param string $nonce nonce provided from client-side hosted fields
	 */
	public function verify_csc( $token, $nonce ) {

		$this->set_resource( 'paymentMethod' );
		$this->set_callback( 'update' );

		$update_data = array(
			'paymentMethodNonce' => $nonce,
			'billingAddress' => $this->get_billing_address(),
			'options' => array(
				'verifyCard' => true,
			),
		);

		$update_data['billingAddress']['options'] = array(
			'updateExisting' => true,
		);

		$this->request_data = array( $token, $update_data );
	}


}
