<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package shipping
 */

class FlutterWoo extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_woo';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_woo_routes'));
        add_filter('wp_rest_cache/allowed_endpoints', array($this, 'wprc_add_flutter_endpoints'));
    }

    /**
     * Register the flutter caching endpoints so they will be cached.
     */
    function wprc_add_flutter_endpoints($allowed_endpoints)
    {
        if (!isset($allowed_endpoints[$this->namespace])) {
            $allowed_endpoints[$this->namespace][] = 'products/video';
        }
        return $allowed_endpoints;
    }

    public function register_flutter_woo_routes()
    {
        register_rest_route($this->namespace, '/shipping_methods', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'shipping_methods'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/ddates', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_ddates'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/payment_methods', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'payment_methods'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/coupon', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'coupon'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/cart', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_cart'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/cart', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'sync_cart_from_mobile'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        $config_file = array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'upload_config_file'),
                'permission_callback' => array($this, 'check_upload_file_permission'),
            ),
        );
        register_rest_route($this->namespace, '/config-file', $config_file);

        register_rest_route($this->namespace, '/taxes', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'get_taxes'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/points', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_points'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/points', array(
            array(
                'methods' => "PATCH",
                'callback' => array($this, 'update_points'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/products/reviews', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'create_product_review'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/products/dynamic', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_product_from_dynamic_link'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/product-category/dynamic', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_product_category_from_dynamic_link'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/blog/dynamic', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_blog_from_dynamic_link'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route( $this->namespace,  '/blog/create', array(
			array(
				'methods' => "POST",
				'callback' => array( $this, 'create_blog' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		));

        register_rest_route( $this->namespace,  '/blog/comment', array(
			array(
				'methods' => "POST",
				'callback' => array( $this, 'create_comment' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		));

        register_rest_route($this->namespace, '/scanner', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_data_from_scanner'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route( $this->namespace,  '/products'. '/(?P<id>[\d]+)'.'/check', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                ),
            ),
			array(
				'methods' => "GET",
				'callback' => array( $this, 'check_product' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		));

        register_rest_route( $this->namespace,  '/products'. '/(?P<id>[\d]+)'.'/rating_counts', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                ),
            ),
			array(
				'methods' => "GET",
				'callback' => array( $this, 'get_product_rating_counts' ),
				'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
			),
		));

        register_rest_route($this->namespace, '/products/video', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_products_video'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/products/min-max-prices', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_min_max_prices'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/products/size-guide' . '/(?P<id>[\d]+)', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                ),
            ),
            array(
                'methods' => "GET",
                'callback' => array($this, 'size_guide'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    private function get_post_id_from_meta($meta_key, $meta_value)
    {
        global $wpdb;

        $sql = "SELECT post_id 
            FROM {$wpdb->prefix}postmeta
            WHERE meta_key = '%s' 
            AND meta_value = '%s'";

        return $wpdb->get_var($wpdb->prepare($sql, $meta_key, $meta_value));
    }

    function get_data_from_scanner($request){
        $raw_data = sanitize_text_field($request['data']);
        $token = sanitize_text_field($request['token']);

        // Get post id from data via meta key

        // YITH WooCommerce Barcodes
        // For old requests (data has been removed 000 at the beginning and the
        // last number at the end). For ex: 123
        $data = $this->get_post_id_from_meta('_ywbc_barcode_value', $raw_data);

        // YITH WooCommerce Barcodes
        // For new requests, get everything that can be scanned. For ex: 000000001236
        if (!isset($data)) {
            $data = $this->get_post_id_from_meta('_ywbc_barcode_display_value', $raw_data);
        }

        // EAN for WooCommerce plugin
        // Scan everything. For ex: 323900045132
        if (!isset($data)) {
            $ean_key = get_option('alg_wc_ean_meta_key', '_alg_ean');
            $data = $this->get_post_id_from_meta($ean_key, $raw_data);
        }

        // Get id from sku
        if (!isset($data)) {
            $data = $this->get_post_id_from_meta('_sku', $raw_data);
        }

        // Try to get id directly
        if (!isset($data)) {
            $data = $raw_data;
        }

		if(isset($data) && is_numeric($data)){
			$type = get_post_type($data);
			
			if($type){
				if($type == 'product'){
					$controller = new CUSTOM_WC_REST_Products_Controller();
            		$req = new WP_REST_Request('GET');
            		$params = array('status' =>'published', 'include' => [$data], 'page'=>1, 'per_page'=>10);
                    $req->set_query_params($params);
            		$response = $controller->get_items($req);
            		return array(
						'type' => $type,
						'data' => $response->get_data(),
					);
				}


				if($type == 'shop_order'){
                    if (isset($token)) {
                        $cookie = urldecode(base64_decode($token));
                    } else {
                        return parent::sendError("unauthorized", "You are not allowed to do this", 401);
                    }
                    $user_id = validateCookieLogin($cookie);
                    if (is_wp_error($user_id)) {
                        return $user_id;
                    }


					$api = new WC_REST_Orders_V1_Controller();
					$order = wc_get_order($data);
                    $customer_id = $order->get_user_id();
                    if($user_id != $customer_id){
                        return parent::sendError("unauthorized", "You are not allowed to do this", 401);
                    }
				    $response = $api->prepare_item_for_response($order, $request);
                    $order = $response->get_data();
                    $count = count($order["line_items"]);
                    $order["product_count"] = $count;
                    $line_items = array();
                    for ($i = 0; $i < $count; $i++) {
                        $image = wp_get_attachment_image_src(
                            get_post_thumbnail_id($product_id)
                        );
                        if (!is_null($image[0])) {
                            $order["line_items"][$i]["featured_image"] = $image[0];
                        }
                        $order_item = new WC_Order_Item_Product($order["line_items"][$i]["id"]);
                        $order["line_items"][$i]["meta"] = $order_item->get_meta_data();
                        if (is_plugin_active('wc-frontend-manager-delivery/wc-frontend-manager-delivery.php')) {
                            $table_name = $wpdb->prefix . "wcfm_delivery_orders";
                            $sql = "SELECT delivery_boy FROM `{$table_name}`";
                            $sql .= " WHERE 1=1";
                            $sql .= " AND product_id = %s";
                            $sql .= " AND order_id = %s";
                            $sql = $wpdb->prepare($sql, $product_id, $item->order_id);
                            $users = $wpdb->get_results($sql);

                            if (count($users) > 0) {
                                $user = get_userdata($users[0]->delivery_boy);
                                $order["line_items"][$i]['delivery_user'] = [
                                    "id" => $user->ID,
                                    "name" => $user->display_name,
                                    "profile_picture" => $profile_pic,
                                ];
                            }
                        }
                        $line_items[] = $order["line_items"][$i];
                    }
                    $order["line_items"] = $line_items;
              
                	return array(
						'type' => $type,
						'data' => [$order],
					);
				}
			}
		}
		return parent::sendError("invalid_data", "Invalid data", 400);
	}

    function check_upload_file_permission($request){
        $base_permission = parent::checkApiPermission();
        if(!$base_permission){
            return false;
        }
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return false;
            }
            return is_super_admin( $user_id );
        }
        return false;
    }

    /**
     * Check any prerequisites for our REST request.
     */
    private function check_prerequisites($request)
    {
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            wp_set_current_user($user_id);
        } elseif (isset($body['customer_id']) && $body['customer_id'] != null) {
            wp_set_current_user($body['customer_id']);
        }

        if (defined('WC_ABSPATH')) {
            // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
            include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
            include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
            include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
        }

        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

            WC()->session = new $session_class();
            WC()->session->init();
        }

        if (null === WC()->customer) {
            WC()->customer = new WC_Customer(get_current_user_id(), true);
        }

        if(get_current_user_id() != 0){
            cleanupAppointmentCartData(get_current_user_id());
        }
        if (null === WC()->cart) {
            WC()->cart = new WC_Cart();
        }
        WC()->cart->empty_cart(true);
        return true;
    }

    function get_product_from_dynamic_link($request)
    {
        if (isset($request['url'])) {
            $url = $request['url'];
            $langs = ["en", "ar", "vi"];
			foreach( $langs as $lang ) {
				$url = str_replace("/". $lang,"",$url);
			 }
            $product_id = url_to_postid($url);
            if (isset($product_id)) {
                $controller = new CUSTOM_WC_REST_Products_Controller();
                $req = new WP_REST_Request('GET');
                $params = array('id' => $product_id);
                $req->set_query_params($params);

                $response = $controller->get_item($req);
                $data = $response->get_data();
                return [$data];
            }else{
                return [];
            }
        }
        return parent::sendError("invalid_url", "Not Found", 404);
    }

    function get_product_category_from_dynamic_link($request)
    {
        if (isset($request['url'])) {
            $url = $request['url'];
            $items = explode("/", $url);
            $slug = null;
            for ($i = count($items) - 1; $i >= 0; $i--) {
                if (strlen($items[$i]) > 0) {
                    $slug = $items[$i];
                    break;
                }
            }
            $term = get_term_by('slug', $slug, 'product_cat');
            if ($term != false) {
                $controller = new WC_REST_Product_Categories_Controller();
                $req = new WP_REST_Request('GET');
                $params = array('include' => [$term->term_id], 'page'=>1, 'per_page'=>10);
                $req->set_query_params($params);
                $response = $controller->get_items($req);
                return $response->get_data();
            } else {
                return parent::sendError("invalid_url", "Not Found", 404);
            }
        }
    }

    /**
     * Add a product to the cart.
     *
     * @param int $product_id contains the id of the product to add to the cart.
     * @param int $quantity contains the quantity of the item to add.
     * @param int $variation_id ID of the variation being added to the cart.
     * @param array $variation attribute values.
     * @param array $cart_item_data extra cart item data we want to pass into the item.
     * @return string|bool $cart_item_key
     * @throws Exception Plugins can throw an exception to prevent adding to cart.
     */
    public function add_to_cart($product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array())
    {
        try {
            $product_id = absint($product_id);
            $variation_id = absint($variation_id);

            // Ensure we don't add a variation to the cart directly by variation ID.
            if ('product_variation' === get_post_type($product_id)) {
                $variation_id = $product_id;
                $product_id = wp_get_post_parent_id($variation_id);
            }

            $product_data = wc_get_product($variation_id ? $variation_id : $product_id);
            $quantity = apply_filters('woocommerce_add_to_cart_quantity', $quantity, $product_id);

            if ($quantity <= 0) {
                throw new Exception("The quantity must be a valid number greater than 0");
            }
            if (!$product_data) {
                throw new Exception("The product is not found");
            }
            if ('trash' === $product_data->get_status()) {
                throw new Exception("The product is trash");
            }

            // Load cart item data - may be added by other plugins.
            $cart_item_data = (array)apply_filters('woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity);

            // Generate a ID based on product ID, variation ID, variation data, and other cart item data.
            $cart_id = WC()->cart->generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

            // Find the cart item key in the existing cart.
            $cart_item_key = WC()->cart->find_product_in_cart($cart_id);

            // Force quantity to 1 if sold individually and check for existing item in cart.
            if ($product_data->is_sold_individually()) {
                $quantity = apply_filters('woocommerce_add_to_cart_sold_individually_quantity', 1, $quantity, $product_id, $variation_id, $cart_item_data);
                $found_in_cart = apply_filters('woocommerce_add_to_cart_sold_individually_found_in_cart', $cart_item_key && WC()->cart->cart_contents[$cart_item_key]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id);

                if ($found_in_cart) {
                    /* translators: %s: product name */
                    throw new Exception(sprintf('<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __('View cart', 'woocommerce'), sprintf(__('You cannot add another "%s" to your cart.', 'woocommerce'), $product_data->get_name())));
                }
            }

            // if (!$product_data->is_purchasable()) {
            //     $message = __('Sorry, this product cannot be purchased.', 'woocommerce');
            //     /**
            //      * Filters message about product unable to be purchased.
            //      *
            //      * @param string $message Message.
            //      * @param WC_Product $product_data Product data.
            //      * @since 3.8.0
            //      */
            //     $message = apply_filters('woocommerce_cart_product_cannot_be_purchased_message', $message, $product_data);
            //     throw new Exception($message);
            // }

            // Stock check - only check if we're managing stock and backorders are not allowed.
            if (!$product_data->is_in_stock()) {
                /* translators: %s: product name */
                throw new Exception(sprintf(__('You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'woocommerce'), $product_data->get_name()));
            }

            if (!$product_data->has_enough_stock($quantity)) {
                /* translators: 1: product name 2: quantity in stock */
                throw new Exception(sprintf(__('You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce'), $product_data->get_name(), wc_format_stock_quantity_for_display($product_data->get_stock_quantity(), $product_data)));
            }

            // Stock check - this time accounting for whats already in-cart.
            if ($product_data->managing_stock()) {
                $products_qty_in_cart = WC()->cart->get_cart_item_quantities();

                if (isset($products_qty_in_cart[$product_data->get_stock_managed_by_id()]) && !$product_data->has_enough_stock($products_qty_in_cart[$product_data->get_stock_managed_by_id()] + $quantity)) {
                    throw new Exception(
                        sprintf(
                            '<a href="%s" class="button wc-forward">%s</a> %s',
                            wc_get_cart_url(),
                            __('View cart', 'woocommerce'),
                            /* translators: 1: quantity in stock 2: current quantity */
                            sprintf(__('You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'woocommerce'), wc_format_stock_quantity_for_display($product_data->get_stock_quantity(), $product_data), wc_format_stock_quantity_for_display($products_qty_in_cart[$product_data->get_stock_managed_by_id()], $product_data))
                        )
                    );
                }
            }

            // If cart_item_key is set, the item is already in the cart.
            if ($cart_item_key) {
                $new_quantity = $quantity + WC()->cart->cart_contents[$cart_item_key]['quantity'];
                WC()->cart->set_quantity($cart_item_key, $new_quantity, false);
            } else {
                $cart_item_key = $cart_id;

                // Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
                WC()->cart->cart_contents[$cart_item_key] = apply_filters(
                    'woocommerce_add_cart_item',
                    array_merge(
                        $cart_item_data,
                        array(
                            'key' => $cart_item_key,
                            'product_id' => $product_id,
                            'variation_id' => $variation_id,
                            'variation' => $variation,
                            'quantity' => $quantity,
                            'data' => $product_data,
                            'data_hash' => wc_get_cart_item_data_hash($product_data),
                        )
                    ),
                    $cart_item_key
                );
            }

            WC()->cart->cart_contents = apply_filters('woocommerce_cart_contents_changed', WC()->cart->cart_contents);

            do_action('woocommerce_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data);

            return true;

        } catch (Exception $e) {
            if ($e->getMessage()) {
                return html_entity_decode(strip_tags($e->getMessage()));
            }
            return false;
        }
    }

    private function add_items_to_cart($products, $isValidate = true)
    {
        try {
            buildCartItemData($products, function($productId, $quantity, $variationId, $attributes, $cart_item_data){
                $error = $this->add_to_cart($productId, $quantity, $variationId, $attributes, $cart_item_data);
                 if (is_string($error) || $error == false) {
                    throw new Exception($error);
                }
            });
            return true;
        } catch (Exception $e) {
            if($isValidate){
                return $e->getMessage();
            }else{
                return true;
            }
            
        }
    }

    private function remove_item_in_cart() {
        $items = WC()->cart->get_cart();
        foreach ( $items as $item_key => $item ) {
            WC()->cart->remove_cart_item($item_key);
        }
    }

    public function shipping_methods($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $check = $this->check_prerequisites($request);
        if(is_wp_error($check)){
            return $check;
        }

        $shipping = $body["shipping"];
        WC()->customer->set_shipping_first_name($shipping["first_name"]);
        WC()->customer->set_shipping_last_name($shipping["last_name"]);
        WC()->customer->set_shipping_company($shipping["company"]);
        WC()->customer->set_shipping_address_1($shipping["address_1"]);
        WC()->customer->set_shipping_address_2($shipping["address_2"]);
        WC()->customer->set_shipping_city($shipping["city"]);
        WC()->customer->set_shipping_state($shipping["state"]);
        WC()->customer->set_shipping_postcode($shipping["postcode"]);
        WC()->customer->set_shipping_country($shipping["country"]);

        $error = $this->add_items_to_cart($body['line_items'], false);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }

        if(isset($body['coupon_lines']) && is_array($body['coupon_lines']) && count($body['coupon_lines']) > 0){
            WC()->cart->apply_coupon($body['coupon_lines'][0]['code']);
        }
        
        /* set calculation type if product is subscription to get shipping methods for subscription product have trial days */
        if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
            foreach ($body['line_items'] as $product) {
                $productId = absint($product['product_id']);
                $variationId = isset($product['variation_id']) ? absint($product['variation_id']) : 0;
                $product_data = wc_get_product($variationId != 0 ? $variationId : $productId);
                if (class_exists('WC_Subscriptions_Product') && WC_Subscriptions_Product::is_subscription($product_data)) {
                    WC_Subscriptions_Cart::set_calculation_type('recurring_total');
                    break;
                }
            }
        }

        if( apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ) ) {
			if ( !empty($shipping["wcfmmp_user_location"]) ) {
				WC()->customer->set_props( array( 'wcfmmp_user_location' => sanitize_text_field( $shipping["wcfmmp_user_location"] ) ) );
				WC()->session->set( '_wcfmmp_user_location', sanitize_text_field( $shipping["wcfmmp_user_location"] ) );
			}
			if ( !empty($shipping["wcfmmp_user_location_lat"]) ) {
				WC()->session->set( '_wcfmmp_user_location_lat', sanitize_text_field( $shipping['wcfmmp_user_location_lat'] ) );
			}
			if ( !empty( $shipping['wcfmmp_user_location_lng'] ) ) {
				WC()->session->set( '_wcfmmp_user_location_lng', sanitize_text_field( $shipping['wcfmmp_user_location_lng'] ) );
			}
		}

        $shipping_methods = WC()->shipping->calculate_shipping(WC()->cart->get_shipping_packages());
        $required_shipping = WC()->cart->needs_shipping() && WC()->cart->show_shipping();
        $this->remove_item_in_cart();

        if(count( $shipping_methods) == 0){
            return new WP_Error(400, 'No Shipping', array('required_shipping' => $required_shipping));
        }

        $results = [];
        foreach ($shipping_methods as $shipping_method) {
            $rates = $shipping_method['rates'];
            foreach ($rates as $rate) {
                $results[] = [
                    "id" => $rate->get_id(),
                    "method_id" => $rate->get_method_id(),
                    "instance_id" => $rate->get_instance_id(),
                    "label" => $rate->get_label(),
                    "cost" => $rate->get_cost(),
                    "taxes" => $rate->get_taxes(),
                    "shipping_tax" => $rate->get_shipping_tax()
                ];
            }
        }
        if(count( $results) == 0){
            return new WP_Error(400, 'No Shipping', array('required_shipping' => $required_shipping));
        }
        return $results;
    }

    public function payment_methods($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $check = $this->check_prerequisites($request);
        if(is_wp_error($check)){
            return $check;
        }

        $shipping = $body["shipping"];
        if (isset($shipping)) {
            WC()->customer->set_shipping_first_name($shipping["first_name"]);
            WC()->customer->set_shipping_last_name($shipping["last_name"]);
            WC()->customer->set_shipping_company($shipping["company"]);
            WC()->customer->set_shipping_address_1($shipping["address_1"]);
            WC()->customer->set_shipping_address_2($shipping["address_2"]);
            WC()->customer->set_shipping_city($shipping["city"]);
            WC()->customer->set_shipping_state($shipping["state"]);
            WC()->customer->set_shipping_postcode($shipping["postcode"]);
            WC()->customer->set_shipping_country($shipping["country"]);
        }
        //Fix to show COD based on the country for WooCommerce Multilingual & Multicurrency
        if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') && !is_plugin_active('elementor-pro/elementor-pro.php')){
			$_GET['wc-ajax'] = 'update_order_review';
            $_POST['country'] = $shipping["country"];
		}
        
        $error = $this->add_items_to_cart($body['line_items']);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }
        if(isset($body['coupon_lines']) && is_array($body['coupon_lines']) && count($body['coupon_lines']) > 0){
            WC()->cart->apply_coupon($body['coupon_lines'][0]['code']);
        }
        if (isset($body["shipping_lines"]) && !empty($body["shipping_lines"])) {
            $shippings = [];
            foreach ($body["shipping_lines"] as $shipping_line) {
                $shippings[] = $shipping_line["method_id"];
            }
            WC()->session->set('chosen_shipping_methods', $shippings);
        }
        $payment_methods = WC()->payment_gateways->get_available_payment_gateways();
        $this->remove_item_in_cart();
        $results = [];
        foreach ($payment_methods as $key => $value) {
            $results[] = ["id" => $value->id, "title" => $value->title, "method_title" => $value->method_title, "description" => $value->description];
        }
        return $results;
    }

    public function coupon($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $check = $this->check_prerequisites($request);
        if(is_wp_error($check)){
            return $check;
        }

        $error = $this->add_items_to_cart($body['line_items']);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            WC()->customer = new WC_Customer($user_id, true);
        }

        $coupon_code = $body["coupon_code"];

        // Sanitize coupon code.
        $coupon_code = wc_format_coupon_code($coupon_code);

        // Get the coupon.
        $the_coupon = new WC_Coupon($coupon_code);

        // Prevent adding coupons by post ID.
        if ($the_coupon->get_code() !== $coupon_code) {
            $the_coupon->set_code($coupon_code);
            return parent::sendError("invalid_coupon", $the_coupon->get_coupon_error(WC_Coupon::E_WC_COUPON_NOT_EXIST), 400);
        }

        // Check it can be used with cart.
        if (!$the_coupon->is_valid()) {
            return parent::sendError("invalid_coupon", html_entity_decode(strip_tags($the_coupon->get_error_message())), 400);
        }

        // Check if applied.
        if (WC()->cart->has_discount($coupon_code)) {
            WC()->cart->remove_coupons();
        }

        // If its individual use then remove other coupons.
        if ($the_coupon->get_individual_use()) {

            foreach (WC()->cart->applied_coupons as $applied_coupon) {
                $keep_key = array_search($applied_coupon, $coupons_to_keep, true);
                if (false === $keep_key) {
                    WC()->cart->remove_coupon($applied_coupon);
                } else {
                    unset($coupons_to_keep[$keep_key]);
                }
            }

            if (!empty($coupons_to_keep)) {
                WC()->cart->applied_coupons += $coupons_to_keep;
            }
        }

        WC()->cart->set_applied_coupons([$coupon_code]);
        WC()->cart->calculate_totals();

        $price = WC()->cart->get_coupon_discount_amount($the_coupon->get_code(), WC()->cart->display_cart_ex_tax);
        return ["coupon" => $this->get_formatted_coupon_data($the_coupon), "discount" => $price];
    }

    protected function get_formatted_coupon_data($object)
    {
        $data = $object->get_data();

        $format_decimal = array('amount', 'minimum_amount', 'maximum_amount');
        $format_date = array('date_created', 'date_modified', 'date_expires');
        $format_null = array('usage_limit', 'usage_limit_per_user', 'limit_usage_to_x_items');

        // Format decimal values.
        foreach ($format_decimal as $key) {
            $data[$key] = wc_format_decimal($data[$key], 2);
        }

        // Format date values.
        foreach ($format_date as $key) {
            $datetime = $data[$key];
            $data[$key] = wc_rest_prepare_date_response($datetime, false);
            $data[$key . '_gmt'] = wc_rest_prepare_date_response($datetime);
        }

        // Format null values.
        foreach ($format_null as $key) {
            $data[$key] = $data[$key] ? $data[$key] : null;
        }

        return array(
            'id' => $object->get_id(),
            'code' => $data['code'],
            'amount' => $data['amount'],
            'date_created' => $data['date_created'],
            'date_created_gmt' => $data['date_created_gmt'],
            'date_modified' => $data['date_modified'],
            'date_modified_gmt' => $data['date_modified_gmt'],
            'discount_type' => $data['discount_type'],
            'description' => $data['description'],
            'date_expires' => $data['date_expires'],
            'date_expires_gmt' => $data['date_expires_gmt'],
            'usage_count' => $data['usage_count'],
            'individual_use' => $data['individual_use'],
            'product_ids' => $data['product_ids'],
            'excluded_product_ids' => $data['excluded_product_ids'],
            'usage_limit' => $data['usage_limit'],
            'usage_limit_per_user' => $data['usage_limit_per_user'],
            'limit_usage_to_x_items' => $data['limit_usage_to_x_items'],
            'free_shipping' => $data['free_shipping'],
            'product_categories' => $data['product_categories'],
            'excluded_product_categories' => $data['excluded_product_categories'],
            'exclude_sale_items' => $data['exclude_sale_items'],
            'minimum_amount' => $data['minimum_amount'],
            'maximum_amount' => $data['maximum_amount'],
            'email_restrictions' => $data['email_restrictions'],
            'used_by' => $data['used_by'],
            'meta_data' => $data['meta_data'],
        );
    }

    public function get_cart($request)
    {
        $cookie = $request["cookie"];
        if (isset($request["token"])) {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        $user_id = validateCookieLogin($cookie);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Get an instance of the WC_Session_Handler Object
        $session_handler = new WC_Session_Handler();

        // Get the user session from its user ID:
        $session = $session_handler->get_session($user_id);

        if($session == false){
			return [];
		}
        
        // Get cart items array
        $cart_items = maybe_unserialize($session['cart']);

        $items = [];

        // Loop through cart items and get cart items details
        $product_controller = new WC_REST_Products_Controller();
        $product_variation_controller = new WC_REST_Product_Variations_Controller();
        if(is_array($cart_items)){
            foreach ($cart_items as $cart_item_key => $cart_item) {
                $product_id = $cart_item['product_id'];
                $variation_id = $cart_item['variation_id'];
                $quantity = $cart_item['quantity'];
    
                $product = wc_get_product($product_id);
                $product_data = $product_controller->prepare_object_for_response($product, $request)->get_data();
    
                if ($variation_id != 0) {
                    $variation = new WC_Product_Variation($variation_id);
                    $variation_data = $product_variation_controller->prepare_object_for_response($variation, $request)->get_data();
                } else {
                    $variation_data = null;
                }
                $items[] = ["product" => $product_data, "quantity" => $quantity, "variation" => $variation_data];
            }
        }

        return $items;
    }

    public function sync_cart_from_mobile($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        if (defined('WC_ABSPATH')) {
            // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
            include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
        }else{
            return parent::sendError("cookie_required","User-Cookie is required", 400);
        }

        $session_expiring = time() + intval(apply_filters('wc_session_expiring', 60 * 60 * 47)); // 47 Hours.
        $session_expiration = time() + intval(apply_filters('wc_session_expiration', 60 * 60 * 48)); // 48 Hours.
        $to_hash = $user_id . '|' . $session_expiration;
        $cookie_hash = hash_hmac('md5', $to_hash, wp_hash($to_hash));
        $_COOKIE['wp_woocommerce_session_' . COOKIEHASH] = $user_id . "||" . $session_expiration . "||" . $session_expiring . "||" . $cookie_hash;

        $user = get_userdata($user_id);
        wp_set_current_user($user_id, $user->user_login);
        wp_set_auth_cookie($user_id);

        // Get an instance of the WC_Session_Handler Object
        WC()->session = new WC_Session_Handler();
        WC()->session->init();

        WC()->customer = new WC_Customer(get_current_user_id(), true);

        WC()->cart = new WC_Cart();
        WC()->cart->empty_cart();

        buildCartItemData($body['line_items'], function($productId, $quantity, $variationId, $attributes, $cart_item_data){
            WC()->cart->add_to_cart($productId, $quantity, $variationId, $attributes, $cart_item_data);
        });

        return WC()->cart->get_totals();
    }

    public function upload_config_file($request){
		if (!isset($_FILES['file'])) {
			return parent::sendError("invalid_key","Key must be 'file'", 400);
		}
		$file = $_FILES['file'];
		if ($file["size"] == 0) {
			return parent::sendError("invalid_file","File is required", 400);
		}
		
		if ($file["type"] !== "application/json") {
			return parent::sendError("invalid_file","You need to upload json file", 400);
		}
		
        $errMsg = FlutterUtils::upload_file_by_admin($file);
		if ($errMsg != null) {
			return parent::sendError("invalid_file","You need to upload config_xx.json file", 400);
		}
        return FlutterUtils::get_json_file_url($file['name']);
	}

    public function get_taxes($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $check = $this->check_prerequisites($request);
        if(is_wp_error($check)){
            return $check;
        }

        $shipping = $body["shipping"];
        if (isset($shipping)) {
            WC()->customer->set_shipping_first_name($shipping["first_name"]);
            WC()->customer->set_shipping_last_name($shipping["last_name"]);
            WC()->customer->set_shipping_company($shipping["company"]);
            WC()->customer->set_shipping_address_1($shipping["address_1"]);
            WC()->customer->set_shipping_address_2($shipping["address_2"]);
            WC()->customer->set_shipping_city($shipping["city"]);
            WC()->customer->set_shipping_state($shipping["state"]);
            WC()->customer->set_shipping_postcode($shipping["postcode"]);
            WC()->customer->set_shipping_country($shipping["country"]);
        }

        $billing = $body["billing"];
        if (isset($billing)) {
            WC()->customer->set_billing_first_name($billing["first_name"]);
            WC()->customer->set_billing_last_name($billing["last_name"]);
            WC()->customer->set_billing_company($billing["company"]);
            WC()->customer->set_billing_address_1($billing["address_1"]);
            WC()->customer->set_billing_address_2($billing["address_2"]);
            WC()->customer->set_billing_city($billing["city"]);
            WC()->customer->set_billing_state($billing["state"]);
            WC()->customer->set_billing_postcode($billing["postcode"]);
            WC()->customer->set_billing_country($billing["country"]);
            WC()->customer->set_billing_email($billing["email"]);
            WC()->customer->set_billing_phone($billing["phone"]);
        }

        $error = $this->add_items_to_cart($body['line_items']);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }
        if(isset($body['coupon_lines']) && is_array($body['coupon_lines']) && count($body['coupon_lines']) > 0){
            WC()->cart->apply_coupon($body['coupon_lines'][0]['code']);
        }
        if (isset($body["shipping_lines"]) && !empty($body["shipping_lines"])) {
            $shippings = [];
            foreach ($body["shipping_lines"] as $shipping_line) {
                $shippings[] = $shipping_line["method_id"];
            }
            WC()->session->set('chosen_shipping_methods', $shippings);
        }

        $results = [];
        if (wc_tax_enabled()) {
            $taxable_address = WC()->customer->get_taxable_address();
            $estimated_text = '';

            if (WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping()) {
                /* translators: %s location. */
                $estimated_text = sprintf(esc_html__('(estimated for %s)', 'woocommerce'), WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]);
            }

            if ('itemized' === get_option('woocommerce_tax_total_display')) {
                foreach (WC()->cart->get_tax_totals() as $code => $tax) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                    $results[] = ["label" => $tax->label . " " . $estimated_text, "value" => $tax->amount];
                }
            } else {
                $results[] = ["label" => WC()->countries->tax_or_vat() . $estimated_text, "value" => WC()->cart->get_taxes_total()];
            }
			
			return ["items" => $results, "taxes_total" => count($results) > 0 ? WC()->cart->get_taxes_total() : "0", "is_including_tax" => WC()->cart->display_prices_including_tax()];
        }else{
			return ["items" => [], "taxes_total" => "0", "is_including_tax" => false];
		}
    }

    public function get_points($request)
    {
        if (!is_plugin_active('woocommerce-points-and-rewards/woocommerce-points-and-rewards.php')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Points and Rewards plugin to use this api");
        }

        $cookie = $request["cookie"];
        if (isset($request["token"])) {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        $user_id = validateCookieLogin($cookie);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        if ('yes' === get_option('wc_points_rewards_partial_redemption_enabled')) {
            $myPoints = WC_Points_Rewards_Manager::get_users_points($user_id);
            list($points, $monetary_value) = explode(':', get_option('wc_points_rewards_redeem_points_ratio', ''));
            $max_product_point_discount = get_option('wc_points_rewards_max_discount');
            $max_point_discount = get_option('wc_points_rewards_cart_max_discount');

            return ["points" => $myPoints, "cart_price_rate" => floatval($monetary_value), "cart_points_rate" => intval($points), "max_point_discount" => $max_point_discount, "max_product_point_discount" => $max_product_point_discount];
        } else {
            return parent::sendError("disabled_redemption", "Disabled partial redemption", 400);
        }
    }

    public function update_points($request)
    {
        if (!is_plugin_active('woocommerce-points-and-rewards/woocommerce-points-and-rewards.php')) {
            return parent::send_invalid_plugin_error("You need to install Points and Rewards for WooCommerce plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $order_id = $body["order_id"];
        $cookie = $body["cookie"];
        $user_id = validateCookieLogin($cookie);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $user = get_user_by('ID', $user_id);
        $user_email = $user->user_email;

        $get_points = WC_Points_Rewards_Manager::get_users_points($user_id);
        list($points, $monetary_value) = explode(':', get_option('wc_points_rewards_redeem_points_ratio', ''));
        $order = wc_get_order($order_id);
        if (isset($order) && !empty($order)) {
            /*Order Fees*/
            $order_fees = $order->get_fees();
            if (!empty($order_fees)) {
                foreach ($order_fees as $fee_item_id => $fee_item) {
                    $fee_id = $fee_item_id;
                    $fee_name = $fee_item->get_name();
                    $fee_amount = $fee_item->get_total();
                    if (isset($fee_name) && !empty($fee_name) && 'Cart Discount' == $fee_name) {
                        $fee_amount = -($fee_amount);
                        $fee_to_point = ceil((intval($points) * $fee_amount) / floatval($monetary_value));
                        $remaining_point = $get_points - $fee_to_point;
                        if ($remaining_point >= 0) {
                            /*update the users points in the*/
                            WC_Points_Rewards_Manager::set_points_balance($user_id, $remaining_point, 'order-redeem');
                        }
                    }
                }
            }
        }
        return true;
    }

    public function create_product_review($request)
    {
		$images = $request['images'];
        $controller = new WC_REST_Product_Reviews_Controller();
		$response = $controller->create_item($request);
		if(is_wp_error($response)){
			return array(
			'message'=>$response->get_error_message ());
		}
		$comment_id = $response->get_data()['id'];
        if(isset($request['comment_meta']) && is_array($request['comment_meta']) && !empty($request['comment_meta'])){
            foreach ($request['comment_meta'] as $key => $value) {
                add_comment_meta($comment_id, $key, $value, true);
            }
        }
		global $WCFMmp;
		if(apply_filters('wcfm_is_pref_vendor_reviews', true) && $WCFMmp){
			$WCFMmp->wcfmmp_reviews->wcfmmp_add_store_review( $comment_id );
		}    
		if(is_plugin_active('woo-photo-reviews/woo-photo-reviews.php') || is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php')){
            if(isset($images)){
                $images = $images;
				$images = array_filter(explode(',', $images));
                $count = 0;
                $img_arr = array();
				$user_id = get_comment($comment_id)->user_id;
                foreach($images as $image){
                    $img_id = upload_image_from_mobile($image, $count ,$user_id);
					$img_arr[] = $img_id;
					$count++;
                }
				update_comment_meta( $comment_id, 'reviews-images', $img_arr );
            }
        }
        $review = get_comment( $comment_id );
        $response = $controller->prepare_item_for_response( $review, $request );
        return $response;
    }

    public function get_ddates($request)
    {
        if (is_plugin_active('wc-frontend-manager-delivery/wc-frontend-manager-delivery.php')) {
            if (isset($request['id'])) {
                $helper = new FlutterWCFMHelper();
                return $helper->generate_vendor_delivery_time_checkout_field($request['id']);
            }else{
                return parent::sendError("required_vendor_id", "id is required", 400);
            }
        }else if (is_plugin_active('order-delivery-date/order_delivery_date.php')) {
            $number_of_dates = get_option('orddd_number_of_dates');
            $options = ORDDD_Functions::orddd_get_dates_for_dropdown($number_of_dates);
            $arr = array();
            foreach ($options as $k => $v) {
                if ($k == 'select') {
                    continue;
                }
                $date['timestamp'] = strtotime($k);
                $date['date'] = $k;
                $arr[] = $date;
            }
            return $arr;
        } else {
            return parent::send_invalid_plugin_error("You need to install Order Delivery Date for WooCommerce or WOOCOMMERCE FRONTEND MANAGER - DELIVERY plugin to use this api");
        }
    }

    function check_product($request){
        $params = $request->get_url_params();
		$token = sanitize_text_field($request['token']);
		$postid = sanitize_text_field($params['id']);

        if (!empty($token)) {
            $cookie = urldecode(base64_decode($token));
        }
        if(!empty($cookie)){
            $userid = validateCookieLogin($cookie);
            if (is_wp_error($userid)) {
                return $userid;
            }
            wp_set_current_user($userid);
        }else{
            wp_set_current_user(0);
        }

        if (!is_plugin_active('indeed-membership-pro/indeed-membership-pro.php')) {
            return parent::send_invalid_plugin_error("You need to install Ultimate Membership Pro plugin to use this api");
        }

        $meta_arr = ihc_post_metas($postid);
        $errMsg = null;
        if(isset($meta_arr['ihc_mb_type']) && $meta_arr['ihc_mb_type'] == 'block'){
            $errMsg = 'This item is blocked';
            return parent::sendError('blocked', $errMsg, 401);
        }else {
            if(isset($meta_arr['ihc_mb_who'])){
                //getting current user type and target user types
                $current_user = ihc_get_user_type();
                if($meta_arr['ihc_mb_who']!=-1 && $meta_arr['ihc_mb_who']!=''){
                    $target_users = explode(',', $meta_arr['ihc_mb_who']);
                } else {
                    $target_users = FALSE;
                }
                //test if current user must be redirect
                if($current_user=='admin'){
                     return true;//show always for admin
                }

                $result = ihc_test_if_must_block($meta_arr['ihc_mb_type'], $current_user, $target_users, $postid);

                if($result == 0){
                    return true;
                }
                if($result == 2){
                    $errMsg = 'This item is expired';
                }else {
                    $errMsg = 'This item is blocked';
                }

                if($meta_arr['ihc_mb_block_type']=='redirect'){
                    return parent::sendError($result == 2 ? 'expired' : 'blocked', $errMsg, 401);
                }else{
                    return parent::sendError('replace_content', $meta_arr['ihc_replace_content'], 401);
                }
            }
            return true;
        }
	}

    function get_blog_from_dynamic_link($request)
    {
        $helper = new FlutterBlogHelper();
        return $helper->get_blog_from_dynamic_link($request);
    }
    
    function create_blog($request){
		$helper = new FlutterBlogHelper();
        return $helper->create_blog($request);
	}

    function create_comment($request){
		$helper = new FlutterBlogHelper();
        return $helper->create_comment($request);
	}

    function get_product_rating_counts($request){
        $params = $request->get_url_params();
		$productId = sanitize_text_field($params['id']);
        $product = wc_get_product( $productId );
        $rating_1 = $product->get_rating_count(1);
        $rating_2 = $product->get_rating_count(2);
        $rating_3 = $product->get_rating_count(3);
        $rating_4 = $product->get_rating_count(4);
        $rating_5 = $product->get_rating_count(5);
        return ["rating_1" => $rating_1, "rating_2" => $rating_2, "rating_3" => $rating_3, "rating_4" => $rating_4, "rating_5" => $rating_5];
    }

    function get_products_video($request){
        global $wpdb;

        $page = 1;
        $per_page = 10;
        $lang = null;

        if (isset($request['page'])) {
            $page = sanitize_text_field($request['page']);
            if(!is_numeric($page)){
                $page = 1;
            }
        }
        if (isset($request['per_page'])) {
            $per_page = sanitize_text_field($request['per_page']);
            if(!is_numeric($per_page)){
                $per_page = 10;
            }
        }
        if (isset($request['lang'])) {
            $lang = sanitize_text_field($request['lang']);
        }
        $page = ($page - 1) * $per_page;

        $postmeta_table = $wpdb->prefix . "postmeta";
        $post_table = $wpdb->prefix . "posts";

        $sql = "SELECT $postmeta_table.post_id AS post_id";
        $sql .= " FROM $postmeta_table";
        $sql .= " INNER JOIN $post_table ON $post_table.ID = $postmeta_table.post_id";
        $sql .= " WHERE $postmeta_table.meta_key='_mstore_video_url' AND $postmeta_table.meta_value IS NOT NULL AND $postmeta_table.meta_value <> ''";
        $sql .= " AND $post_table.post_type = 'product' AND $post_table.post_status = 'publish'";
        $sql .= " ORDER BY $post_table.post_modified DESC";
        if($lang == null){
            $sql .= " LIMIT %d OFFSET %d";
            $sql = $wpdb->prepare($sql, $per_page, $page);
        }
       
        $items = $wpdb->get_results($sql);

        if(count($items) > 0){
            $controller = new CUSTOM_WC_REST_Products_Controller();
            $req = new WP_REST_Request('GET');
            $params = array('include' => array_map(function($item){
                return $item->post_id;
            }, $items), 'page' => 1, 'per_page' => count($items), 'orderby' => 'modified', 'order' => 'DESC');
            if($lang != null){
                $params['lang'] = $lang;
            }
            $req->set_query_params($params);
            $response = $controller->get_items($req);
            return $response->get_data();
        }else{
            return [];
        }
	}

    function get_min_max_prices($request)
    {
        global $wpdb;

        $sql = "SELECT MAX( CAST(meta_value AS UNSIGNED )) max_price, MIN( CAST(meta_value AS UNSIGNED )) min_price FROM {$wpdb->prefix}postmeta WHERE meta_key = '_price' AND post_id IN (
            SELECT ID
            FROM {$wpdb->prefix}posts
            WHERE post_type = 'product'
            AND post_status = 'publish'
        )";

        $result = $wpdb->get_results($sql);

        if (count($result) > 0) {
            return $result[0];
        } else {
            return new WP_Error(400, "Unable to get product price", array('status' => 400));
        }
    }

    function size_guide($request)
    {
        $params = $request->get_url_params();
        $product_id = sanitize_text_field($params['id']);

        if (function_exists('woodmart_sguide_display')) {
            // Support WoodMart - Multipurpose WooCommerce Theme
            ob_start();

            // We can clone and customize this function if needed instead of
            // using `ob_get_contents`
            woodmart_sguide_display($product_id);

            $result = ob_get_contents();
            ob_end_clean();
        }

        if ((!isset($result) || empty($result)) &&
            class_exists('PSCW_PRODUCT_SIZE_CHART_F_WOO_Front_end')
        ) {
            // Support Product Size Chart For WooCommerce Plugin:
            // https://wordpress.org/plugins/product-size-chart-for-woo/
            $result = $this->custom_product_tabs_content($product_id);
        }

        if (!isset($result) || empty($result)) {
            return parent::sendError("invalid_data", "Not found", 400);
        } else {
            return array(
                'data' => $result
            );
        }
    }

    /**
     * custom_product_tabs_content
     * 
     * Clone from function `custom_product_tabs_content` of
     * `PSCW_PRODUCT_SIZE_CHART_F_WOO_Front_end`
     * 
     * Plugin Product Size Chart For WooCommerce
     * https://wordpress.org/plugins/product-size-chart-for-woo/
     *
     * @param  mixed $post_id
     * @return String
     */
    private function custom_product_tabs_content($post_id)
    {
        $html = '';

        if (!class_exists('PSCW_PRODUCT_SIZE_CHART_F_WOO_Front_end')) {
            return $html;
        }

        // Initialize controller manually
        $controller = new PSCW_PRODUCT_SIZE_CHART_F_WOO_Front_end();
        $controller->option        = get_option('woo_sc_setting');
        $controller->option['position'] = 'product_tabs';

        $product_id           = $post_id;
        $sizechart_id         = $controller->woo_sc_function->get_posts_id();
        $sizechart_posts_data = [];
        if (!empty($sizechart_id) && is_array($sizechart_id)) {
            //rsort array size chart post id by DESC
            rsort($sizechart_id);
            foreach ($sizechart_id as $sizechart_post_id) {
                $sizechart_posts_data[$sizechart_post_id] = get_post_meta($sizechart_post_id, 'woo_sc_size_chart_data', true);
            }
            foreach ($sizechart_posts_data as $sc_id => $val) {

                $assign_product               = !empty($val['search_product']) ? $val['search_product'] : [];
                $assign_cate                  = !empty($val['categories']) ? $val['categories'] : [];
                $all_pro_id_in_assign_cate    = $controller->woo_sc_function->get_products_in_cate($assign_cate);
                $all_product_ids_in_sizechart = array_merge($all_pro_id_in_assign_cate, $assign_product);
                if (!empty($all_product_ids_in_sizechart) && is_array($all_product_ids_in_sizechart)) {
                    $unique_product_id = array_unique($all_product_ids_in_sizechart);
                }
                if (!empty($unique_product_id) && is_array($unique_product_id)) {
                    if (in_array($product_id, $unique_product_id)) {
                        $get_meta_box_data = $controller->get_meta_box_data($sc_id);
                        if (isset($get_meta_box_data['hide']) && $get_meta_box_data['hide'] !== 'hide_all') {
                            $html .=  wp_kses_post($controller->woo_sc_function->content_data($sc_id));
                        }
                        if ($controller->option['multi_sc'] == "0") {
                            break;
                        }
                    }
                }
            }
        }
        return $html;
    }
}

new FlutterWoo;
