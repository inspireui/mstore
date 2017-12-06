<?php
	
	//=============================================================
	//Theme Scripts
	//=============================================================
	
	//Register JS Scripts for later use
if ( ! function_exists( 'shophistic_lite_enqueue_scripts' ) ){
	function shophistic_lite_enqueue_scripts() {
		
		//hoverIntent Plugin ==============================================
		wp_register_script('hoverIntent', SHOPHISTIC_JS . '/jquery.hoverIntent.js', array('jquery'), '6.0', true );
		wp_enqueue_script('hoverIntent');
		//=================================================================

		//photoSwipe and UI Plugin ==============================================
		wp_register_script('photoswipe-and-ui', SHOPHISTIC_JS . '/photoswipe-ui-default.js', array('jquery'), '4.0.8', true );
		wp_enqueue_script('photoswipe-and-ui');
		//=================================================================

		//Modernizr Plugin ==============================================
		wp_register_script('modernizr', SHOPHISTIC_JS . '/modernizr.custom.67069.js', '2.8.3', true );
		wp_enqueue_script('modernizr');
		//=================================================================
		
		//Owl Carousel ========================================================
		wp_register_script('owl', SHOPHISTIC_JS . '/owl.carousel.js', array('jquery'), '2.0.0', true );
		wp_enqueue_script('owl');
		//=================================================================
		
		//Pace  =============================================
		wp_register_script('pace', SHOPHISTIC_JS . '/pace.js', array(), '0.2.0', true);
		wp_enqueue_script('pace');
		//=================================================================
		
		//Bootstrap JS ========================================
		wp_register_script('bootstrap', SHOPHISTIC_JS . '/bootstrap.js', array(), '2.1.0', true);
		wp_enqueue_script('bootstrap');
		//=================================================================
		
		//Comment Reply ===================================================
		if ( is_singular() ) wp_enqueue_script( 'comment-reply' );
		//=================================================================




		
		//Customs Scripts =================================================
		wp_register_script('theme-custom', SHOPHISTIC_JS . '/script.js', array('jquery', 'bootstrap'), '1.0', true );
		wp_enqueue_script('theme-custom');
		//=================================================================
	}
}//end if function_exists
	add_action('wp_enqueue_scripts', 'shophistic_lite_enqueue_scripts');






/*
Enqueue Script for Live previw in the Theme Customizer

*/
if ( ! function_exists( 'shophistic_lite_customizer_live_preview' ) ){
	function shophistic_lite_customizer_live_preview()
	{
		wp_enqueue_script( 'ql-themecustomizer',			//Give the script an ID
			  SHOPHISTIC_JS.'/theme-customizer.js',//Point to file
			  array( 'jquery','customize-preview' ),	//Define dependencies
			  '',						//Define a version (optional) 
			  true						//Put script in footer?
		);
	}
}//end if function_exists
add_action( 'customize_preview_init', 'shophistic_lite_customizer_live_preview' );
?>