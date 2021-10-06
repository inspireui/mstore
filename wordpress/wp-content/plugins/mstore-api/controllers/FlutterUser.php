<?php
require_once(__DIR__ . '/FlutterBase.php');

class FlutterUserController extends FlutterBaseController
{

    public function __construct()
    {
        $this->namespace = 'api/flutter_user';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/reset-password', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'reset_password'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/notification', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'chat_notification'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/sign_up', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'register'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/sign_up_2', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'register_2'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/register', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'register'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/generate_auth_cookie', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'generate_auth_cookie'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/fb_connect', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'fb_connect'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/sms_login', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'sms_login'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/firebase_sms_login', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'firebase_sms_login'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/firebase_sms_login_v2', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'firebase_sms_login_v2'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/apple_login', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'apple_login'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/google_login', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'google_login'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/post_comment', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'post_comment'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/get_currentuserinfo', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_currentuserinfo'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/get_points', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_points'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/update_user_profile', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'update_user_profile'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/checkout', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'prepare_checkout'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/get_currency_rates', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_currency_rates'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/get_countries', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_countries'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/get_states', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_states'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/check-user', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'check_user'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/test_push_notification', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'test_push_notification'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }


    public function check_user($request)
    {
        $phone = $request['phone'];
        $username = $request['username'];
        if (isset($phone)) {
            $args = array('meta_key' => 'registered_phone_number', 'meta_value' => $phone);
            $search_users = get_users($args);
            if (empty($search_users)) {
                return false;
            }
        }
        if (isset($username)) {
            if (strpos($username, '@')) {
                $user_data = get_user_by('email', trim(wp_unslash($username)));
            } else {
                $login = trim($username);
                $user_data = get_user_by('login', $login);
            }
            if (empty($user_data)) {
                return false;
            }
        }

        return true;
    }


    public function reset_password()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $usernameReq = $params["user_login"];

        $errors = new WP_Error();
        if (empty($usernameReq) || !is_string($usernameReq)) {
            return parent::sendError("empty_username", "Enter a username or email address.", 400);
        } elseif (strpos($usernameReq, '@')) {
            $user_data = get_user_by('email', trim(wp_unslash($usernameReq)));
            if (empty($user_data)) {
                return parent::sendError("invalid_email", "There is no account with that username or email address.", 404);
            }
        } else {
            $login = trim($usernameReq);
            $user_data = get_user_by('login', $login);
        }
        if (!$user_data) {
            return parent::sendError("invalid_email", "There is no account with that username or email address.", 404);
        }

        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = get_password_reset_key($user_data);

        if (is_wp_error($key)) {
            return $key;
        }

        if (is_multisite()) {
            $site_name = get_network()->site_name;
        } else {
            $site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }

        $message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
        $message .= sprintf(__('Site Name: %s'), $site_name) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
        $title = sprintf(__('[%s] Password Reset'), $site_name);
        $title = apply_filters('retrieve_password_title', $title, $user_login, $user_data);
        $message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data);

