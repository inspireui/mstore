<?php
if(!class_exists('shophistic_lite_Theme')){
/**
 * Theme Class
 */
class shophistic_lite_Theme {

	/**
	 * Here are loaded all the initial files, constant, etc.
	 */
	function __construct($theme_info){

		/* Define theme's constants. */
		$this->constants($theme_info);

		/* Add Theme support */
		$this->theme_support();

		/* Add Stylesheets for the Theme (CSS) */
		$this->stylesheets();

		/* Add JS Scripts for the Theme (JS) */
		$this->scripts();
		
		/* Enable Plugins Activations */
		$this->plugins_activation();
		
		/* Add Theme Functions */
		$this->theme_functions();
		
		/* Create all the widget areas */
		$this->widget_areas();

		/* Customizer */
		$this->customizer();

	}
	
	
	
	
	/**
	 * Defines the constant paths for use within the theme.
	 */
	private function constants($theme_info) {
		define('SHOPHISTIC_NAME', $theme_info['theme_name']);
		define('SHOPHISTIC_SLUG', $theme_info['theme_slug']);
		define('SHOPHISTIC_VERSION', $theme_info['theme_version']);
		define('SHOPHISTIC_AUTHOR', $theme_info['theme_author']);
		define('SHOPHISTIC_AUTHOR_URI', $theme_info['theme_author_uri']);

		define('SHOPHISTIC_DIR', get_template_directory());
		define('SHOPHISTIC_URI', get_template_directory_uri());

		
		define('SHOPHISTIC_CSS', SHOPHISTIC_URI . '/css');
		define('SHOPHISTIC_JS', SHOPHISTIC_URI . '/js');
		define('SHOPHISTIC_IMAGES', SHOPHISTIC_URI . '/images');


		define('SHOPHISTIC_FRAMEWORK', SHOPHISTIC_DIR . '/framework');
		define('SHOPHISTIC_FRAMEWORK_URI', SHOPHISTIC_URI . '/framework');

		define('SHOPHISTIC_ADMIN', SHOPHISTIC_FRAMEWORK_URI . '/admin');
		define('SHOPHISTIC_FUNCTIONS', SHOPHISTIC_FRAMEWORK . '/functions');
		define('SHOPHISTIC_SCRIPTS', SHOPHISTIC_FRAMEWORK . '/theme_scripts');
		define('SHOPHISTIC_POST_TYPES', SHOPHISTIC_FRAMEWORK . '/post_types');
		define('SHOPHISTIC_META_BOXES', SHOPHISTIC_FRAMEWORK . '/meta_boxes');
		define('SHOPHISTIC_META_BOXES_URI', SHOPHISTIC_FRAMEWORK_URI . '/meta_boxes');
		define('SHOPHISTIC_WIDGET_AREAS', SHOPHISTIC_FRAMEWORK . '/widget_areas');
		define('SHOPHISTIC_WIDGETS', SHOPHISTIC_FRAMEWORK . '/widgets');
		define('SHOPHISTIC_SHORTCODES', SHOPHISTIC_FRAMEWORK . '/shortcodes');
		define('SHOPHISTIC_FULLSCREEN', SHOPHISTIC_FRAMEWORK . '/fullscreen');
		define('SHOPHISTIC_PLUGINS', SHOPHISTIC_FRAMEWORK . '/plugins');
		define('SHOPHISTIC_PLUGINS_URI', SHOPHISTIC_FRAMEWORK_URI . '/plugins');

		
		define('SHOPHISTIC_LENGUAGES', SHOPHISTIC_DIR . '/languages');



		//Constant for Child Themes
		define('CHILD_SHOPHISTIC_DIR', get_stylesheet_directory());
		define('CHILD_SHOPHISTIC_URI', get_stylesheet_directory_uri());
		
		define('CHILD_SHOPHISTIC_CSS', CHILD_SHOPHISTIC_URI . '/css');
		define('CHILD_SHOPHISTIC_JS', CHILD_SHOPHISTIC_URI . '/js');
		define('CHILD_SHOPHISTIC_IMAGES', CHILD_SHOPHISTIC_URI . '/images');

		define('CHILD_SHOPHISTIC_FRAMEWORK', CHILD_SHOPHISTIC_DIR . '/framework');
		define('CHILD_SHOPHISTIC_FRAMEWORK_URI', CHILD_SHOPHISTIC_URI . '/framework');

		define('CHILD_SHOPHISTIC_ADMIN', CHILD_SHOPHISTIC_FRAMEWORK_URI . '/admin');
		define('CHILD_SHOPHISTIC_FUNCTIONS', CHILD_SHOPHISTIC_FRAMEWORK . '/functions');
		define('CHILD_SHOPHISTIC_SCRIPTS', CHILD_SHOPHISTIC_FRAMEWORK . '/theme_scripts');
		define('CHILD_SHOPHISTIC_POST_TYPES', CHILD_SHOPHISTIC_FRAMEWORK . '/post_types');
		define('CHILD_SHOPHISTIC_META_BOXES', CHILD_SHOPHISTIC_FRAMEWORK . '/meta_boxes');
		define('CHILD_SHOPHISTIC_META_BOXES_URI', CHILD_SHOPHISTIC_FRAMEWORK_URI . '/meta_boxes');
		define('CHILD_SHOPHISTIC_WIDGET_AREAS', CHILD_SHOPHISTIC_FRAMEWORK . '/widget_areas');
		define('CHILD_SHOPHISTIC_WIDGETS', CHILD_SHOPHISTIC_FRAMEWORK . '/widgets');
		define('CHILD_SHOPHISTIC_SHORTCODES', CHILD_SHOPHISTIC_FRAMEWORK . '/shortcodes');
		define('CHILD_SHOPHISTIC_FULLSCREEN', CHILD_SHOPHISTIC_FRAMEWORK . '/fullscreen');
		define('CHILD_SHOPHISTIC_PLUGINS', CHILD_SHOPHISTIC_FRAMEWORK . '/plugins');
		define('CHILD_SHOPHISTIC_PLUGINS_URI', CHILD_SHOPHISTIC_FRAMEWORK_URI . '/plugins');

		
		define('CHILD_SHOPHISTIC_LENGUAGES', CHILD_SHOPHISTIC_DIR . '/languages');
	}
	
	

	
	/**
	 * Add Stylesheets for the Theme (CSS)
	 */
	public function stylesheets(){
		shophistic_lite_require_file("/stylesheets.php", SHOPHISTIC_SCRIPTS, CHILD_SHOPHISTIC_SCRIPTS);
	}



	/**
	 * Add JS Scripts for the Theme (JS)
	 */
	public function scripts(){
		shophistic_lite_require_file("/scripts.php", SHOPHISTIC_SCRIPTS, CHILD_SHOPHISTIC_SCRIPTS);
	}

	
	

	/**
	 * Enable Plugins Activations
	 */
	public function plugins_activation(){
		shophistic_lite_require_file("/ql_tgm_plugin_activation.php", SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);
	}




	
	
	
	/**
	 * Add Theme Support
	 */
	public function theme_support(){
		function shophistic_lite_setup() {

			load_theme_textdomain( 'shophistic-lite', get_template_directory() . '/languages' );

			add_theme_support( 'post-thumbnails' );

			if ( function_exists( 'add_image_size' ) ) {				
				//Blog Thumbnails
				add_image_size( 'post', 1002, 563, true );
			}
		
			// Add RSS links to <head> section
			add_theme_support('automatic-feed-links');
			
			//Add Menu Manager---------------------------
			add_theme_support( 'nav-menus' );
			register_nav_menus( array( 'menu-1' => esc_attr__( 'Navigation Menu' , 'shophistic-lite' ) ) );
			register_nav_menus( array('social' => esc_attr__( 'Social' , 'shophistic-lite' ) ) );

			//Bootstrap Walker		
			shophistic_lite_require_file("/wp_bootstrap_navwalker.php", SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);

			//Title support---------------------------
			add_theme_support( 'title-tag' );

			// Setup the WordPress core custom background feature.
			add_theme_support( 'custom-background', apply_filters( 'shophistic_lite_custom_background_args', array(
				'default-color'      => "#F1F1F1",
				'default-attachment' => 'fixed',
			) ) );

			//HTML5 support
			add_theme_support( 'html5', array(
				'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
			) );

			// Styles for TinyMCE
    		$font_url = str_replace( ',', '%2C', '//fonts.googleapis.com/css?family=Lato:300,400,700' );
    		add_editor_style( array( 'css/bootstrap.css', 'css/editor-style.css', $font_url )  );

		}
		add_action( 'after_setup_theme', 'shophistic_lite_setup' );
	}
	
	
	
	/**
	 * Add Theme Functions
	 */
	public function theme_functions(){

		//Custom Comments		
		shophistic_lite_require_file("/custom_comments.php", SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);	
		
		//Single Functions		
		shophistic_lite_require_file( "/custom_functions.php", SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);		

		//WooCommerce Support		
		shophistic_lite_require_file("/woocommerce_support.php", SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);

	}
	
	
	
	/**
	 * Create all the widget areas
	 */
	public function widget_areas(){
		shophistic_lite_require_file("/widget_areas.php", SHOPHISTIC_WIDGET_AREAS, CHILD_SHOPHISTIC_WIDGET_AREAS);
	}



	/**
	 * Front End Customizer
	 *
	 * WordPress 3.4 Required
	 */
	public function customizer(){
		shophistic_lite_require_file('/theme_customizer.php', SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);
	}
	
	
	
	

}//class Theme

}//if !class_exists
?>