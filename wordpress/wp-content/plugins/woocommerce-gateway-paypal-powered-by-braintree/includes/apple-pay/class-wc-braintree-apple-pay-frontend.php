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
 * @package   WC-Braintree/Gateway/Credit-Card
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace WC_Braintree\Apple_Pay;

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * The Braintree Apple Pay frontend handler.
 *
 * @since 2.2.0
 */
class Frontend extends WC_Braintree_Framework\SV_WC_Payment_Gateway_Apple_Pay_Frontend {


	/**
	 * Enqueues the scripts.
	 *
	 * @see WC_Braintree_Framework\SV_WC_Payment_Gateway_Apple_Pay_Frontend::enqueue_scripts()
	 *
	 * @since 2.2.0
	 */
	public function enqueue_scripts() {

		parent::enqueue_scripts();

		// braintree.js library
		wp_enqueue_script( 'braintree-js-client', 'https://js.braintreegateway.com/web/' . \WC_Braintree::BRAINTREE_JS_SDK_VERSION . '/js/client.min.js', array(), \WC_Braintree::VERSION, true );

		wp_enqueue_script( 'braintree-js-apple-pay', 'https://js.braintreegateway.com/web/' . \WC_Braintree::BRAINTREE_JS_SDK_VERSION . '/js/apple-pay.min.js', array( 'braintree-js-client' ), \WC_Braintree::VERSION, true );

		wp_enqueue_script( 'wc-braintree-apple-pay-js', $this->get_plugin()->get_plugin_url() . '/assets/js/frontend/wc-braintree-apple-pay.min.js', array( 'jquery' ), $this->get_plugin()->get_version(), true );
	}


	/**
	 * Gets the JS handler name.
	 *
	 * Braintree requires its own JS handler that extends the FW implementation.
	 *
	 * @see WC_Braintree_Framework\SV_WC_Payment_Gateway_Apple_Pay_Frontend::get_js_handler_name()
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	protected function get_js_handler_name() {

		return 'WC_Braintree_Apple_Pay_Handler';
	}


	/**
	 * Gets the parameters to be passed to the JS handler.
	 *
	 * @see WC_Braintree_Framework\SV_WC_Payment_Gateway_Apple_Pay_Frontend::get_js_handler_params()
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	protected function get_js_handler_params() {

		$params = parent::get_js_handler_params();

		$params['store_name']         = get_bloginfo( 'name' );
		$params['client_token_nonce'] = wp_create_nonce( 'wc_' . $this->get_gateway()->get_id() . '_get_client_token' );

		return $params;
	}


}
