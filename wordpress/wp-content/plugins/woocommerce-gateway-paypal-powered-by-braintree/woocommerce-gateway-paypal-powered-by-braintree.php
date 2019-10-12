<?php
/**
 * Plugin Name: WooCommerce PayPal Powered by Braintree Gateway
 * Plugin URI: https://docs.woocommerce.com/document/woocommerce-gateway-paypal-powered-by-braintree/
 * Description: Receive credit card or PayPal payments using Paypal Powered by Braintree.  A server with cURL, SSL support, and a valid SSL certificate is required (for security reasons) for this gateway to function. Requires PHP 5.4+
 * Author: WooCommerce
 * Author URI: http://woocommerce.com/
 * Version: 2.3.0
 * Text Domain: woocommerce-gateway-paypal-powered-by-braintree
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 2.6.14
 * WC tested up to: 3.7.0
 *
 * Copyright (c) 2016-2019, Automattic, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) or exit;

/**
 * Required minimums
 */
define( 'WC_PAYPAL_BRAINTREE_MIN_PHP_VER', '5.4.0' );

/**
 * Base plugin file
 */
define( 'WC_PAYPAL_BRAINTREE_FILE', __FILE__ );

/**
 * The plugin loader class.
 *
 * @since 1.0.0
 */
class WC_PayPal_Braintree_Loader {


	/** @var \WC_PayPal_Braintree_Loader the singleton instance of the class */
	private static $instance;

	/** @var array the admin notices to add */
	public $notices = array();


	/**
	 * Gets the singleton instance of the class.
	 *
	 * @return \WC_PayPal_Braintree_Loader
	 */
	public static function getInstance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Prevents cloning the instance of this class.
	 *
	 * @since 1.0.0
	 */
	private function __clone() { }


	/**
	 * Prevents unserializing the instance of this class.
	 *
	 * @since 1.0.0
	 */
	private function __wakeup() { }


	/**
	 * Constructs the loader.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {

		add_action( 'admin_init', array( $this, 'check_environment' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );

		// Don't hook anything else in the plugin if we're in an incompatible environment
		if ( self::get_environment_warning() ) {
			return;
		}

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @since 2.0.0
	 */
	public function init_plugin() {

		// if the legacy plugin is active, let the admin know
		if ( function_exists( 'wc_braintree' ) ) {
			$this->add_admin_notice( 'bad_environment', 'error', __( 'WooCommerce PayPal powered by Braintree is inactive. Please deactivate the retired WooCommerce Braintree plugin.', 'woocommerce-gateway-paypal-powered-by-braintree' ) );
			return;
		}

		// autoload the Braintree SDK
		require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );

		// Required framework classes class
		require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-plugin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/payment-gateway/class-sv-wc-payment-gateway-plugin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'class-wc-braintree.php' );
	}


	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_notice( $slug, $class, $message ) {

		$this->notices[ $slug ] = array(
			'class' => $class,
			'message' => $message
		);
	}

	/**
	 * Checks the server environment and other factors and deactivates plugins
	 * as necessary.
	 *
	 * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
	 *
	 * @since 1.0.0
	 */
	public static function activation_check() {

		// deactivate the retired plugin if active
		if ( is_plugin_active( 'woocommerce-gateway-braintree/woocommerce-gateway-braintree.php' ) ) {
			deactivate_plugins( 'woocommerce-gateway-braintree/woocommerce-gateway-braintree.php' );
		}

		$environment_warning = self::get_environment_warning( true );

		if ( $environment_warning ) {

			deactivate_plugins( plugin_basename( __FILE__ ) );

			wp_die( $environment_warning );
		}

		// enable the PayPal gateway on activation
		$paypal_settings = get_option( 'woocommerce_braintree_paypal_settings', [] );
		$paypal_settings['enabled'] = 'yes';
		update_option( 'woocommerce_braintree_paypal_settings', $paypal_settings );
	}

	/**
	 * Checks the environment on loading WordPress, just in case the environment
	 * changes after activation.
	 *
	 * @since 1.0.0
	 */
	public function check_environment() {

		$environment_warning = self::get_environment_warning();

		if ( $environment_warning && is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			deactivate_plugins( plugin_basename( __FILE__ ) );

			$this->add_admin_notice( 'bad_environment', 'error', $environment_warning );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}

	/**
	 * Checks the environment for compatibility problems.
	 *
	 * @since 1.0.0
	 * @param bool $during_activation whether this check is during plugin activation
	 * @return string|bool the error message if one exists, or false if everything's okay
	 */
	public static function get_environment_warning( $during_activation = false ) {

		$message = false;

		// check the PHP version
		if ( version_compare( phpversion(), WC_PAYPAL_BRAINTREE_MIN_PHP_VER, '<' ) ) {

			$message = sprintf( __( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-paypal-powered-by-braintree' ), WC_PAYPAL_BRAINTREE_MIN_PHP_VER, phpversion() );

			$prefix = ( $during_activation ) ? 'The plugin could not be activated. ' : 'WooCommerce PayPal Powered by Braintree has been deactivated. ';

			$message = $prefix . $message;
		}

		return $message;
	}

	/**
	 * Displays any admin notices added with \WC_PayPal_Braintree_Loader::add_admin_notice()
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {

		foreach ( (array) $this->notices as $notice_key => $notice ) {

			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
			echo "</p></div>";
		}
	}
}

WC_PayPal_Braintree_Loader::getInstance();
register_activation_hook( __FILE__, array( 'WC_PayPal_Braintree_Loader', 'activation_check' ) );
