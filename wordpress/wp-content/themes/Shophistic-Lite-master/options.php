<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 */

function optionsframework_option_name() {


	//CUSTOM QUEMA LABS-----------------------------------------
	 // $optionsframework_settings = of_get_option('optionsframework');
	 // $optionsframework_settings['id'] = 'quemalabs_options';
	 // update_option('optionsframework', $optionsframework_settings);


	return 'quemalabs_options';
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fie lds, make sure to use all lowercase and no spaces.
 *
 * If you are making your theme translatable, you should replace 'shophistic-lite'
 * with the actual text domain for your theme.  Read more:
 * http://codex.wordpress.org/Function_Reference/load_theme_textdomain
 */

function optionsframework_options() {

	//Fonts options
	$font_options = array(
		'"Helvetica Neue", Helvetica, sans-serif' => 'Helvetica',
		'Verdana' => 'Verdana',
		'Georgia' => 'Georgia',
		'Trebuchet' => 'Trebuchet',
		'Tahoma' => 'Tahoma' 
		);

	// Test data
	$test_array = array(
		'one' => __('One', 'shophistic-lite'),
		'two' => __('Two', 'shophistic-lite'),
		'three' => __('Three', 'shophistic-lite'),
		'four' => __('Four', 'shophistic-lite'),
		'five' => __('Five', 'shophistic-lite')
		);


	// Multicheck Array
	$multicheck_array = array(
		'one' => __('French Toast', 'shophistic-lite'),
		'two' => __('Pancake', 'shophistic-lite'),
		'three' => __('Omelette', 'shophistic-lite'),
		'four' => __('Crepe', 'shophistic-lite'),
		'five' => __('Waffle', 'shophistic-lite')
		);

	// Multicheck Defaults
	$multicheck_defaults = array(
		'one' => '1',
		'five' => '1'
		);

	// Background Defaults
	$background_defaults = array(
		'color' => '',
		'image' => '',
		'repeat' => 'repeat',
		'position' => 'top center',
		'attachment'=>'scroll' );

	// Typography Defaults
	$typography_defaults = array(
		'size' => '13px',
		'face' => 'Helvetica',
		'style' => 'normal',
		'color' => '#737373' );

	// Typography Options
	$typography_options = array(
		'sizes' => false,
		'faces' => array( 'Helvetica' => '"Helvetica Neue", Helvetica, sans-serif','Verdana' => 'Verdana','Georgia' => 'Georgia','Trebuchet' => 'Trebuchet', 'Tahoma' => 'Tahoma' ),
		'styles' => array( 'normal' => 'Normal','bold' => 'Bold' ),
		'color' => '#737373'
		);

	//True/False options
	$options_true_false = array("true" => "True","false" => "False");


	// Pull all the categories into an array
	$options_categories = array();
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	// Pull all tags into an array
	$options_tags = array();
	$options_tags_obj = get_tags();
	foreach ( $options_tags_obj as $tag ) {
		$options_tags[$tag->term_id] = $tag->name;
	}


	// Pull all the pages into an array
	$options_pages = array();
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
		$options_pages[$page->ID] = $page->post_title;
	}

	// If using image radio buttons, define a directory path
	$imagepath =  SHOPHISTIC_IMAGES ;


	$shortname = "shophistic_lite_"; //deprecated








	$options = array();

	$options[] = array(
		'name' => __('General Settings', 'shophistic-lite'),
		'type' => 'heading');


	$options[] = array(
		'name' => __('Theme Info', 'shophistic-lite'),
		'desc' => '<strong>Name:</strong> '. SHOPHISTIC_NAME . '<br />' .
		'<strong>Version:</strong> '. SHOPHISTIC_VERSION . '<br />' .
		'<strong>Author:</strong> ' . SHOPHISTIC_AUTHOR,
		'type' => 'info');



	/**
	 * For $settings options see:
	 * http://codex.wordpress.org/Function_Reference/wp_editor
	 *
	 * 'media_buttons' are not supported as there is no post to attach items to
	 * 'textarea_name' is set by the 'id' you choose
	 */

	$wp_editor_settings = array(
		'wpautop' => true, // Default
		'textarea_rows' => 5,
		'tinymce' => array( 'plugins' => 'wordpress' )
		);


	

	$options['sidebar_side'] = array(
		'name' => __('Sidebar side', 'shophistic-lite'),
		'desc' => __('Select if you want the Sidebar on the right or left.', 'shophistic-lite'),
		'id' => "sidebar_side",
		'std' => "left",
		'type' => "images",
		'options' => array(
			'right' => $imagepath . '/2cr.png',
			'left' => $imagepath . '/2cl.png'
		));







	$options[] = array(
		'name' => __('Header', 'shophistic-lite'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Custom Logo', 'shophistic-lite'),
		'desc' => __("Upload a logo for your theme, or specify the image address of your online logo. (http://yoursite.com/logo.png)", 'shophistic-lite'),
		'id' =>  'logo',
		'type' => 'upload');

	$options[] = array(
		'name' => __('Custom Logo Retina size', 'shophistic-lite'),
		'desc' => __("Upload the logo for retina screens. (Same logo, twice the size)", 'shophistic-lite'),
		'id' =>  'logo_retina',
		'type' => 'upload');






	$options[] = array(
		'name' => __('Styling', 'shophistic-lite'),
		'type' => 'heading');


	$options[ 'featured_color'] = array(
		'name' => __('Featured Color', 'shophistic-lite'),
		'desc' => __('Select the featured color for the Theme.', 'shophistic-lite'),
		'id' =>  'featured_color',
		'std' => '#c3a769',
		'class' => 'mini',
		'type' => 'color' );

	$options[ 'contrast_color'] = array(
		'name' => __('Contrast Color', 'shophistic-lite'),
		'desc' => __('This color should contrast with the Featured Color.', 'shophistic-lite'),
		'id' =>  'contrast_color',
		'std' => '#FFFFFF',
		'class' => 'mini',
		'type' => 'color' );


	$options['headings_color'] = array(
		'name' => __('Headings Color', 'shophistic-lite'),
		'desc' => __('Select the background color for the Theme.', 'shophistic-lite'),
		'id' =>  'headings_color',
		'std' => '#6a6a6a',
		'class' => 'mini',
		'type' => 'color' );

	$options[] = array(
		'name' => __('Header', 'shophistic-lite'),
		'desc' => '',
		'type' => 'info');

    $options['header_color_text'] = array(
		'name' => __('Text Color', 'shophistic-lite'),
		'desc' => __('Select the color for text in the header.', 'shophistic-lite'),
		'id' =>  'header_color_text',
		'std' => '#777777',
		'class' => 'mini',
		'type' => 'color' );

    $options['header_color_links'] = array(
		'name' => __('Links Color', 'shophistic-lite'),
		'desc' => __('Select the color for links in the header (not main menu).', 'shophistic-lite'),
		'id' =>  'header_color_links',
		'std' => '#555555',
		'class' => 'mini',
		'type' => 'color' );

    $options[] = array(
		'name' => '',
		'desc' => '',
		'type' => 'info');

	$options[] = array(
		'name' => __('Custom CSS', 'shophistic-lite'),
		'desc' => __('Quickly add some CSS to your theme by adding it to this block.', 'shophistic-lite'),
		'id' =>  'custom_css',
		'std' => '',
		'type' => 'textarea');












	$options[] = array( "name" => "Footer",
		"type" => "heading");    


	$options[] = array(
		'name' => __('Footer Text', 'shophistic-lite'),
		'desc' => __('Custom HTML and Text that will appear at the bottom of your site.', 'shophistic-lite'),
		'id' =>  'footer_text',
		'type' => 'textarea',
		'std' => "Copyright &copy; ".date('Y')." <a href='".esc_url(home_url())."' title='".get_bloginfo('name')."'>".get_bloginfo('name')."</a>.");

	$options[] = array(
		'name' => __('Designed by Quema Labs', 'shophistic-lite'),
		'desc' => __('If you are happy with the Theme you can give us credit just by adding a small link.', 'shophistic-lite'),
		'id' =>  'quemalabs_credit',
		'std' => true,
		'type' => 'checkbox' );

	$options[] = array(
		'name' => __('Payment Methods Icons', 'shophistic-lite'),
		'desc' => '',
		'type' => 'info');

	$options[] = array(
		'name' => __('Paypal', 'shophistic-lite'),
		'desc' => __('Show Paypal Icon', 'shophistic-lite'),
		'id' =>  'footer_icon_paypal',
		'std' => true,
		'type' => 'checkbox' );

	$options[] = array(
		'name' => __('American Express', 'shophistic-lite'),
		'desc' => __('Show American Express Icon', 'shophistic-lite'),
		'id' =>  'footer_icon_amex',
		'std' => true,
		'type' => 'checkbox' );

	$options[] = array(
		'name' => __('Discover', 'shophistic-lite'),
		'desc' => __('Show Discover Icon', 'shophistic-lite'),
		'id' =>  'footer_icon_discover',
		'std' => true,
		'type' => 'checkbox' );

	$options[] = array(
		'name' => __('Master Card', 'shophistic-lite'),
		'desc' => __('Show Master Card Icon', 'shophistic-lite'),
		'id' =>  'footer_icon_mastercard',
		'std' => true,
		'type' => 'checkbox' );

	$options[] = array(
		'name' => __('Stripe', 'shophistic-lite'),
		'desc' => __('Show Stripe Icon', 'shophistic-lite'),
		'id' =>  'footer_icon_stripe',
		'std' => true,
		'type' => 'checkbox' );

	$options[] = array(
		'name' => __('Visa', 'shophistic-lite'),
		'desc' => __('Show Visa Icon', 'shophistic-lite'),
		'id' =>  'footer_icon_visa',
		'std' => true,
		'type' => 'checkbox' );








	$options[] = array(
		'name' => __('Shop', 'shophistic-lite'),
		'type' => 'heading');

	$options['shop_sidebar'] = array(
		'name' => __('Show sidebar on Single Products page', 'shophistic-lite'),
		'desc' => __('Select if you want the Shop Sidebar on your single product page.', 'shophistic-lite'),
		'id' => "shop_sidebar",
		'class' => 'mini',
		'std' => "no",
		'type' => "select",
		'options' => array(
			'no' => 'No Sidebar',
			'yes' => 'With Sidebar'
		));













	$options[] = array( 'name' => __('Typography', 'shophistic-lite'),
		'type' => 'heading');


	$options[] = array(
		'name' => __('Content Typography', 'shophistic-lite'),
		'desc' => '',
		'type' => 'info');

	$options['content_typography_font'] = array(
		'name' => __('Content Font', 'shophistic-lite'),
		'desc' => __('Select the font for your text.', 'shophistic-lite'),
		'id' => 'content_typography_font',
		'std' => 'Helvetica',
		'type' => 'select',
		'class' => 'mini', //mini, tiny, small
		'options' => $font_options);

	$options['content_typography_color'] = array(
		'name' => __('Content Font Color', 'shophistic-lite'),
		'desc' => __('Select the color for your text.', 'shophistic-lite'),
		'id' =>  'content_typography_color',
		'std' => '#555555',
		'class' => 'mini',
		'type' => 'color' );

	$options['content_typography_weight'] = array(
		'name' => __('Content Font Weight', 'shophistic-lite'),
		'desc' => __('Select the font weight for your text.', 'shophistic-lite'),
		'id' => 'content_typography_weight',
		'std' => 'normal',
		'type' => 'select',
		'class' => 'mini', //mini, tiny, small
		'options' => array('normal' => __('Normal', 'shophistic-lite'),'bold' => __('Bold', 'shophistic-lite')) );


	$options[] = array(
		'name' => __('Links Typography', 'shophistic-lite'),
		'desc' => '',
		'type' => 'info');

	$options['links_typography_font'] = array(
		'name' => __('Links Font', 'shophistic-lite'),
		'desc' => __('Select the font for your links.', 'shophistic-lite'),
		'id' => 'links_typography_font',
		'std' => 'Helvetica',
		'type' => 'select',
		'class' => 'mini', //mini, tiny, small
		'options' => $font_options);

	$options['links_typography_color'] = array(
		'name' => __('Links Font Color', 'shophistic-lite'),
		'desc' => __('Select the color for your links.', 'shophistic-lite'),
		'id' =>  'links_typography_color',
		'std' => '#c3a769',
		'class' => 'mini',
		'type' => 'color' );

	$options['links_typography_weight'] = array(
		'name' => __('Links Font Weight', 'shophistic-lite'),
		'desc' => __('Select the font wheight for your links.', 'shophistic-lite'),
		'id' => 'links_typography_weight',
		'std' => 'normal',
		'type' => 'select',
		'class' => 'mini', //mini, tiny, small
		'options' => array('normal' => __('Normal', 'shophistic-lite'),'bold' => __('Bold', 'shophistic-lite')) );

	$options[] = array(
		'name' => __('Font Systems', 'shophistic-lite'),
		'desc' => '',
		'type' => 'info');

	$options[] = array(
		'name' => __('Font Systems', 'shophistic-lite'),
		'desc' => __('Use a font system (Google Fonts)', 'shophistic-lite'),
		'id' =>  'font_system',
		'std' => true,
		'type' => 'checkbox' );

	
	$options[] = array( 'name' => 'Google Fonts',
		'desc' => 'Some of the best fonts on <a href="' . esc_url( 'http://www.google.com/webfonts' ) . '">google fonts</a>.',
		'id' =>  'google_font',
		'std' => array( 'size' => '36px', 'face' => 'Lato, sans-serif', 'color' => '#00bc96'),
		'type' => 'typography',
		'options' => array(
			'faces' => shophistic_lite_options_typography_get_google_fonts(),
			'styles' => false,
			'sizes' => false,
			'color' => false )
		);







	$options[] = array(
		'name' => __('Sidebars Generator', 'shophistic-lite'),
		'type' => 'heading');

	$options[] = array(
		'name' => __('Sidebars', 'shophistic-lite'),
		'desc' => 'Create sidebar for later use on pages.',
		'id' => $shortname. 'sidebars',
		'std' => '',
		'type' => 'sidebar');





	return $options;
}


/**
 * Front End Customizer
 *
 * WordPress 3.4 Required
 */

shophistic_lite_require_file('/theme_customizer.php', SHOPHISTIC_FUNCTIONS, CHILD_SHOPHISTIC_FUNCTIONS);











