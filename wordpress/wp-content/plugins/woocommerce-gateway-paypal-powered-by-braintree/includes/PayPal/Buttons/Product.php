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
 * Product page button class.
 *
 * @since 2.3.0
 */
class Product extends Abstract_Button {


	/** @var \WC_Product|null|false the product object if on a product page or false if not on a product page */
	protected $product;


	/**
	 * Checks if this button should be enabled or not.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	protected function is_enabled() {

		return (bool) $this->get_gateway()->product_page_buy_now_enabled();
	}


	/**
	 * Adds necessary actions and filters for this button.
	 *
	 * @since 2.3.0
	 */
	protected function add_hooks() {

		parent::add_hooks();

		add_action( 'wp', function() { $this->init_product(); } );

		add_action( 'woocommerce_api_' . strtolower( get_class( $this->get_gateway() ) ) . '_product_button_checkout', [ $this, 'handle_wc_api' ] );

		if ( $this->should_validate_product_data() ) {
			add_action( 'woocommerce_api_' . strtolower( get_class( $this->get_gateway() ) ) . '_validate_product_data', [ $this, 'validate_product_data' ] );
		}
	}


	/**
	 * Initializes the product page buy now button.
	 *
	 * @internal
	 *
	 * @since 2.3.0
	 */
	public function init_product() {

		if ( ! is_product() || ! $this->get_product() ) {
			return;
		}

		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'render' ] );
	}


	/**
	 * Gets the form handler params.
	 *
	 * @since 2.3.0
	 */
	protected function get_form_handler_params() {

		$params = parent::get_form_handler_params();

		$params['button_styles']['label']  = 'buynow';
		$params['button_styles']['layout'] = 'horizontal';

		/**
		 * Filters the PayPal product button style parameters.
		 *
		 * See https://developer.paypal.com/docs/integration/direct/express-checkout/integration-jsv4/customize-button/
		 *
		 * @since 2.3.0
		 *
		 * @param array $styles style parameters
		 */
		$params['button_styles'] = (array) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_product_button_styles', $params['button_styles'] );

		return $params;
	}


	/**
	 * Validates a WC API request.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	protected function is_wc_api_request_valid() {

		return (bool) wp_verify_nonce( WC_Braintree_Framework\SV_WC_Helper::get_post( 'wp_nonce' ), 'wc_' . $this->get_gateway()->get_id() . '_product_button_checkout' );
	}


	/**
	 * Processes a WC API request that contains data from the button JS response.
	 *
	 * @since 2.3.0
	 */
	protected function process_wc_api_request() {

		$product_id = (int) WC_Braintree_Framework\SV_WC_Helper::get_post( 'product_id' );
		$product    = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product ) {
			wp_send_json_error( 'Invalid Product Data' );
		}

		$serialized = WC_Braintree_Framework\SV_WC_Helper::get_post( 'cart_form' );
		$cart_data  = [];

		if ( ! empty( $serialized ) ) {
			parse_str( $serialized, $cart_data );
		}

		$quantity     = isset( $cart_data['quantity'] ) ? (int) $cart_data['quantity'] : 1;
		$variation_id = isset( $cart_data['variation_id'] ) ? (int) $cart_data['variation_id'] : 0;

		do_action( 'wc_' . $this->get_gateway()->get_id() . '_before_product_button_add_to_cart', $product_id, $quantity, $variation_id, $cart_data );

		try {

			WC()->cart->empty_cart();
			WC()->cart->add_to_cart( $product->get_id(), max( $quantity, 1 ), $variation_id );

			parent::process_wc_api_request();

		// generic Exception to catch any exceptions that may be thrown by third-party code during add_to_cart()
		} catch ( \Exception $e) {

			$this->get_gateway()->get_plugin()->log( 'Error while processing button callback: ' . $e->getMessage() );

			wp_send_json_error( 'An error occurred while processing the PayPal button callback.' );
		}
	}


	/**
	 * Determines if product data should be validated before displaying a buy button.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	public function should_validate_product_data() {

		/**
		 * Filters whether the product data should be validated for this product button to be shown.
		 *
		 * @since 2.3.0
		 *
		 * @param bool $should_validate
		 * @param Product $product product button instance
		 */
		return (bool) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_product_button_should_validate_product_data', true, $this );
	}


	/**
	 * Validates product add-ons via AJAX to show/hide the PayPal button appropriately.
	 *
	 * @since 2.3.0
	 */
	public function validate_product_data() {

		if ( ! wp_verify_nonce( WC_Braintree_Framework\SV_WC_Helper::get_post( 'wp_nonce' ), 'wc_' . $this->get_gateway()->get_id() . '_validate_product_data' ) ) {
			return;
		}

		/**
		 * Validates the product data for displaying the product button.
		 *
		 * @since 2.3.0
		 *
		 * @param bool $is_valid
		 * @param Product $product product button instance
		 */
		$is_valid = (bool) apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_product_button_validate_product_data', true, $this );

		wp_send_json_success( [ 'is_valid' => $is_valid ] );
	}


	/**
	 * Gets any additional JS handler params needed for this button.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected function get_additional_js_handler_params() {

		return [
			'is_product_page'              => is_product(),
			'product_checkout_url'         => add_query_arg( 'wc-api', strtolower( get_class( $this->get_gateway() ) . '_product_button_checkout' ), home_url() ),
			'product_checkout_nonce'       => wp_create_nonce( 'wc_' . $this->get_gateway()->get_id() . '_product_button_checkout' ),
			'validate_product_url'         => add_query_arg( 'wc-api', strtolower( get_class( $this->get_gateway() ) . '_validate_product_data' ), home_url() ),
			'validate_product_nonce'       => wp_create_nonce( 'wc_' . $this->get_gateway()->get_id() . '_validate_product_data' ),
			'should_validate_product_data' => $this->should_validate_product_data(),
		];
	}


	/**
	 * Gets additional button markup params.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected function get_additional_button_params() {

		$params = [];

		if ( $this->get_product() ) {
			$params['product_id'] = $this->get_product()->get_id();
		}

		return $params;
	}


	/**
	 * Gets the product total.
	 *
	 * @since 2.3.0
	 *
	 * @return float
	 */
	protected function get_button_total() {

		return $this->get_product() ? $this->get_product()->get_price() : 0.0;
	}


	/**
	 * Gets the JS handler class name.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	protected function get_js_handler_object_name() {
		return 'wc_braintree_paypal_product_button_handler';
	}


	/**
	 * Gets the JS handler class name.
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */
	protected function get_js_handler_name() {
		return 'WC_Braintree_PayPal_Product_Button_Handler';
	}


	/**
	 * Returns whether the button is for single-use transaction or not.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	protected function is_single_use() {

		$single_use = true;

		if ( $this->get_gateway()->get_plugin()->is_pre_orders_active() && \WC_Pre_Orders_Product::product_is_charged_upon_release( $this->get_product() ) ) {
			$single_use = false;
		}

		if ( $this->get_gateway()->get_plugin()->is_subscriptions_active() && \WC_Subscriptions_Product::is_subscription( $this->get_product() ) ) {
			$single_use = false;
		}

		return $single_use;
	}


	/**
	 * Gets the product page product object, or false if not on a product page.
	 *
	 * @since 2.3.0
	 *
	 * @return \WC_Product|false
	 */
	protected function get_product() {

		if ( null === $this->product ) {

			$product       = wc_get_product( get_the_ID() );
			$this->product = $product instanceof \WC_Product ? $product : false;
		}

		return $this->product;
	}

}