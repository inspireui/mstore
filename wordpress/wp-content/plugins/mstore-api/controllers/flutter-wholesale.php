<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Midtrans
 */

class FlutterWholesale extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_wholesale';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_wholesale_routes'));
    }

    public function register_flutter_wholesale_routes()
    {
        register_rest_route($this->namespace, '/roles', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_roles'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/register', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'register'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function get_roles($request)
    {
        if (!class_exists('WooCommerceWholeSalePrices')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Wholesale Prices plugin to use this api");
        }
        global $wc_wholesale_prices;
        $data =  $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();
        $roles = [];
        $keys = array_keys($data);
        foreach ($keys as $key) {
            $roles[] = array_merge($data[$key], ['key'=>$key]);
        }
        return $roles;
    }

    public function register()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $usernameReq = $params["username"];
        $emailReq = $params["email"];
        $role = $params["role"];
        
        if (!class_exists('WooCommerceWholeSalePrices')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Wholesale Prices plugin to use this api");
        }
        global $wc_wholesale_prices;
        $data =  $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();
        $roles = array_keys($data);
        $roles[] = 'subscriber';
        if (!isset($role) || !in_array($role,$roles, true)) {
            return parent::sendError("invalid_role", "Role is invalid.", 400);
        }

        $username = sanitize_user($usernameReq);
        $email = sanitize_email($emailReq);

        $params["user_email"] = $email;
        $params["user_login"] = $usernameReq;
        $params["user_pass"] = $params['password'];

        if (!validate_username($username)) {
            return parent::sendError("invalid_username", "Username is invalid.", 400);
        } elseif (username_exists($username)) {
            return parent::sendError("existed_username", "Username already exists.", 400);
        } else {
            if (!is_email($email)) {
                return parent::sendError("invalid_email", "E-mail address is invalid.", 400);
            } elseif (email_exists($email)) {
                return parent::sendError("existed_email", "E-mail address is already in use.", 400);
            } else {
                $allowed_params = array('user_login', 'user_email', 'user_pass', 'display_name', 'user_nicename', 'user_url', 'nickname', 'first_name',
                    'last_name', 'description', 'rich_editing', 'user_registered', 'role', 'jabber', 'aim', 'yim',
                    'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front',
                );

                $dataRequest = $params;

                foreach ($dataRequest as $field => $value) {
                    if (in_array($field, $allowed_params)) {
                        $user[$field] = trim(sanitize_text_field($value));
                    }
                }

                $user['role'] = isset($params["role"]) ? sanitize_text_field($params["role"]) : get_option('default_role');
                $user_id = wp_insert_user($user);

                if (is_wp_error($user_id)) {
                    return parent::sendError($user_id->get_error_code(), $user_id->get_error_message(), 400);
                }
            }
        }
        wp_new_user_notification($user_id, null, 'both');
        $cookie = generateCookieByUserId($user_id);

        return array(
            "cookie" => $cookie,
            "user_id" => $user_id,
        );
    }
}

new FlutterWholesale;