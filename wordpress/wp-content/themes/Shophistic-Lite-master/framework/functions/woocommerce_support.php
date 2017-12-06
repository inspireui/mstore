<?php
//Add WooCommerce Support
add_action( 'after_setup_theme', 'shophistic_lite_woocommerce_support' );
if (!function_exists('shophistic_lite_woocommerce_support')) {
	function shophistic_lite_woocommerce_support() {
	    add_theme_support( 'woocommerce' );
	}
}

//Change the default Before & After content
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'shophistic_lite_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'shophistic_lite_wrapper_end', 10);

if (!function_exists('shophistic_lite_wrapper_start')) {
	function shophistic_lite_wrapper_start() {
	  		if (is_single()) {
	  			get_template_part( "/templates/beforeloop", "woocommerce-single" ) ;
	  		}else{
	  			get_template_part( "/templates/beforeloop", "woocommerce" ) ;
	  		}
	}
}

if (!function_exists('shophistic_lite_wrapper_end')) {
	function shophistic_lite_wrapper_end() {
	  		if (is_single()) {
	  			get_template_part( "/templates/afterloop", "woocommerce-single" ) ;
	  		}else{
	  			get_template_part( "/templates/afterloop", "woocommerce" ) ;
	  		}
	}
}

// Removes the "Product Category:" from the Archive Title
add_filter( 'get_the_archive_title', 'shophistic_lite_remove_archive_title' );
function shophistic_lite_remove_archive_title( $title ) {
    if( is_tax() ) {
        $title = single_cat_title( '', false );
    }
    return $title;
}


//Adds rating into the Product Thumbnail
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);




//Change the number of Related products
add_filter( 'woocommerce_output_related_products_args', 'shophistic_lite_related_products_args' );
function shophistic_lite_related_products_args( $args ) {
	$args['posts_per_page']     = 5; // 4 related products
	$args['columns']            = 5; // arranged in columns

	return $args;
}








//Remove categories from Single Product page
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);


//Remove Tabs from Single Product page
//remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);


//Remove Add to cart button by default
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
//Add 'Add to cart' button
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 15 );




//Move up ratings in Single Product page
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 3);


//Adds category above the product title on Single page
if (!function_exists('shophistic_lite_product_category')) {
	function shophistic_lite_product_category() {
		global $post;
		$terms = get_the_terms( $post->ID, 'product_cat' );
		if ( $terms ) {
			$terms_print = array();
			foreach ($terms as $term ) {
				if ( $term->parent != 0 ) { //has parent
					array_push( $terms_print, $term->name );
				}				   
			}
			if ( $terms_print ) {
				echo '<div class="product_category">';
				echo wp_kses_post( $terms_print[0] ); //Prints the first of the child terms
				echo '</div>';
			}else{
				echo '<div class="product_category">';
			    echo wp_kses_post( $terms[0]->name ); //Print the only term
			    echo '</div>';
			}
		}//if terms	
	}
}
add_action('woocommerce_single_product_summary', 'shophistic_lite_product_category', 2);


//Remove Upsell products from Single Page
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
/**
 * Hook in on activation
 */

/**
 * Define image sizes
 */
if (!function_exists('shophistic_lite_woocommerce_image_dimensions')) {
	function shophistic_lite_woocommerce_image_dimensions() {
		global $pagenow;
	 
		if ( ! isset( $_GET['activated'] ) || $pagenow != 'themes.php' ) {
			return;
		}

	  	$catalog = array(
			'width' 	=> '348',	// px
			'height'	=> '445',	// px
			'crop'		=> 1 		// true
		);
	 
		$single = array(
			'width' 	=> '568',	// px
			'height'	=> '725',	// px
			'crop'		=> 1 		// true
		);
	 
		$thumbnail = array(
			'width' 	=> '78',	// px
			'height'	=> '99',	// px
			'crop'		=> 1 		// true
		);

		// Image sizes
		update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
		update_option( 'shop_single_image_size', $single ); 		// Single product image
		update_option( 'shop_thumbnail_image_size', $thumbnail ); 	// Image gallery thumbs
	}
}
add_action( 'after_switch_theme', 'shophistic_lite_woocommerce_image_dimensions', 1 );





/**
 * Replace default thumbnail function
 */
if (!function_exists('shophistic_lite_template_loop_product_thumbnail')) {
	function shophistic_lite_template_loop_product_thumbnail() {
		echo woocommerce_get_product_thumbnail();

		//Get one more image
		global $product;
		$attachment_ids = $product->get_gallery_image_ids();
		if ( $attachment_ids > 0 ) {
			$default_attr = array(
				'class'	=> "product_second_img"
			);
			$image = wp_get_attachment_image( $attachment_ids[0], 'shop_catalog', false, $default_attr );
			echo wp_kses_post( $image );
		}

	}
}
//Replace default thumbnail function for "shophistic_lite_template_loop_product_thumbnail"
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action('woocommerce_before_shop_loop_item_title', 'shophistic_lite_template_loop_product_thumbnail', 10);





/**
 * Remove Description tab
 */
add_filter( 'woocommerce_product_tabs', 'shophistic_lite_remove_product_tabs', 98 );
if ( !function_exists( 'shophistic_lite_remove_product_tabs' ) ) {
	function shophistic_lite_remove_product_tabs( $tabs ) {
	    unset( $tabs['description'] );      	// Remove the description tab
	    return $tabs;
	}
}