        if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message)) {
            return parent::sendError("retrieve_password_email_failure", "The email could not be sent. Your site may not be correctly configured to send emails.", 401);
        }

        return new WP_REST_Response(array(
            'status' => 'success',
        ), 200);;
    }

    public function register()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $usernameReq = $params["username"];
        $emailReq = $params["email"];
        $role = $params["role"];
        if (isset($role)) {
            if (!in_array($role, ['subscriber', 'wcfm_vendor', 'seller', 'wcfm_delivery_boy', 'driver'], true)) {
                return parent::sendError("invalid_role", "Role is invalid.", 400);
            }
        }
        $userPassReq = $params["user_pass"];
        $userLoginReq = $params["user_login"];
        $userEmailReq = $params["user_email"];

        $username = sanitize_user($usernameReq);

        $email = sanitize_email($emailReq);
        if (isset($params["seconds"])) {
            $seconds = (int)$params["seconds"];
        } else {
            $seconds = 1209600;
        }

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
                if (!$userPassReq) {
                    $params->user_pass = wp_generate_password();
                }

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
                } elseif (isset($params["phone"])) {
                    update_user_meta($user_id, 'billing_phone', $params["phone"]);
                    update_user_meta($user_id, 'registered_phone_number', $params["phone"]);
                    wp_new_user_notification($user_id, '', '');
                }
            }
        }

        $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user_id, true);
        $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

        return array(
            "cookie" => $cookie,
            "user_id" => $user_id,
        );
    }


    public function register_2()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $usernameReq = $params["username"];
        $emailReq = $params["email"];
        $role = $params["role"];
        if (isset($role)) {
            if (!in_array($role, ['subscriber', 'wcfm_vendor', 'seller', 'wcfm_delivery_boy', 'driver'], true)) {
                return parent::sendError("invalid_role", "Role is invalid.", 400);
            }
        }
        $userPassReq = $params["user_pass"];
        $userLoginReq = $params["user_login"];
        $userEmailReq = $params["user_email"];
        $username = sanitize_user($usernameReq);

        if ($username == $userEmailReq && $username == $userLoginReq) {
            $is_email = is_email($username);
            if ($is_email) {
                $email = $username;
                $user_name = explode("@", $email)[0];
                $params["user_email"] = $email;
                $params["user_login"] = $user_name;
            } else {
                $user_name = $username;
                $params["user_login"] = $user_name;
                $params["user_email"] = '';
            }

            if (!validate_username($user_name)) {
                return parent::sendError("invalid_username", "Username is invalid.", 400);
            }
            if (username_exists($user_name)) {
                return parent::sendError("existed_username", "Username already exists.", 400);
            }
            if (isset($email)) {
                if (!is_email($email)) {
                    return parent::sendError("invalid_email", "E-mail address is invalid.", 400);
                }

                if (email_exists($email)) {
                    return parent::sendError("existed_email", "E-mail address is already in use.", 400);
                }
            }
            if (!$userPassReq) {
                $params["user_pass"] = wp_generate_password();
            }
        }

        if (isset($params["seconds"])) {
            $seconds = (int)$params["seconds"];
        } else {
            $seconds = 1209600;
        }
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
        } elseif (isset($params["phone"])) {
            update_user_meta($user_id, 'billing_phone', $params["phone"]);
            update_user_meta($user_id, 'registered_phone_number', $params["phone"]);
            wp_new_user_notification($user_id, '', '');
        }


        $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user_id, true);
        $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

        return array(
            "cookie" => $cookie,
            "user_id" => $user_id,
        );
    }

    private function get_shipping_address($userId)
    {
        $shipping = [];

        $shipping["first_name"] = get_user_meta($userId, 'shipping_first_name', true);
        $shipping["last_name"] = get_user_meta($userId, 'shipping_last_name', true);
        $shipping["company"] = get_user_meta($userId, 'shipping_company', true);
        $shipping["address_1"] = get_user_meta($userId, 'shipping_address_1', true);
        $shipping["address_2"] = get_user_meta($userId, 'shipping_address_2', true);
        $shipping["city"] = get_user_meta($userId, 'shipping_city', true);
        $shipping["state"] = get_user_meta($userId, 'shipping_state', true);
        $shipping["postcode"] = get_user_meta($userId, 'shipping_postcode', true);
        $shipping["country"] = get_user_meta($userId, 'shipping_country', true);
        $shipping["email"] = get_user_meta($userId, 'shipping_email', true);
        $shipping["phone"] = get_user_meta($userId, 'shipping_phone', true);

        if (empty($shipping["first_name"]) && empty($shipping["last_name"]) && empty($shipping["company"]) && empty($shipping["address_1"]) && empty($shipping["address_2"]) && empty($shipping["city"]) && empty($shipping["state"]) && empty($shipping["postcode"]) && empty($shipping["country"]) && empty($shipping["email"]) && empty($shipping["phone"])) {
            return null;
        }
        return $shipping;
    }

    private function get_billing_address($userId)
    {
        $billing = [];

        $billing["first_name"] = get_user_meta($userId, 'billing_first_name', true);
        $billing["last_name"] = get_user_meta($userId, 'billing_last_name', true);
        $billing["company"] = get_user_meta($userId, 'billing_company', true);
        $billing["address_1"] = get_user_meta($userId, 'billing_address_1', true);
        $billing["address_2"] = get_user_meta($userId, 'billing_address_2', true);
        $billing["city"] = get_user_meta($userId, 'billing_city', true);
        $billing["state"] = get_user_meta($userId, 'billing_state', true);
        $billing["postcode"] = get_user_meta($userId, 'billing_postcode', true);
        $billing["country"] = get_user_meta($userId, 'billing_country', true);
        $billing["email"] = get_user_meta($userId, 'billing_email', true);
        $billing["phone"] = get_user_meta($userId, 'billing_phone', true);

        if (empty($billing["first_name"]) && empty($billing["last_name"]) && empty($billing["company"]) && empty($billing["address_1"]) && empty($billing["address_2"]) && empty($billing["city"]) && empty($billing["state"]) && empty($billing["postcode"]) && empty($billing["country"]) && empty($billing["email"]) && empty($billing["phone"])) {
            return null;
        }

        return $billing;
    }

    function getResponseUserInfo($user)
    {
        $shipping = $this->get_shipping_address($user->ID);
        $billing = $this->get_billing_address($user->ID);
        $avatar = get_user_meta($user->ID, 'user_avatar', true);
        if (!isset($avatar) || $avatar == "" || is_bool($avatar)) {
            $avatar = get_avatar_url($user->ID);
        } else {
            $avatar = $avatar[0];
        }
        return array(
            "id" => $user->ID,
            "username" => $user->user_login,
            "nicename" => $user->user_nicename,
            "email" => $user->user_email,
            "url" => $user->user_url,
            "registered" => $user->user_registered,
            "displayname" => $user->display_name,
            "firstname" => $user->user_firstname,
            "lastname" => $user->last_name,
            "nickname" => $user->nickname,
            "description" => $user->user_description,
            "capabilities" => $user->wp_capabilities,
            "role" => $user->roles,
            "shipping" => $shipping,
            "billing" => $billing,
            "avatar" => $avatar,
            "dokan_enable_selling" => $user->dokan_enable_selling
        );
    }

    public function generate_auth_cookie()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        if (!isset($params["username"]) || !isset($params["password"])) {
            return parent::sendError("invalid_login", "Invalid params", 400);
        }
        $username = $params["username"];
        $password = $params["password"];


        if (isset($params["seconds"])) {
            $seconds = (int)$params["seconds"];
        } else {
            $seconds = 1209600;
        }

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            return parent::sendError($user->get_error_code(), "Invalid username/email and/or password.", 401);
        }

        $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);
        $cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');

        return array(
            "cookie" => $cookie,
            "cookie_name" => LOGGED_IN_COOKIE,
            "user" => $this->getResponseUserInfo($user),
        );
    }

    function createSocialAccount($email, $name, $firstName, $lastName, $userName)
    {
        $email_exists = email_exists($email);
        if ($email_exists) {
            $user = get_user_by('email', $email);
            $user_id = $user->ID;
        } else {
            $i = 0;
            while (username_exists($userName)) {
                $i++;
                $userName = strtolower($userName) . '.' . $i;
            }
            $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
            $userdata = array(
                'user_login' => $userName,
                'user_email' => $email,
                'user_pass' => $random_password,
                'display_name' => $name,
                'first_name' => $firstName,
                'last_name' => $lastName);
            $user_id = wp_insert_user($userdata);
        }

        $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, true);
        $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
        $user = get_userdata($user_id);

        $response['wp_user_id'] = $user_id;
        $response['cookie'] = $cookie;
        $response['user_login'] = $user->user_login;
        $response['user'] = $this->getResponseUserInfo($user);
        return $response;
    }

    public function fb_connect($request)
    {
        $fields = 'id,name,first_name,last_name,email';
        $enable_ssl = true;
        $access_token = $request["access_token"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login", "You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.", 400);
        }
        $url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token=' . $access_token;

        $result = wp_remote_retrieve_body(wp_remote_get($url));

        $result = json_decode($result, true);

        if (isset($result["email"])) {
            $user_name = strtolower($result['first_name'] . '.' . $result['last_name']);
            return $this->createSocialAccount($result["email"], $result['name'], $result['first_name'], $result['last_name'], $user_name);
        } else {
            return parent::sendError("invalid_login", "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.", 400);
        }
    }

    public function sms_login($request)
    {
        $access_token = $request["access_token"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login", "You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.", 400);
        }
        $url = 'https://graph.accountkit.com/v1.3/me/?access_token=' . $access_token;

        $WP_Http_Curl = new WP_Http_Curl();
        $result = $WP_Http_Curl->request($url, array(
            'method' => 'GET',
            'timeout' => 5,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => null,
            'cookies' => array(),
        ));

        $result = json_decode($result, true);

        if (isset($result["phone"])) {
            $user_name = $result["phone"]["number"];
            $user_email = $result["phone"]["number"] . "@flutter.io";
            return $this->createSocialAccount($user_email, $user_name, $user_name, "", $user_name);
        } else {
            return parent::sendError("invalid_login", "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.", 400);
        }
        return $response;

    }

    public function firebase_sms_login($request)
    {
        $phone = $request["phone"];
        if (!isset($phone)) {
            return parent::sendError("invalid_login", "You must include a 'phone' variable.", 400);
        }
        $domain = $_SERVER['SERVER_NAME'];
        if (count(explode(".", $domain)) == 1) {
            $domain = "flutter.io";
        }
        $user_name = $phone;
        $user_email = $phone . "@" . $domain;
        return $this->createSocialAccount($user_email, $user_name, $user_name, "", $user_name);
    }

    public function firebase_sms_login_v2($request)
    {
        $phone = $request["phone"];
        if (!isset($phone)) {
            return parent::sendError("invalid_login", "You must include a 'phone' variable.", 400);
        }

        if (isset($phone)) {
            $args = array('meta_key' => 'registered_phone_number', 'meta_value' => $phone);
            $search_users = get_users($args);
            if (empty($search_users)) {
                $domain = $_SERVER['SERVER_NAME'];
                if (count(explode(".", $domain)) == 1) {
                    $domain = "flutter.io";
                }
                $user_name = $phone;
                $user_email = $phone . "@" . $domain;
                $user = get_user_by('email', $user_email);
                if (!$user) {
                    return parent::sendError("invalid_login", "User does not exist", 400);
                }
                $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user->ID, true);
                $cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
                $response['wp_user_id'] = $user->ID;
                $response['cookie'] = $cookie;
                $response['user_login'] = $user->user_login;
                $response['user'] = $this->getResponseUserInfo($user);
                return $response;
            }
            if (count($search_users) > 1) {
                return parent::sendError("invalid_login", "Too many users with the same phone number", 400);
            }
            $user = $search_users[0];
            $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user->ID, true);
            $cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
            $response['wp_user_id'] = $user->ID;
            $response['cookie'] = $cookie;
            $response['user_login'] = $user->user_login;
            $response['user'] = $this->getResponseUserInfo($user);
            return $response;
        }
        return parent::sendError("invalid_login", "Unknown Error", 400);
    }


    function jwtDecode($token)
    {
        $splitToken = explode(".", $token);
        $payloadBase64 = $splitToken[1]; // Payload is always the index 1
        $decodedPayload = json_decode(urldecode(base64_decode($payloadBase64)), true);
        return $decodedPayload;
    }

    public function apple_login($request)
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $token = $params["token"];
        $decoded = $this->jwtDecode($token);
        $user_email = $decoded["email"];
        if (!isset($user_email)) {
            return parent::sendError("invalid_login", "Can't get the email to create account.", 400);
        }
        $display_name = explode("@", $user_email)[0];
        $user_name = $display_name;

        return $this->createSocialAccount($user_email, $display_name, $display_name, "", $user_name);
    }

    public function google_login($request)
    {
        $access_token = $request["access_token"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login", "You must include a 'access_token' variable. Get the valid access_token for this app from Google API.", 400);
        }

        $url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=' . $access_token;

        $result = wp_remote_retrieve_body(wp_remote_get($url));

        $result = json_decode($result, true);
        if (isset($result["email"])) {
            $firstName = $result["given_name"];
            $lastName = $result["family_name"];
            $email = $result["email"];
            $display_name = $firstName . " " . $lastName;
            $user_name = $firstName . "." . $lastName;
            return $this->createSocialAccount($email, $display_name, $firstName, $lastName, $user_name);
        } else {
            return parent::sendError("invalid_login", "Your 'token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Google app.", 400);
        }
    }

    /*
     * Post commment function
     */
    public function post_comment($request)
    {
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "Invalid cookie. Use the `generate_auth_cookie` method.", 401);
        }
        if (!$request["post_id"]) {
            return parent::sendError("invalid_data", "No post specified. Include 'post_id' var in your request.", 400);
        } elseif (!$request["content"]) {
            return parent::sendError("invalid_data", "Please include 'content' var in your request.", 400);
        }

        $comment_approved = 0;
        $user_info = get_userdata($user_id);
        $time = current_time('mysql');
        $agent = filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT') ? filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') : 'Mozilla';
        $ips = filter_has_var(INPUT_SERVER, 'REMOTE_ADDR') ? filter_input(INPUT_SERVER, 'REMOTE_ADDR') : '127.0.0.1';
        $data = array(
            'comment_post_ID' => $request["post_id"],
            'comment_author' => $user_info->user_login,
            'comment_author_email' => $user_info->user_email,
            'comment_author_url' => $user_info->user_url,
            'comment_content' => $request["content"],
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => $user_info->ID,
            'comment_author_IP' => $ips,
            'comment_agent' => $agent,
            'comment_date' => $time,
            'comment_approved' => $comment_approved,
        );
        //print_r($data);
        $comment_id = wp_insert_comment($data);
        //add metafields
        $meta = json_decode(stripcslashes($request["meta"]), true);
        //extra function
        add_comment_meta($comment_id, 'rating', $meta['rating']);
        add_comment_meta($comment_id, 'verified', 0);

        return array(
            "comment_id" => $comment_id,
        );
    }

    public function get_currentuserinfo($request)
    {
        $cookie = $request["cookie"];
        if (isset($request["token"])) {
            $cookie = urldecode(base64_decode($request["token"]));
        }
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_token", "Invalid cookie", 401);
        }
        $user = get_userdata($user_id);
        return array(
            "user" => $this->getResponseUserInfo($user)
        );
    }

    /**
     * Get Point Reward by User ID
     *
     * @return void
     */
    function get_points($request)
    {
        global $wc_points_rewards;
        $user_id = (int)$request['user_id'];
        $current_page = (int)$request['page'];

        $points_balance = WC_Points_Rewards_Manager::get_users_points($user_id);
        $points_label = $wc_points_rewards->get_points_label($points_balance);
        $count = apply_filters('wc_points_rewards_my_account_points_events', 5, $user_id);
        $current_page = empty($current_page) ? 1 : absint($current_page);

        $args = array(
            'calc_found_rows' => true,
            'orderby' => array(
                'field' => 'date',
                'order' => 'DESC',
            ),
            'per_page' => $count,
            'paged' => $current_page,
            'user' => $user_id,
        );
        $total_rows = WC_Points_Rewards_Points_Log::$found_rows;
        $events = WC_Points_Rewards_Points_Log::get_points_log_entries($args);

        return array(
            'points_balance' => $points_balance,
            'points_label' => $points_label,
            'total_rows' => $total_rows,
            'page' => $current_page,
            'count' => $count,
            'events' => $events
        );
    }

    function update_user_profile()
    {
        global $json_api;
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        $cookie = $params->cookie;
        if (!isset($cookie)) {
            return parent::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_token", "Invalid cookie` method.", 401);
        }

        $user_update = array('ID' => $user_id);
        if (isset($params->user_pass)) {
            $user_update['user_pass'] = $params->user_pass;
        }
        if (isset($params->user_nicename)) {
            $user_update['user_nicename'] = $params->user_nicename;
        }
        if (isset($params->user_email)) {
            $user_update['user_email'] = $params->user_email;
        }
        if (isset($params->user_url)) {
            $user_update['user_url'] = $params->user_url;
        }
        if (isset($params->display_name)) {
            $user_update['display_name'] = $params->display_name;
        }
        if (isset($params->first_name)) {
            $user_update['first_name'] = $params->first_name;
            update_user_meta($user_id, 'billing_first_name', $params->first_name, '');
        }
        if (isset($params->last_name)) {
            $user_update['last_name'] = $params->last_name;
            update_user_meta($user_id, 'billing_last_name', $params->last_name, '');
        }
        if (isset($params->shipping_company)) {
            update_user_meta($user_id, 'shipping_company', $params->shipping_company, '');
        }
        if (isset($params->shipping_state)) {
            update_user_meta($user_id, 'shipping_state', $params->shipping_state, '');
        }
        if (isset($params->shipping_address_1)) {
            update_user_meta($user_id, 'shipping_address_1', $params->shipping_address_1, '');
        }
        if (isset($params->shipping_address_2)) {
            update_user_meta($user_id, 'shipping_address_2', $params->shipping_address_2, '');
        }
        if (isset($params->shipping_city)) {
            update_user_meta($user_id, 'shipping_city', $params->shipping_city, '');
        }
        if (isset($params->shipping_country)) {
            update_user_meta($user_id, 'shipping_country', $params->shipping_country, '');
        }
        if (isset($params->shipping_postcode)) {
            update_user_meta($user_id, 'shipping_postcode', $params->shipping_postcode, '');
        }

        if (isset($params->avatar)) {
            $count = 1;
            require_once(ABSPATH . '/wp-load.php');
            require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
            require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
            $imgdata = $params->avatar;
            $imgdata = trim($imgdata);
            $imgdata = str_replace('data:image/png;base64,', '', $imgdata);
            $imgdata = str_replace('data:image/jpg;base64,', '', $imgdata);
            $imgdata = str_replace('data:image/jpeg;base64,', '', $imgdata);
            $imgdata = str_replace('data:image/gif;base64,', '', $imgdata);
            $imgdata = str_replace(' ', '+', $imgdata);
            $imgdata = base64_decode($imgdata);
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
            $type_file = explode('/', $mime_type);
            $avatar = time() . '_' . $count . '.' . $type_file[1];

            $uploaddir = wp_upload_dir();
            $myDirPath = $uploaddir["path"];
            $myDirUrl = $uploaddir["url"];

            file_put_contents($uploaddir["path"] . '/' . $avatar, $imgdata);

            $filename = $myDirUrl . '/' . basename($avatar);
            $wp_filetype = wp_check_filetype(basename($filename), null);
            $uploadfile = $uploaddir["path"] . '/' . basename($filename);

            $attachment = array(
                "post_mime_type" => $wp_filetype["type"],
                "post_title" => preg_replace("/\.[^.]+$/", "", basename($filename)),
                "post_content" => "",
                "post_author" => $user_id,
                "post_status" => "inherit",
                'guid' => $myDirUrl . '/' . basename($filename),
            );

            $attachment_id = wp_insert_attachment($attachment, $uploadfile);
            $attach_data = apply_filters('wp_generate_attachment_metadata', $attachment, $attachment_id, 'create');
            // $attach_data = wp_generate_attachment_metadata($attachment_id, $uploadfile);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            $url = wp_get_attachment_image_src($attachment_id);
            update_user_meta($user_id, 'user_avatar', $url, '');

        }

        if (isset($params->deviceToken)) {
            if (isset($params->is_manager) && $params->is_manager) {
                update_user_meta($user_id, "mstore_manager_device_token", $params->deviceToken);
            } else if (isset($params->is_delivery) && $params->is_delivery) {
                update_user_meta($user_id, "mstore_delivery_device_token", $params->deviceToken);
            }

            if (!isset($params->is_delivery) && !isset($params->is_manager)) {
                update_user_meta($user_id, "mstore_device_token", $params->deviceToken);
            }
        }
        $user_data = wp_update_user($user_update);

        if (is_wp_error($user_data)) {
            // There was an error; possibly this user doesn't exist.
            echo 'Error.';
        }
        $user = get_userdata($user_id);
        return $this->getResponseUserInfo($user);
    }

    function prepare_checkout()
    {
        global $json_api;
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        $order = $params->order;
        if (!isset($order)) {
            return parent::sendError("invalid_checkout", "You must include a 'order' var in your request", 400);
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "mstore_checkout";

        $code = md5(mt_rand() . strtotime("now"));
        $success = $wpdb->insert($table_name, array(
                'code' => $code,
                'order' => $order
            )
        );
        if ($success) {
            return $code;
        } else {
            return parent::sendError("error_insert_database", "Can't insert to database", 400);
        }
    }

    public function get_currency_rates()
    {
        global $woocommerce_wpml;

        if (!empty($woocommerce_wpml->multi_currency) && !empty($woocommerce_wpml->settings['currencies_order'])) {
            return $woocommerce_wpml->settings['currency_options'];
        }
        return parent::sendError("not_install_woocommerce_wpml", "WooCommerce WPML hasn't been installed yet.", 404);
    }

    public function get_countries()
    {
        $wc_countries = new WC_Countries();
        $array = $wc_countries->get_countries();
        $keys = array_keys($array);
        $countries = array();
        for ($i = 0; $i < count($keys); $i++) {
            $countries[] = ["code" => $keys[$i], "name" => $array[$keys[$i]]];
        }
        return $countries;
    }

    public function get_states($request)
    {
        $wc_countries = new WC_Countries();
        $array = $wc_countries->get_states($request["country_code"]);
        if ($array) {
            $keys = array_keys($array);
            $states = array();
            for ($i = 0; $i < count($keys); $i++) {
                $states[] = ["code" => $keys[$i], "name" => $array[$keys[$i]]];
            }
            return $states;
        } else {
            return [];
        }
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
        $serverKey = get_option("mstore_firebase_server_key");
        $status = false;
        if (isset($is_manager)) {
            if ($is_manager) {
                $deviceToken = get_user_meta($user_id, 'mstore_manager_device_token', true);
                if ($deviceToken) {
                    $status = pushNotification("Fluxstore", "Test push notification", $deviceToken);
                }
            }
            return ["deviceToken" => $deviceToken, 'serverKey' => $serverKey, 'status' => $status];
        }
        if (isset($is_delivery)) {
            if ($is_delivery) {
                $deviceToken = get_user_meta($user_id, 'mstore_delivery_device_token', true);
                if ($deviceToken) {
                    $status = pushNotification("Fluxstore", "Test push notification", $deviceToken);
                }
            }
            return ["deviceToken" => $deviceToken, 'serverKey' => $serverKey, 'status' => $status];
        }
        $deviceToken = get_user_meta($user_id, 'mstore_device_token', true);
        if ($deviceToken) {
            $status = pushNotification("Fluxstore", "Test push notification", $deviceToken);
        }
        return ["deviceToken" => $deviceToken, 'serverKey' => $serverKey, 'status' => $status];
    }

    function chat_notification($request)
    {
        $token = $request['token'];
        if (isset($token)) {
            $cookie = urldecode(base64_decode($token));
        } else {
            return parent::sendError("unauthorized", "You are not allowed to do this", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login", "You do not exist in this world. Please re-check your existence with your Creator :)", 401);
        }
        $receiver_email = $request['receiver'];
        $sender_name = $request['sender'];
        if (is_email($sender_name)) {
            $sender = get_user_by('email', $sender_name);
            $sender_name = $sender->display_name;
        }
        $receiver = get_user_by('email', $receiver_email);

        if (!$receiver) {
            return parent::sendError("invalid_user", "User does not exist in this world. Please re-check user's existence with the Creator :)", 401);
        }

        $serverKey = get_option("mstore_firebase_server_key");
        $message = $request['message'];

        $deviceToken = get_user_meta($receiver->ID, 'mstore_device_token', true);
        $manager_device_token = get_user_meta($receiver->ID, 'mstore_manager_device_token', true);
        pushNotification($sender_name, $message, $deviceToken);
        pushNotification($sender_name, $message, $manager_device_token);

    }
}