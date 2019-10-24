<?php
/**
 * View for the body of the WP REST Cache Settings page.
 *
 * @link: http://www.acato.nl
 * @since 2018.1
 *
 * @package    WP_Rest_Cache_Plugin
 * @subpackage WP_Rest_Cache_Plugin/Admin/Partials
 */

$wp_rest_cache_sub = filter_input( INPUT_GET, 'sub', FILTER_SANITIZE_STRING );
?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder">
		<div class="meta-box-sortables ui-sortable">
			<form method="post">
				<input type="hidden" name="page" value="wp-rest-cache"/>
				<input type="hidden" name="sub" value="<?php echo esc_attr( $wp_rest_cache_sub ); ?>"/>
				<?php
				$wp_rest_cache_list->prepare_items();
				$wp_rest_cache_list->search_box( __( 'Search', 'wp-rest-cache' ), 'search_id' );
				$wp_rest_cache_list->display();
				?>
			</form>
		</div>
	</div>
</div>
<br class="clear">
