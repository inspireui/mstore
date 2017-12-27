<?php

/*
  Controller name: MStoreUser
  Controller description: Controller that extend from User Controller: Registration, Authentication, FB Login
  Controller Author: InspireUI
*/

class JSON_API_MStore_User_Controller
{

    /**
     * Returns an Array with registered userid & valid cookie
     * @param String username: username to register
     * @param String email: email address for user registration
     * @param String user_pass: user_pass to be set (optional)
     * @param String display_name: display_name for user
     */
    public function __construct()
    {
        global $json_api;
        // allow only connection over https. because, well, you care about your passwords and sniffing.
        // turn this sanity-check off if you feel safe inside your localhost or intranet.
        // send an extra POST parameter: insecure=cool
        if (empty($_SERVER['HTTPS']) ||
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'off')) {
            if (empty($_REQUEST['insecure']) || $_REQUEST['insecure'] != 'cool') {
                $json_api->error("SSL is not enabled. Either use _https_ or provide 'insecure' var as insecure=cool to confirm you want to use http protocol.");
            }
        }
    }

    public function register()
    {
        global $json_api;

        if (!get_option('users_can_register')) {
            $json_api->error("User registration is disabled. Please enable it in Settings > Gereral.");
        }

        if (!$json_api->query->username) {
            $json_api->error("You must include 'username' var in your request. ");
        } else $username = sanitize_user($json_api->query->username);


        if (!$json_api->query->email) {
            $json_api->error("You must include 'email' var in your request. ");
        } else $email = sanitize_email($json_api->query->email);

        if (!$json_api->query->nonce) {
            $json_api->error("You must include 'nonce' var in your request. Use the 'get_nonce' Core API method. ");
        } else $nonce = sanitize_text_field($json_api->query->nonce);

        if (!$json_api->query->display_name) {
            $json_api->error("You must include 'display_name' var in your request. ");
        } else $display_name = sanitize_text_field($json_api->query->display_name);

        $user_pass = sanitize_text_field($_REQUEST['user_pass']);

        if ($json_api->query->seconds) $seconds = (int)$json_api->query->seconds;

        else $seconds = 120960000;//1400 days

        //Add usernames we don't want used

        $invalid_usernames = array('admin');

        //Do username validation

        $nonce_id = $json_api->get_nonce_id('mstore_user', 'register');

        if (!wp_verify_nonce($json_api->query->nonce, $nonce_id)) {

            $json_api->error("Invalid access, unverifiable 'nonce' value. Use the 'get_nonce' Core API method. ");
        } else {

            if (!validate_username($username) || in_array($username, $invalid_usernames)) {

                $json_api->error("Username is invalid.");

            } elseif (username_exists($username)) {

                $json_api->error("Username already exists.");

            } else {


                if (!is_email($email)) {
                    $json_api->error("E-mail address is invalid.");
                } elseif (email_exists($email)) {

                    $json_api->error("E-mail address is already in use.");

                } else {

                    //Everything has been validated, proceed with creating the user
                    //Create the user
                    if (!isset($_REQUEST['user_pass'])) {
                        $user_pass = wp_generate_password();
                        $_REQUEST['user_pass'] = $user_pass;
                    }

                    $_REQUEST['user_login'] = $username;
                    $_REQUEST['user_email'] = $email;

                    $allowed_params = array('user_login', 'user_email', 'user_pass', 'display_name', 'user_nicename', 'user_url', 'nickname', 'first_name',
                        'last_name', 'description', 'rich_editing', 'user_registered', 'role', 'jabber', 'aim', 'yim',
                        'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front'
                    );


                    foreach ($_REQUEST as $field => $value) {

                        if (in_array($field, $allowed_params)) $user[$field] = trim(sanitize_text_field($value));

                    }
                    $user['role'] = get_option('default_role');
                    $user_id = wp_insert_user($user);

                    /*Send e-mail to admin and new user -
                    You could create your own e-mail instead of using this function*/

                    if (isset($_REQUEST['user_pass']) && $_REQUEST['notify'] == 'no') {
                        $notify = '';
                    } elseif ($_REQUEST['notify'] != 'no') $notify = $_REQUEST['notify'];


                    if ($user_id) wp_new_user_notification($user_id, '', $notify);


                }
            }
        }


        $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user_id, true);

        $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

        return array(
            "cookie" => $cookie,
            "user_id" => $user_id
        );

    }

    public function validate_auth_cookie()
    {
        global $json_api;
        if (!$json_api->query->cookie) {

            $json_api->error("You must include a 'cookie' authentication cookie. Use the `create_auth_cookie` method.");

        }
        $valid = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in') ? true : false;

        return array(
            'cookie' => $json_api->query->cookie,
            "valid" => $valid
        );
    }

    public function generate_auth_cookie()
    {

        global $json_api;

        foreach ($_POST as $k => $val) {
            if (isset($_POST[$k])) {
                $json_api->query->$k = $val;
            }
        }

        if (!$json_api->query->username && !$json_api->query->email) {
            $json_api->error("You must include 'username' or 'email' var in your request to generate cookie.");
        }


        if (!$json_api->query->password) {
            $json_api->error("You must include a 'password' var in your request.");
        }

        if ($json_api->query->seconds) $seconds = (int)$json_api->query->seconds;
        else $seconds = 1209600;//14 days

        if ($json_api->query->email) {


            if (is_email($json_api->query->email)) {
                if (!email_exists($json_api->query->email)) {
                    $json_api->error("email does not exist.");
                }
            } else  $json_api->error("Invalid email address.");

            $user_obj = get_user_by('email', $json_api->query->email);


            $user = wp_authenticate($user_obj->data->user_login, $json_api->query->password);
        } else {

            $user = wp_authenticate($json_api->query->username, $json_api->query->password);
        }


        if (is_wp_error($user)) {

            $json_api->error("Invalid username/email and/or password.", 'error', '401');

            remove_action('wp_login_failed', $json_api->query->username);

        }


        $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);

        $cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');

        preg_match('|src="(.+?)"|', get_avatar($user->ID, 512), $avatar);

        return array(
            "cookie" => $cookie,
            "cookie_name" => LOGGED_IN_COOKIE,
            "user" => array(
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
                "avatar" => $avatar[1]

            ),
        );
    }

    public function fb_connect()
    {

        global $json_api;

        if ($json_api->query->fields) {

            $fields = $json_api->query->fields;

        } else $fields = 'id,name,first_name,last_name,email,picture';

        if ($json_api->query->ssl) {
            $enable_ssl = $json_api->query->ssl;
        } else $enable_ssl = true;

        if (!$json_api->query->access_token) {
            $json_api->error("You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.");
        } else {

            $url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token=' . $json_api->query->access_token;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);

            if (isset($result["email"])) {

                $user_email = $result["email"];
                $email_exists = email_exists($user_email);

                if ($email_exists) {
                    $user = get_user_by('email', $user_email);
                    $user_id = $user->ID;
                    $user_name = $user->user_login;
                }


                if (!$user_id && $email_exists == false) {

                    $user_name = strtolower($result['first_name'] . '.' . $result['last_name']);

                    while (username_exists($user_name)) {
                        $i++;
                        $user_name = strtolower($result['first_name'] . '.' . $result['last_name']) . '.' . $i;

                    }

                    $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                    $userdata = array(
                        'user_login' => $user_name,
                        'user_email' => $user_email,
                        'user_pass' => $random_password,
                        'display_name' => $result["name"],
                        'first_name' => $result['first_name'],
                        'last_name' => $result['last_name']
                    );

                    $user_id = wp_insert_user($userdata);
                    if ($user_id) $user_account = 'user registered.';

                } else {

                    if ($user_id) $user_account = 'user logged in.';
                }

                $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
                $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

                $response['msg'] = $user_account;
                $response['wp_user_id'] = $user_id;
                $response['cookie'] = $cookie;
                $response['user_login'] = $user_name;
                $response['user'] = $result;
            } else {
                $response['msg'] = "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.";

            }

        }

        return $response;

    }
}
 
 