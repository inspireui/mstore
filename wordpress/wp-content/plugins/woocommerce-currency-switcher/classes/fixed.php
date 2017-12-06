<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');


final class WOOCS_FIXED {

    public function __construct() {
	add_action('woocommerce_product_options_general_product_data', array($this, 'woocommerce_product_options_general_product_data'), 9999);
	add_action('woocommerce_process_product_meta', array($this, 'woocommerce_process_product_meta'), 9999, 1);
	add_action('woocommerce_product_after_variable_attributes', array($this, 'woocommerce_product_after_variable_attributes'), 9999, 3);
	add_action('woocommerce_process_product_meta_variable', array($this, 'woocommerce_process_product_meta_variable'), 9999, 1);
	add_action('woocommerce_save_product_variation', array($this, 'woocommerce_process_product_meta_variable'), 9999, 1);
    }

    public function admin_footer() {
	wp_enqueue_script('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'));
	wp_enqueue_style('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.min.css');
	wp_enqueue_script('woocs-fixed', WOOCS_LINK . 'js/fixed.js', array('jquery'));
	wp_enqueue_style('woocs-fixed', WOOCS_LINK . 'css/fixed.css');
    }

    public function woocommerce_product_options_general_product_data() {
	global $WOOCS;
	global $post;
	$_product = wc_get_product($post->ID);
	add_action('admin_footer', array($this, 'admin_footer'));
	if ($_product->is_type('simple') OR $_product->is_type('external')) {
	    $data = array();
	    $data['currencies'] = $WOOCS->get_currencies();
	    $data['default_currency'] = $WOOCS->default_currency;
	    $data['is_fixed_enabled'] = $WOOCS->is_fixed_enabled;
	    $data['is_geoip_manipulation'] = $WOOCS->is_geoip_manipulation;
	    $data['post_id'] = $post->ID;
	    $data['type'] = 'simple';
	    $data['product_geo_data'] = $this->get_product_geo_data($post->ID);

	    echo $this->render_html(WOOCS_PATH . 'views/fixed/product_price_data.php', $data);
	}
    }

    //saving data for simple product
    public function woocommerce_process_product_meta($post_id) {
	$this->save_product_prices($post_id);
    }

    public function woocommerce_product_after_variable_attributes($loop, $variation_data, $variation) {
	global $WOOCS;
	$data = array();
	$data['currencies'] = $WOOCS->get_currencies();
	$data['default_currency'] = $WOOCS->default_currency;
	$data['is_fixed_enabled'] = $WOOCS->is_fixed_enabled;
	$data['is_geoip_manipulation'] = $WOOCS->is_geoip_manipulation;
	$data['post_id'] = $variation->ID;
	$data['type'] = 'var';
	$data['product_geo_data'] = $this->get_product_geo_data($variation->ID);
	echo $this->render_html(WOOCS_PATH . 'views/fixed/product_price_data.php', $data);
    }

    //saving data for variable product
    public function woocommerce_process_product_meta_variable($post_id) {
	if (isset($_POST['variable_post_id']) AND ! empty($_POST['variable_post_id'])) {
	    foreach ($_POST['variable_post_id'] as $key => $p_id) {
		$this->save_product_prices($p_id);
	    }
	}
    }

    public function save_product_prices($post_id) {
	if (!current_user_can('manage_options')) {
	    return;
	}

	//***

	global $WOOCS;
	$currencies = $WOOCS->get_currencies();

	//+++
	if (isset($_POST['woocs_regular_price'][$post_id])) {
	    unset($_POST['woocs_regular_price'][0]);
	    unset($_POST['woocs_regular_price']['__POST_ID__']);
	    unset($_POST['woocs_sale_price'][0]);
	    unset($_POST['woocs_sale_price']['__POST_ID__']);

	    //clean all data before apply new selected data
	    foreach ($currencies as $code => $curr) {
		delete_post_meta($post_id, '_woocs_regular_price_' . $code);
		delete_post_meta($post_id, '_woocs_sale_price_' . $code);
	    }
	    //+++
	    if (is_array($_POST['woocs_regular_price'][$post_id])) {
		foreach ($_POST['woocs_regular_price'][$post_id] as $code => $price) {
		    $price = floatval($price);
		    if ($price > 0) {
			update_post_meta($post_id, '_woocs_regular_price_' . $code, $price);
		    } else {
			update_post_meta($post_id, '_woocs_regular_price_' . $code, -1);
		    }
		}
	    }
	    //+++
	    if (is_array($_POST['woocs_sale_price'][$post_id])) {
		foreach ($_POST['woocs_sale_price'][$post_id] as $code => $price) {
		    $price = floatval($price);
		    if ($price > 0) {
			update_post_meta($post_id, '_woocs_sale_price_' . $code, $price);
		    } else {
			update_post_meta($post_id, '_woocs_sale_price_' . $code, -1);
		    }
		}
	    }
	}
	//+++

	if (isset($_POST['woocs_price_geo_countries'])) {
	    update_post_meta($post_id, '_woocs_price_geo_countries', '');
	    update_post_meta($post_id, '_woocs_regular_price_geo', '');
	    update_post_meta($post_id, '_woocs_sale_price_geo', '');
	    if (is_array($_POST['woocs_price_geo_countries'])) {
		foreach ($_POST['woocs_price_geo_countries'] as $post_id => $rules) {
		    update_post_meta($post_id, '_woocs_price_geo_countries', $rules);
		}

		foreach ($_POST['woocs_regular_price_geo'] as $post_id => $rules) {
		    update_post_meta($post_id, '_woocs_regular_price_geo', $rules);
		}

		foreach ($_POST['woocs_sale_price_geo'] as $post_id => $rules) {
		    update_post_meta($post_id, '_woocs_sale_price_geo', $rules);
		}
	    }
	}
    }

    public function get_product_geo_data($post_id) {
	$data = array();
	$data['price_geo_countries'] = (array) get_post_meta($post_id, '_woocs_price_geo_countries', true);
	$data['regular_price_geo'] = (array) get_post_meta($post_id, '_woocs_regular_price_geo', true);
	$data['sale_price_geo'] = (array) get_post_meta($post_id, '_woocs_sale_price_geo', true);

	//*** some corrections
	if (!empty($data['regular_price_geo'])) {
	    foreach ($data['regular_price_geo'] as $key => $value) {
		if (empty($data['sale_price_geo'][$key])) {
		    //for example sale price should be, but user leave it empty for any currency, in such
		    //case without such correction price will be free for the product, what is wrong behaviour
		    $data['sale_price_geo'][$key] = $value;
		}
	    }
	}

	return $data;
    }

    /*     * ********************************************************* */

    //$type - regular or sale
    public function get_value($post_id, $code, $type) {
	//echo $post_id.'+++_woocs_' . $type . '_price_' . strtoupper($code).'<br />';
	return get_post_meta($post_id, '_woocs_' . $type . '_price_' . strtoupper($code), true);
    }

    public function is_exists($post_id, $code, $type) {
	$is = false;
	$val = $this->get_value($post_id, $code, $type);
	if (floatval($val) > 0 OR (int) $val === -1) {
	    $is = true;
	}
	return $is;
    }
    
    
    public function is_empty($post_id, $code, $type) {
	$is = false;
	$val = $this->get_value($post_id, $code, $type);
	if ((int) $val === -1) {
	    $is = true;
	}
	return $is;
    }

    public function get_price_type($product, $price) {
	$type = 'regular';

	static $products_data = array();
	$product_id = 0;
	//$_regular_price = get_post_meta($post_id, '_regular_price', true);

	/*
	  if ($product instanceof WC_Product AND method_exists($product, 'is_on_sale')) {
	  if ($product->is_on_sale('edit')) {
	  //doesn work for fixed and geo-prices
	  $type = 'sale';
	  }
	  } else {

	  }
	 */

	if (version_compare(WOOCOMMERCE_VERSION, '2.7', '>=')) {
	    global $WOOCS;
	    $p_id = 0;
	    if (method_exists($product, 'get_id')) {
		$p_id = $product->get_id();
	    } else {
		$p_id = $product->id;
	    }

	    if (method_exists($product, 'get_sale_price')) {
		if ($this->is_exists($p_id, $WOOCS->current_currency, 'sale') AND ! $product->get_sale_price('edit')) {
		    return 'sale';
		}
	    } else {
		if (isset($product->sale_price)) {
		    if ($this->is_exists($p_id, $WOOCS->current_currency, 'sale') AND ! $product->sale_price) {
			return 'sale';
		    }
		}
	    }
	}

	if (version_compare(WOOCOMMERCE_VERSION, '2.7', '>=')) {
	    if (method_exists($product, 'get_sale_price')) {
		$sale_price = $product->get_sale_price('edit');
		$product_id = $product->get_id();
	    } else {
		if (isset($product->sale_price)) {
		    $sale_price = $product->sale_price;
		}
		$product_id = $product->id;
	    }
	} else {
	    if (isset($product->sale_price)) {
		$sale_price = $product->sale_price;
	    }
	    $product_id = $product->id;
	}

	//***

	if (version_compare(WOOCOMMERCE_VERSION, '2.7', '>=')) {
	    if (method_exists($product, 'get_regular_price')) {
		$regular_price = $product->get_regular_price('edit');
		$product_id = $product->get_id();
	    } else {
		if (isset($product->regular_price)) {
		    $regular_price = $product->regular_price;
		}
		$product_id = $product->id;
	    }
	} else {
	    if (isset($product->regular_price)) {
		$regular_price = $product->regular_price;
	    }
	    $product_id = $product->id;
	}


	//***


	if (isset($products_data[$product_id])) {
	    if ($products_data[$product_id] < $price) {
		$type = 'regular';
	    } else {
		$type = 'sale';
	    }
	} else {
	    $products_data[$product_id] = $price;
	    $type = 'sale';
	}


	/*
	  if (floatval($price) === floatval($sale_price)) {
	  $type = 'sale';
	  }
	 */


	//$type = 'sale';
	return $type;
    }

    public function render_html($pagepath, $data = array()) {
	@extract($data);
	ob_start();
	include($pagepath);
	return ob_get_clean();
    }

}
