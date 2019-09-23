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
 * @package   WC-Braintree/Gateway/API/Requests/Vault
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Abstract Vault Request class
 *
 * Handles common methods for vault requests - Customers/Payment Methods
 *
 * @since 3.0.0
 */
abstract class WC_Braintree_API_Vault_Request extends WC_Braintree_API_Request {


	/**
	 * Return the billing address in the format required by Braintree
	 *
	 * @link https://developers.braintreepayments.com/reference/request/payment-method/create/php#billing_address
	 * @link https://developers.braintreepayments.com/reference/request/customer/create/php#credit_card.billing_address
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_billing_address() {

		return array(
			'firstName'         => $this->get_order_prop( 'billing_first_name' ),
			'lastName'          => $this->get_order_prop( 'billing_last_name' ),
			'company'           => $this->get_order_prop( 'billing_company' ),
			'streetAddress'     => $this->get_order_prop( 'billing_address_1' ),
			'extendedAddress'   => $this->get_order_prop( 'billing_address_2' ),
			'locality'          => $this->get_order_prop( 'billing_city' ),
			'region'            => $this->get_order_prop( 'billing_state' ),
			'postalCode'        => $this->get_order_prop( 'billing_postcode' ),
			'countryCodeAlpha2' => $this->get_order_prop( 'billing_country' ),
		);
	}


	/**
	 * Return the options used for creating a payment method, mostly for
	 * credit cards. This verifies the card by running CVV/AVS checks and prevents
	 * duplicate payment methods from being added.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_credit_card_options() {

		$options = array(
			'failOnDuplicatePaymentMethod' => true,
			'verifyCard'                   => true,
		);

		if ( ! empty( $this->get_order()->payment->merchant_account_id ) ) {
			$options['verificationMerchantAccountId'] = $this->get_order()->payment->merchant_account_id;
		}

		return $options;
	}


	/**
	 * Add device data for advanced fraud handling, if it's present
	 *
	 * @since 3.0.0
	 */
	protected function add_device_data() {

		if ( $device_data = $this->get_order()->payment->device_data ) {

			$this->request_data['deviceData'] = $device_data;
		}
	}


}
