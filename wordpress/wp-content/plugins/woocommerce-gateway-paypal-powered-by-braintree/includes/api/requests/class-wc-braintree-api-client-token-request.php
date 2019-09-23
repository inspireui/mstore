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
 * @package   WC-Braintree/Gateway/API/Requests/Client-Token
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Client Token Request class
 *
 * Handles building the request for a client token
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Client_Token_Request extends WC_Braintree_API_Request {


	/**
	 * Get the client token
	 *
	 * @see https://developers.braintreepayments.com/javascript+php/reference/request/client-token/generate
	 *
	 * @since 3.0.0
	 * @param array $args token args
	 */
	public function get_token( array $args ) {

		$this->set_resource( 'clientToken' );
		$this->set_callback( 'generate' );

		$this->request_data = array( 'merchantAccountId' => $args['merchantAccountId'] );
	}


}
