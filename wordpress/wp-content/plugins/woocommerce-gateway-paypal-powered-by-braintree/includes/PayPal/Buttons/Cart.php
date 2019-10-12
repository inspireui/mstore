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
 * @package   WC-Braintree/Buttons
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace WC_Braintree\PayPal\Buttons;

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Cart page button class.
 *
 * @since 2.3.0
 */
class Cart extends Abstract_Button {


	/**
	 * Checks if this button should be enabled or not.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	protected function is_enabled() {

		return $this->get_gateway()->cart_checkout_enabled();
	}


	/**
	 * Adds any actions and filters needed for the button.
	 *
	 * @since 2.3.0
	 */
	protected function add_hooks() {

		parent::add_hooks();

		// add the PayPal button below "Proceed to Checkout"
		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'render' ], 50 );
	}


	/**
	 * Validates the WC API request.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	protected function is_wc_api_request_valid() {

		return (bool) wp_verify_nonce( WC_Braintree_Framework\SV_WC_Helper::get_post( 'wp_nonce' ), 'wc_' . $this->get_gateway()->get_id() . '_cart_set_payment_method' );
	}


	/**
	 * Renders the PayPal button JS.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected function get_form_handler_params() {

		$params = parent::get_form_handler_params();

		$params['button_styles']['label'] = 'checkout';

		/**
		 * Filters the PayPal cart button style parameters.
		 *
		 * See https://developer.paypal.com/docs/integration/direct/express-checkout/integration-jsv4/customize-button/
		 *
		 * @since 2.1.0
		 *
		 * @param array $styles style parameters
		 */
		$params['button_styles'] = (array) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_cart_button_styles', $params['button_styles'] );

		return $params;
	}


	/**
	 * Gets the total amount the button should charge.
	 *
	 * @since 2.3.0
	 *
	 * @return float
	 */
	protected function get_button_total() {

		return WC()->cart->total;
	}


	/**
	 * Gets the JS handler class name.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	protected function get_js_handler_name() {
		return 'WC_Braintree_PayPal_Cart_Handler';
	}


	/**
	 * Gets the JS handler object name.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	protected function get_js_handler_object_name() {
		return 'wc_braintree_paypal_cart_handler';
	}


}