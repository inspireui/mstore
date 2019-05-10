<?php
/**
 * Plugin Name: Mstore CheckOut Plugin and API
 * Plugin URI: http://inspireui.com
 * Description: The MStore Checkout Wordpress Plugin which use for the Mstore app - Complete React Native template for e-commerce
 * Version: 1.1.5
 * Author: InspireUI
 * Author URI: http://inspireui.com
 *
 * Text Domain: MStore-checkout
 */

if (!defined('ABSPATH')) {
    exit;
}

// use MstoreCheckout\Templates\MobileDetect\Mobile_Detect;
include plugin_dir_path(__FILE__).'templates/class-mobile-detect.php';
include plugin_dir_path(__FILE__).'templates/class-rename-generate.php';

class MstoreCheckOut
{
    public $version = '1.1.2';

    public function __construct()
    {
        define('MSTORE_CHECKOUT_VERSION', $this->version);
        define('MSTORE_PLUGIN_FILE', __FILE__);
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php') == false) {
            return 0;
        }


        if (isset($_GET['order']) && strlen($_GET['order'])>0) {
            add_filter('woocommerce_is_checkout', '__return_true'); 
        }

        include_once plugin_dir_path(__FILE__).'controllers/MstoreDokan.php';
        /* Checkout Template*/
//        require_once('templates/class-page-templater.php');
//        add_action('plugins_loaded', array('PageTemplater', 'get_instance'));
        // require_once __DIR__ . '/wp-templater/src/Templater.php';
        // require_once __DIR__ . '/templates/class-mobile-detect.php';
        add_action('wp_print_scripts', array($this, 'handle_received_order_page'));
    }

    public function handle_received_order_page()
    {
        // default return true for getting checkout library working
        if (is_order_received_page()) {
            $detect = new Mobile_Detect;
            if ($detect->isMobile()) {
                wp_register_style('mstore-order-custom-style', plugins_url('assets/css/mstore-order-style.css', MSTORE_PLUGIN_FILE));
                wp_enqueue_style('mstore-order-custom-style');
            }
        }

    }
}

$mstoreCheckOut = new MstoreCheckOut();

// use JO\Module\Templater\Templater;
include plugin_dir_path(__FILE__).'wp-templater/src/Templater.php';

add_action('plugins_loaded', 'load_templater');
function load_templater()
{
    
    // add our new custom templates
    $my_templater = new Templater(
        array(
            // YOUR_PLUGIN_DIR or plugin_dir_path(__FILE__)
            'plugin_directory' => plugin_dir_path(__FILE__),
            // should end with _ > prefix_
            'plugin_prefix' => 'plugin_prefix_',
            // templates directory inside your plugin
            'plugin_template_directory' => 'templates',
        )
    );
    $my_templater->add(
        array(
            'page' => array(
                'mstore-checkout-template.php' => 'Page Custom Template',
            ),
        )
    )->register();
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// Define for the API User wrapper which is based on json api user plugin
///////////////////////////////////////////////////////////////////////////////////////////////////

if (!is_plugin_active('json-api/json-api.php')) {
    // add_action('admin_notices', 'pim_draw_notice_json_api');
    return;
}

add_filter('json_api_controllers', 'registerJsonApiController');
add_filter('json_api_mstore_user_controller_path', 'setMstoreUserControllerPath');
add_action('init', 'json_api_mstore_user_checkAuthCookie', 100);

function registerJsonApiController($aControllers)
{
    $aControllers[] = 'Mstore_User';
    return $aControllers;
}

function setMstoreUserControllerPath($sDefaultPath)
{
    return dirname(__FILE__) . '/controllers/MstoreUser.php';
}

function json_api_mstore_user_checkAuthCookie($sDefaultPath)
{
    global $json_api;

    if ($json_api->query->cookie) {
        $user_id = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in');
        if ($user_id) {
            $user = get_userdata($user_id);
            wp_set_current_user($user->ID, $user->user_login);
        }
    }
}