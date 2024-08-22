<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Notification
 */

class FlutterNotification extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_notification';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_notification_routes'));
    }

    public function register_flutter_notification_routes()
    {
        register_rest_route($this->namespace, '/test_push_notification', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'test_push_notification'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/test_push_notification_created_order', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'test_push_notification_created_order'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/settings', array(
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_settings'),
                'permission_callback' => array($this, 'get_settings_permissions_check'),
            ),
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_settings'),
                'permission_callback' => array($this, 'get_settings_permissions_check'),
            ),
        ));
    }

    public function test_push_notification()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        $email = $params->email;
        $is_manager = $params->is_manager;
        $is_delivery = $params->is_delivery;
        $user = get_user_by('email', $email);
        $user_id = $user->ID;
        $is_onesignal = $params->is_onesignal;
        if($is_onesignal){
            $status = one_signal_push_notification("Fluxstore", "Test push notification", array($user_id));
            return ['status' => $status];
        }
        if (isset($is_manager)) {
            pushNotificationForVendor($user_id, "Fluxstore", "Test push notification");
        }else if (isset($is_delivery)) {
             pushNotificationForDeliveryBoy($user_id, "Fluxstore", "Test push notification");
        }else {
            pushNotificationForUser($user_id, "Fluxstore", "Test push notification");
        }
        return [];
    }

    function test_push_notification_created_order(){
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        return trackNewOrder($params->order_id);
    }

    function get_settings_permissions_check($request)
    {
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return false;
            }
            $request["user_id"] = $user_id;
            return true;
        } else {
            return false;
        }
    }

    function update_settings($request){
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $user_id = $request["user_id"];
        update_user_meta($user_id, "mstore_notification_status", $params['is_on'] == true ? 'on' : 'off');

        return ['is_on' => isNotificationEnabled($user_id)];
    }

    function get_settings($request){
        $user_id = $request["user_id"];
        return ['is_on' => isNotificationEnabled($user_id)];
    }
}

new FlutterNotification;