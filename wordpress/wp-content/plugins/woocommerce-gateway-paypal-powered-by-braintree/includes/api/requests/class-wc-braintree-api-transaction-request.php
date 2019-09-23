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
 * @package   WC-Braintree/Gateway/API/Requests/Transaction
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Transaction Request Class
 *
 * Handles transaction requests (charges, auths, captures, refunds, voids)
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Transaction_Request extends WC_Braintree_API_Request {


	/** auth and capture transaction type */
	const AUTHORIZE_AND_CAPTURE = true;

	/** authorize-only transaction type */
	const AUTHORIZE_ONLY = false;

	/** @var string Braintree partner ID */
	protected $channel;


	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 * @param \WC_Order|null $order order if available
	 * @param string $channel Braintree Partner ID/channel
	 */
	public function __construct( $order = null, $channel = '' ) {

		parent::__construct( $order );

		$this->channel = $channel;
	}


	/**
	 * Creates a credit card charge request for the payment method / customer
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php
	 *
	 * @since 3.0.0
	 */
	public function create_credit_card_charge() {

		$this->create_transaction( self::AUTHORIZE_AND_CAPTURE );
	}


	/**
	 * Creates a credit card auth request for the payment method / customer
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php
	 *
	 * @since 3.0.0
	 */
	public function create_credit_card_auth() {

		$this->create_transaction( self::AUTHORIZE_ONLY );
	}


	/**
	 * Capture funds for a previous credit card authorization
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/submit-for-settlement/php
	 *
	 * @since 3.0.0
	 */
	public function create_credit_card_capture() {

		$this->set_resource( 'transaction' );
		$this->set_callback( 'submitForSettlement' );

		$this->request_data = array( $this->get_order()->capture->trans_id, $this->get_order()->capture->amount );
	}


	/**
	 * Refund funds from a previous transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/refund/php
	 *
	 * @since 3.0.0
	 */
	public function create_refund() {

		$this->set_resource( 'transaction' );
		$this->set_callback( 'refund' );

		$this->request_data = array( $this->get_order()->refund->trans_id, $this->get_order()->refund->amount );
	}


	/**
	 * Void a previous transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/void/php
	 *
	 * @since 3.0.0
	 */
	public function create_void() {

		$this->set_resource( 'transaction' );
		$this->set_callback( 'void' );

		$this->request_data = $this->get_order()->refund->trans_id;
	}


	/**
	 * Create a sale transaction with the given settlement type
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php
	 *
	 * @since 3.0.0
	 * @param bool $settlement_type true = auth/capture, false = auth-only
	 */
	protected function create_transaction( $settlement_type ) {

		$this->set_resource( 'transaction' );
		$this->set_callback( 'sale' );

		$this->request_data = array(
			'amount'            => $this->get_order()->payment_total,
			'orderId'           => $this->get_order()->get_order_number(),
			'merchantAccountId' => empty( $this->get_order()->payment->merchant_account_id ) ? null : $this->get_order()->payment->merchant_account_id,
			'shipping'          => $this->get_shipping_address(),
			'options'           => $this->get_options( $settlement_type ),
			'channel'           => $this->get_channel(),
			'deviceData'        => empty( $this->get_order()->payment->device_data ) ? null : $this->get_order()->payment->device_data,
			'taxAmount'         => WC_Braintree_Framework\SV_WC_Helper::number_format( $this->get_order()->get_total_tax() ),
			'taxExempt'         => $this->get_order()->get_user_id() > 0 && is_callable( array( WC()->customer, 'is_vat_exempt' ) ) ? WC()->customer->is_vat_exempt() : false,
		);

		// set customer data
		$this->set_customer();

		// set billing data
		$this->set_billing();

		// set payment method, either existing token or nonce
		$this->set_payment_method();

		// add dynamic descriptors
		$this->set_dynamic_descriptors();

		/**
		 * Filters the request data for new transactions.
		 *
		 * @since 2.0.0
		 * @param array $data The transaction/sale data
		 * @param \WC_Braintree_API_Transaction_Request $request the request object
		 */
		$this->request_data = apply_filters( 'wc_braintree_transaction_data', $this->request_data, $this );
	}


	/**
	 * Set the customer data for the transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#customer
	 *
	 * @since 3.0.0
	 */
	protected function set_customer() {

		if ( $this->get_order()->customer_id ) {

			// use existing customer ID
			$this->request_data['customerId'] = $this->get_order()->customer_id;

		} else {

			// set customer info
			// a customer will only be created if tokenization is required and
			// storeInVaultOnSuccess is set to true, see get_options() below
			$this->request_data['customer'] = array(
				'firstName' => $this->get_order_prop( 'billing_first_name' ),
				'lastName'  => $this->get_order_prop( 'billing_last_name' ),
				'company'   => $this->get_order_prop( 'billing_company' ),
				'phone'     => WC_Braintree_Framework\SV_WC_Helper::str_truncate( preg_replace( '/[^\d-().]/', '', $this->get_order_prop( 'billing_phone' ) ), 14, '' ),
				'email'     => $this->get_order_prop( 'billing_email' ),
			);
		}
	}


	/**
	 * Get the billing address for the transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#billing
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function set_billing() {

		if ( ! empty( $this->get_order()->payment->billing_address_id ) ) {

			// use the existing billing address when using a saved payment method
			$this->request_data['billingAddressId'] = $this->get_order()->payment->billing_address_id;

		} else {

			// otherwise just set the billing address directly
			$this->request_data['billing'] = array(
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
	}


	/**
	 * Get the shipping address for the transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#shipping
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_shipping_address() {

		return array(
			'firstName'         => $this->get_order_prop( 'shipping_first_name' ),
			'lastName'          => $this->get_order_prop( 'shipping_last_name' ),
			'company'           => $this->get_order_prop( 'shipping_company' ),
			'streetAddress'     => $this->get_order_prop( 'shipping_address_1' ),
			'extendedAddress'   => $this->get_order_prop( 'shipping_address_2' ),
			'locality'          => $this->get_order_prop( 'shipping_city' ),
			'region'            => $this->get_order_prop( 'shipping_state' ),
			'postalCode'        => $this->get_order_prop( 'shipping_postcode' ),
			'countryCodeAlpha2' => $this->get_order_prop( 'shipping_country' ),
		);
	}


	/**
	 * Set the payment method for the transaction, either a previously saved payment
	 * method (token) or a new payment method (nonce)
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#payment_method_nonce
	 *
	 * @since 3.0.0
	 */
	protected function set_payment_method() {

		if ( ! empty( $this->get_order()->payment->token ) && empty( $this->get_order()->payment->use_3ds_nonce ) ) {

			// use saved payment method (token)
			$this->request_data['paymentMethodToken'] = $this->get_order()->payment->token;

		} else {

			// use new payment method (nonce)
			$this->request_data['paymentMethodNonce'] = $this->get_order()->payment->nonce;

			// set cardholder name when adding a credit card, note this isn't possible
			// when using a 3DS nonce
			if ( 'credit_card' === $this->get_order()->payment->type && empty( $this->get_order()->payment->use_3ds_nonce ) ) {
				$this->request_data['creditCard'] = array( 'cardholderName' => $this->get_order()->get_formatted_billing_full_name() );
			}
		}

		// add recurring flag to transactions that are subscription renewals
		if ( ! empty( $this->get_order()->payment->subscription ) ) {
			$this->request_data['transactionSource'] = $this->get_order()->payment->subscription->is_renewal ? 'recurring' : 'recurring_first';
		}
	}


	/**
	 * Set the dynamic descriptors for the transaction, these are set by the
	 * admin in the gateway settings
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#descriptor
	 *
	 * @since 3.0.0
	 */
	protected function set_dynamic_descriptors() {

		// dynamic descriptors
		if ( ! empty( $this->get_order()->payment->dynamic_descriptors ) ) {

			$this->request_data['descriptor'] = array();

			foreach ( array( 'name', 'phone', 'url' ) as $key ) {

				if ( ! empty( $this->get_order()->payment->dynamic_descriptors->$key ) ) {
					$this->request_data['descriptor'][ $key ] = $this->get_order()->payment->dynamic_descriptors->$key;
				}
			}
		}
	}


	/**
	 * Get the options for the transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#options
	 *
	 * @since 3.0.0
	 * @param bool $settlement_type, authorize or auth/capture
	 * @return array
	 */
	protected function get_options( $settlement_type ) {

		$options = array(
			'submitForSettlement'   => $settlement_type,
			'storeInVaultOnSuccess' => $this->get_order()->payment->tokenize,
		);

		if ( $this->get_order()->payment->tokenize ) {
			$options['addBillingAddressToPaymentMethod'] = true;
		}

		if ( ! empty( $this->get_order()->payment->is_3ds_required ) ) {
			$options['three_d_secure'] = array( 'required' => true );
		}

		return $options;
	}


	/**
	 * Gets the channel ID for the transaction.
	 *
	 * @link https://developers.braintreepayments.com/reference/request/transaction/sale/php#channel
	 *
	 * @since 3.0.0
	 */
	protected function get_channel() {

		return $this->channel;
	}


}
