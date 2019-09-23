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

defined( 'ABSPATH' ) or exit;

/**
 * Braintree Frontend class
 *
 * Modifies the "My Payment Methods" table output from WC_Braintree_Framework files.
 *
 * TODO: This class can be removed if / when the SV Plugin Framework implements card icons in their own table cells.
 * @see https://github.com/skyverge/wc-plugin-framework/issues/198
 *
 * @since 2.0.0
 */
class WC_Braintree_Frontend {


	/**
	 * Modifies the "My Payment Methods" table headers.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0
	 *
	 * @param string[] $headers the table headers
	 * @return string[] updated headers
	 */
	public function modify_table_headers( $headers ) {

		// be sure the card icon is the first column in the row
		$new_headers = array(
			'icon' => esc_html_x( 'Type', 'Payment Method Type', 'woocommerce-gateway-paypal-powered-by-braintree' ),
		);

		return array_merge( $new_headers, $headers );
	}


	/**
	 * Adds a new table cell for the card icon, e.g. an Amex logo.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0
	 *
	 * @param string[] $methods {
	 *     @type string $title payment method title
	 *     @type string $expiry payment method expiry
	 *     @type string $actions actions for payment method
	 * }
	 * @param \SV_WC_Payment_Gateway_Payment_Token $token token
	 * @return string[] updated method data
	 */
	public function add_card_icon_cell( $method, $token ) {

		$method['icon'] = $this->get_payment_token_icon( $token );
		return $method;
	}


	/**
	 * Since we want to remove the card image, we need to re-build the method title html.
	 *
	 * Note: the textdomain is intentionally different here, which allows for existing framework translations to remain.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0
	 *
	 * @param string $html the method title html
	 * @param \SV_WC_Payment_Gateway_Payment_Token $token token
	 * @return string updated html
	 */
	public function remove_card_icon_from_title( $html, $token ) {

		$last_four = $token->get_last_four();
		$title     = $token->get_type_full();

		// add "ending in XXXX" if available
		if ( $last_four ) {

			/* translators: %s - last four digits of a card/account */
			$title .= '&nbsp;' . sprintf( esc_html__( 'ending in %s', 'woocommerce-gateway-paypal-powered-by-braintree' ), $last_four );
		}

		// add "(default)" if token is set as default
		if ( $token->is_default() ) {

			$title .= ' ' . esc_html__( '(default)', 'woocommerce-gateway-paypal-powered-by-braintree' );
		}

		return $title;
	}


	/**
	 * Get the payment method icon for a given token, e.g.: the Amex logo.
	 *
	 * @since 2.0.0
	 * @deprecated 2.2.0
	 *
	 * @param \SV_WC_Payment_Gateway_Payment_Token $token token
	 * @return string payment method icon html
	 */
	protected function get_payment_token_icon( $token ) {

		$image_url  = $token->get_image_url();
		$type       = $token->get_type_full();

		if ( $image_url ) {

			// format like "<Amex logo image> American Express"
			$image_html = sprintf( '<img src="%1$s" alt="%2$s" title="%2$s" width="40" height="25" />', esc_url( $image_url ), $type );

		} else {

			// missing payment method image, format like "American Express"
			$image_html = $type;
		}

		/**
		 * My Payment Methods Table Method Icon Filter.
		 *
		 * Allow actors to modify the table payment method icon.
		 *
		 * @since 2.0.0
		 * @param string $image_html payment method icon html
		 * @param \SV_WC_Payment_Gateway_Payment_Token $token token object
		 */
		return apply_filters( 'wc_braintree_my_payment_methods_table_method_icon', $image_html, $token );
	}


}
