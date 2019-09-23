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
 * @package   WC-Braintree/Gateway/Payment-Form
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Braintree Abstract Payment Form
 *
 * @since 3.0.0
 */
abstract class WC_Braintree_Payment_Form extends WC_Braintree_Framework\SV_WC_Payment_Gateway_Payment_Form {


	/**
	 * Render a test amount input field that can be used to override the order total
	 * when using the gateway in sandbox mode. The order total can then be set to
	 * various amounts to simulate various authorization/settlement responses
	 *
	 * @link https://developers.braintreepayments.com/reference/general/testing/php
	 *
	 * @since 3.0.0
	 */
	public function render_payment_form_description() {

		parent::render_payment_form_description();

		if ( $this->get_gateway()->is_test_environment() && $this->get_gateway()->is_credit_card_gateway() ) {

			?><p>Test credit card numbers: <code>378282246310005</code> or <code>4111111111111111</code></p><?php
		}

		if ( $this->get_gateway()->is_test_environment() && ! is_add_payment_method_page() ) {

			$id = 'wc-' . $this->get_gateway()->get_id_dasherized() . '-test-amount';

			?>
			<p class="form-row">
				<label for="<?php echo esc_attr( $id ); ?>">Test Amount <span style="font-size: 10px;" class="description">- Enter a <a href="https://developers.braintreepayments.com/reference/general/testing/php#test-amounts">test amount</a> to trigger a specific error response, or leave blank to use the order total.</span></label>
				<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" />
			</p>
			<?php
		}
	}


	/**
	 * Render a hidden input for the payment nonce before the credit card/PayPal
	 * fields. This is populated by the payment form javascript when it receives
	 * a nonce from Braintree.
	 *
	 * @since 3.0.0
	 */
	public function render_payment_fields() {

		?><input type="hidden" id="<?php echo esc_attr( 'wc_' . $this->get_gateway()->get_id() . '_payment_nonce' ); ?>" name="<?php echo esc_attr( 'wc_' . $this->get_gateway()->get_id() . '_payment_nonce' ); ?>" /><?php

		parent::render_payment_fields();
	}


	/**
	 * Get gateway-specific JS params that are passed to the payment form handler script
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_payment_form_handler_js_params() {

		return [
			'integration_error_message' => __( 'Currently unavailable. Please try a different payment method.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'payment_error_message'     => __( 'Oops, something went wrong. Please try a different payment method.', 'woocommerce-gateway-paypal-powered-by-braintree' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
		];
	}


	/**
	 * Render JS to instantiate the Braintree-specific payment form handler class.
	 * Note that this intentionally does not instantiate the standard payment
	 * form handler, as Braintree replaces it entirely.
	 *
	 * @since 3.0.0
	 */
	public function render_js() {

		// bail if not on a payment form page
		if ( ! $this->get_gateway()->is_available() || ! $this->get_gateway()->is_payment_form_page() ) {
			return;
		}

		// defaults for both gateways
		$params = array_merge( array(
			'id'                 => $this->get_gateway()->get_id(),
			'id_dasherized'      => $this->get_gateway()->get_id_dasherized(),
			'name'               => $this->get_gateway()->get_method_title(),
			'debug'              => $this->get_gateway()->debug_log(),
			'type'               => str_replace( '-', '_', $this->get_gateway()->get_payment_type() ),
			'client_token_nonce' => wp_create_nonce( 'wc_' . $this->get_gateway()->get_id() . '_get_client_token' ),
		), $this->get_payment_form_handler_js_params() );

		$handler_class = $this->get_gateway()->is_credit_card_gateway() ? 'WC_Braintree_Credit_Card_Payment_Form_Handler' : 'WC_Braintree_PayPal_Payment_Form_Handler';

		wc_enqueue_js( sprintf( 'window.wc_%1$s_handler = new %2$s( %3$s );', esc_js( $this->get_gateway()->get_id() ), $handler_class, json_encode( $params ) ) );
	}


	/**
	 * Gets the order total for both checkout and order pay page instances.
	 *
	 * @since 3.3.2-1
	 */
	public function get_order_total() {

		if ( is_checkout_pay_page() ) {

			$order = wc_get_order( $this->get_gateway()->get_checkout_pay_page_order_id() );
			return $order->get_total();

		} else {

			return WC()->cart->total;
		}

	}


}
