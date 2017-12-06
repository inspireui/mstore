<?php
/**
 * Front End Customizer
 *
 * WordPress 3.4 Required
 */
 
add_action( 'customize_register', 'shophistic_lite_quemalabs_options_register' );

function shophistic_lite_quemalabs_options_register($wp_customize) {

	class shophistic_lite_Pro_Version extends WP_Customize_Control
	{
		public function render_content()
		{
			$args = array(
			    'a' => array(
			        'href' => array(),
			        'title' => array()
			    ),
			    'br' => array(),
			    'em' => array(),
			    'strong' => array(),
			);
			echo wp_kses( $this->label, $args );
		}
	}


	$wp_customize->get_setting('blogname')->transport='postMessage';
	$wp_customize->get_setting('blogdescription')->transport='postMessage';
	$wp_customize->get_setting('header_textcolor')->transport='postMessage';	



	/*
    Logo
    ===================================================== */
    $section_args = array(
    	'wp_customize' => $wp_customize,
    	'title' => esc_html__( 'Logo', 'shophistic-lite' ),
    	'label' => sprintf( __( 'Check out the <a href="%s" target="_blank">PRO version</a> to change logo.', 'shophistic-lite' ), esc_url( 'http://www.quemalabs.com/theme/shophistic-lite/?utm_source=Shophistic%20Lite%20Theme&utm_medium=Pro%20Button&utm_campaign=Shophistic' ) ),
    	'priority' => 100
    );
    shophistic_lite_pro_btns( $section_args );
    /*
    Home Page
    ===================================================== */
    $section_args = array(
    	'wp_customize' => $wp_customize,
    	'title' => esc_html__( 'Home Page', 'shophistic-lite' ),
    	'label' => sprintf( __( 'Check out the <a href="%s" target="_blank">PRO version</a> to create an awesome home page.', 'shophistic-lite' ), esc_url( 'http://www.quemalabs.com/theme/shophistic-lite/?utm_source=Shophistic%20Lite%20Theme&utm_medium=Pro%20Button&utm_campaign=Shophistic' ) ),
    	'priority' => 100
    );
    shophistic_lite_pro_btns( $section_args );
    /*
    Sidebar side
    ===================================================== */
    $section_args = array(
    	'wp_customize' => $wp_customize,
    	'title' => esc_html__( 'Sidebar Side', 'shophistic-lite' ),
    	'label' => sprintf( __( 'Check out the <a href="%s" target="_blank">PRO version</a> to choose the side of the sidebar.', 'shophistic-lite' ), esc_url( 'http://www.quemalabs.com/theme/shophistic-lite/?utm_source=Shophistic%20Lite%20Theme&utm_medium=Pro%20Button&utm_campaign=Shophistic' ) ),
    	'priority' => 100
    );
    shophistic_lite_pro_btns( $section_args );
    /*
    Mega Menus
    ===================================================== */
    $section_args = array(
    	'wp_customize' => $wp_customize,
    	'title' => esc_html__( 'Mega Menus', 'shophistic-lite' ),
    	'label' => sprintf( __( 'Check out the <a href="%s" target="_blank">PRO version</a> to create mega menus.', 'shophistic-lite' ), esc_url( 'http://www.quemalabs.com/theme/shophistic-lite/?utm_source=Shophistic%20Lite%20Theme&utm_medium=Pro%20Button&utm_campaign=Shophistic' ) ),
    	'priority' => 100
    );
    shophistic_lite_pro_btns( $section_args );
    /*
    Footer Widgets
    ===================================================== */
    $section_args = array(
    	'wp_customize' => $wp_customize,
    	'title' => esc_html__( 'Footer Widgets', 'shophistic-lite' ),
    	'label' => sprintf( __( 'Check out the <a href="%s" target="_blank">PRO version</a> to add widgets in the footer of your site.', 'shophistic-lite' ), esc_url( 'http://www.quemalabs.com/theme/shophistic-lite/?utm_source=Shophistic%20Lite%20Theme&utm_medium=Pro%20Button&utm_campaign=Shophistic' ) ),
    	'priority' => 100
    );
    shophistic_lite_pro_btns( $section_args );





}






/*
Enqueue Script for top buttons
*/
if ( ! function_exists( 'shophistic_lite_customizer_controls' ) ){
	function shophistic_lite_customizer_controls(){

		wp_register_script( 'shophistic_lite_customizer_top_buttons', SHOPHISTIC_JS . '/theme-customizer-top-buttons.js', array( 'jquery' ), true  );
		wp_enqueue_script( 'shophistic_lite_customizer_top_buttons' );

		wp_localize_script( 'shophistic_lite_customizer_top_buttons', 'topbtns', array(
			'pro' => esc_html__( 'View PRO version', 'shophistic-lite' ),
            'documentation' => esc_html__( 'Documentation', 'shophistic-lite' ),
            'reviews' => esc_html__( 'Leave a review (it help us)', 'shophistic-lite' )
		) );
	}
}//end if function_exists
add_action( 'customize_controls_enqueue_scripts', 'shophistic_lite_customizer_controls' );


/*
*Sanitize for Sidebar Side
*/
function shophistic_lite_sanitize_sidebar_side( $value ) {
	$sidebar_side = array(
			'right' => __('Right', 'shophistic-lite'),
			'left' => __('Left', 'shophistic-lite')
		);
	    if ( ! array_key_exists( $value, $sidebar_side  ) )
	        $value = 'left';
	
    return $value;
}

/*
*Sanitize Fonts
*/
function shophistic_lite_sanitize_fonts( $value ) {
    return $value;
}





/*
Create the "PRO version" buttons
*/
if ( ! function_exists( 'shophistic_lite_pro_btns' ) ){
	function shophistic_lite_pro_btns( $args ){

		$wp_customize = $args['wp_customize'];
		$title = $args['title'];
		$label = $args['label'];
		if ( isset( $args['priority'] ) || array_key_exists( 'priority', $args ) ) {
			$priority = $args['priority'];
		}else{
			$priority = 120;
		}

		$section_id = sanitize_title( $title );

		$wp_customize->add_section( $section_id , array(
			'title'       => $title,
			'priority'    => $priority
		) );
		$wp_customize->add_setting( $section_id, array(
			'sanitize_callback' => 'shophistic_lite_pro_version'
		) );
		$wp_customize->add_control( new shophistic_lite_Pro_Version( $wp_customize, $section_id, array(
	        'section' => $section_id,
	        'label' => $label
		   )
		) );
	}
}//end if function_exists

?>