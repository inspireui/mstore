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
 * The lifecycle handler class.
 *
 * @since 2.2.0
 *
 * @method \WC_Braintree get_plugin()
 */
class Lifecycle extends WC_Braintree_Framework\Plugin\Lifecycle {


	/**
	 * Initializes the plugin lifecycle.
	 *
	 * @since 2.2.0
	 */
	public function init() {

		$installed_version = $this->get_installed_version();

		// if installing from the retired WooCommerce Braintree plugin (higher version number)
		if ( version_compare( $installed_version, $this->get_plugin()->get_version(), '>' ) ) {

			$this->migrate_from_sv();

		// if upgrading from 1.x, which won't have a version number set
		} elseif ( ! $installed_version && ( get_option( 'woocommerce_paypalbraintree_cards_settings' ) || get_option( 'wc_paypal_braintree_merchant_access_token' ) ) ) {

			// set the version number
			$this->set_installed_version( '1.2.7' );
		}

		parent::init();
	}


	/**
	 * Performs any upgrade tasks based on the provided installed version.
	 *
	 * @since 2.2.0
	 *
	 * @param string $installed_version currently installed version
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to 2.0.0
		if ( version_compare( $installed_version, '2.0.0', '<' ) ) {

			global $wpdb;

			$this->get_plugin()->log( 'Starting upgrade to 2.0.0' );

			$environment = ( 'sandbox' === get_option( 'wc_paypal_braintree_environment' ) ) ? \WC_Gateway_Braintree::ENVIRONMENT_SANDBOX : \WC_Gateway_Braintree::ENVIRONMENT_PRODUCTION;
			$merchant_id = get_option( 'wc_paypal_braintree_merchant_id', '' );

			// Begin settings upgrade
			$this->get_plugin()->log( 'Upgrading settings' );

			// we need to parse args here because it's possible that the legacy
			// gateway was connected & processing payments but the settings were
			// never saved.
			$legacy_settings = wp_parse_args( get_option( 'woocommerce_paypalbraintree_cards_settings', array() ), array(
				'enabled'            => ( get_option( 'wc_paypal_braintree_merchant_access_token' ) ) ? 'yes' : 'no',
				'capture'            => 'yes',
				'debug'              => 'no',
				'title_cards'        => 'Credit Card',
				'description_cards'  => 'Pay securely using your credit card.',
				'title_paypal'       => 'PayPal',
				'description_paypal' => 'Click the PayPal icon below to sign into your PayPal account and pay securely.',
			) );

			$common_settings = array(
				'enabled'          => $legacy_settings['enabled'],
				'transaction_type' => ( 'yes' === $legacy_settings['capture'] ) ? \WC_Gateway_Braintree::TRANSACTION_TYPE_CHARGE : \WC_Gateway_Braintree::TRANSACTION_TYPE_AUTHORIZATION,
				'tokenization'     => 'yes',
				'environment'      => $environment,
				'debug_mode'       => ( 'yes' === $legacy_settings['debug'] ) ? \WC_Gateway_Braintree::DEBUG_MODE_LOG : \WC_Gateway_Braintree::DEBUG_MODE_OFF,
				'connect_manually' => 'no',
				'inherit_settings' => 'no',
			);

			if ( $environment === \WC_Gateway_Braintree::ENVIRONMENT_PRODUCTION ) {
				$common_settings['merchant_id'] = $merchant_id;
			} else {
				$common_settings[ $environment . '_merchant_id'] = $merchant_id;
			}

			$credit_card_settings = array(
				'title'       => $legacy_settings['title_cards'],
				'description' => $legacy_settings['description_cards'],
				'require_csc' => 'yes', // no option to disable this in v1, so enable by default
				'card_types'  => array( 'VISA', 'MC', 'AMEX', 'DISC', 'DINERS', 'JCB', ),
			);

			update_option( 'woocommerce_braintree_credit_card_settings', array_merge( $common_settings, $credit_card_settings ) );

			$paypal_settings = array(
				'title'            => $legacy_settings['title_paypal'],
				'description'      => $legacy_settings['description_paypal'],
			);

			update_option( 'woocommerce_braintree_paypal_settings', array_merge( $common_settings, $paypal_settings ) );

			// the Braintree Auth options
			$wpdb->update( $wpdb->options, array( 'option_name' => 'wc_braintree_auth_access_token' ), array( 'option_name' => 'wc_paypal_braintree_merchant_access_token' ) );
			$wpdb->update( $wpdb->options, array( 'option_name' => 'wc_braintree_auth_environment' ),  array( 'option_name' => 'wc_paypal_braintree_environment' ) );
			$wpdb->update( $wpdb->options, array( 'option_name' => 'wc_braintree_auth_merchant_id' ),  array( 'option_name' => 'wc_paypal_braintree_merchant_id' ) );

			$this->get_plugin()->log( 'Settings upgraded' );

			// update the legacy order & user meta
			$this->update_legacy_meta();

			// flush the options cache to ensure notices are displayed correctly
			wp_cache_flush();

			$this->get_plugin()->log( 'Completed upgrade for 2.0.0' );

		} elseif ( version_compare( $installed_version, '2.0.1', '<' ) ) {

			// update meta again for those that may be seeing the legacy migration issue from previous installs
			$this->update_legacy_meta();
		}

		// upgrade to v2.0.2
		if ( version_compare( $installed_version, '2.0.2', '<' ) ) {

			$this->get_plugin()->log( 'Starting upgrade to 2.0.2' );

			$cc_settings = get_option( 'woocommerce_braintree_credit_card_settings', array() );

			// if the require CSC setting was never set, set it to avoid various false error notices
			if ( ! empty( $cc_settings ) && empty( $cc_settings['require_csc'] ) ) {

				$this->get_plugin()->log( 'Updating missing CSC setting' );

				$cc_settings['require_csc'] = 'yes';

				update_option( 'woocommerce_braintree_credit_card_settings', $cc_settings );
			}

			$this->get_plugin()->log( 'Completed upgrade for 2.0.2' );
		}
	}


	/**
	 * Migrate the necessary settings from the retired plugin.
	 *
	 * @since 2.2.0
	 */
	protected function migrate_from_sv() {

		$this->get_plugin()->log( 'Starting migration to ' . $this->get_plugin()->get_plugin_name() );

		// set the version number
		$this->set_installed_version( $this->get_plugin()->get_version() );

		foreach ( $this->get_plugin()->get_gateway_ids() as $gateway_id ) {

			$settings = $this->get_plugin()->get_gateway_settings( $gateway_id );

			// if the API credentials have been previously configured
			if ( 'yes' === $settings['inherit_settings'] || ( ! empty( $settings['public_key'] ) && ! empty( $settings['private_key'] ) && ! empty( $settings['merchant_id'] ) ) || ( ! empty( $settings['sandbox_public_key'] ) && ! empty( $settings['sandbox_private_key'] ) && ! empty( $settings['sandbox_merchant_id'] ) ) ) {

				$settings['connect_manually'] = 'yes';

				update_option( $this->get_plugin()->get_gateway_settings_name( $gateway_id ), $settings );
			}
		}

		update_option( 'wc_braintree_legacy_migrated', 'yes' );

		// update legacy meta in case users had previously switched to v1 from
		// the SkyVerge plugin prior to this migration
		$this->update_legacy_meta();

		$this->get_plugin()->log( 'Completed migration to ' . $this->get_plugin()->get_plugin_name() );
	}


