<?php
/*
Controller Name: Auth
Controller Description: Authentication add-on controller for the Wordpress JSON API plugin
Controller Author: Matt Berg, Ali Qureshi
Controller Author Twitter: @parorrey
*/


class JSON_API_Auth_Controller {
	public function __construct() {
		global $json_api;
		// allow only connection over https. because, well, you care about your passwords and sniffing.
		// turn this sanity-check off if you feel safe inside your localhost or intranet.
		// send an extra POST parameter: insecure=cool
		if (empty($_SERVER['HTTPS']) ||
		    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'off')) {
			if (empty($_REQUEST['insecure']) || $_REQUEST['insecure'] != 'cool') {
				$json_api->error("I'm sorry Dave. I'm afraid I can't do that. (use _https_ please)");
			}
		}
		$allowed_from_post = array('cookie', 'username', 'password', 'seconds', 'nonce');
		foreach($allowed_from_post as $param) {
			if (isset($_POST[$param])) {
				$json_api->query->$param = $_POST[$param];
			}
		}
	}
	public function validate_auth_cookie() {
		global $json_api;
		if (!$json_api->query->cookie) {
			$json_api->error("You must include a 'cookie' authentication cookie. Use the `create_auth_cookie` Auth API method.");
		}
		$valid = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in') ? true : false;
		return array(
			"valid" => $valid
			);
	}
	public function generate_auth_cookie() {
		global $json_api;
		if (!$json_api->query->username) {
			$json_api->error("You must include a 'username' var in your request.");
		}
		if (!$json_api->query->password) {
			$json_api->error("You must include a 'password' var in your request.");
		}
		if ($json_api->query->seconds) 	$seconds = (int) $json_api->query->seconds;
		else $seconds = 1209600;//14 days
		$user = wp_authenticate($json_api->query->username, $json_api->query->password);
		if (is_wp_error($user)) {
			$json_api->error("Invalid username and/or password.", 'error', '401');
			remove_action('wp_login_failed', $json_api->query->username);
		}
		$expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);
		$cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
		preg_match('|src="(.+?)"|', get_avatar( $user->ID, 32 ), $avatar);
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
	public function get_currentuserinfo() {
		global $json_api;
		if (!$json_api->query->cookie) {
			$json_api->error("You must include a 'cookie' var in your request. Use the `generate_auth_cookie` Auth API method.");
		}
		$user_id = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in');
		if (!$user_id) {
			$json_api->error("Invalid authentication cookie. Use the `generate_auth_cookie` Auth API method.");
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
				"avatar" => $avatar[1]
			)
		);
	}
}