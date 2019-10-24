<?php
/**
 * Class responsible for caching and saving cache relations.
 *
 * @link: http://www.acato.nl
 * @since 2018.3
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Caching
 */

namespace WP_Rest_Cache_Plugin\Includes\Caching;

/**
 * Class responsible for caching and saving cache relations.
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Includes/Caching
 * @author:    Richard Korthuis - Acato <richardkorthuis@acato.nl>
 */
class Caching {

	/**
	 * The current version of the database tables, used to determine if they need updating after plugin update.
	 *
	 * @var string DB_VERSION The current version of the database tables.
	 */
	const DB_VERSION = '2019.4.0';

	/**
	 * The table name for the table where caches are stored together with their statistics.
	 *
	 * @var string TABLE_CACHES The table name for the table where caches are stored.
	 */
	const TABLE_CACHES = 'wrc_caches';

	/**
	 * The table name for the table where cache relations are stored.
	 *
	 * @var string TABLE_RELATIONS The table name for the table where cache relations are stored.
	 */
	const TABLE_RELATIONS = 'wrc_relations';

	/**
	 * Delete caches by an endpoint path matching only this exact same endpoint path (and query params).
	 *
	 * @var string FLUSH_STRICT
	 */
	const FLUSH_STRICT = 'strict';

	/**
	 * Delete caches by an endpoint path matching the exact same endpoint path with any query params that might have
	 * been used.
	 *
	 * @var string FLUSH_PARAMS
	 */
	const FLUSH_PARAMS = 'params';

	/**
	 * Delete caches by an endpoint path matching any cache that was called starting with the endpoint path (ignoring
	 * any query params or subpaths following the given path).
	 *
	 * @var string FLUSH_LOOSE
	 */
	const FLUSH_LOOSE = 'loose';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    \WP_Rest_Cache_Plugin\Includes\Caching\Caching $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * The complete table name for the table where caches are stored. A combination of the database prefix and the
	 * constant TABLE_CACHES.
	 *
	 * @access private
	 * @var    string $db_table_caches The complete table name for the table where caches are stored.
	 */
	private $db_table_caches;

	/**
	 * The complete table name for the table where cache relations are stored. A combination of the database prefix and
	 * the constant TABLE_RELATIONS.
	 *
	 * @access private
	 * @var    string $db_table_relations The complete table name for the table where cache relations are stored.
	 */
	private $db_table_relations;

	/**
	 * A boolean defining if the current cache is a single item cache or a multi-item endpoint cache.
	 *
	 * @access private
	 * @var    bool $is_single Whether the current cache is a single item cache.
	 */
	private $is_single;

