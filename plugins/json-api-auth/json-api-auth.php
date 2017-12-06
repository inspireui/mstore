<?php

/*

  Plugin Name: JSON API Auth  
  Plugin URI: http://www.parorrey.com/solutions/json-api-auth/
  Description: Extends the JSON API Plugin for RESTful user authentiocation
  Version: 1.9
  Author: Ali Qureshi
  Author URI: http://www.parorrey.com
  License: GPLv3
  
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('JSON_API_AUTH_HOME', dirname(__FILE__));

if (!is_plugin_active('json-api/json-api.php')) {

    add_action('admin_notices', 'pim_auth_draw_notice_json_api');

    return;

}

add_filter('json_api_controllers', 'pimAuthJsonApiController');

add_filter('json_api_auth_controller_path', 'setAuthControllerPath');

add_action('init', 'json_api_auth_checkAuthCookie', 100);

load_plugin_textdomain('json-api-auth', false, basename(dirname(__FILE__)) . '/languages');

function pim_auth_draw_notice_json_api() {

    echo '<div id="message" class="error fade"><p style="line-height: 150%">';

    _e('<strong>JSON API Auth</strong></a> requires the JSON API plugin to be activated. Please <a href="https://wordpress.org/plugins/json-api/">install / activate JSON API</a> first.', 'json-api-user');

    echo '</p></div>';

}


function pimAuthJsonApiController($aControllers) {

    $aControllers[] = 'Auth';
    return $aControllers;

}


function setAuthControllerPath($sDefaultPath) {

    return dirname(__FILE__) . '/controllers/Auth.php';

}

function json_api_auth_checkAuthCookie($sDefaultPath) {
    global $json_api;

    if ($json_api->query->cookie) {
      $user_id = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in');
      if ($user_id) {
        $user = get_userdata($user_id);

        wp_set_current_user($user->ID, $user->user_login);
      }
    }
}