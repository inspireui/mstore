<?php

// Load function
//	this functions check if the files exists in the Child Theme's folder first.
//------------------------------------------------------->
if ( ! function_exists( 'shophistic_lite_require_file' ) ) :
	function shophistic_lite_require_file($file, $parent_path, $child_path) {
		if (file_exists($child_path . $file)) {
		    require_once ($child_path . $file);
		} else {
		    require_once ($parent_path . $file);
		}	
	}    
endif;// if function_exists


/* Set the Full Width Image value */
if ( ! isset( $content_width ) ) $content_width = 1186;


/* Load the Theme class. */
require_once (get_template_directory() . '/framework/Theme.php');

//Theme Information
$shophistic_lite_theme_info = include(get_template_directory() . '/framework/info.php');

//Instance of the Theme
$shophistic_lite_Theme = new shophistic_lite_Theme($shophistic_lite_theme_info);


	
// Load jQuery------------------------------------------------------->
if ( ! function_exists( 'shophistic_lite_jquery_script' ) ) :
	function shophistic_lite_jquery_script() {
		wp_enqueue_script( 'jquery' );
	}    
endif;// if function_exists
add_action('wp_enqueue_scripts', 'shophistic_lite_jquery_script');

// Load jQuery-------------------------------------------------------<
	



//You can start adding your code below
//==================================================================






add_action('init', 'json_api_generate_token_init', 12);
/**
 * Register our enhanced token field to the login user api
 */
function json_api_generate_token_init()
{

    if (function_exists('register_rest_field')) {
        register_rest_field(
            'user',
            'generate_auth_cookie',
            array(
                'get_callback' => 'json_api_generate_token',
                'schema' => null,
            )
        );
    } elseif (function_exists('register_api_field')) {
        register_api_field(
            'user',
            'generate_auth_cookie',
            array(
                'get_callback' => 'json_api_generate_token',
                'schema' => null,
            )
        );
    }
}

function json_api_generate_token() {
    return 'test token';
}
?>