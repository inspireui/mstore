<?php
/**
 * Fired during plugin activation
 *
 * @link: http://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 */

namespace WP_Rest_Cache_Plugin\Includes;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Activator {

	/**
	 * Activate the plugin. Add default options and copy Must-Use plugin to correct directory.
	 */
	public static function activate() {
		if ( ! get_option( 'wp_rest_cache_allowed_endpoints' ) ) {
			add_option( 'wp_rest_cache_allowed_endpoints', [], '', false );
		}
		if ( ! get_option( 'wp_rest_cache_rest_prefix' ) ) {
			add_option( 'wp_rest_cache_rest_prefix', rest_get_url_prefix(), '', false );
		}
		if ( ! get_option( 'wp_rest_cache_cacheable_request_headers' ) ) {
			add_option( 'wp_rest_cache_cacheable_request_headers', [], '', false );
		}

		self::create_mu_plugin();
	}

	/**
	 * Create a Must Use plugin to handle caching asap. Before loading of other plugins and/or theme.
	 */
	public static function create_mu_plugin() {
		$access_type = get_filesystem_method();
		if ( 'direct' !== $access_type ) {
			return;
		}
		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL );
		$url         = get_home_url() . $request_uri;
		$creds       = request_filesystem_credentials( $url );
		if ( ! WP_Filesystem( $creds ) ) {
			return;
		}
		global $wp_filesystem;

		if ( ! $wp_filesystem->is_dir( WPMU_PLUGIN_DIR ) ) {
			$wp_filesystem->mkdir( WPMU_PLUGIN_DIR );
		}

		$source = plugin_dir_path( __DIR__ ) . 'sources/wp-rest-cache.php';
		$target = WPMU_PLUGIN_DIR . '/wp-rest-cache.php';
		$wp_filesystem->copy( $source, $target );
	}
}
