<?php
/**
 * WC-API endpoint handler.
 *
 * This handles API related functionality in WooCommerce.
 * - wc-api endpoint - Commonly used by Payment gateways for callbacks.
 * - Legacy REST API - Deprecated in 2.6.0. @see class-wc-legacy-api.php
 * - WP REST API - The main REST API in WooCommerce which is built on top of the WP REST API.
 *
 * @package WooCommerce\RestApi
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_API class.
 */
class WC_API extends WC_Legacy_API {

	/**
	 * This property is added only to be able to check if the WC_API class comes from WooCommerce or from the plugin.
	 */
	public static $legacy_api_is_in_separate_plugin = true;

	/**
	 * Init the API by setting up action and filter hooks.
	 */
	public function init() {
		parent::init();

		//These are still handled by WooCommerce core:
		//add_action( 'init', array( $this, 'add_endpoint' ), 0 );
		//add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		//add_action( 'rest_api_init', array( $this, 'register_wp_admin_settings' ) );

		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
	}

	/**
	 * Get the version of the REST API package being ran. Since API package was merged into core, this now follows WC version.
	 *
	 * @since 3.7.0
	 * @return string|null
	 */
	public function get_rest_api_package_version() {
        return get_option( 'woocommerce_version' );
	}

	/**
	 * Get the version of the REST API package being ran.
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_rest_api_package_path() {
        return dirname( __FILE__ );
	}

	/**
	 * Return if the rest API classes were already loaded.
	 *
	 * @since 3.7.0
	 * @return boolean
	 */
	protected function is_rest_api_loaded() {
		return class_exists( '\Automattic\WooCommerce\RestApi\Server', false );
	}

	/**
	 * Get data from a WooCommerce API endpoint.
	 *
	 * @since 3.7.0
	 * @param string $endpoint Endpoint.
	 * @param array  $params Params to pass with request.
	 * @return array|\WP_Error
	 */
	public function get_endpoint_data( $endpoint, $params = array() ) {
		if ( ! $this->is_rest_api_loaded() ) {
			return new WP_Error( 'rest_api_unavailable', __( 'The Rest API is unavailable.', 'woocommerce-legacy-rest-api' ) );
		}
		$request = new \WP_REST_Request( 'GET', $endpoint );
		if ( $params ) {
			$request->set_query_params( $params );
		}
		$response = rest_do_request( $request );
		$server   = rest_get_server();
		$json     = wp_json_encode( $server->response_to_data( $response, false ) );
		return json_decode( $json, true );
	}

	/**
	 * Add new query vars.
	 *
	 * @since 2.0
	 * @param array $vars Query vars.
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars   = parent::add_query_vars( $vars );
		$vars[] = 'wc-api';
		return $vars;
	}

	/**
	 * WC API for payment gateway IPNs, etc.
	 *
	 * @since 2.0
	 */
	public static function add_endpoint() {
		parent::add_endpoint();
		add_rewrite_endpoint( 'wc-api', EP_ALL );
	}

	/**
	 * API request - Trigger any API requests.
	 *
	 * @since   2.0
	 * @version 2.4
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['wc-api'] ) ) { // WPCS: input var okay, CSRF ok.
			$wp->query_vars['wc-api'] = sanitize_key( wp_unslash( $_GET['wc-api'] ) ); // WPCS: input var okay, CSRF ok.
		}

		// wc-api endpoint requests.
		if ( ! empty( $wp->query_vars['wc-api'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			wc_nocache_headers();

			// Clean the API request.
			$api_request = strtolower( wc_clean( $wp->query_vars['wc-api'] ) );

			// Make sure gateways are available for request.
			WC()->payment_gateways();

			// Trigger generic action before request hook.
			do_action( 'woocommerce_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'woocommerce_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'woocommerce_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}

	/**
	 * Register WC settings from WP-API to the REST API.
	 *
	 * @since  3.0.0
	 */
	public function register_wp_admin_settings() {
		$pages = WC_Admin_Settings::get_settings_pages();
		foreach ( $pages as $page ) {
			new WC_Register_WP_Admin_Settings( $page, 'page' );
		}

		$emails = WC_Emails::instance();
		foreach ( $emails->get_emails() as $email ) {
			new WC_Register_WP_Admin_Settings( $email, 'email' );
		}
	}

	/**
	 * Get API payload for a webhook.
	 * 
	 * This used to be the get_legacy_api_payload method in the WC_Webhook class
	 * in WooCommerce core, that method was removed in WooCommerce 9.0.
	 *
	 * @param  string $resource    Resource type.
	 * @param  int    $resource_id Resource ID.
	 * @param  string $event       Event type.
	 * @return array
	 */
	public function get_webhook_api_payload( $resource, $resource_id, $event ) {
		// Include & load API classes.
		WC()->api->includes();
		WC()->api->register_resources( new WC_API_Server( '/' ) );

		switch ( $resource ) {
			case 'coupon':
				$payload = WC()->api->WC_API_Coupons->get_coupon( $resource_id );
				break;

			case 'customer':
				$payload = WC()->api->WC_API_Customers->get_customer( $resource_id );
				break;

			case 'order':
				$payload = WC()->api->WC_API_Orders->get_order( $resource_id, null, apply_filters( 'woocommerce_webhook_order_payload_filters', array() ) );
				break;

			case 'product':
				// Bulk and quick edit action hooks return a product object instead of an ID.
				if ( 'updated' === $event && is_a( $resource_id, 'WC_Product' ) ) {
					$resource_id = $resource_id->get_id();
				}
				$payload = WC()->api->WC_API_Products->get_product( $resource_id );
				break;

			// Custom topics include the first hook argument.
			case 'action':
				$payload = array(
					'action' => current( $this->get_hooks() ),
					'arg'    => $resource_id,
				);
				break;

			default:
				$payload = array();
				break;
		}

		return $payload;
	}
}
