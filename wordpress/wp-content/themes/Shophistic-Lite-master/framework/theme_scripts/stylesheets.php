<?php
	
	//=============================================================
	//Theme Stylesheets
	//=============================================================
if ( ! function_exists( 'shophistic_lite_enqueue_stylesheets' ) ){
	function shophistic_lite_enqueue_stylesheets() {
		
		//Bootstrap =======================================================
		wp_register_style('bootstrap', SHOPHISTIC_CSS . '/bootstrap.css', array(), '3.1', 'all');  
		wp_enqueue_style('bootstrap');  
		//=================================================================

		//Owl ======================================================
		wp_register_style('owl', SHOPHISTIC_CSS . '/owl.carousel.css', array(), '2.0.0', 'all');  
		wp_enqueue_style('owl');  
		//=================================================================


		//Photoswipe ======================================================
		wp_register_style('photoswipe', SHOPHISTIC_CSS . '/photoswipe.css', array(), '2.0.0', 'all');  
		wp_enqueue_style('photoswipe');  
		//=================================================================

		//Photoswipe Skin ======================================================
		wp_register_style('photoswipe-skin', SHOPHISTIC_CSS . '/default-skin/default-skin.css', array(), '2.0.0', 'all');  
		wp_enqueue_style('photoswipe-skin');  
		//=================================================================




		//Main Stylesheet =================================================
		wp_register_style('main-stylesheet', get_bloginfo('stylesheet_url'), array('bootstrap'), '1.0', 'all');  
		wp_enqueue_style('main-stylesheet');  
		//=================================================================


	}
}
	add_action('wp_enqueue_scripts', 'shophistic_lite_enqueue_stylesheets');

?>