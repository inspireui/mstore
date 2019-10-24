<?php
/**
 * View for the Item API Caches tab.
 *
 * @link: http://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

?>
<div class="wrap">
	<?php
	$wp_rest_cache_list = new \WP_Rest_Cache_Plugin\Admin\Includes\API_Caches_Table( 'item' );
	require_once 'caches-table.php';
	?>
</div>
