<?php
require_once(__DIR__ . '/helpers/delivery-wcfm-helper.php');
require_once(__DIR__ . '/helpers/delivery-woo-helper.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
*/

class FlutterDelivery extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'delivery';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array(
            $this,
            'register_flutter_delivery_routes'
        ));
    }

    public function register_flutter_delivery_routes()
    {
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
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_delivery_profile'
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
                    'update_delivery_profile'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/orders', array(
            array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_delivery_orders'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/stores', array(
            array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_delivery_stores'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/orders/(?P<id>[\d]+)/', array(
            array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_delivery_order'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/orders', array(
            array(
                'methods' => 'PUT',
                'callback' => array(
                    $this,
                    'update_delivery_order'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/stat', array(
            array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_delivery_stat'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/offtime', array(
            array(
                'methods' => 'PUT',
                'callback' => array(
                    $this,
                    'set_off_time'
                ),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }


    function get_delivery_orders($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->get_delivery_orders($user_id, $request);
    }

    function get_delivery_stores($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->get_delivery_stores($user_id, $request);
    }

    function get_delivery_order($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->get_delivery_order($user_id, $request);
    }

    function get_delivery_stat($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->get_delivery_stat($user_id);
    }

    function get_notification($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->get_notification($request, $user_id);
    }

    public function update_delivery_profile($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->update_delivery_profile($request, $user_id);
    }

    public function update_delivery_order($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->update_delivery_order($request['order_id']);
    }

    public function get_delivery_profile($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->get_delivery_profile($user_id);
    }
    
    public function set_off_time($request)
    {
        $user_id = $this->authorize_user($request['token']);
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $helper = new DeliveryWCFMHelper();
        if ($request['platform'] == 'woo' ||$request['platform'] == 'dokan') {
            $helper = new DeliveryWooHelper();
        }
        return $helper->set_off_time($user_id,sanitize_text_field($request['is_available']));
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

new FlutterDelivery;

