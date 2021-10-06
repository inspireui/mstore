<?php
require_once(__DIR__ . '/helpers/WCFM.php');
require_once(__DIR__ . '/FlutterProducts.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
 */

class FlutterVendor extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'wc/v2/flutter';//prefix must be wc/ or wc- to reuse check permission function in woo commerce
    protected $namespace_v3 = 'wc/v3/flutter';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_vendor_routes'));
        add_filter('woocommerce_rest_prepare_product_object', array($this, 'prepeare_product_response'), 31, 3);
        add_filter('dokan_rest_prepare_product_object', array($this, 'prepeare_product_response'), 11, 3);
    }

    public function register_flutter_vendor_routes()
    {
        $media = array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'upload_image'),
                'args' => $this->get_params_upload(),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        );
        register_rest_route($this->namespace, '/media', $media);
        register_rest_route($this->namespace_v3, '/media', $media);

        $product = array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'flutter_create_product'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        );
        register_rest_route($this->namespace, '/product', $product);
        register_rest_route($this->namespace_v3, '/product', $product);

        register_rest_route($this->namespace, '/products/owner', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'flutter_get_products'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/wcfm-stores', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_wcfm_stores'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/wcfm-stores' . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the object.', 'wcfm-marketplace-rest-api'),
                    'type' => 'integer',
                )
            ),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'flutter_get_wcfm_stores_by_id'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/shipping-methods', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'flutter_get_shipping_methods'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/vendor-orders', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_vendor_orders'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/get-nearby-stores', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_nearby_stores'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/vendor/dynamic', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_vendor_from_dynamic_link'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/vendor/vacation', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_vendor_vacation_option'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/vendor/vacation', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'set_vendor_vacation_option'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }


    function get_vendor_vacation_option($request)
    {
        if (isset($request['store_id'])) {
            if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
                global $WCFM, $WCFMmp;
                $is_marketplace = wcfm_is_marketplace();
                $vendor_id = $request['store_id'];
                $type = get_user_meta($vendor_id, 'wcfm_vacation_mode_type', true);
                $vendor_has_vacation = $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability($vendor_id, 'vacation');
                if ($vendor_has_vacation) {
                    if ($is_marketplace == 'wcfmmarketplace') {
                        $vendor_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
                        $vacation_mode = isset($vendor_data['wcfm_vacation_mode']) ? $vendor_data['wcfm_vacation_mode'] : 'no';
                        $disable_vacation_purchase = isset($vendor_data['wcfm_disable_vacation_purchase']) ? $vendor_data['wcfm_disable_vacation_purchase'] : 'no';
                        $wcfm_vacation_mode_type = isset($vendor_data['wcfm_vacation_mode_type']) ? $vendor_data['wcfm_vacation_mode_type'] : 'instant';
                        $wcfm_vacation_start_date = isset($vendor_data['wcfm_vacation_start_date']) ? $vendor_data['wcfm_vacation_start_date'] : '';
                        $wcfm_vacation_end_date = isset($vendor_data['wcfm_vacation_end_date']) ? $vendor_data['wcfm_vacation_end_date'] : '';
                        $vacation_msg = !empty($vendor_data['wcfm_vacation_mode_msg']) ? $vendor_data['wcfm_vacation_mode_msg'] : '';
                    } else {
                        $vacation_mode = (get_user_meta($vendor_id, 'wcfm_vacation_mode', true)) ? get_user_meta($vendor_id, 'wcfm_vacation_mode', true) : 'no';
                        $disable_vacation_purchase = (get_user_meta($vendor_id, 'wcfm_disable_vacation_purchase', true)) ? get_user_meta($vendor_id, 'wcfm_disable_vacation_purchase', true) : 'no';
                        $wcfm_vacation_mode_type = (get_user_meta($vendor_id, 'wcfm_vacation_mode_type', true)) ? get_user_meta($vendor_id, 'wcfm_vacation_mode_type', true) : 'instant';
                        $wcfm_vacation_start_date = (get_user_meta($vendor_id, 'wcfm_vacation_start_date', true)) ? get_user_meta($vendor_id, 'wcfm_vacation_start_date', true) : '';
                        $wcfm_vacation_end_date = (get_user_meta($vendor_id, 'wcfm_vacation_end_date', true)) ? get_user_meta($vendor_id, 'wcfm_vacation_end_date', true) : '';
                        $vacation_msg = ($vacation_mode) ? get_user_meta($vendor_id, 'wcfm_vacation_mode_msg', true) : '';
                    }

                    $data = array(
                        'wcfm_vacation_mode' => $vacation_mode,
                        'wcfm_disable_vacation_purchase' => $disable_vacation_purchase,
                        'wcfm_vacation_mode_type' => $wcfm_vacation_mode_type,
                        'wcfm_vacation_start_date' => $wcfm_vacation_start_date,
                        'wcfm_vacation_end_date' => $wcfm_vacation_end_date,
                        'wcfm_vacation_mode_msg' => $vacation_msg,
                    );
                    return $data;
                }
                return [];
            }
            return parent::sendError("invalid_platform", "Dokan is not supported", 404);
        }
        return parent::sendError("invalid_id", "Not Found", 404);
    }

    function set_vendor_vacation_option($request)
    {
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }
        $user = get_userdata($user_id);
        $isSeller = in_array("seller", $user->roles) || in_array("wcfm_vendor", $user->roles) || in_array("administrator", $user->roles);
        if ($isSeller) {
            if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
                global $WCFM, $WCFMmp;
                $is_marketplace = wcfm_is_marketplace();
                $vendor_id = $user_id;
                $type = get_user_meta($vendor_id, 'wcfm_vacation_mode_type', true);
                $vendor_has_vacation = $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability($vendor_id, 'vacation');
                if ($vendor_has_vacation) {
                    $vacation_mode = $request['wcfm_vacation_mode'];
                    $disable_vacation_purchase = $request['wcfm_disable_vacation_purchase'];
                    $wcfm_vacation_mode_type = $request['wcfm_vacation_mode_type'];
                    $wcfm_vacation_start_date = ($vacation_mode) ? $request['wcfm_vacation_start_date'] : '';
                    $wcfm_vacation_end_date = ($vacation_mode) ? $request['wcfm_vacation_end_date'] : '';
                    $vacation_msg = ($vacation_mode) ? $request['wcfm_vacation_mode_msg'] : '';

                    if ($is_marketplace == 'wcfmmarketplace') {
                        $vendor_data = get_user_meta($vendor_id, 'wcfmmp_profile_settings', true);
                        $vendor_data['wcfm_vacation_mode'] = $vacation_mode;
                        $vendor_data['wcfm_disable_vacation_purchase'] = $disable_vacation_purchase;
                        $vendor_data['wcfm_vacation_mode_type'] = $wcfm_vacation_mode_type;
                        $vendor_data['wcfm_vacation_start_date'] = $wcfm_vacation_start_date;
                        $vendor_data['wcfm_vacation_end_date'] = $wcfm_vacation_end_date;
                        $vendor_data['wcfm_vacation_mode_msg'] = $vacation_msg;
                        update_user_meta($vendor_id, "wcfmmp_profile_settings", $vendor_data);
                    } else {
                        update_user_meta($vendor_id, "wcfm_vacation_mode", $vacation_mode);
                        update_user_meta($vendor_id, "wcfm_disable_vacation_purchase", $disable_vacation_purchase);
                        update_user_meta($vendor_id, "wcfm_vacation_mode_type", $wcfm_vacation_mode_type);
                        update_user_meta($vendor_id, "wcfm_vacation_start_date", $wcfm_vacation_start_date);
                        update_user_meta($vendor_id, "wcfm_vacation_end_date", $wcfm_vacation_end_date);
                        update_user_meta($vendor_id, "wcfm_vacation_mode_msg", $vacation_msg);
                    }

                    return true;
                }
                return false;
            }
            return parent::sendError("invalid_platform", "Dokan is not supported", 404);
        }
        return parent::sendError("invalid_role", "You can't do this", 401);

    }

    function get_vendor_from_dynamic_link($request)
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
            if (!is_null($slug)) {
                if (is_plugin_active('dokan-lite/dokan.php')) {
                    $user = get_user_by('slug', $slug);
                    if ($user) {
                        $store = dokan()->vendor->get($user->ID);
                        return $store->to_array();
                    }
                }
                if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
                    global $wpdb;
                    $results = [];
                    $table_name = $wpdb->prefix . "users";
                    $sql = "SELECT {$table_name}.ID";
                    $sql .= " FROM {$table_name}";
                    $sql .= " WHERE {$table_name}.user_nicename = '{$slug}' ";
                    $users = $wpdb->get_results($sql);
                    if (count($users) != 1) {
                        return parent::sendError("invalid_url", "Not Found", 404);
                    }
                    $helper = new FlutterWCFMHelper();
                    return $helper->flutter_get_wcfm_stores_by_id($users[0]->ID)->get_data();
                }
            }
        }
        return parent::sendError("invalid_url", "Not Found", 404);
    }

    public function get_params_upload()
    {
        $params = array(
            'media_attachment' => array(
                'required' => true,
                'description' => __('Image encoded as base64.', 'image-from-base64'),
                'type' => 'string'
            ),
            'title' => array(
                'required' => true,
                'description' => __('The title for the object.', 'image-from-base64'),
                'type' => 'json'
            ),
            'media_path' => array(
                'description' => __('Path to directory where file will be uploaded.', 'image-from-base64'),
                'type' => 'string'
            )
        );
        return $params;
    }

    public function upload_image($request)
    {
        $response = array();
        try {
            if (!empty($request['media_path'])) {
                $this->upload_dir = $request['media_path'];
                $this->upload_dir = '/' . trim($this->upload_dir, '/');
                add_filter('upload_dir', array($this, 'change_wp_upload_dir'));
            }

            if (!class_exists('WP_REST_Attachments_Controller')) {
                throw new Exception('WP API not installed.');
            }
            $media_controller = new WP_REST_Attachments_Controller('attachment');

            $filename = $request['title']['rendered'];

            $img = $request['media_attachment'];
            $decoded = base64_decode($img);

            // disable this check to use woocommerce keys with readonly permission
            // $permission_check = $media_controller->create_item_permissions_check( $request );
            // if( is_wp_error($permission_check) ){
            // 	throw new Exception( $permission_check->get_error_message() );
            // }

            $request->set_body($decoded);
            $request->add_header('Content-Disposition', "attachment;filename=\"{$filename}\"");
            $result = $media_controller->create_item($request);
            $response = rest_ensure_response($result);
        } catch (Exception $e) {
            $response['result'] = "error";
            $response['message'] = $e->getMessage();
        }

        if (!empty($request['media_path'])) {
            remove_filter('upload_dir', array($this, 'change_wp_upload_dir'));
        }

        return $response;
    }

    public function flutter_create_product($request)
    {
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }
        $user = get_userdata($user_id);
        $isSeller = in_array("seller", $user->roles) || in_array("wcfm_vendor", $user->roles) || in_array("administrator", $user->roles);

        $requestStatus = "draft";
        if ($request["status"] != null) {
            $requestStatus = $request["status"];
        }

        if ($isSeller) {
            $args = array(
                'post_author' => $user_id,
                'post_content' => $request["content"],
                'post_status' => $requestStatus, // (Draft | Pending | Publish)
                'post_title' => $request["title"],
                'post_parent' => '',
                'post_type' => "product"
            );
            // Create a simple WooCommerce product
            $post_id = wp_insert_post($args);
            $product = wc_get_product($post_id);

            if (isset($request['regular_price'])) {
                $product->set_regular_price($request['regular_price']);
            }

            // Sale Price.
            if (isset($request['sale_price'])) {
                $product->set_sale_price($request['sale_price']);
            }

            if (isset($request['date_on_sale_from'])) {
                $product->set_date_on_sale_from($request['date_on_sale_from']);
            }

            if (isset($request['date_on_sale_from_gmt'])) {
                $product->set_date_on_sale_from($request['date_on_sale_from_gmt'] ? strtotime($request['date_on_sale_from_gmt']) : null);
            }

            if (isset($request['date_on_sale_to'])) {
                $product->set_date_on_sale_to($request['date_on_sale_to']);
            }

            if (isset($request['date_on_sale_to_gmt'])) {
                $product->set_date_on_sale_to($request['date_on_sale_to_gmt'] ? strtotime($request['date_on_sale_to_gmt']) : null);
            }

            if (isset($request['image_ids'])) {
                update_post_meta($post_id, '_product_image_gallery', join(",", $request['image_ids']));
                if (count($request['image_ids']) > 0) {
                    set_post_thumbnail($post_id, $request['image_ids'][0]);
                }
            }

            wp_set_object_terms($post_id, isset($request['product_type']) ? $request['product_type'] : "simple", 'product_type');
            $product->save();
            $product = wc_get_product($post_id);
            if (isset($request["categories"]) && count($request["categories"]) > 0) {
                $product->set_category_ids([$request["categories"][0]["id"]]);
                $product->save();
            }
            return $product->get_data();
        } else {
            return parent::sendError("invalid_role", "You must be seller to create product", 401);
        }
    }

    public function flutter_delete_product($request)
    {
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $product_id = $request['id'];

        global $woocommerce, $wpdb;
        $table_name = $wpdb->prefix . "posts";
        $sql = "SELECT count(*) as count  FROM `$table_name` WHERE `$table_name`.`post_author` = $user_id AND `$table_name`.`post_type` = 'product' AND `$table_name`.`id` = $product_id LIMIT 1";
        $results = $wpdb->get_row($sql);
        if ($results->count == 1) {
            $controller = new CUSTOM_WC_REST_Products_Controller();
            $req = new WP_REST_Request('GET');
            $params = array('id' => $product_id, 'force' => true);
            $req->set_query_params($params);
            return $controller->delete_item($req);
        } else {
            return parent::sendError("invalid_product", "The product is invalid", 400);
        }
    }

    public function flutter_get_products($request)
    {
        $cookie = $request["cookie"];
        $id = $request["id"];
        if (!isset($cookie) && !isset($id)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $user_id = isset($id) ? $id : wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $page = isset($request["page"]) ? $request["page"] : 1;
        $page = $page - 1;
        $limit = isset($request["limit"]) ? $request["limit"] : 10;
        $page = $page * $limit;

        if (isset($id)) {
            $args = array(
                'author' => $user_id,
                'post_type' => 'product',
                'posts_per_page' => $limit,
                'offset' => $page,
                'post_status' => 'published',
            );

            if (isset($request['on_sale']) && $request['on_sale'] == 'true') {
                $args['meta_query'] = array(
                    'relation' => 'OR',
                    array( // Simple products type
                        'key' => '_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'numeric'
                    ),
                    array( // Variable products type
                        'key' => '_min_variation_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'numeric'
                    )
                );
            }

            if (isset($request['order']) && isset($request['orderby'])) {
                $args['meta_key'] = 'total_sales';
                $args['order'] = $request['order'];
                if ($request['orderby'] == 'popularity') {
                    $args['orderby'] = 'meta_value_num';
                    $args['meta_query'] = array(
                        array(
                            'key' => 'total_sales',
                            'value' => 0,
                            'compare' => '>'
                        )
                    );
                }
                if ($request['orderby'] == 'date') {
                    $args['orderby'] = 'date';
                }
            }

            if (isset($request['search'])) {
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                $args['s'] = $request['search'];
            }
            if (isset($request['category'])) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $request['category'],
                        'operator' => 'IN'
                    ),);
            }


            $products = get_posts($args);
        } else {
            global $woocommerce, $wpdb;
            $table_name = $wpdb->prefix . "posts";
            $sql = "SELECT * FROM `$table_name` WHERE `$table_name`.`post_author` = $user_id AND `$table_name`.`post_type` = 'product' LIMIT $limit OFFSET $page";
            $products = $wpdb->get_results($sql);
        }

        $ids = array();
        foreach ($products as $object) {
            $ids[] = $object->ID;
        }
        if (count($ids) > 0) {
            $controller = new CUSTOM_WC_REST_Products_Controller();
            $req = new WP_REST_Request('GET');
            $params = array('status' => isset($id) ? 'published' : 'any', 'include' => $ids);
            if (isset($request['lang'])) {
                $params['lang'] = $request['lang'];
            }
            $req->set_query_params($params);
            $response = $controller->get_items($req);
            return $response->get_data();
        } else {
            return [];
        }

    }

    public function flutter_get_wcfm_stores($request)
    {
        $helper = new FlutterWCFMHelper();
        return $helper->flutter_get_wcfm_stores($request);
    }

    public function flutter_get_wcfm_stores_by_id($request)
    {
        $helper = new FlutterWCFMHelper();
        $id = isset($request['id']) ? absint($request['id']) : 0;
        return $helper->flutter_get_wcfm_stores_by_id($id);
    }

    public function prepeare_product_response($response, $object, $request)
    {
        $response = customProductResponse($response, $object, $request);
        $data = $response->get_data();
        $author_id = get_post_field('post_author', $data['id']);
        if (is_plugin_active('dokan-lite/dokan.php')) {
            $store = dokan()->vendor->get($author_id);
            $dataStore = $store->to_array();
            $dataStore = array_merge($dataStore, apply_filters('dokan_rest_store_additional_fields', [], $store, $request));
            $data['store'] = $dataStore;
        }
        if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
            $helper = new FlutterWCFMHelper();
            $data['store'] = $helper->flutter_get_wcfm_stores_by_id($author_id)->get_data();
        }

        $response->set_data($data);
        return $response;
    }

    public function flutter_get_shipping_methods($request)
    {
        $json = file_get_contents('php://input');
        $package = json_decode($json, TRUE);
        $results = [];
        $controller = new WC_REST_Shipping_Zone_Methods_V2_Controller();
        $zone = WC_Shipping_Zones::get_zone_matching_package($package);
        $request['zone_id'] = $zone->get_id();

        if (class_exists('WeDevs\DokanPro\Shipping\ShippingZone')) {
            $seller_id = $package['seller_id'];
            $shipping_methods = WeDevs\DokanPro\Shipping\ShippingZone::get_shipping_methods($zone->get_id(), $seller_id);
            if (count($shipping_methods) == 0) {
                $shipping_methods = $controller->get_items($request);
                foreach ($shipping_methods->data as $method) {
                    if ($method['method_id'] != 'dokan_vendor_shipping') {
                        $results[] = $method;
                    }
                }
            } else {
                foreach ($shipping_methods as $key => $method) {
                    $results[] = $method;
                }
            }
        } else {
            $shipping_methods = $controller->get_items($request);
            return $shipping_methods->data;
        }
        return $results;
    }

    public function flutter_get_vendor_orders($request)
    {
        $cookie = $request["cookie"];
        if (isset($request["token"])) {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "You must include a 'cookie' or 'user id' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $api = new WC_REST_Orders_V1_Controller();
        $papi = new WC_REST_Products_Controller();
        $req = new WP_REST_Request('GET');

        $page = isset($request["page"]) ? $request["page"] : 1;
        $page = $page - 1;
        $limit = isset($request["per_page"]) ? $request["per_page"] : 10;
        $page = $page * $limit;

        $orders = [];
        $results = [];
        if (is_plugin_active('dokan-lite/dokan.php')) {
            $orders = dokan_get_seller_orders($user_id, 'all', null, 10000000, 0);
        }

        if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
            global $wpdb;
            $table_name = $wpdb->prefix . "wcfm_marketplace_orders";
            $orders = $wpdb->get_results("SELECT * FROM $table_name WHERE vendor_id = '$user_id' AND is_trashed != 1 ORDER BY order_id DESC LIMIT $page,$limit");
        }

        foreach ($orders as $item) {
            $order = wc_get_order($item->order_id);
            if ($order != false) {
                $response = $api->prepare_item_for_response($order, $request);
                $line_items = [];
                foreach ($response->data['line_items'] as $item) {
                    $product_id = $item['product_id'];
                    $product = get_post($product_id);
                    $product_author = $product->post_author;
                    if (absint($product_author) != absint($user_id)) {
                        continue;
                    }
                    $req->set_query_params(["id" => $product_id]);
                    $res = $papi->get_item($req);
                    if (is_wp_error($res)) {
                        $item["product_data"] = null;
                    } else {
                        $item["product_data"]['images'] = $res->get_data()['images'];
                    }
                    if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
                        $vendor_id = wcfm_get_vendor_id_by_post($product_id);
                        if ($vendor_id != $user_id) {
                            continue;
                        }
                    }
                    $line_items[] = $item;
                    $response->data['line_items'] = $line_items;
                }
                $results[] = $response->get_data();
            }
        }

        return $results;
    }

    public function flutter_get_nearby_stores($request)
    {
        if (is_plugin_active('dokan-lite/dokan.php') && is_plugin_active('dokan-pro/dokan-pro.php')) {
            $queries_data = array();
            parse_str($_SERVER['QUERY_STRING'], $queries_data);
            $sellers = dokan_get_sellers(apply_filters('dokan_seller_listing_args', $queries_data, $_GET));
            if ($sellers["count"] > 0) {
                $data_objects = [];
                $storeController = new WeDevs\Dokan\REST\StoreController();
                foreach ($sellers["users"] as $user) {
                    $vendor = dokan()->vendor->get($user->ID);
                    $stores_data = $storeController->prepare_item_for_response($vendor, $request);
                    $data_objects[] = $storeController->prepare_response_for_collection($stores_data);
                }

                $response = rest_ensure_response($data_objects);
                $response = $storeController->format_collection_response($response, $request, dokan()->vendor->get_total());

                return $response;
            } else {
                return [];
            }
        } else {
            return parent::sendError("invalid_plugin", "Please install Dokan Lite and Dokan Pro plugin", 400);
        }
    }

    public function flutter_get_reviews($request)
    {
        $page = $request['page'];
        $per_page = $request['per_page'];
        $store_id = $request['store_id'];
        $status = 1;

        if (!isset($store_id)) {
            return [];
        }
        if (!isset($page)) {
            $page = 1;
        }
        if (!isset($per_page)) {
            $per_page = 10;
        }
        if (!isset($request['status_type'])) {
            if ($request['status_type'] == 'approved') {
                $status = 1;
            } else {
                $status = 0;
            }

        }

        if (is_plugin_active('wcfm-marketplace-rest-api/wcfm-marketplace-rest-api.php')) {
            global $wpdb, $WCFM;
            $table_name = $wpdb->prefix . "wcfm_marketplace_reviews";
            $offset = ($page - 1) * $per_page;
            $sql = "SELECT * FROM $table_name WHERE vendor_id = $store_id AND approved = $status ORDER BY created DESC LIMIT $per_page OFFSET $offset";

            $reviews = $wpdb->get_results($sql);
            foreach ($reviews as $each_review) {
                $wp_user_avatar_id = get_user_meta($each_review->author_id, 'wp_user_avatar', true);
                $wp_user_avatar = wp_get_attachment_url($wp_user_avatar_id);
                if (!$wp_user_avatar) {
                    $wp_user_avatar = $WCFM->plugin_url . 'assets/images/avatar.png';
                }
                $each_review->author_image = $wp_user_avatar;
            }
            return $reviews;
        } else {
            return parent::sendError("invalid_plugin", "Please install WCFM Marketplace Rest API plugin", 400);
        }
    }


}

new FlutterVendor;