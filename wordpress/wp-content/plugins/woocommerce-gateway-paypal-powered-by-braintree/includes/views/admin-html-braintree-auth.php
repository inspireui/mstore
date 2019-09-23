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
 * @package   WC-Braintree/Gateway/Auth
 * @author    WooCommerce
 * @copyright Copyright: (c) 2016-2019, Automattic, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

?>

<tr class="wc-braintree-auth">
	<th>

		<?php esc_html_e( 'Connect/Disconnect', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?>

		<?php echo wc_help_tip( $help_tip ); ?>
	</th>
	<td>
		<?php if ( $is_connected ) : ?>

			<a href="<?php echo esc_url( $disconnect_url ); ?>" class='button-primary'>
				<?php echo esc_html__( 'Disconnect from PayPal Powered by Braintree', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?>
			</a>

		<?php else : ?>

			<a href="<?php echo esc_url( $connect_url ); ?>" class="wc-braintree-connect-button"><img src="<?php echo esc_url( $button_image_url ); ?>"/></a>
			<br />
			<br />
			<a href="<?php echo esc_url( $sandbox_connect_url ); ?>" class="wc-braintree-connect-button"><?php esc_html_e( 'Not ready to accept live payments? Click here to connect using sandbox mode.', 'woocommerce-gateway-paypal-powered-by-braintree' ); ?></a>

		<?php endif; ?>
	</td>
</tr>
