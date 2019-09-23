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

namespace WC_Braintree;

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * The Braintree Apple Pay base handler.
 *
 * @since 2.2.0
 */
class Apple_Pay extends WC_Braintree_Framework\SV_WC_Payment_Gateway_Apple_Pay {


	/**
	 * Initializes the frontend handler.
	 *
	 * @since 2.2.0
	 */
	protected function init_frontend() {

		$this->frontend = new Apple_Pay\Frontend( $this->get_plugin(), $this );
	}


	/**
	 * Builds a new payment request.
	 *
	 * Overridden to remove some properties that are set by Braintree from account configuration.
	 *
	 * @since 2.2.0
	 *
	 * @param float|int $amount payment amount
	 * @param array $args payment args
	 * @return array
	 */
	public function build_payment_request( $amount, $args = array() ) {

		$request = parent::build_payment_request( $amount, $args );

		// these values are populated by the Braintree SDK
		unset(
			$request['currencyCode'],
			$request['countryCode'],
			$request['merchantCapabilities'],
			$request['supportedNetworks']
		);

		return $request;
	}


	/**
	 * Builds a payment response object based on an array of data.
	 *
	 * @since 2.2.0
	 *
	 * @param string $data response data as a JSON string
	 *
	 * @return Apple_Pay\API\Payment_Response
	 */
	protected function build_payment_response( $data ) {

		return new Apple_Pay\API\Payment_Response( $data );
	}


	/**
	 * Determines if a local Apple Pay certificate is required.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function requires_certificate() {

		return false;
	}


	/**
	 * Determines if a merchant ID is required.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function requires_merchant_id() {

		return false;
	}


}