	/**
	 * Migrates Braintree legacy order, subscription, and user meta to v2.
	 *
	 * @since 2.2.0
	 */
	protected function update_legacy_meta() {
		global $wpdb;

		$this->get_plugin()->log( 'Updating legacy meta' );

		$order_meta = array(
			'_wc_paypal_braintree_customer_id'          => 'customer_id',
			'_wc_paypal_braintree_payment_method_token' => 'payment_token',
			'_pp_braintree_charge_captured'             => 'charge_captured',
		);

		$count = 0;

		foreach ( $order_meta as $legacy_key => $new_suffix ) {

			// update for the credit card gateway
			$rows = $wpdb->query(
				$wpdb->prepare(
					"
						UPDATE {$wpdb->postmeta} meta1, {$wpdb->postmeta} meta2
						SET meta1.meta_key = %s
						WHERE meta1.meta_key = %s
						AND meta2.meta_key   = '_payment_method'
						AND meta2.meta_value = 'paypalbraintree_cards'
						AND meta1.post_id    = meta2.post_id
					",
					[
						'_wc_braintree_credit_card_' . $new_suffix,
						$legacy_key,
					]
				)
			);

			$count += $rows;

			// update for the paypal gateway
			$rows = $wpdb->query(
				$wpdb->prepare(
					"
						UPDATE {$wpdb->postmeta} meta1, {$wpdb->postmeta} meta2
						SET meta1.meta_key = %s
						WHERE meta1.meta_key = %s
						AND meta2.meta_key   = '_payment_method'
						AND meta2.meta_value = 'paypalbraintree_paypal'
						AND meta1.post_id    = meta2.post_id
					",
					[
						'_wc_braintree_paypal_' . $new_suffix,
						$legacy_key,
					]
				)
			);

			$count += $rows;
		}

		if ( $rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'braintree_credit_card' ), array( 'meta_key' => '_payment_method', 'meta_value' => 'paypalbraintree_cards' ) ) ) {
			$count += $rows;
		}
		if ( $rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'braintree_paypal' ), array( 'meta_key' => '_payment_method', 'meta_value' => 'paypalbraintree_paypal' ) ) ) {
			$count += $rows;
		}

		if ( $count ) {
			$this->get_plugin()->log( sprintf( '%d rows of order meta data updated.', $count ) );
		}

		// Customer IDs
		// old key: _wc_paypal_braintree_customer_id
		// new key: wc_braintree_customer_id
		if ( $rows = $wpdb->update( $wpdb->usermeta, array( 'meta_key' => 'wc_braintree_customer_id' ), array( 'meta_key' => '_wc_paypal_braintree_customer_id' ) ) ) {
			$this->get_plugin()->log( sprintf( '%d user customer IDs updated.', $rows ) );
		}
	}


}
