<div class="clearfix"></div>
<div class="pagination_wrap">
<?php
$temp_query = $wp_query;

if (isset($the_query)) {
	$wp_query = $the_query;
}

the_posts_pagination( array(
				'prev_text' => __( 'Previous page', 'shophistic-lite' ),
				'next_text' => __( 'Next page', 'shophistic-lite' )
			) );
$wp_query = $temp_query;
wp_reset_postdata();
?>
</div><!-- /pagination_wrap -->