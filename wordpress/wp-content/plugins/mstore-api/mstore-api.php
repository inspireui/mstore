<?php
/**
 * Plugin Name: Mstore API
 * Plugin URI: https://github.com/inspireui/mstore-api
 * Description: The MStore API Plugin which is used for the Mstore and FluxStore App
 * Version: 1.4.2
 * Author: InspireUI
 * Author URI: http://inspireui.com
 *
 * Text Domain: MStore-Api
 */

defined('ABSPATH') or wp_die( 'No script kiddies please!' );


// use MStoreCheckout\Templates\MDetect;

include plugin_dir_path(__FILE__)."templates/class-mobile-detect.php";
include plugin_dir_path(__FILE__)."templates/class-rename-generate.php";

class MstoreCheckOut
{
    public $version = '1.4.2';

    public function __construct()
    {
        define('MSTORE_CHECKOUT_VERSION', $this->version);
        define('MSTORE_PLUGIN_FILE', __FILE__);
        include_once (ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php') == false) {
            return 0;
        }

        $path = get_template_directory()."/templates";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $templatePath = plugin_dir_path(__FILE__)."templates/mstore-api-template.php";
        if (!copy($templatePath,$path."/mstore-api-template.php")) {
            return 0;
        }

        $order = filter_has_var(INPUT_GET, 'order') && strlen(filter_input(INPUT_GET, 'order')) > 0 ? true : false;
        if ($order) {
            add_filter('woocommerce_is_checkout', '__return_true');
        }

        include_once plugin_dir_path(__FILE__)."controllers/MStoreHome.php";
        include_once plugin_dir_path(__FILE__)."controllers/MStoreDokan.php";

        add_action('wp_print_scripts', array($this, 'handle_received_order_page'));
    }

    public function handle_received_order_page()
    {
        // default return true for getting checkout library working
        if (is_order_received_page()) {
            $detect = new MDetect;
            if ($detect->isMobile()) {
                wp_register_style('mstore-order-custom-style', plugins_url('assets/css/mstore-order-style.css', MSTORE_PLUGIN_FILE));
                wp_enqueue_style('mstore-order-custom-style');
            }
        }

    }
}

$mstoreCheckOut = new MstoreCheckOut();

// use JO\Module\Templater\Templater;
include plugin_dir_path(__FILE__)."wp-templater/src/Templater.php";

add_action('plugins_loaded', 'load_mstore_templater');
function load_mstore_templater()
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
                'mstore-api-template.php' => 'Page Custom Template',
            ),
        )
    )->register();
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Define for the API User wrapper which is based on json api user plugin
///////////////////////////////////////////////////////////////////////////////////////////////////

if (!is_plugin_active('json-api/json-api.php') && !is_plugin_active('json-api-master/json-api.php')) {
    // add_action('admin_notices', 'pim_draw_notice_json_api');
    return;
}

add_filter('json_api_controllers', 'registerJsonApiController');
add_filter('json_api_mstore_user_controller_path', 'setMstoreUserControllerPath');
add_action('init', 'json_apiCheckAuthCookie', 100);

add_action( 'rest_api_init', 'my_register_route' );
function my_register_route() {
    register_rest_route( 'order', 'verify', array(
                    'methods' => 'GET',
                    'callback' => 'check_payment'
                )
            );
}
function check_payment() {
    return true;
}

add_action('admin_menu', 'mstore_plugin_setup_menu');
function mstore_plugin_setup_menu(){
        add_menu_page( 'MStore Api', 'MStore Api', 'manage_options', 'mstore-plugin', 'mstore_init' );
}
function mstore_init(){
    load_template( dirname( __FILE__ ) . '/templates/mstore-api-admin-page.php' );
}

function registerJsonApiController($aControllers)
{
    $aControllers[] = 'Mstore_User';
    return $aControllers;
}

function setMstoreUserControllerPath()
{
    return plugin_dir_path(__FILE__) . '/controllers/MstoreUser.php';
}

function json_apiCheckAuthCookie()
{
    global $json_api;

    if ($json_api->query->cookie) {
        $user_id = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in');
        if ($user_id) {
            $user = get_userdata($user_id);
            wp_set_current_user($user->ID, $user->user_login);
        }
    }
    add_checkout_page();
}

function add_checkout_page() {
    $page = get_page_by_title('Mstore Checkout');
    if($page == null || strpos($page->post_name,"mstore-checkout") === false || $page->post_status != "publish") {
        $my_post = array(
            'post_type' => 'page',
            'post_name' => 'mstore-checkout',
            'post_title'    => 'Mstore Checkout',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page'
        );

        // Insert the post into the database
        $page_id = wp_insert_post( $my_post );
        update_post_meta( $page_id, '_wp_page_template', 'templates/mstore-api-template.php' );
    }
    
}

/**
 * Register the mstore caching endpoints so they will be cached.
 */
function wprc_add_mstore_endpoints( $allowed_endpoints ) {
    if ( ! isset( $allowed_endpoints[ 'mstore/v1' ] ) || ! in_array( 'cache', $allowed_endpoints[ 'mstore/v1' ] ) ) {
        $allowed_endpoints[ 'mstore/v1' ][] = 'cache';
    }
    return $allowed_endpoints;
}
add_filter( 'wp_rest_cache/allowed_endpoints', 'wprc_add_mstore_endpoints', 10, 1);