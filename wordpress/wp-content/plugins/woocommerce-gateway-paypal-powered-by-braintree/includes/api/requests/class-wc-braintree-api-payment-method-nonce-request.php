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
 * @package   WC-Braintree/Gateway/API/Requests/Payment-Nonce
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Payment Method Nonce Request class
 *
 * Handles creating and getting payment method nonces, generally for use with 3D Secure
 * transactions
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Payment_Method_Nonce_Request extends WC_Braintree_API_Request {


	/**
	 * Get the payment method associated with a nonce. This is used to retrieve
	 * 3D Secure information about a nonce server-side before processing the transaction.
	 *
	 * @link https://developers.braintreepayments.com/reference/request/payment-method-nonce/create/php
	 *
	 * @since 3.0.0
	 * @param string $nonce nonce from 3D secure verification
	 */
	public function get_payment_method( $nonce ) {

		$this->set_resource( 'paymentMethodNonce' );
		$this->set_callback( 'find' );

		$this->request_data = $nonce;
	}


	/**
	 * Create a nonce given an existing vaulted payment token. This is used to
	 * provide a nonce to the 3D Secure verification method client-side.
	 *
	 * @link https://developers.braintreepayments.com/reference/request/payment-method-nonce/find/php
	 *
	 * @since 3.0.0
	 * @param string $token vault token ID
	 */
	public function create_nonce( $token ) {

		$this->set_resource( 'paymentMethodNonce' );
		$this->set_callback( 'create' );

		$this->request_data = $token;
	}


}
