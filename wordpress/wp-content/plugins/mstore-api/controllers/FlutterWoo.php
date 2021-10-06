<?php
require_once(__DIR__ . '/FlutterBase.php');

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
    protected $namespace_oauth = 'wc-flutter-woo';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_woo_routes'));
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
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        );
        register_rest_route($this->namespace_oauth, '/config-file', $config_file);

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
    }

    /**
     * Check any prerequisites for our REST request.
     */
    private function check_prerequisites()
    {
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

        if (null === WC()->cart) {
            WC()->cart = new WC_Cart();
        }
        WC()->cart->empty_cart(true);
    }

    function get_product_from_dynamic_link($request)
    {
        if (isset($request['url'])) {
            $url = $request['url'];
            $product_id = url_to_postid($url);

            $product = wc_get_product($product_id);

            $controller = new CUSTOM_WC_REST_Products_Controller();
            $req = new WP_REST_Request('GET');
            $params = array('status' => 'published', 'include' => [$product_id]);
            $req->set_query_params($params);

            $response = $controller->get_items($req);
            return $response->get_data();
        }
        return parent::sendError("invalid_url", "Not Found", 404);
    }

    function get_blog_from_dynamic_link($request)
    {
        if (isset($request['url'])) {
            $url = $request['url'];
            $product_id = url_to_postid($url);
            if ($product_id) {
                $post = get_post($product_id);
                $controller = new WP_REST_Posts_Controller('post');
                $req = new WP_REST_Request('GET');
                $params = array('id' => $product_id);
                $req->set_query_params($params);
                $response = $controller->prepare_item_for_response($post, $req);
                $data = $controller->prepare_response_for_collection($response);
                return $data;
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
                $params = array('include' => [$term->term_id]);
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

            if (!$product_data->is_purchasable()) {
                $message = __('Sorry, this product cannot be purchased.', 'woocommerce');
                /**
                 * Filters message about product unable to be purchased.
                 *
                 * @param string $message Message.
                 * @param WC_Product $product_data Product data.
                 * @since 3.8.0
                 */
                $message = apply_filters('woocommerce_cart_product_cannot_be_purchased_message', $message, $product_data);
                throw new Exception($message);
            }

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
            foreach ($products as $product) {
                $productId = absint($product['product_id']);

                $quantity = $product['quantity'];
                $variationId = isset($product['variation_id']) ? $product['variation_id'] : "";

                $attributes = [];
                if (isset($product["meta_data"])) {
                    foreach ($product["meta_data"] as $item) {
                        $attributes[strtolower($item["key"])] = $item["value"];
                    }
                }

                // Check the product variation
                if (!empty($variationId)) {
                    $productVariable = new WC_Product_Variable($productId);
                    $listVariations = $productVariable->get_available_variations();
                    foreach ($listVariations as $vartiation => $value) {
                        if ($variationId == $value['variation_id']) {
                            $attributes = array_merge($value['attributes'], $attributes);
                            $error = $this->add_to_cart($productId, $quantity, $variationId, $attributes);
                            if ((is_string($error) || $error == false) && $isValidate) {
                                throw new Exception($error);
                            }
                        }
                    }
                } else {
                    parseMetaDataForBookingProduct($product);
                    $error = $this->add_to_cart($productId, $quantity, 0, $attributes);
                    if ((is_string($error) || $error == false) && $isValidate) {
                        throw new Exception($error);
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function shipping_methods($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $this->check_prerequisites();

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

        $shipping_methods = WC()->shipping->calculate_shipping(WC()->cart->get_shipping_packages());
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
        return $results;
    }

    public function payment_methods($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
            }
            wp_set_current_user($user_id);
        } elseif (isset($body['customer_id']) && $body['customer_id'] != null) {
            wp_set_current_user($body['customer_id']);
        }

        $this->check_prerequisites();

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

        $error = $this->add_items_to_cart($body['line_items']);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }
        if (isset($body["shipping_lines"]) && !empty($body["shipping_lines"])) {
            $shippings = [];
            foreach ($body["shipping_lines"] as $shipping_line) {
                $shippings[] = $shipping_line["method_id"];
            }
            WC()->session->set('chosen_shipping_methods', $shippings);
        }
        $payment_methods = WC()->payment_gateways->get_available_payment_gateways();
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

        $this->check_prerequisites();
        $error = $this->add_items_to_cart($body['line_items']);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }

        if (isset($body["customer_id"]) && $body["customer_id"] != null) {
            $userId = $body["customer_id"];
            $user = get_userdata($userId);
            if ($user) {
                wp_set_current_user($userId, $user->user_login);
                wp_set_auth_cookie($userId);
                WC()->customer = new WC_Customer($userId, true);
            }
        }

        $coupon_code = $body["coupon_code"];

        // Coupons are globally disabled.
        if (!wc_coupons_enabled()) {
            return parent::sendError("invalid_coupon", "Coupon is disabled", 400);
        }

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
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "Invalid cookie", 401);
        }

        // Get an instance of the WC_Session_Handler Object
        $session_handler = new WC_Session_Handler();

        // Get the user session from its user ID:
        $session = $session_handler->get_session($user_id);

        // Get cart items array
        $cart_items = maybe_unserialize($session['cart']);

        $items = [];

        // Loop through cart items and get cart items details
        $product_controller = new WC_REST_Products_Controller();
        $product_variation_controller = new WC_REST_Product_Variations_Controller();
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

        $user_id = $body["customer_id"];

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

        $products = $body['line_items'];
        foreach ($products as $product) {
            $productId = absint($product['product_id']);

            $quantity = $product['quantity'];
            $variationId = isset($product['variation_id']) ? $product['variation_id'] : "";

            $attributes = [];
            foreach ($product["meta_data"] as $item) {
                $attributes[$item["key"]] = $item["value"];
            }
            // Check the product variation
            if (!empty($variationId)) {
                $productVariable = new WC_Product_Variable($productId);
                $listVariations = $productVariable->get_available_variations();
                foreach ($listVariations as $vartiation => $value) {
                    if ($variationId == $value['variation_id']) {
                        $attributes = array_merge($value['attributes'], $attributes);
                        WC()->cart->add_to_cart($productId, $quantity, $variationId, $attributes);
                    }
                }
            } else {
                WC()->cart->add_to_cart($productId, $quantity, 0, $attributes);
            }
        }

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
		
		preg_match('/config_[a-z]{2}.json/', $file["name"], $output_array);
		if (count($output_array) == 0) {
			return parent::sendError("invalid_file","You need to upload config_xx.json file", 400);
		}
		$fileContent = file_get_contents($file['tmp_name']);
        $array = json_decode($fileContent, true);
		if($array){
			wp_upload_bits($file['name'], null, $fileContent); 
			$uploads_dir   = wp_upload_dir();
			$upload_dir = $uploads_dir["basedir"];
			$upload_url = $uploads_dir["baseurl"];
			$source      = $file['tmp_name'];
			$destination = trailingslashit( $upload_dir ) . '2000/01/'.$file['name'];
			if (!file_exists($upload_dir."/2000/01")) {
			  mkdir($upload_dir."/2000/01", 0777, true);
			}
			move_uploaded_file($source, $destination);
			return $upload_url."/2000/01/".$file['name'];
		}else{
			return parent::sendError("invalid_file","The config_xx.json file is invalid format", 400);
		}
	}

    public function get_taxes($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $this->check_prerequisites();

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

        $error = $this->add_items_to_cart($body['line_items']);
        if (is_string($error)) {
            return parent::sendError("invalid_item", $error, 400);
        }
        if (isset($body["shipping_lines"]) && !empty($body["shipping_lines"])) {
            $shippings = [];
            foreach ($body["shipping_lines"] as $shipping_line) {
                $shippings[] = $shipping_line["method_id"];
            }
            WC()->session->set('chosen_shipping_methods', $shippings);
        }

        $results = [];
        if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
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
        }
        if ('yes' === get_option('woocommerce_prices_include_tax')) {
            return ["items" => [], "taxes_total" => "0"];
        } else {
            return ["items" => $results, "taxes_total" => count($results) > 0 ? WC()->cart->get_taxes_total() : "0"];
        }
    }

    public function get_points($request)
    {
        if (!is_plugin_active('woocommerce-points-and-rewards/woocommerce-points-and-rewards.php')) {
            return parent::sendError("invalid_plugin", "You need to install WooCommerce Points and Rewards plugin to use this api", 404);
        }

        $cookie = $request["cookie"];
        if (isset($request["token"])) {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "Invalid cookie", 401);
        }
        if ('yes' === get_option('wc_points_rewards_partial_redemption_enabled')) {
            $myPoints = WC_Points_Rewards_Manager::get_users_points($user_id);
            list($points, $monetary_value) = explode(':', get_option('wc_points_rewards_redeem_points_ratio', ''));
            $max_product_point_discount = get_option('wc_points_rewards_max_discount');
            $max_point_discount = get_option('wc_points_rewards_cart_max_discount');

            return ["points" => $myPoints, "cart_price_rate" => floatval($monetary_value), "cart_points_rate" => intval($points), "max_point_discount" => intval($max_point_discount), "max_product_point_discount" => intval($max_product_point_discount)];
        } else {
            return parent::sendError("disabled_redemption", "Disabled partial redemption", 400);
        }
    }

    public function update_points($request)
    {
        if (!is_plugin_active('woocommerce-points-and-rewards/woocommerce-points-and-rewards.php')) {
            return parent::sendError("invalid_plugin", "You need to install Points and Rewards for WooCommerce plugin to use this api", 404);
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $order_id = $body["order_id"];
        $cookie = $body["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request.", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "Invalid cookie", 401);
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
        $controller = new WC_REST_Product_Reviews_Controller();
        return $controller->create_item($request);
    }

    public function get_ddates($request)
    {
        if (is_plugin_active('wc-frontend-manager-delivery/wc-frontend-manager-delivery.php')) {
            if (isset($request['id'])) {
                $helper = new FlutterWCFMHelper();
                return $helper->generate_vendor_delivery_time_checkout_field($request['id']);
            }
        }

        if (is_plugin_active('order-delivery-date/order_delivery_date.php')) {

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
        }
    }
}

new FlutterWoo;