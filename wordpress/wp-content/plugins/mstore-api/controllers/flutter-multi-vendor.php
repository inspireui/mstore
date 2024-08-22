<?php

class FlutterMultiVendor
{

    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_multi_vendor';

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
        register_rest_route($this->namespace, '/media', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'upload_image'),
                'args' => $this->get_params_upload(),
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));

        register_rest_route($this->namespace, '/product', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'flutter_create_product'),
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));

        register_rest_route($this->namespace, '/product' . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                )
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'flutter_delete_product'),
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));

        register_rest_route($this->namespace, '/products/owner', array(
            /// React Native apps use this api
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'flutter_get_products'),
                'permission_callback' => array($this, 'allow_permissions'),
            ),

            /// Flutter apps use this api
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_products'),
                'permission_callback' => array($this, 'allow_permissions'),
            ),
        ));

        register_rest_route($this->namespace, '/wcfm-stores', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_wcfm_stores'),
                'permission_callback' => array($this, 'custom_permissions_check'),
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
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));

        register_rest_route($this->namespace, '/shipping-methods', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'flutter_get_shipping_methods'),
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));

        register_rest_route($this->namespace, '/vendor-orders', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_vendor_orders'),
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));
        register_rest_route($this->namespace, '/get-nearby-stores', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_nearby_stores'),
                'permission_callback' => array($this, 'custom_permissions_check'),
            ),
        ));

        register_rest_route($this->namespace, '/product-categories', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_product_categories'),
                'permission_callback' => array($this, 'allow_permissions'),
            ),
        ));

        register_rest_route($this->namespace, '/get-reviews', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flutter_get_reviews'),
                'permission_callback' => array($this, 'allow_permissions'),
            ),
        ));
    }

    function allow_permissions()
    {
        return true;
    }

    function custom_permissions_check($request)
    {
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            return !is_wp_error($user_id);
        } else {
            return false;
        }
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
        $vendor = new FlutterVendor();
        return $vendor->upload_image($request);
    }

    public function flutter_create_product($request)
    {
        $request["cookie"] = $request->get_header("User-Cookie");
        $vendor = new FlutterVendor();
        return $vendor->flutter_create_product($request);
    }

    public function flutter_delete_product($request)
    {
        $request["cookie"] = $request->get_header("User-Cookie");
        $vendor = new FlutterVendor();
        return $vendor->flutter_delete_product($request);
    }

    public function flutter_get_products($request)
    {
        $request["cookie"] = $request->get_header("User-Cookie");
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_products($request);
    }

    public function flutter_get_wcfm_stores($request)
    {
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_wcfm_stores($request);
    }

    public function flutter_get_wcfm_stores_by_id($request)
    {
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_wcfm_stores_by_id($request);
    }

    public function flutter_get_shipping_methods($request)
    {
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_shipping_methods($request);
    }

    public function flutter_get_vendor_orders($request)
    {
        $request["cookie"] = $request->get_header("User-Cookie");
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_vendor_orders($request);
    }

    public function flutter_get_nearby_stores($request)
    {
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_nearby_stores($request);
    }

    public function flutter_get_product_categories($request)
    {
        $helper = new FlutterWCFMHelper();
        return $helper->get_product_categories($request);
    }

    public function flutter_get_reviews($request)
    {
        $vendor = new FlutterVendor();
        return $vendor->flutter_get_reviews($request);
    }
}

new FlutterMultiVendor();