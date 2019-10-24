<?php
/**
 * API for endpoint caching.
 *
 * @link: http://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 */

namespace WP_Rest_Cache_Plugin\Includes\API;

/**
 * API for endpoint caching.
 *
 * Caches complete endpoints and handles the deletion if single items are updated.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/API
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Endpoint_Api {


	/**
	 * The requested URI.
	 *
	 * @access private
	 * @var    string $request_uri The requested URI string.
	 */
	private $request_uri;

	/**
	 * The current cache key.
	 *
	 * @access private
	 * @var    string $cache_key The current cache key.
	 */
	private $cache_key;

	/**
	 * The response headers that need to be send with the cached call.
	 *
	 * @access private
	 * @var    array $response_headers The response headers.
	 */
	private $response_headers = array(
		'Content-Type'                  => 'application/json; charset=UTF-8',
		'X-WP-Cached-Call'              => 'served-cache',
		'X-Robots-Tag'                  => 'noindex',
		'X-Content-Type-Options'        => 'nosniff',
		'Access-Control-Expose-Headers' => 'X-WP-Total, X-WP-TotalPages',
		'Access-Control-Allow-Headers'  => 'Authorization, Content-Type',
	);

	/**
	 * The request headers that need to be used to distinguish separate caches.
	 *
	 * @access private
	 * @var    array $request_headers The request headers.
	 */
	private $request_headers = array();

	/**
	 * The default WordPress REST endpoints, that can be cached.
	 *
	 * @access private
	 * @var    array $wordpress_endpoints An array of default WordPress endpoints.
	 */
	private $wordpress_endpoints = array(
		'wp/v2' => array(
			'statuses',
			'taxonomies',
			'types',
			'users',
			'comments',
		),
	);

	/**
	 * Get the requested URI.
	 *
	 * @return string The request URI.
	 */
	private function build_request_uri() {
		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		$request_uri = filter_var( $_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL );
		// Remove home_url from request_uri for uri's with WordPress in a subdir (like /wp).
		$request_uri  = str_replace( get_home_url(), '', $request_uri );
		$uri_parts    = wp_parse_url( $request_uri );
		$request_path = rtrim( $uri_parts['path'], '/' );

		if ( isset( $uri_parts['query'] ) && ! empty( $uri_parts['query'] ) ) {
			parse_str( $uri_parts['query'], $params );
			ksort( $params );
			$request_path .= '?' . http_build_query( $params );
		}

		$this->request_uri = $request_path;

		return $request_path;
	}

	/**
	 * Create an array of cacheable request headers based upon settings and hooks.
	 */
	private function set_cacheable_request_headers() {
		$request = new \WP_REST_Request();
		$server  = new \WP_REST_Server();
		$request->set_headers( $server->get_headers( wp_unslash( $_SERVER ) ) );

		$cacheable_headers = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_global_cacheable_request_headers();
		$cacheable_headers = explode( ',', $cacheable_headers );
		if ( count( $cacheable_headers ) ) {
			foreach ( $cacheable_headers as $header ) {
				if ( strlen( $header ) ) {
					$this->request_headers[ $header ] = $request->get_header( $header );
				}
			}
		}

		$rest_prefix               = sprintf( '/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ) );
		$cacheable_request_headers = get_option( 'wp_rest_cache_cacheable_request_headers', [] );
		if ( count( $cacheable_request_headers ) ) {
			foreach ( $cacheable_request_headers as $endpoint => $cacheable_headers ) {
				if ( false === strpos( $this->request_uri, $rest_prefix . $endpoint ) ) {
					continue;
				}

				$cacheable_headers = explode( ',', $cacheable_headers );
				if ( count( $cacheable_headers ) ) {
					foreach ( $cacheable_headers as $header ) {
						if ( strlen( $header ) ) {
							$this->request_headers[ $header ] = $request->get_header( $header );
						}
					}
				}
			}
		}

		ksort( $this->request_headers );
	}

	/**
	 * Build the cache key. A hashed combination of request uri and cacheable request headers.
	 */
	private function build_cache_key() {
		$this->build_request_uri();
		$this->set_cacheable_request_headers();

		$this->cache_key = md5( $this->request_uri . wp_json_encode( $this->request_headers ) );
	}

	/**
	 * Save the response headers so they can be added to the cache.
	 *
	 * @param bool              $served  Whether the request has already been served. Default false.
	 * @param \WP_HTTP_Response $result  Result to send to the client.
	 * @param \WP_REST_Request  $request Request used to generate the response.
	 * @param \WP_REST_Server   $server  Server instance.
	 */
	public function save_cache_headers( $served, \WP_HTTP_Response $result, \WP_REST_Request $request, \WP_REST_Server $server ) {
		$headers = $result->get_headers();

		/**
		 * Filter the cache headers.
		 *
		 * Allow to filter the cache headers before they are send with the cache response.
		 *
		 * @since 2019.1.5
		 *
		 * @param array $headers An array of all headers for this cache response.
		 * @param string $request_uri The requested URI.
		 */
		$headers = apply_filters( 'wp_rest_cache/cache_headers', $headers, $this->request_uri );
		if ( isset( $headers ) && ! empty( $headers ) ) {
			foreach ( $headers as $key => $value ) {
				/**
				 * Filter the cache header.
				 *
				 * Allow to change the cache header value.
				 *
				 * @since 2019.1.5
				 *
				 * @param string $value The value for the cache header.
				 * @param string $key The cache header field name.
				 * @param string $request_uri The requested URI.
				 */
				$value                          = apply_filters( 'wp_rest_cache/cache_header', $value, $key, $this->request_uri );
				$this->response_headers[ $key ] = $value;
			}
		}
	}

	/**
	 * Cache the response data.
	 *
	 * @param array            $result  Response data to send to the client.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 *
	 * @return array Response data to send to the client.
	 */
	public function save_cache( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
		// Only Avoid cache if not 200.
		if ( ! empty( $result ) && is_array( $result ) && isset( $result['data']['status'] ) && 200 !== (int) $result['data']['status'] ) {
			return $result;
		}

		// Do not cache if empty result set.
		if ( empty( $result ) ) {
			return $result;
		}

		$data = array(
			'data'    => $result,
			'headers' => $this->response_headers,
		);
		\WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->set_cache( $this->cache_key, $data, 'endpoint', $this->request_uri, '', $this->request_headers );

		return $result;
	}

	/**
	 * Check if caching should be skipped.
	 *
	 * @return bool True if no caching should be applied, false if caching can be applied.
	 */
	public function skip_caching() {
		// Only cache GET-requests.
		// No filter_input, see https://stackoverflow.com/questions/25232975/php-filter-inputinput-server-request-method-returns-null/36205923.
		if ( 'GET' !== filter_var( $_SERVER['REQUEST_METHOD'], FILTER_SANITIZE_STRING ) ) {
			return true;
		}

		// Parameter to skip caching.
		if ( true === filter_has_var( INPUT_GET, 'skip_cache' ) ) {
			return true;
		}

		// Make sure we only apply to allowed api calls.
		$rest_prefix = sprintf( '/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ) );
		if ( strpos( $this->request_uri, $rest_prefix ) === false ) {
			return true;
		}

		$allowed_endpoints = get_option( 'wp_rest_cache_allowed_endpoints', [] );

		$allowed_endpoint = false;
		foreach ( $allowed_endpoints as $namespace => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( strpos( $this->request_uri, $rest_prefix . $namespace . '/' . $endpoint ) !== false ) {
					$allowed_endpoint = true;
					break 2;
				}
			}
		}

		if ( ! $allowed_endpoint ) {
			return true;
		}

		// We dont skip.
		return false;
	}

	/**
	 * Check if the current call is a REST API call, if so check if it has already been cached, otherwise cache it.
	 */
	public function get_api_cache() {

		$this->build_cache_key();

		if ( $this->skip_caching() ) {
			return;
		}

		$cache = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->get_cache( $this->cache_key );

		if ( false !== $cache ) {
			// We want the data to be json.
			$data       = wp_json_encode( $cache['data'] );
			$last_error = json_last_error();

			if ( JSON_ERROR_NONE === $last_error ) {
				foreach ( $cache['headers'] as $key => $value ) {
					$header = sprintf( '%s: %s', $key, $value );
					header( $header );
				}
				$this->rest_send_cors_headers( '' );

				echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				exit;
			}
		}

		// Catch the headers after serving.
		add_filter( 'rest_pre_serve_request', [ $this, 'save_cache_headers' ], 9999, 4 );

		// Catch the result after serving.
		add_filter( 'rest_pre_echo_response', [ $this, 'save_cache' ], 1000, 3 );
	}

	/**
	 * Sends Cross-Origin Resource Sharing headers with API requests.
	 *
	 * @param mixed $value Response data.
	 * @return mixed Response data.
	 */
	private function rest_send_cors_headers( $value ) {
		$origin = get_http_origin();

		if ( $origin ) {
			// Requests from file:// and data: URLs send "Origin: null".
			if ( 'null' !== $origin ) {
				$origin = esc_url_raw( $origin );
			}
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Vary: Origin', false );
		} elseif ( ! headers_sent() && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			header( 'Vary: Origin' );
		}

		return $value;
	}

	/**
	 * Re-save the options if they have changed. We need them as options since we are going to use them early in the
	 * WordPress process even before several hooks are fired.
	 */
	public function save_options() {
		$original_allowed_endpoints = get_option( 'wp_rest_cache_allowed_endpoints', [] );
		$item_allowed_endpoints     = get_option( 'wp_rest_cache_item_allowed_endpoints', [] );

		/**
		 * Override cache-enabled endpoints.
		 *
		 * Allows to override the endpoints that will be cached by the WP REST Cache plugin.
		 *
		 * @since 2018.2.0
		 *
		 * @param array $original_allowed_endpoints An array of endpoints that are allowed to be cached.
		 */
		$allowed_endpoints = apply_filters( 'wp_rest_cache/allowed_endpoints', $item_allowed_endpoints );
		if ( $original_allowed_endpoints !== $allowed_endpoints ) {
			update_option( 'wp_rest_cache_allowed_endpoints', $allowed_endpoints );
		}

		$original_rest_prefix = get_option( 'wp_rest_cache_rest_prefix' );
		$rest_prefix          = rest_get_url_prefix();
		if ( $original_rest_prefix !== $rest_prefix ) {
			update_option( 'wp_rest_cache_rest_prefix', $rest_prefix );
		}

		$original_cacheable_request_headers = get_option( 'wp_rest_cache_cacheable_request_headers', [] );

		/**
		 * Filter cacheable request headers.
		 *
		 * Allow to set cacheable request headers per endpoint in the format [ '/wp/v2/posts' => 'HEADER_1,HEADER_2' ].
		 *
		 * @since 2019.4.0
		 *
		 * @param array $original_cacheable_request_headers An array of endpoints and
		 */
		$cacheable_request_headers = apply_filters( 'wp_rest_cache/cacheable_request_headers', $original_cacheable_request_headers );
		if ( $original_cacheable_request_headers !== $cacheable_request_headers ) {
			update_option( 'wp_rest_cache_cacheable_request_headers', $cacheable_request_headers );
		}
	}

	/**
	 * Add the default WordPress endpoints to the allowed endpoints for caching.
	 *
	 * @param array $allowed_endpoints The endpoints that are allowed to be cache.
	 *
	 * @return mixed An array of endpoints that are allowed to be cache.
	 */
	public function add_wordpress_endpoints( array $allowed_endpoints ) {
		foreach ( $this->wordpress_endpoints as $rest_base => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( ! isset( $allowed_endpoints[ $rest_base ] ) || ! in_array( $endpoint, $allowed_endpoints[ $rest_base ], true ) ) {
					$allowed_endpoints[ $rest_base ][] = $endpoint;
				}
			}
		}

		return $allowed_endpoints;
	}

	/**
	 * Determine the object type for caches of WordPress endpoints (if it has not yet been automatically determined).
	 *
	 * @param string $object_type The automatically determined object type ('unknown' if it couldn't be deterrmined).
	 * @param string $cache_key   The cache key.
	 * @param mixed  $data        The cached data.
	 * @param string $uri         The requested URI.
	 *
	 * @return string The determined object type.
	 */
	public function determine_object_type( $object_type, $cache_key, $data, $uri ) {
		if ( 'unknown' !== $object_type ) {
			return $object_type;
		}

		foreach ( $this->wordpress_endpoints as $rest_base => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( strpos( $uri, $rest_base . '/' . $endpoint ) !== false ) {
					return $endpoint;
				}
			}
		}

		return $object_type;
	}
}