/**
 * Adds the description under the 'add to cart' button
 */
add_action( 'woocommerce_single_product_summary', 'shophistic_lite_woocommerce_product_excerpt', 35);
if (!function_exists('shophistic_lite_woocommerce_product_excerpt')){
	function shophistic_lite_woocommerce_product_excerpt(){
	     echo '<div class="product-content">';
	     the_content();
	     echo '</div>';
	}
}




/**
 * Updates the total with AJAX
 */
if (!function_exists('shophistic_lite_header_add_to_cart_fragment')) {
	function shophistic_lite_header_add_to_cart_fragment( $fragments ) {
		ob_start();
		?>
		<button href="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" class="ql_cart-btn">
	        <?php echo wp_kses_post( WC()->cart->get_cart_total() ); ?>
	        <span class="count">(<?php echo esc_html( WC()->cart->cart_contents_count );?>)</span>
	        <i class="ql-bag"></i><i class="ql-chevron-down"></i>
	    </button>
		<?php
		
		$fragments['.ql_cart-btn'] = ob_get_clean();
		
		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'shophistic_lite_header_add_to_cart_fragment' );







//Change order of items in before shop loop
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
add_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 30);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
add_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 20);



if (!function_exists('shophistic_lite_order_review_before')) {
	function shophistic_lite_order_review_before() {
		?>
		<div class="row">
			<div class="col-md-6">
		<?php
	}
}
if (!function_exists('shophistic_lite_order_review_after')) {
	function shophistic_lite_order_review_after() {
		?>
		</div>
		<div class="col-md-6">
		<?php
	}
}
if (!function_exists('shophistic_lite_checkout_payment_after')) {
	function shophistic_lite_checkout_payment_after() {
		?>
			</div>
		</div>
		<?php
	}
}
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
add_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 15 );
add_action( 'woocommerce_checkout_order_review', 'shophistic_lite_order_review_before', 12 );
add_action( 'woocommerce_checkout_order_review', 'shophistic_lite_order_review_after', 17 );
add_action( 'woocommerce_checkout_order_review', 'shophistic_lite_checkout_payment_after', 25 );


/**
 * Adds the Switch View buttons
 */
function shophistic_lite_show_attribute() {
	global $product;
    $attributes = $product->get_attributes();
    if ( ! $attributes ) {
        return;
    }
    $out = '<div class="product_options">';

    foreach ( $attributes as $attribute ) {

        if ( $attribute['is_taxonomy'] ) {

            $terms = wp_get_post_terms( $product->get_id(), $attribute['name'], 'all' );

            if ( !is_wp_error( $terms ) ) {
	            // get the taxonomy
	            $tax = $terms[0]->taxonomy;
	            // get the tax object
	            $tax_object = get_taxonomy($tax);
	            // get tax label
	            if ( isset ($tax_object->labels->name) ) {
	                $tax_label = $tax_object->labels->name;
	            } elseif ( isset( $tax_object->label ) ) {
	                $tax_label = $tax_object->label;
	            }
	            $out .= '<h4>'. $tax_label .'</h4>';
	            $out .= '<ul>';
	            foreach ( $terms as $term ) {
	                $out .= '<li class="' . esc_attr( $attribute['name'] ) . ' ' . esc_attr( $term->slug ) . '">';
	                $out .= '<a href="#">' . $term->name . '</a></li>';
	            }
	            $out .= '</ul>';
            }//if WP_Error
 
        } else {
 			$out .= '<ul>';
            $out .= '<li class="' . sanitize_title($attribute['name']) . ' ' . sanitize_title($attribute['value']) . '">';
            $out .= '<span class="attribute-label">' . $attribute['name'] . ': </span> ';
            $out .= '<span class="attribute-value">' . $attribute['value'] . '</span></li>';
            $out .= '</ul>';
        }
    }
 	
    $out .= '</div>';
 
    echo $out;
}
add_action( 'woocommerce_after_shop_loop_item_title', 'shophistic_lite_show_attribute', 15 );




//Remove prettyPhoto lightbox
add_action( 'wp_enqueue_scripts', 'shophistic_lite_remove_woo_lightbox', 99 );
function shophistic_lite_remove_woo_lightbox() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    	remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
        wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
        wp_dequeue_script( 'prettyPhoto' );
        wp_dequeue_script( 'prettyPhoto-init' );
	}
}

/**
 * Add support for PhotoSwipe on WooCommerce Images
 */
if ( ! function_exists( 'shophistic_lite_woocommerce_image_photoswipe' ) ) {
	function shophistic_lite_woocommerce_image_photoswipe( $html, $thumbnail_id ) {

		$full_size_image   = wp_get_attachment_image_src( $thumbnail_id, 'full' );
		$to_replace = 'class="woocommerce-product-gallery__image"><a data-width="' . esc_attr( $full_size_image[1] ) . '" data-height="' . esc_attr( $full_size_image[2] ) . '"';

		$html = str_replace( 'class="woocommerce-product-gallery__image"><a', $to_replace, $html );

		return $html;

	}
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'shophistic_lite_woocommerce_image_photoswipe', 10, 2 );