	/**
	 * Class constructor.
	 *
	 * Set the database table variables.
	 */
	private function __construct() {
		global $wpdb;

		$this->db_table_caches    = $wpdb->prefix . self::TABLE_CACHES;
		$this->db_table_relations = $wpdb->prefix . self::TABLE_RELATIONS;
	}

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return \WP_Rest_Cache_Plugin\Includes\Caching\Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Caching();
		}

		return self::$instance;
	}

	/**
	 * Get a cached item from the transient cache and register a cache hit.
	 *
	 * @param string $cache_key The cache key for the requested cache.
	 *
	 * @return mixed The cache item.
	 */
	public function get_cache( $cache_key ) {
		$cache = get_transient( $this->transient_key( $cache_key ) );
		if ( $cache ) {
			$this->register_cache_hit( $cache_key );
		}

		return $cache;
	}

	/**
	 * Set the transient cache and register the cache + its relations.
	 *
	 * @param string $cache_key The cache key for the cache.
	 * @param mixed  $value The item to be cached.
	 * @param string $type The type of cache (endpoint|item).
	 * @param string $uri The requested uri for this cache if available.
	 * @param string $object_type The object type for this cache if available.
	 * @param array  $request_headers An array of cacheable request headers.
	 */
	public function set_cache( $cache_key, $value, $type, $uri = '', $object_type = '', $request_headers = [] ) {
		set_transient( $this->transient_key( $cache_key ), $value, $this->get_timeout() );
		switch ( $type ) {
			case 'endpoint':
				$this->register_endpoint_cache( $cache_key, $value, $uri, $request_headers );
				break;
			case 'item':
				$this->register_item_cache( $cache_key, $object_type, $value );
				break;
		}
	}

	/**
	 * Delete a cached item. Possibly also delete cache statistics.
	 *
	 * @param string $cache_key The cache key for the cache.
	 * @param bool   $force Whether to delete the cache statistics.
	 */
	public function delete_cache( $cache_key, $force = false ) {
		global $wpdb;
		delete_transient( $this->transient_key( $cache_key ) );

		$cache_id = $this->get_cache_row_id( $cache_key );

		if ( is_null( $cache_id ) ) {
			return;
		}

		$sql =
			"DELETE FROM `{$this->db_table_relations}` 
                WHERE `cache_id` = %d";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $wpdb->prepare( $sql, $cache_id ) );

		if ( $force ) {
			$sql =
				"DELETE FROM `{$this->db_table_caches}`
                    WHERE `cache_id` = %d";

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $wpdb->prepare( $sql, $cache_id ) );
		} else {
			$this->update_cache_expiration( $cache_id, date( 'Y-m-d H:i:s', 0 ) );
		}
	}

	/**
	 * Delete caches by endpoint path.
	 *
	 * Delete caches by an endpoint path either strict (\WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_STRICT)
	 * matching only this exact same endpoint path (and query params), params (\WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_PARAMS)
	 * matching the exact same endpoint path with any query params that might have been used, or loose
	 * (\WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_LOOSE) matching any cache that was called starting with
	 * the endpoint path (ignoring any query params or subpaths following the given path).
	 *
	 * @param string $endpoint The endpoint path to match.
	 * @param string $strictness The match type. (Can be either \WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_STRICT, \WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_PARAMS or \WP_Rest_Cache_Plugin\Includes\Caching\Caching::FLUSH_LOOSE).
	 * @param bool   $force Should the caches be deleted ($force = true) or just flushed ($force = false).
	 *
	 * @return bool|\WP_Error
	 */
	public function delete_cache_by_endpoint( $endpoint, $strictness = self::FLUSH_STRICT, $force = false ) {
		global $wpdb;

		$uri_parts    = wp_parse_url( $endpoint );
		$request_path = rtrim( $uri_parts['path'], '/' );

		if ( self::FLUSH_STRICT === $strictness && isset( $uri_parts['query'] ) && ! empty( $uri_parts['query'] ) ) {
			parse_str( $uri_parts['query'], $params );
			ksort( $params );
			$request_path .= '?' . http_build_query( $params );
		}

		$sql = "SELECT `cache_key`
        FROM `{$this->db_table_caches}`
        WHERE ";
		switch ( $strictness ) {
			case self::FLUSH_STRICT:
				$sql             .= ' `request_uri` = %s ';
				$prepare_params[] = $request_path;
				break;
			case self::FLUSH_PARAMS:
				$sql             .= ' `request_uri` LIKE %s ';
				$prepare_params[] = $request_path . '?%';
				break;
			case self::FLUSH_LOOSE:
				$sql             .= ' `request_uri` LIKE %s ';
				$prepare_params[] = $request_path . '%';
				break;
			default:
				return new \WP_Error( 'wp_rest_cache_invalid_strictness', __( 'Invalid strictness', 'wp-rest-cache' ) );
		}

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$caches = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_params ) );

		if ( $caches ) {
			foreach ( $caches as $cache ) {
				$this->delete_cache( $cache->cache_key, $force );
			}

			return true;
		}

		return false;
	}

	/**
	 * Clear all saved caches. Possibly also delete all statistics.
	 *
	 * @param bool $force Whether to delete statistics.
	 *
	 * @return bool True if there were caches to delete.
	 */
	public function clear_caches( $force = false ) {
		global $wpdb;

		$sql =
			"SELECT `cache_key`
            FROM `{$this->db_table_caches}`";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$caches = $wpdb->get_results( $sql );

		if ( $caches ) {
			foreach ( $caches as $cache ) {
				$this->delete_cache( $cache->cache_key, $force );
			}

			return true;
		}

		return false;
	}

	/**
	 * Fired upon WordPress 'save_post' hook. On post update delete all related caches, on post creation delete all
	 * non-single endpoint caches for this post type.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool     $update Whether this is an existing post being updated or not.
	 */
	public function save_post( $post_id, $post, $update ) {
		if ( $update ) {
			$this->delete_related_caches( $post_id, $post->post_type );
		} else {
			$this->delete_object_type_caches( $post->post_type );
		}
	}

	/**
	 * Fired upon WordPress 'delete_post' hook. Delete all related caches, including all single cache statistics.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_post( $post_id ) {
		$post = get_post( $post_id );
		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		$this->delete_related_caches( $post_id, $post->post_type, true );
	}

	/**
	 * Fired upon WordPress 'transition_post_status' hook. Delete all non-single endpoint caches for this post type if
	 * the new or the old status is 'publish'.
	 *
	 * @param string   $new_status The new status of the post.
	 * @param string   $old_status The old status of the post.
	 * @param \WP_Post $post The post which status has been transitioned.
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
			return;
		}

		$this->delete_object_type_caches( $post->post_type );
	}

	/**
	 * Fired upon WordPress 'created_term' hook. Delete all non-single endpoint caches for this taxonomy.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function created_term( $term_id, $tt_id, $taxonomy ) {
		$this->delete_object_type_caches( $taxonomy );
	}

	/**
	 * Fired upon WordPress 'edited_term' hook. Delete all related caches for this term.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function edited_term( $term_id, $tt_id, $taxonomy ) {
		$this->delete_related_caches( $term_id, $taxonomy );
	}

	/**
	 * Fired upon WordPress 'delete_term' hook. Delete all related caches for this term, including all single cache
	 * statistics.
	 *
	 * @param int    $term Term ID.
	 * @param int    $tt_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param mixed  $deleted_term Copy of the already-deleted term, in the form specified by the parent function.
	 *                               \WP_Error otherwise.
	 *                              \WP_Error otherwise.
	 * @param array  $object_ids List of term object IDs.
	 */
	public function delete_term( $term, $tt_id, $taxonomy, $deleted_term, $object_ids ) {
		$this->delete_related_caches( $term, $taxonomy, true );
	}

	/**
	 * Fired upon WordPress 'profile_update' hook. Delete all related caches for this user.
	 *
	 * @param int      $user_id User ID.
	 * @param \WP_User $old_user_data Object containing user's data prior to update.
	 */
	public function profile_update( $user_id, $old_user_data ) {
		$this->delete_related_caches( $user_id, 'user' );
	}

	/**
	 * Fired upon WordPress 'user_register' hook. Delete all non-single endpoint caches for users.
	 *
	 * @param int $user_id User ID.
	 */
	public function user_register( $user_id ) {
		$this->delete_object_type_caches( 'users' );
	}

	/**
	 * Fired upon WordPress 'deleted_user' hook. Delete all related caches for this user, including all single cache
	 * statistics.
	 *
	 * @param int $user_id User ID.
	 */
	public function deleted_user( $user_id ) {
		$this->delete_related_caches( $user_id, 'user', true );
	}

	/**
	 * Fired upon WordPress 'deleted_comment', 'trashed_comment' and 'spammed_comment' hooks. Delete all related caches
	 * for this comment, including all single cache statistics if comment is deleted.
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment The comment for which the hook was triggered.
	 */
	public function delete_comment_related_caches( $comment_id, $comment ) {
		switch ( current_filter() ) {
			case 'deleted_comment':
				$force_single_delete = true;
				break;
			default:
				$force_single_delete = false;
				break;
		}
		$this->delete_related_caches( $comment_id, 'comment', $force_single_delete );
	}

	/**
	 * Fired upon WordPress 'edit_comment', 'untrashed_comment', 'unspammed_comment', 'wp_insert_comment' and
	 * 'comment_post' hooks. Delete all non-single endpoint caches for comments.
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment The comment for which the hook was triggered.
	 */
	public function delete_comment_type_related_caches( $comment_id, $comment ) {
		$this->delete_object_type_caches( 'comment' );
	}

	/**
	 * Get all related caches for an object ID and object type.
	 *
	 * @param int    $id The ID of the object.
	 * @param string $object_type The type of the object.
	 *
	 * @return array|null|object An array of objects containing all related caches.
	 */
	private function get_related_caches( $id, $object_type ) {
		global $wpdb;

		$sql =
			"SELECT `c`.`cache_key`,
                    `c`.`is_single`
                FROM `{$this->db_table_caches}` AS `c`
                JOIN `{$this->db_table_relations}` AS `r`
                    ON `r`.`cache_id` = `c`.`cache_id`
                WHERE `r`.`object_id` = %d
                AND `r`.`object_type` = %s
                GROUP BY `c`.`cache_key`";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $id, $object_type ) );
	}

	/**
	 * Delete all related caches for an object ID and object type. Possibly also delete cache statistics for single
	 * endpoint caches.
	 *
	 * @param int    $id The ID of the object.
	 * @param string $object_type The type of the object.
	 * @param bool   $force_single_delete Whether to delete cache statistics for single endpoint caches.
	 */
	private function delete_related_caches( $id, $object_type, $force_single_delete = false ) {
		$caches = $this->get_related_caches( $id, $object_type );
		if ( $caches ) {
			foreach ( $caches as $cache ) {
				$this->delete_cache( $cache->cache_key, ( $force_single_delete && $cache->is_single ) );
			}
		}
	}

	/**
	 * Get all non-single caches for an object type.
	 *
	 * @param string $object_type The type of the object.
	 *
	 * @return array|null|object An array of objects containing all non-single object type caches.
	 */
	private function get_object_type_caches( $object_type ) {
		global $wpdb;

		$sql =
			"SELECT `cache_key`
                FROM `{$this->db_table_caches}`
                WHERE `cache_type` = %s 
                AND `object_type` = %s
                AND `is_single` = %d";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( $sql, 'endpoint', $object_type, false ) );

		return $results;
	}

	/**
	 * Delete all non-single caches for an object type.
	 *
	 * @param string $object_type The type of the object.
	 */
	public function delete_object_type_caches( $object_type ) {
		$caches = $this->get_object_type_caches( $object_type );
		if ( $caches ) {
			foreach ( $caches as $cache ) {
				$this->delete_cache( $cache->cache_key );
			}
		}
	}

	/**
	 * Get the cache row ID for a specific cache key.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return null|int The ID of the cache row.
	 */
	private function get_cache_row_id( $cache_key ) {
		global $wpdb;

		$sql =
			"SELECT `cache_id`
                FROM `{$this->db_table_caches}`
                WHERE `cache_key` = %s 
                LIMIT 1";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $wpdb->prepare( $sql, $cache_key ) );
	}

	/**
	 * Insert a new cache into the database.
	 *
	 * @param string $cache_key The cache key.
	 * @param string $cache_type The cache type (endpoint|item).
	 * @param string $uri The requested URI.
	 * @param string $object_type The object type for the cache.
	 * @param bool   $is_single Whether it is a single item cache.
	 * @param array  $request_headers An array of cacheable request headers.
	 *
	 * @return int The ID of the inserted row.
	 */
	private function insert_cache_row( $cache_key, $cache_type, $uri, $object_type, $is_single = true, $request_headers = [] ) {
		global $wpdb;

		$expiration = self::get_timeout();
		if ( ! self::get_memcache_used() ) {
			$expiration += current_time( 'timestamp' );
		}

		$wpdb->insert(
			$this->db_table_caches,
			[
				'cache_key'       => $cache_key,
				'cache_type'      => $cache_type,
				'request_uri'     => $uri,
				'request_headers' => wp_json_encode( $request_headers ),
				'object_type'     => $object_type,
				'cache_hits'      => 1,
				'is_single'       => $is_single,
				'expiration'      => date_i18n( 'Y-m-d H:i:s', $expiration ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s' ]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get the cache row data for a single cache.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return array The cache row data.
	 */
	private function get_cache_row( $cache_key ) {
		global $wpdb;

		$sql =
			"SELECT *
                FROM `{$this->db_table_caches}`
                WHERE `cache_key` = %s
                LIMIT 1";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $cache_key ), ARRAY_A );

		$result['is_active'] = ( get_transient( $this->transient_key( $result['cache_key'] ) ) !== false );
		if ( ! $result['is_active'] ) {
			if ( strtotime( $result['expiration'] ) === 0 ) {
				$result['expiration'] = __( 'Flushed', 'wp-rest-cache' );
			} else {
				$result['expiration'] = __( 'Expired', 'wp-rest-cache' );
			}
		}

		return $result;
	}

	/**
	 * Update the expiration date/time for a specific cache.
	 *
	 * @param int         $cache_id The ID of the cache row.
	 * @param null|string $expiration The specific expiration date/time. If none supplied it will be calculated.
	 */
	private function update_cache_expiration( $cache_id, $expiration = null ) {
		global $wpdb;

		if ( is_null( $expiration ) ) {
			$expiration = self::get_timeout();
			if ( ! self::get_memcache_used() ) {
				$expiration += current_time( 'timestamp' );
			}
			$expiration = date_i18n( 'Y-m-d H:i:s', $expiration );
		}

		$wpdb->update(
			$this->db_table_caches,
			[ 'expiration' => $expiration ],
			[ 'cache_id' => $cache_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Insert a cache relation into the database.
	 *
	 * @param int    $cache_id The ID of the cache row.
	 * @param int    $object_id The ID of the related object.
	 * @param string $object_type The object type of the relation.
	 */
	private function insert_cache_relation( $cache_id, $object_id, $object_type ) {
		global $wpdb;

		$wpdb->replace(
			$this->db_table_relations,
			[
				'cache_id'    => $cache_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			],
			[ '%d', '%d', '%s' ]
		);
	}

	/**
	 * Register a cache hit in the database.
	 *
	 * @param string $cache_key The cache key.
	 */
	private function register_cache_hit( $cache_key ) {
		global $wpdb;

		$sql =
			"UPDATE `{$this->db_table_caches}`
                SET `cache_hits` = `cache_hits` + 1
                WHERE `cache_key` = %s";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $wpdb->prepare( $sql, $cache_key ) );
	}

	/**
	 * Register an endpoint cache in the database.
	 *
	 * @param string $cache_key The cache key.
	 * @param mixed  $data The cached data.
	 * @param string $uri The requested URI.
	 * @param array  $request_headers An array of cacheable request headers.
	 */
	private function register_endpoint_cache( $cache_key, $data, $uri, $request_headers ) {
		$cache_id = $this->get_cache_row_id( $cache_key );

		/**
		 * Determine object type of cache.
		 *
		 * Allows external determination of object type of current cache.
		 *
		 * @since 2018.4.2
		 *
		 * @param string Object type
		 * @param string $cache_key Cache key
		 * @param mixed $data The data that is to be cached
		 * @param string $uri The requested URI
		 */
		$object_type = apply_filters( 'wp_rest_cache/determine_object_type', $this->determine_object_type( $data ), $cache_key, $data, $uri );

		if ( is_null( $cache_id ) ) {
			$cache_id = $this->insert_cache_row( $cache_key, 'endpoint', $uri, $object_type, $this->is_single, $request_headers );
		} else {
			$this->update_cache_expiration( $cache_id );
		}

		// Force data to be an array.
		$data['data'] = json_decode( wp_json_encode( $data['data'] ), true );

		$this->process_recursive_cache_relations( $cache_id, $data['data'] );

		/**
		 * Process cache relations.
		 *
		 * Allows external processing of cache relations.
		 *
		 * @since 2018.4.2
		 *
		 * @param int $cache_id The row id of the current cache.
		 * @param mixed $data The data that is to be cached.
		 */
		do_action( 'wp_rest_cache/process_cache_relations', $cache_id, $data );
	}

	/**
	 * Register an item cache in the database.
	 *
	 * @param string $cache_key The cache key.
	 * @param string $object_type The object type of the cached item.
	 * @param mixed  $data The cached data.
	 */
	private function register_item_cache( $cache_key, $object_type, $data ) {
		$cache_id = $this->get_cache_row_id( $cache_key );

		if ( is_null( $cache_id ) ) {
			$cache_id = $this->insert_cache_row( $cache_key, 'item', '', $object_type );
		} else {
			$this->update_cache_expiration( $cache_id );
		}

		// Force data to be an array.
		$data = json_decode( wp_json_encode( $data->data ), true );

		$this->process_recursive_cache_relations( $cache_id, $data );
	}

	/**
	 * Loop through the cached data to determine all cache relations recursively.
	 *
	 * @param int   $cache_id The ID of the cache row.
	 * @param array $record An array of data to be checked for relations.
	 */
	private function process_recursive_cache_relations( $cache_id, $record ) {
		if ( ! is_array( $record ) ) {
			return;
		}
		$record = array_change_key_case( $record, CASE_LOWER );
		if ( array_key_exists( 'id', $record ) && array_key_exists( 'post_type', $record ) ) {
			$this->insert_cache_relation( $cache_id, $record['id'], $record['post_type'] );
		} elseif ( array_key_exists( 'taxonomy', $record ) ) {
			$this->process_taxonomy_relations( $cache_id, $record );
		} elseif ( array_key_exists( 'id', $record )
			&& array_key_exists( 'type', $record )
			&& ( array_key_exists( 'slug', $record ) || array_key_exists( 'status', $record ) )
		) {
			$this->insert_cache_relation( $cache_id, $record['id'], $record['type'] );
		} elseif ( array_key_exists( 'id', $record )
			&& array_key_exists( 'slug', $record )
			&& array_key_exists( '_links', $record )
		) {
			if ( isset( $record['_links']['collection'][0]['href'] ) ) {
				if ( substr( $record['_links']['collection'][0]['href'], - 12 ) === '/wp/v2/users' ) {
					$this->insert_cache_relation( $cache_id, $record['id'], 'user' );
				}
			}
		}

		foreach ( $record as $field => $value ) {
			if ( is_array( $value ) ) {
				$this->process_recursive_cache_relations( $cache_id, $value );
			}
		}
	}

	/**
	 * Current record is a taxonomy, process its relations.
	 *
	 * @param int   $cache_id The ID of the cache row.
	 * @param array $record An array of data to be checked for relations.
	 */
	private function process_taxonomy_relations( $cache_id, $record ) {
		if ( array_key_exists( 'id', $record )
			&& array_key_exists( 'name', $record )
			&& array_key_exists( 'slug', $record )
		) {
			$this->insert_cache_relation( $cache_id, $record['id'], $record['taxonomy'] );
		} elseif ( array_key_exists( 'term_id', $record ) ) {
			$this->insert_cache_relation( $cache_id, $record['term_id'], $record['taxonomy'] );
		}
	}

	/**
	 * Determine the cache object type, based upon the cached data.
	 *
	 * @param array $data The cached data.
	 *
	 * @return bool|string The object type, or false if it could not be determined.
	 */
	private function determine_object_type( $data ) {
		// Force data to be an array.
		$data['data'] = json_decode( wp_json_encode( $data['data'] ), true );

		if ( array_key_exists( 'id', $data['data'] ) ) {
			$this->is_single = true;
			if ( array_key_exists( 'type', $data['data'] ) ) {
				return $data['data']['type'];
			} elseif ( array_key_exists( 'taxonomy', $data['data'] ) ) {
				return $data['data']['taxonomy'];
			}
		} else {
			$this->is_single = false;
			if ( count( $data['data'] ) && isset( $data['data'][0] ) ) {
				if ( array_key_exists( 'type', $data['data'][0] ) ) {
					return $data['data'][0]['type'];
				} elseif ( array_key_exists( 'taxonomy', $data['data'][0] ) ) {
					return $data['data'][0]['taxonomy'];
				}
			}
		}

		return 'unknown';
	}

	/**
	 * Get an array of cache data for a specific API type.
	 *
	 * @param string $api_type The type of the API for which the data is retrieved (endpoint|item).
	 * @param int    $per_page Number of items to return per page.
	 * @param int    $page_number The requested page.
	 *
	 * @return array An array containing the requested cache data.
	 */
	public function get_api_data( $api_type, $per_page, $page_number ) {
		global $wpdb;

		$page         = $page_number - 1;
		$prepare_args = [];

		$where = $this->get_where_clause( $api_type, $prepare_args );

		$order = $this->get_orderby_clause();

		$prepare_args[] = ( $page * $per_page );
		$prepare_args[] = ( ( $page + 1 ) * $per_page );

		$sql =
			"SELECT * 
            FROM `{$this->db_table_caches}`
            WHERE {$where}
            ORDER BY {$order}
            LIMIT %d, %d";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_args ), ARRAY_A );
		foreach ( $results as &$result ) {
			$result['is_active'] = ( get_transient( $this->transient_key( $result['cache_key'] ) ) !== false );
			if ( ! $result['is_active'] ) {
				if ( 0 === strtotime( $result['expiration'] ) ) {
					$result['expiration'] = __( 'Flushed', 'wp-rest-cache' );
				} else {
					$result['expiration'] = __( 'Expired', 'wp-rest-cache' );
				}
			}
		}

		return $results;
	}

	/**
	 * Get the number of records for the requested API type.
	 *
	 * @param string $api_type The type of the API for which the data is retrieved (endpoint|item).
	 *
	 * @return int The number of records.
	 */
	public function get_record_count( $api_type ) {
		global $wpdb;

		$prepare_args = [];
		$where        = $this->get_where_clause( $api_type, $prepare_args );

		$sql =
			"SELECT COUNT(*)
            FROM `{$this->db_table_caches}`
            WHERE {$where}";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $prepare_args ) );
	}

	/**
	 * Build the where clause for the query that retrieves the cache data for a specific API type.
	 *
	 * @param string $api_type The type of the API for which the data is retrieved (endpoint|item).
	 * @param array  $prepare_args A reference to an array containing the arguments for the prepare statement.
	 *
	 * @return string The where clause.
	 */
	private function get_where_clause( $api_type, &$prepare_args ) {
		$where          = '`cache_type` = %s';
		$prepare_args[] = $api_type;
		$search         = filter_input( INPUT_POST, 's', FILTER_SANITIZE_STRING );

		if ( ! empty( $search ) ) {
			$where         .= ' AND ( `request_uri` LIKE %s OR `object_type` LIKE %s )';
			$prepare_args[] = '%' . $search . '%';
			$prepare_args[] = '%' . $search . '%';
		}

		return $where;
	}

	/**
	 * Build the order by clause for the query.
	 *
	 * @return string The order by clause.
	 */
	private function get_orderby_clause() {
		$order   = '`cache_id` DESC';
		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );

		if ( in_array(
			$orderby,
			[
				'request_uri',
				'object_type',
				'cache_hits',
				'cache_key',
				'expiration',
			],
			true
		)
		) {
			$order = '`' . $orderby . '` ' . ( filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) === 'desc' ? 'DESC' : 'ASC' );
		}

		return $order;
	}

	/**
	 * Get the cache data for a specific cache. Used for the details-page in the backend, so no cache hit is triggered.
	 *
	 * @param string $cache_key The cache key.
	 *
	 * @return array|null An array of cache data, or null if the cache row could not be found.
	 */
	public function get_cache_data( $cache_key ) {
		$cache        = [];
		$cache['row'] = $this->get_cache_row( $cache_key );
		if ( ! $cache['row'] ) {
			return null;
		}

		$cache['data'] = get_transient( $this->transient_key( $cache_key ) );

		// Force data to be an array.
		$cache['data'] = json_decode( wp_json_encode( $cache['data'] ), true );

		return $cache;
	}

	/**
	 * Get the cache timeout as set in the plugin Settings.
	 *
	 * @param boolean $calculated If the returned value should be calculated using the interval.
	 *
	 * @return int Timeout (in seconds if calculated).
	 */
	public function get_timeout( $calculated = true ) {
		$timeout = get_option( 'wp_rest_cache_timeout', 1 );
		if ( $calculated ) {
			$timeout_interval = $this->get_timeout_interval();
			$timeout          = $timeout * $timeout_interval;
			if ( $this->get_memcache_used() ) {
				$timeout += time();
			}
		}

		return $timeout;
	}

	/**
	 * Get the cache timeout interval, default is 1 year.
	 *
	 * @return int Timeout interval in seconds.
	 */
	public function get_timeout_interval() {
		return get_option( 'wp_rest_cache_timeout_interval', YEAR_IN_SECONDS );
	}

	/**
	 * Should a cron be activated to regenerate the expired caches?
	 *
	 * @return bool Whether or not a cron should be activated.
	 */
	public function should_regenerate() {
		return get_option( 'wp_rest_cache_regenerate', false ) === '1';
	}

	/**
	 * Get the regeneration interval, default is twice daily.
	 *
	 * @return string The interval key.
	 */
	public function get_regenerate_interval() {
		return get_option( 'wp_rest_cache_regenerate_interval', 'twicedaily' );
	}

	/**
	 * Get the maximum number of regenerated caches per cronjob, default is 10.
	 *
	 * @return int Maximum nuber of caches.
	 */
	public function get_regenerate_number() {
		return get_option( 'wp_rest_cache_regenerate_number', 10 );
	}

	/**
	 * Cronjob to automatically regenerate expired caches.
	 */
	public function regenerate_expired_caches() {
		global $wpdb;

		$regenerate_number = (int) $this->get_regenerate_number();

		$sql =
			"SELECT * 
            FROM `{$this->db_table_caches}`
            WHERE `cache_type` = 'endpoint'
            ORDER BY `cache_hits` DESC";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( $results as &$result ) {
			if ( false === get_transient( $this->transient_key( $result['cache_key'] ) ) ) {
				// Regenerate.
				$url    = get_home_url() . $result['request_uri'];
				$return = wp_remote_get(
					$url,
					[
						'timeout'   => 10,
						'sslverify' => false,
						'headers'   => json_decode( $result['request_headers'], true ),
					]
				);

				$regenerate_number --;
				if ( $regenerate_number <= 0 ) {
					break;
				}
			}
		}
	}

	/**
	 * Get whether Memcache(d) is being used as external object cache.
	 *
	 * @return bool Whether Memcache(d) is being used.
	 */
	public function get_memcache_used() {
		return '1' === get_option( 'wp_rest_cache_memcache_used', false );
	}

	/**
	 * Get a list of global cacheable request headers, the list is comma separated.
	 *
	 * @return string The list of global cacheable request headers.
	 */
	public function get_global_cacheable_request_headers() {
		return get_option( 'wp_rest_cache_global_cacheable_request_headers', '' );
	}

	/**
	 * Get the cache key for the current ID.
	 *
	 * @param string|int $id The ID used for the cache key.
	 *
	 * @return string The cache key.
	 */
	public function transient_key( $id ) {
		return 'wp_rest_cache_' . $id;
	}

	/**
	 * Update the database structure needed for saving caches and their relations and statistics.
	 */
	public function update_database_structure() {

		$version = get_option( 'wp_rest_cache_database_version' );

		if ( self::DB_VERSION !== $version ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql_caches =
				"CREATE TABLE `{$this->db_table_caches}` (
                `cache_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `cache_key` VARCHAR(181) NOT NULL,
                `cache_type` VARCHAR(10) NOT NULL,
                `request_uri` LONGTEXT NOT NULL,
                `request_headers` LONGTEXT NOT NULL,
                `object_type` VARCHAR(200) NOT NULL,
                `cache_hits` BIGINT(20) NOT NULL,
                `is_single` TINYINT(1) NOT NULL,
                `expiration` DATETIME NOT NULL,
                PRIMARY KEY (`cache_id`),
                UNIQUE INDEX `cache_key` (`cache_key`),
                INDEX `cache_type` (`cache_type`),
                INDEX `non_single_caches` (`cache_type`, `object_type`, `is_single`)
                )";

			dbDelta( $sql_caches );

			$sql_relations =
				"CREATE TABLE `{$this->db_table_relations}` (
	            `cache_id` BIGINT(20) NOT NULL,
	            `object_id` BIGINT(20) NOT NULL,
	            `object_type` VARCHAR(200) NOT NULL,
	            PRIMARY KEY (`cache_id`, `object_id`),
	            INDEX `cache_id` (`cache_id`),
	            INDEX `object` (`object_id`, `object_type`)
                )";

			dbDelta( $sql_relations );

			update_option( 'wp_rest_cache_database_version', self::DB_VERSION );
		}

		if ( version_compare( '2019.4.0', $version, '>' ) ) {
			$this->upgrade_2019_4_0();
		}
	}

	/**
	 * Delete deprecated item api caches.
	 */
	private function upgrade_2019_4_0() {
		$nr_of_item_caches = $this->get_record_count( 'item' );
		for ( $count = 0; $count * 100 < $nr_of_item_caches; $count ++ ) {
			$item_caches = $this->get_api_data( 'item', 100, $count + 1 );
			foreach ( $item_caches as $item_cache ) {
				$this->delete_cache( $item_cache['cache_key'], true );
			}
		}
	}
}
