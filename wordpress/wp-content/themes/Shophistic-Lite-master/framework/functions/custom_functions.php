<?php
/**
 * Add CSS class to the body
 */
if ( ! function_exists( 'shophistic_lite_add_body_class' ) ){
	function shophistic_lite_add_body_class( $classes ) {
		// add 'class-name' to the $classes array
		$classes[] = SHOPHISTIC_SLUG .' ver' . SHOPHISTIC_VERSION;
		// return the $classes array
		return $classes;
	}
}// end function_exists
add_filter( 'body_class', 'shophistic_lite_add_body_class' );

/**
 * Add read more button
 */
if ( ! function_exists( 'shophistic_lite_new_content_more' ) ){
	function shophistic_lite_new_content_more($more) {
	       global $post;
	       return ' <br><a href="' . esc_url( get_permalink() ) . '" class="more-link btn btn-ql">'.__('Read more', 'shophistic-lite').'</a>';
	}   
}// end function_exists
	add_filter( 'the_content_more_link', 'shophistic_lite_new_content_more' );

//In case WP is older than 4.1
if ( ! function_exists( '_wp_render_title_tag' ) ) :
	function shophistic_lite_render_title() {
	?>
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php
	}
	add_action( 'wp_head', 'shophistic_lite_render_title' );
endif;



/**
 * Check if the Sidebar is active and retrive the correct content class
 */
if ( ! function_exists( 'shophistic_lite_content_check_sidebar' ) ){
	function shophistic_lite_content_check_sidebar() {
		if ( is_active_sidebar( 'sidebar-widgets' ) ) { 
            return "col-md-10 col-md-push-2";
        }else{
            return "col-md-12";
        }
    }
}// end function_exists
?>