<?php
//Footer and Sidebar Widgets
function shophistic_lite_widgets_init() {

	if ( function_exists( 'register_sidebar' ) ) {
    	register_sidebar(array(
    		'name' => 'Sidebar Widgets',
    		'id'   => 'sidebar-widgets',
    		'description'   => 'These are widgets for the sidebar.',
    		'before_widget' => '<div id="%1$s" class="widget col-sm-6 col-md-12 %2$s">',
    		'after_widget'  => '</div>',
    		'before_title'  => '<h4>',
    		'after_title'   => '</h4>'
    	));
    }

}
add_action( 'widgets_init', 'shophistic_lite_widgets_init' );
?>