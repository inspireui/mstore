<?php
/**
 * Plugin Name: WooCommerce Legacy REST API
 * Plugin URI: https://github.com/woocommerce/woocommerce-legacy-rest-api
 * Description: The legacy WooCommerce REST API, which used to be part of WooCommerce itself but is removed as of WooCommerce 9.0.
 * Version: 1.0.4
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * Text Domain: woocommerce
 * Domain Path: /i18n/languages/
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 2.6
 * WC tested up to: 8.2
 * Requires PHP: 7.4
 * 
 * Copyright: © 2023 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-wc-legacy-rest-api-plugin.php';
WC_Legacy_REST_API_Plugin::register_hook_handlers( __FILE__ );
