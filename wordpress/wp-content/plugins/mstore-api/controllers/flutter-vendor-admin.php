<?php
require_once(__DIR__ . '/helpers/vendor-admin-woo-helper.php');
require_once(__DIR__ . '/helpers/vendor-admin-wcfm-helper.php');
require_once(__DIR__ . '/helpers/vendor-admin-dokan-helper.php');
require_once(__DIR__ . '/helpers/product-management.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
*/

class FlutterVendorAdmin extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'vendor-admin';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array(
            $this,
            'register_flutter_vendor_admin_routes'
        ));
    }

    public function register_flutter_vendor_admin_routes()
    {
        /// Product endpoints
        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'vendor_admin_get_products'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'vendor_admin_create_product'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => 'PUT',
                'callback' => array(
                    $this,
                    'vendor_admin_create_product'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => 'DELETE',
                'callback' => array(
                    $this,
                    'vendor_admin_delete_product'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        register_rest_route($this->namespace, '/products/attributes', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'vendor_admin_get_product_attributes'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        /// Order endpoints
        register_rest_route($this->namespace, '/vendor-orders', array(
            array(
                'methods' => "GET",
                'callback' => array(
                    $this,
                    'vendor_admin_get_orders'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        register_rest_route($this->namespace, '/vendor-orders', array(
            array(
                'methods' => "PUT",
                'callback' => array(
                    $this,
                    'vendor_admin_update_order_status'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        // Review endpoints
        register_rest_route($this->namespace, '/reviews', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'flutter_get_reviews_single_vendor'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        // Update review status
        register_rest_route($this->namespace, '/reviews/(?P<id>[\d]+)/', array(
            array(
                'methods' => "PUT",
                'callback' => array(
                    $this,
                    'flutter_update_review_status'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        /// Get Sale Stats
        register_rest_route($this->namespace, '/sale-stats', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'flutter_get_sale_stats'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        // Get notification
        register_rest_route($this->namespace, '/notifications', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'get_notification'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/profile', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array(
                    $this,
                    'get_vendor_profile'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/profile', array(
            array(
                'methods' => 'PUT',
                'callback' => array(
                    $this,
                    'update_vendor_profile'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        register_rest_route($this->namespace, '/delivery', array(
            array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'add_delivery_person_to_order'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));


        register_rest_route($this->namespace, '/delivery/get-users', array(
            array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_delivery_users'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function get_delivery_users($request)
    {
        $helper = new VendorAdminWCFMHelper();
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo' || $request['platform'] == 'dokan') {
                $args = array(
                    'role'    => 'driver',
                );
                $users = get_users( $args );

                $results = [];
                foreach ($users as $user) {
                    $avatar = get_user_meta($user->ID, 'user_avatar', true);
                    if (!isset($avatar) || $avatar == "" || is_bool($avatar)) {
                        $avatar = get_avatar_url($user->ID);
                    } else {
                        $avatar = $avatar[0];
                    }
                    $results[] = [
                        "id" => $user->ID,
                        "name" => $user->display_name,
                        "profile_picture" => $avatar,
                    ];
                }
        
                return new WP_REST_Response(
                    [
                        "status" => "success",
                        "response" => $results,
                    ],
                    200
                );
            }

        }
        return $helper->get_delivery_users($request['name']);
    }

    public function add_delivery_person_to_order($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo' || $request['platform'] == 'dokan') {
                $order_id = sanitize_text_field($request["wcfm_tracking_order_id"]);
                $delivery_boy = sanitize_text_field($request["wcfm_delivery_boy"]);
                $meta_key   = 'ddwc_driver_id';

                // Update driver ID for order.
                update_post_meta( $order_id, $meta_key, $delivery_boy );

                // Get order.
                $order = new WC_Order( $order_id );
                // Update order status.
                $order->update_status( 'driver-assigned' );
                return new WP_REST_Response(
                    [
                        "status" => "success",
                    ],
                    200
                );
            }

        }
        return $helper->wcfmd_delivery_boy_assigned($request, $user_id);
    }

    public function update_vendor_profile($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->update_vendor_profile($request['data'], $user_id);
    }

    public function get_vendor_profile($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->get_vendor_profile($user_id);
    }


    /// Edit product


    public function vendor_admin_delete_product($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->vendor_admin_delete_product($request, $user_id);
    }

    public function vendor_admin_update_product($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }

        return $helper->vendor_admin_update_product($request, $user_id);
    }

    // UPDATE ORDER STATUS
    public function vendor_admin_update_order_status($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }

        return $helper->flutter_update_order_status($request, $user_id);
    }


    // Update review
    public function flutter_update_review_status($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->flutter_update_review($request);
    }

    /* ---------------------------*/


    ///------ CREATE FUNCTIONS ------///
    public function vendor_admin_create_product($request)
    {

        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new ProductManagementHelper();
        return $helper->create_or_update_product($request, $user_id);
    }


    public function vendor_admin_create_coupon($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->vendor_admin_create_coupon($request, $user_id);
    }


    ///----- GET FUNCTIONS -----///
    public function vendor_admin_get_products($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new ProductManagementHelper();
        return new WP_REST_Response(array(
            'status' => 'success',
            'response' => $helper->get_products($request, $user_id),
        ), 200);
    }

    public function vendor_admin_get_orders($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            $is_admin = checkIsAdmin($user_id);
            if ($request['platform'] == 'woo' || $is_admin) {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->flutter_get_orders($request, $user_id);
    }

    public function get_notification($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->get_notification_by_vendor($request, $user_id);
    }

    public function flutter_get_reviews_single_vendor($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->flutter_get_reviews($request, $user_id);
    }

    public function vendor_admin_get_product_attributes($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $attributes = array();
        foreach ($attribute_taxonomies as $tax) {
            $data = [];
            if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
                $taxonomy_terms = get_terms(wc_attribute_taxonomy_name($tax->attribute_name), array('hide_empty' => false, 'orderby' => 'name'));
                $data['id'] = $tax->attribute_id;
                $data['label'] = $tax->attribute_label;
                $data['name'] =  $tax->labels->singular_name;
                foreach ($taxonomy_terms as $term) {
                    $data['options'][] = $term->name;
                    $data['slugs'][] = $term->slug;
                }
                $data['slug'] ='pa_'.$tax->attribute_name;
				$data['default'] = true;
                $attributes[] = $data;
               
            }
        }
        return $attributes;
    }

    public function flutter_get_sale_stats($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            $is_admin = checkIsAdmin($user_id);
            if ($request['platform'] == 'woo' || $is_admin) {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        return $helper->flutter_get_sale_stats($user_id);

    }


    ///----- UPDATE FUNCTIONS -----///

    public function update_review_status($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $helper = new VendorAdminWCFMHelper();
        if (isset($request['platform'])) {
            if ($request['platform'] == 'woo') {
                $helper = new VendorAdminWooHelper();
            }
            if ($request['platform'] == 'dokan') {
                $helper = new VendorAdminDokanHelper();
            }
        }
        $helper->flutter_update_review($request);
        return new WP_REST_Response(array(
            'status' => 'success',
        ), 200);
    }


    protected function authorize_user($token)
    {
        $token = sanitize_text_field($token);
        if (isset($token)) {
            $cookie = urldecode(base64_decode($token));
        } else {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = validateCookieLogin($cookie);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        return apply_filters("authorize_user", $user_id, $token);
    }

}

new FlutterVendorAdmin;

