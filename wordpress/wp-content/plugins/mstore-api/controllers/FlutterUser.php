<?php
require_once( __DIR__ . '/FlutterBase.php');

class FlutterUserController extends FlutterBaseController {

    public function __construct() {
        $this->namespace     = '/api/flutter_user';
    }
 
    public function register_routes() {
        register_rest_route( $this->namespace, '/register', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'register' )
            ),
        ));

        register_rest_route( $this->namespace, '/generate_auth_cookie', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'generate_auth_cookie' )
            ),
        ));

        register_rest_route( $this->namespace, '/fb_connect', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'fb_connect' )
            ),
        ));

        register_rest_route( $this->namespace, '/sms_login', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'sms_login' )
            ),
        ));

        register_rest_route( $this->namespace, '/firebase_sms_login', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'firebase_sms_login' )
            ),
        ));

        register_rest_route( $this->namespace, '/apple_login', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'apple_login' )
            ),
        ));

        register_rest_route( $this->namespace, '/google_login', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'google_login' )
            ),
        ));

        register_rest_route( $this->namespace, '/post_comment', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'post_comment' )
            ),
        ));

        register_rest_route( $this->namespace, '/get_currentuserinfo', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_currentuserinfo' )
            ),
        ));

        register_rest_route( $this->namespace, '/get_points', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_points' )
            ),
        ));

        register_rest_route( $this->namespace, '/update_user_profile', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'update_user_profile' )
            ),
        ));
    }
 
    public function register()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        $usernameReq = $params->username;
        $emailReq = $params->email;
        $secondsReq = $params->seconds;
        $nonceReq = $params->nonce;
        $roleReq = $params->role;
        if ($roleReq && $roleReq != "subscriber" && $roleReq != "wcfm_vendor" && $roleReq != "seller") {
            return parent::sendError("invalid_role","Role is invalid.", 400);
        }
        $userPassReq = $params->user_pass;
        $userLoginReq = $params->user_login;
        $userEmailReq = $params->user_email;
        $notifyReq = $params->notify;
        
        $username = sanitize_user($usernameReq);

        $email = sanitize_email($emailReq);

        if ($secondsReq) {
            $seconds = (int) $secondsReq;
        } else {
            $seconds = 120960000;
        }
        if (!validate_username($username)) {
            return parent::sendError("invalid_username","Username is invalid.", 400);
        } elseif (username_exists($username)) {
            return parent::sendError("existed_username","Username already exists.", 400);
        } else {
            if (!is_email($email)) {
                return parent::sendError("invalid_email","E-mail address is invalid.", 400);
            } elseif (email_exists($email)) {
                return parent::sendError("existed_email","E-mail address is already in use.", 400);
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
                
                $user['role'] = $roleReq ? sanitize_text_field($roleReq) : get_option('default_role');
                $user_id = wp_insert_user($user);

                if(is_wp_error($user_id)){
                    return parent::sendError($user_id->get_error_code(),$user_id->get_error_message(), 400);
                }

                // if ($userPassReq && $notifyReq && $notifyReq == 'no') {
                //     $notify = '';
                // } elseif ($notifyReq && $notifyReq != 'no') {
                //     $notify = $notifyReq;
                // }

                // if ($user_id) {
                //     wp_new_user_notification($user_id, '', $notify);
                // }
            }
        }

        $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user_id, true);
        $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

        return array(
            "cookie" => $cookie,
            "user_id" => $user_id,
        );
    }

    public function generate_auth_cookie()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        if(!isset($params->username) || !isset($params->username)){
            return parent::sendError("invalid_login","Invalid params", 400);
        }
        $username = $params->username;
        $password = $params->password;


        if ($params->seconds) {
            $seconds = (int) $params->seconds;
        } else {
            $seconds = 1209600;
        }

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            return parent::sendError($user->get_error_code(),"Invalid username/email and/or password.", 401);
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
                "role" => $user->roles,
                "avatar" => $avatar[1],

            ),
        );
    }

    public function fb_connect($request)
    {
        $fields = 'id,name,first_name,last_name,email';
		$enable_ssl = true;
        $access_token = $request["access_token"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login","You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.", 400);
        }
        $url='https://graph.facebook.com/me/?fields='.$fields.'&access_token='.$access_token;
                
        $result = wp_remote_retrieve_body(wp_remote_get($url));

                $result = json_decode($result, true);
                
            if(isset($result["email"])){
                    
                        $user_email = $result["email"];
                        $email_exists = email_exists($user_email);
                        
                        if($email_exists) {
                            $user = get_user_by( 'email', $user_email );
                        $user_id = $user->ID;
                        $user_name = $user->user_login;
                        }
                        
                    
                    
                        if ( !$user_id && $email_exists == false ) {
                            
                        $user_name = strtolower($result['first_name'].'.'.$result['last_name']);
                                        
                            while(username_exists($user_name)){		        
                            $i++;
                            $user_name = strtolower($result['first_name'].'.'.$result['last_name']).'.'.$i;			     
                
                                }
                            
                        $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                        $userdata = array(
                                    'user_login'    => $user_name,
                                    'user_email'    => $user_email,
                                    'user_pass'  => $random_password,
                                    'display_name'  => $result["name"],
                                    'first_name'  => $result['first_name'],
                                    'last_name'  => $result['last_name'],
                                     'user' => $result
                                                );

                            $user_id = wp_insert_user( $userdata ) ;				   
                            if($user_id) $user_account = 'user registered.';
                            
                        } else {
                            
                            if($user_id) $user_account = 'user logged in.';
                            }
                        
                        $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, true);
                        $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
                    
                    $response['msg'] = $user_account;
                    $response['wp_user_id'] = $user_id;
                    $response['cookie'] = $cookie;
                    $response['user_login'] = $user_name;	
                    $response['user'] = $result;
                    }
                    else {
                        return parent::sendError("invalid_login","Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.", 400);
                    }

                return $response;
    }

    public function sms_login($request)
    {
        $access_token = $request["access_token"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login","You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.", 400);
        }
        $url = 'https://graph.accountkit.com/v1.3/me/?access_token=' . $access_token;

            $WP_Http_Curl = new WP_Http_Curl();
            $result = $WP_Http_Curl->request( $url, array(
                'method'      => 'GET',
                'timeout'     => 5,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(),
                'body'        => null,
                'cookies'     => array(),
            ));

            $result = json_decode($result, true);

            if (isset($result["phone"])) {
                $user_name = $result["phone"]["number"];
                $user_email = $result["phone"]["number"] . "@flutter.io";
                $email_exists = email_exists($user_email);

                if ($email_exists) {
                    $user = get_user_by('email', $user_email);
                    $user_id = $user->ID;
                    $user_name = $user->user_login;
                }

                if (!$user_id && $email_exists == false) {
                    $i = 1;
                    while (username_exists($user_name)) {
                        $i++;
                        $user_name = strtolower($user_name) . '.' . $i;

                    }
                    $random_password = wp_generate_password();
                    $userdata = array(
                        'user_login' => $user_name,
                        'user_email' => $user_email,
                        'user_pass' => $random_password,
                        'display_name' => $user_name,
                        'first_name' => $user_name,
                        'last_name' => "",
                    );

                    $user_id = wp_insert_user($userdata);
                    if ($user_id) {
                        $user_account = 'user registered.';
                    }

                } else {
                    if ($user_id) {
                        $user_account = 'user logged in.';
                    }
                }
                $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
                $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

                $response['msg'] = $user_account;
                $response['wp_user_id'] = $user_id;
                $response['cookie'] = $cookie;
                $response['user_login'] = $user_name;
                $response['user'] = $result;
            } else {
                return parent::sendError("invalid_login","Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.", 400);
            }
        return $response;

    }

    public function firebase_sms_login($request)
    {
        $phone = $request["phone"];
        if (!isset($phone)) {
            return parent::sendError("invalid_login","You must include a 'phone' variable.", 400);
        }
        $domain = $_SERVER['SERVER_NAME'];
            if (count(explode(".",$domain)) == 1) {
                $domain = "flutter.io";
            }
            $user_name = $phone;
            $user_email = $phone."@".$domain;
            $email_exists = email_exists($user_email);
            $user_pass = wp_generate_password($length = 12, $include_standard_special_chars = false);
            if ($email_exists) {
                $user = get_user_by('email', $user_email);
                $user_id = $user->ID;
                $user_name = $user->user_login;
                wp_update_user( array( 'ID' => $user_id, 'user_pass' => $user_pass ) );
            }


            if (!$user_id && $email_exists == false) {

                while (username_exists($user_name)) {
                    $i++;
                    $user_name = strtolower($user_name) . '.' . $i;

                }

                $userdata = array(
                    'user_login' => $user_name,
                    'user_email' => $user_email,
                    'user_pass' => $user_pass,
                    'display_name' => $user_name,
                    'first_name' => $user_name,
                    'last_name' => ""
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
            $response['user_pass'] = $user_pass;

        return $response;

    }

    public function apple_login($request)
    {
        $email = $request["email"];
        if (!isset($email)) {
            return parent::sendError("invalid_login","You must include a 'email' variable.", 400);
        }
        $display_name = $request["display_name"];
            $user_name = $request["user_name"];
            $user_email = $email;
            $email_exists = email_exists($user_email);

            if ($email_exists) {
                $user = get_user_by('email', $user_email);
                $user_id = $user->ID;
                $user_name = $user->user_login;
            }


            if (!$user_id && $email_exists == false) {

                while (username_exists($user_name)) {
                    $i++;
                    $user_name = strtolower($user_name) . '.' . $i;

                }

                $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                $userdata = array(
                    'user_login' => $user_name,
                    'user_email' => $user_email,
                    'user_pass' => $random_password,
                    'display_name' => $display_name,
                    'first_name' => $display_name,
                    'last_name' => ""
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
            $response['user'] = $user;

        return $response;

    }

    public function google_login($request)
    {
        $access_token = $request["access_token"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login","You must include a 'access_token' variable. Get the valid access_token for this app from Google API.", 400);
        }

        $url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=' . $access_token;

        $result = wp_remote_retrieve_body(wp_remote_get($url));

            $result = json_decode($result, true);
            if (isset($result["email"])) {
                $firstName = $result["given_name"];
                $lastName = $result["family_name"];
                $email = $result["email"];
                $avatar = $result["picture"];

                $display_name = $firstName." ".$lastName;
                $user_name = $firstName.".".$lastName;
                $user_email = $email;
                $email_exists = email_exists($user_email);
    
                if ($email_exists) {
                    $user = get_user_by('email', $user_email);
                    $user_id = $user->ID;
                    $user_name = $user->user_login;
                }
    
                if (!$user_id && $email_exists == false) {
                    while (username_exists($user_name)) {
                        $i++;
                        $user_name = strtolower($user_name) . '.' . $i;
                    }
    
                    $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                    $userdata = array(
                        'user_login' => $user_name,
                        'user_email' => $user_email,
                        'user_pass' => $random_password,
                        'display_name' => $display_name,
                        'first_name' => $display_name,
                        'last_name' => ""
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
                return $response;
            }else{
                return parent::sendError("invalid_login","Your 'token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Google app.", 400);
            }  
    }

    /*
     * Post commment function
     */
    public function post_comment($request)
    {
        $cookie = $request["cookie"];
        if (!isset($access_token)) {
            return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id) {
            return parent::sendError("invalid_login","Invalid cookie. Use the `generate_auth_cookie` method.", 401);
        }
        if (!$request["post_id"]) {
            return parent::sendError("invalid_data","No post specified. Include 'post_id' var in your request.", 400);
        } elseif (!$request["content"]) {
            return parent::sendError("invalid_data","Please include 'content' var in your request.", 400);
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

    public function get_currentuserinfo($request) {
        $cookie = $request["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
		}
		$user = get_userdata($user_id);
		preg_match('|src="(.+?)"|', get_avatar( $user->ID, 32 ), $avatar);
		return array(
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
                "role" => $user->roles,
				"avatar" => $avatar[1]
			)
		);
    }
    
    /**
     * Get Point Reward by User ID
     *
     * @return void
     */
    function get_points($request){       
        global $wc_points_rewards;
        $user_id = (int) $request['user_id'];
        $current_page = (int) $request['page'];
       
		$points_balance = WC_Points_Rewards_Manager::get_users_points( $user_id );
		$points_label   = $wc_points_rewards->get_points_label( $points_balance );
		$count        = apply_filters( 'wc_points_rewards_my_account_points_events', 5, $user_id );
		$current_page = empty( $current_page ) ? 1 : absint( $current_page );
        
		$args = array(
			'calc_found_rows' => true,
			'orderby' => array(
				'field' => 'date',
				'order' => 'DESC',
			),
			'per_page' => $count,
			'paged'    => $current_page,
			'user'     => $user_id,
        );
        $total_rows = WC_Points_Rewards_Points_Log::$found_rows;
		$events = WC_Points_Rewards_Points_Log::get_points_log_entries( $args );
        
        return array(
            'points_balance' => $points_balance,
            'points_label'   => $points_label,
            'total_rows'     => $total_rows,
            'page'   => $current_page,
            'count'          => $count,
            'events'         => $events
        );
    }

    function update_user_profile() {
        global $json_api;
        $json = file_get_contents('php://input');
        $params = json_decode($json);
        $cookie = $params["cookie"];
        if (!isset($cookie)) {
            return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
        }
		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		if (!$user_id) {
			return parent::sendError("invalid_login","You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
		}

        $user_update = array( 'ID' => $user_id);
        if ($params->user_pass) {
            $user_update['user_pass'] = $params->user_pass;
        }
        if ($params->user_nicename) {
            $user_update['user_nicename'] = $params->user_nicename;
        }
        if ($params->user_email) {
            $user_update['user_email'] = $params->user_email;
        }
        if ($params->user_url) {
            $user_update['user_url'] = $params->user_url;
        }
        if ($params->display_name) {
            $user_update['display_name'] = $params->display_name;
        }
        $user_data = wp_update_user($user_update);
 
        if ( is_wp_error( $user_data ) ) {
          // There was an error; possibly this user doesn't exist.
            echo 'Error.';
        }
        return get_userdata($user_id);
    }
}
