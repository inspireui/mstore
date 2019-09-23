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
 * @package   WC-Braintree/Gateway/Payment-Method
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace WC_Braintree;

use WC_Braintree\Plugin_Framework as WC_Braintree_Framework;

defined( 'ABSPATH' ) or exit;

/**
 * The Braintree capture handler.
 *
 * @since 2.2.0
 */
class Capture extends WC_Braintree_Framework\Payment_Gateway\Handlers\Capture {


	/**
	 * Determines if an order's authorization has expired.
	 *
	 * @since 2.2.0
	 *
	 * @param \WC_Order $order
	 * @return bool
	 */
	public function has_order_authorization_expired( \WC_Order $order ) {

		if ( ! $this->get_gateway()->get_order_meta( $order, 'trans_id' ) ) {
			$this->get_gateway()->update_order_meta( $order, 'trans_id', WC_Braintree_Framework\SV_WC_Order_Compatibility::get_prop( $order, 'transaction_id' ) );
		}

		$date_created = WC_Braintree_Framework\SV_WC_Order_Compatibility::get_date_created( $order );

		if ( ! $this->get_gateway()->get_order_meta( $order, 'trans_date' ) && $date_created ) {
			$this->get_gateway()->update_order_meta( $order, 'trans_date', $date_created->date( 'Y-m-d H:i:s' ) );
		}

		return parent::has_order_authorization_expired( $order );
	}


	/**
	 * Determines if an order is eligible for capture.
	 *
	 * @since 2.2.0
	 *
	 * @param \WC_Order $order order object
	 * @return bool
	 */
	public function order_can_be_captured( \WC_Order $order ) {

		// if v1 never set the capture status, assume it has been captured
		if ( ! in_array( $this->get_gateway()->get_order_meta( $order, 'charge_captured' ), array( 'yes', 'no' ), true ) ) {
			$this->get_gateway()->update_order_meta( $order, 'charge_captured', 'yes' );
		}

		return parent::order_can_be_captured( $order );
	}


}
