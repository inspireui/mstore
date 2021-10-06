<?php
require_once(dirname(__DIR__) . '/FlutterBase.php');

class FlutterMembership extends FlutterBaseController
{

    protected $namespace = 'api/flutter_membership';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_membership_routes'));
    }

    public function register_membership_routes()
    {
        register_rest_route($this->namespace, '/plans', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_plans'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/register', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'membership_register'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/payments', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_payments'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function get_plans()
    {
        if (is_plugin_active('indeed-membership-pro/indeed-membership-pro.php')) {
            $levels = \Indeed\Ihc\Db\Memberships::getAll();
            return array_values($levels);
        } else {
            return parent::sendError("plugin_not_found", "Please install Membership Pro Ultimate WP", 404);
        }
    }

    public function get_payments()
    {
        if (is_plugin_active('indeed-membership-pro/indeed-membership-pro.php')) {
            $payments = ihc_get_active_payments_services();
            return $payments;
        } else {
            return parent::sendError("plugin_not_found", "Please install Membership Pro Ultimate WP", 404);
        }
    }

    public function membership_register()
    {
        if (is_plugin_active('indeed-membership-pro/indeed-membership-pro.php')) {
            if (!class_exists('FlutterUserAddEdit')) {
                require_once(__DIR__ . '/FlutterUserAddEdit.class.php');
            }
            $nonce = wp_create_nonce('ihc_user_add_edit_nonce');
            $json = file_get_contents('php://input');
            $_POST = json_decode($json, TRUE);
            $_POST['ihc_user_add_edit_nonce'] = $nonce;
            $_REQUEST['ihc_payment_gateway_radio'] = $_POST['ihc_payment_gateway_radio'];
            $_REQUEST['ihc_payment_gateway'] = $_POST['ihc_payment_gateway'];
            $args = array(
                'user_id' => false,
                'type' => 'create',
                'tos' => true,
                'captcha' => true,
                'action' => '',
                'is_public' => true,
                'url' => $url,
            );
            $obj = new FlutterUserAddEdit();
            $obj->setVariable($args);//setting the object variables
            $res = $obj->save_update_user();
            if (is_wp_error($res)) {
                return $res;
            } else {
                return $res;
            }
        } else {
            return parent::sendError("plugin_not_found", "Please install Membership Pro Ultimate WP", 404);
        }

    }
}

new FlutterMembership;