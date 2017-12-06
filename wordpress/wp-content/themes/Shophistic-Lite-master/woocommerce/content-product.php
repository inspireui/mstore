<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php post_class(); ?>>
	<?php
	/**
	 * woocommerce_before_shop_loop_item hook.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );
	?>
	<div class="ql_regular_product">
        <div class="product_wrap">
            <div class="product_content">
                <a class="product_thumbnail_wrap" href="<?php the_permalink(); ?>">
					<?php
						/**
						 * woocommerce_before_shop_loop_item_title hook
						 *
						 * @hooked woocommerce_show_product_loop_sale_flash - 10
						 * @hooked shophistic_lite_template_loop_product_thumbnail - 10
						 * @hooked woocommerce_template_loop_add_to_cart - 15
						 */
						do_action( 'woocommerce_before_shop_loop_item_title' );
					?>
				</a>
                <div class="product_text">
                	<?php shophistic_lite_product_category(); ?>

						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

						<?php
							/**
							 * woocommerce_after_shop_loop_item_title hook
							 *
							 * @hooked woocommerce_template_loop_rating - 5
							 * @hooked woocommerce_template_loop_price - 10
							 */
							do_action( 'woocommerce_after_shop_loop_item_title' );
						?>


					<?php

							/**
							 * woocommerce_after_shop_loop_item hook.
							 *
							 * @hooked woocommerce_template_loop_product_link_close - 5
							 */
							do_action( 'woocommerce_after_shop_loop_item' );
					?>
					<div class="clearfix"></div>
				</div><!-- /product_text -->
            </div>
        </div>
        <div class="product_content_hidden">
                <a class="product_thumbnail_wrap" href="<?php the_permalink(); ?>">
					<?php
						/**
						 * woocommerce_before_shop_loop_item_title hook
						 *
						 * @hooked woocommerce_show_product_loop_sale_flash - 10
						 * @hooked shophistic_lite_template_loop_product_thumbnail - 10
						 */
						do_action( 'woocommerce_before_shop_loop_item_title' );
					?>
				</a>
                <div class="product_text">
                	<?php shophistic_lite_product_category(); ?>

						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

						<?php
							/**
							 * woocommerce_after_shop_loop_item_title hook
							 *
							 * @hooked woocommerce_template_loop_rating - 5
							 * @hooked woocommerce_template_loop_price - 10
							 * @hooked woocommerce_template_loop_add_to_cart - 20
							 */
							do_action( 'woocommerce_after_shop_loop_item_title' );
						?>


					<?php

						/**
						 * woocommerce_after_shop_loop_item hook
						 *
						 * @hooked woocommerce_template_loop_add_to_cart - 10
						 */
						do_action( 'woocommerce_after_shop_loop_item' ); 

					?>

				</div><!-- /product_text -->
        </div><!-- /product_content_hidden -->
    </div><!-- /ql_regular_product -->

</li>
