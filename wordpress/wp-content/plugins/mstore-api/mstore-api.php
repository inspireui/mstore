<?php
/**
 * Plugin Name: MStore API
 * Plugin URI: https://github.com/inspireui/mstore-api
 * Description: The MStore API Plugin which is used for the FluxBuilder and FluxStore Mobile App
 * Version: 4.15.3
 * Author: FluxBuilder
 * Author URI: https://fluxbuilder.com
 *
 * Text Domain: MStore-Api
 */

defined('ABSPATH') or wp_die('No script kiddies please!');


// use MStoreCheckout\Templates\MDetect;

include plugin_dir_path(__FILE__) . "templates/class-mobile-detect.php";
include plugin_dir_path(__FILE__) . "templates/class-rename-generate.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-user.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-home.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-booking.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-vendor-admin.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-woo.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-delivery.php";
include_once plugin_dir_path(__FILE__) . "functions/index.php";
include_once plugin_dir_path(__FILE__) . "functions/utils.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-tera-wallet.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-paytm.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-paystack.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-flutterwave.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-myfatoorah.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-midtrans.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-paid-memberships-pro.php";
include_once plugin_dir_path(__FILE__) . "controllers/listing-rest-api/class.api.fields.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-blog.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-wholesale.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-stripe.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-notification.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-thawani.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-expresspay.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-2c2p.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-cc-avenue.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-flow-flow.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-store-locator.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-composite-products.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-b2bking.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-review.php";
include_once plugin_dir_path(__FILE__) . "controllers/helpers/firebase-message-helper.php";
include_once plugin_dir_path(__FILE__) . "controllers/flutter-fib.php";
include_once plugin_dir_path(__FILE__) . "controllers/helpers/firebase-phone-auth-helper.php";

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

class MstoreCheckOut
{
    public $version = '4.15.3';

    public function __construct()
    {
        define('MSTORE_CHECKOUT_VERSION', $this->version);
        define('MSTORE_PLUGIN_FILE', __FILE__);
        
        /**
         * Prepare data before checkout by webview
         */
        add_action('template_redirect', 'flutter_prepare_checkout');

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        //include_once(ABSPATH . 'wp-includes/pluggable.php');

        //migrate old versions to re-verify purchase code automatically
        verifyPurchaseCodeAuto();

        add_filter( 'get_avatar_url', array( $this, 'filter_avatar' ), 10, 3 );

        if (is_plugin_active('woocommerce/woocommerce.php') == false) {
            return 0;
        }
        add_action('woocommerce_init', 'woocommerce_mstore_init');
        function woocommerce_mstore_init()
        {
            include_once plugin_dir_path(__FILE__) . "controllers/flutter-order.php";
            include_once plugin_dir_path(__FILE__) . "controllers/flutter-multi-vendor.php";
            include_once plugin_dir_path(__FILE__) . "controllers/flutter-vendor.php";
            include_once plugin_dir_path(__FILE__) . "controllers/helpers/delivery-wcfm-helper.php";
            include_once plugin_dir_path(__FILE__) . "controllers/helpers/delivery-wcfm-helper.php";
            include_once plugin_dir_path(__FILE__) . "controllers/helpers/vendor-admin-woo-helper.php";
            include_once plugin_dir_path(__FILE__) . "controllers/helpers/vendor-admin-wcfm-helper.php";
            include_once plugin_dir_path(__FILE__) . "controllers/helpers/vendor-admin-dokan-helper.php";
            include_once plugin_dir_path(__FILE__) . "controllers/flutter-customer.php";
            include_once plugin_dir_path(__FILE__) . "functions/video-setting-embed.php";
        }

        $order = filter_has_var(INPUT_GET, 'code') && strlen(filter_input(INPUT_GET, 'code')) > 0 ? true : false;
        if ($order) {
            add_filter('woocommerce_is_checkout', '__return_true');
        }

        /*
		add_filter( 'woocommerce_get_item_data', 'display_custom_product_field_data_mstore_api', 10, 2 );

		function display_custom_product_field_data_mstore_api( $cart_data, $cart_item ) {

			if( !empty( $cart_data ) ){
                $custom_items = $cart_data;

				$code = sanitize_text_field($_GET['code']) ?: get_transient( 'mstore_code' );
				set_transient( 'mstore_code', $code, 600 );

				global $wpdb;
				$table_name = $wpdb->prefix . "mstore_checkout";
				$item = $wpdb->get_row("SELECT * FROM $table_name WHERE code = '$code'");
				if ($item) {
					$data = json_decode(urldecode(base64_decode($item->order)), true);
					$line_items = $data['line_items'];
					$product_ids = [];
					foreach($line_items as $line => $item) {
						$product_ids[$item['product_id']] = $item;
					}

					if (array_key_exists($cart_item['product_id'], $product_ids)) {
						if ($varian = $product_ids[$cart_item['product_id']]) {
							$variations = $varian['meta_data'];
							foreach($variations as $v => $f) {
								preg_match('#\((.*?)\)#', $f['key'], $match);
								$val = $match[1];
								$custom_items[] = array(
									'key'       => $f['value'],
									'value'     => $val,
									'display'   => $val,
								);
							}
						}
					}
				}

			    return $custom_items;
            }
            return $cart_data;
		}


		add_action( 'woocommerce_before_calculate_totals', 'add_custom_price_mstore_api' );

		function add_custom_price_mstore_api( $cart_object ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$add_price = 0;
				if ($variations = $cart_item['variation']) {
					foreach($variations as $v => $f) {
						preg_match('#\((.*?)\)#', $v, $match);
                        if(is_array($match) && array_key_exists(1,$match)){
                            $val = $match[1];
                            $cents = filter_var($val, FILTER_SANITIZE_NUMBER_INT);
                            if(is_numeric($cents)){
                                $add_price += floatval($cents / 100);
                            }
                        }
					}
				}
				$new_price = $cart_item['data']->get_price() + $add_price;
				$cart_item['data']->set_price($new_price);   
			}
		}
        */

        add_action('wp_print_scripts', array($this, 'handle_received_order_page'));

        //add meta box shipping location in order detail
        add_action('add_meta_boxes', 'mv_add_meta_boxes');
        if (!function_exists('mv_add_meta_boxes')) {
            function mv_add_meta_boxes()
            {
                add_meta_box('mv_other_fields', __('Shipping Location', 'woocommerce'), 'mv_add_other_fields_for_packaging', 'shop_order', 'side', 'core');
            }
        }
        // Adding Meta field in the meta container admin shop_order pages
        if (!function_exists('mv_add_other_fields_for_packaging')) {
            function mv_add_other_fields_for_packaging()
            {
                global $post;
                $note = $post->post_excerpt;
                $items = explode("\n", $note);
                if (strpos($items[0], "URL:") !== false) {
                    $url = str_replace("URL:", "", $items[0]);
                    echo esc_html('<iframe width="600" height="500" src="' . esc_url($url) . '"></iframe>');
                }
            }
        }

        register_activation_hook(__FILE__, array($this, 'create_custom_mstore_table'));

        // Setup Ajax action hook
        add_action('wp_ajax_mstore_delete_json_file', array($this, 'mstore_delete_json_file'));
        add_action('wp_ajax_mstore_delete_apple_file', array($this, 'mstore_delete_apple_file'));
        add_action('wp_ajax_mstore_delete_firebase_file', array($this, 'mstore_delete_firebase_file'));
        add_action('wp_ajax_mstore_update_limit_product', array($this, 'mstore_update_limit_product'));
        add_action('wp_ajax_mstore_update_new_order_title', array($this, 'mstore_update_new_order_title'));
        add_action('wp_ajax_mstore_update_new_order_message', array($this, 'mstore_update_new_order_message'));
        add_action('wp_ajax_mstore_update_status_order_title', array($this, 'mstore_update_status_order_title'));
        add_action('wp_ajax_mstore_update_status_order_message', array($this, 'mstore_update_status_order_message'));

        // listen changed order status to notify
        add_action('woocommerce_order_status_changed', array($this, 'track_order_status_changed'), 9, 4);
        add_action('woocommerce_checkout_update_order_meta', array($this, 'track_new_order'));
        add_action('woocommerce_rest_insert_shop_order_object', array($this, 'track_api_new_order'), 10, 4);

        //WCFM - WooCommerce Frontend Manager - Delivery
        //Handle listen to assign delivery boy on the website
        add_action( 'wcfmd_after_delivery_boy_assigned', array($this, 'track_delivery_boy_assigned'), 400, 6 );

        $path = get_template_directory() . "/templates";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        if (file_exists($path)) {
            $templatePath = plugin_dir_path(__FILE__) . "templates/mstore-api-template.php";
            if (!copy($templatePath, $path . "/mstore-api-template.php")) {
                return 0;
            }
        }
    }

    public function filter_avatar( $url, $id_or_email, $args ) {
		$finder = false;
		$is_id  = is_numeric( $id_or_email );

		if ( $is_id ) {
			$finder = absint( $id_or_email );
		} elseif ( is_string( $id_or_email ) ) {
			$finder = $id_or_email;
		} elseif ( $id_or_email instanceof \WP_User ) {
			// User Object.
			$finder = $id_or_email->ID;
		} elseif ( $id_or_email instanceof \WP_Post ) {
			// Post Object.
			$finder = (int) $id_or_email->post_author;
		} elseif ( $id_or_email instanceof \WP_Comment ) {
			return $url;
		}

		if ( ! $finder ) {
			return $url;
		}

		$user = get_user_by( $is_id ? 'ID' : 'email', $finder );

		if ( $user ) {
			$avatar = get_user_meta( $user->ID, 'user_avatar', true );
			if (isset($avatar) && $avatar != "" && !is_bool($avatar)) {
                $url = $avatar[0];
            }
		}
		return $url;
	}

    function mstore_delete_json_file(){
        if(checkIsAdmin(get_current_user_id())){
            $id = sanitize_text_field($_REQUEST['id']);
            $nonce = sanitize_text_field($_REQUEST['nonce']);
            FlutterUtils::delete_config_file($id, $nonce);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_delete_apple_file(){
        if(checkIsAdmin(get_current_user_id())){
            $nonce = sanitize_text_field($_REQUEST['nonce']);
            FlutterAppleSignInUtils::delete_config_file($nonce);
            wp_send_json('success',200);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_delete_firebase_file(){
        if(checkIsAdmin(get_current_user_id())){
            $nonce = sanitize_text_field($_REQUEST['nonce']);
            FirebaseMessageHelper::delete_config_file($nonce);
            wp_send_json('success',200);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_limit_product()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_limit_product')){
            $limit = sanitize_text_field($_REQUEST['limit']);
            if (is_numeric($limit)) {
                update_option("mstore_limit_product", intval($limit));
            }
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_new_order_title()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_new_order_title')){
            $title = sanitize_text_field($_REQUEST['title']);
            update_option("mstore_new_order_title", $title);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_new_order_message()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_new_order_message')){
            $message = sanitize_text_field($_REQUEST['message']);
            update_option("mstore_new_order_message", $message);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_status_order_title()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_status_order_title')){
            $title = sanitize_text_field($_REQUEST['title']);
            update_option("mstore_status_order_title", $title);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    function mstore_update_status_order_message()
    {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        if(checkIsAdmin(get_current_user_id()) && wp_verify_nonce($nonce, 'update_status_order_message')){
            $message = sanitize_text_field($_REQUEST['message']);
            update_option("mstore_status_order_message", $message);
        }else{
            wp_send_json_error('No Permission',401);
        }
    }

    // update order via website
    function track_order_status_changed($id, $previous_status, $next_status)
    {
        trackOrderStatusChanged($id, $previous_status, $next_status);
    }

    // new order via website
    function track_new_order($order_id)
    {
        trackNewOrder($order_id);
    }

    //new order or update order via API
    function track_api_new_order($object,$request, $creating)
    {
        if($creating){
            trackNewOrder($object->id);
        }else{
            $body = $request->get_body_params();
            if(isset($body['status'])){
                sendNotificationForOrderStatusUpdated($object->id, $body['status']);
            }
        }
    }

    function track_delivery_boy_assigned( $order_id, $order_item_id, $wcfm_tracking_data, $product_id, $wcfm_delivery_boy, $wcfm_messages ) {
        $notification_message = strip_tags($wcfm_messages);
        $title = "You have new notification";
        pushNotificationForDeliveryBoy($wcfm_delivery_boy, $title, $notification_message);
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

    function create_custom_mstore_table()
    {
        global $wpdb;
        // include upgrade-functions for maybe_create_table;
        if (!function_exists('maybe_create_table')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'mstore_checkout';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            `code` tinytext NOT NULL,
            `order` text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        $success = maybe_create_table($table_name, $sql);
    }
}

$mstoreCheckOut = new MstoreCheckOut();

// use JO\Module\Templater\Templater;
include plugin_dir_path(__FILE__) . "templates/class-templater.php";

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

//custom rest api
function flutter_users_routes()
{
    $controller = new FlutterUserController();
    $controller->register_routes();
}

add_action('rest_api_init', 'flutter_users_routes');
add_action('rest_api_init', 'mstore_check_payment_routes');
function mstore_check_payment_routes()
{
    register_rest_route('order', '/verify', array(
            'methods' => 'GET',
            'callback' => 'mstore_check_payment',
            'permission_callback' => function () {
                return true;
            },
        )
    );
}

function mstore_check_payment()
{
    return true;
}


// Add menu Setting
add_action('admin_menu', 'mstore_plugin_setup_menu');

function mstore_plugin_setup_menu()
{
    add_menu_page('MStore Api', 'MStore Api', 'manage_options', 'mstore-plugin', 'mstore_init');
}

function mstore_init()
{
    load_template(dirname(__FILE__) . '/templates/mstore-api-admin-page.php');
}

add_filter('woocommerce_rest_prepare_product_variation_object', 'custom_woocommerce_rest_prepare_product_variation_object', 20, 3);
add_filter('woocommerce_rest_prepare_product_object', 'flutter_custom_change_product_response', 20, 3);
add_filter('woocommerce_rest_prepare_product_review', 'custom_product_review', 20, 3);
add_filter('woocommerce_rest_prepare_product_cat', 'custom_product_category', 20, 3);
add_filter('woocommerce_rest_prepare_shop_order_object', 'flutter_custom_change_order_response', 20, 3);
add_filter('woocommerce_rest_prepare_product_attribute', 'flutter_custom_change_product_attribute', 20, 3);
add_filter('woocommerce_rest_prepare_product_tag', 'flutter_custom_change_product_taxonomy', 20, 3);
add_filter('woocommerce_rest_prepare_product_brand', 'flutter_custom_change_product_taxonomy', 20, 3);
add_filter('woocommerce_rest_product_object_query', 'flutter_custom_rest_product_object_query', 10, 2);
add_filter('woocommerce_rest_product_tag_query', 'flutter_custom_rest_product_tag_query', 10, 2);
add_filter('woocommerce_rest_product_brand_query', 'flutter_custom_rest_product_brand_query', 10, 2);
add_filter('rest_product_collection_params', 'flutter_custom_rest_product_collection_params', 10, 1);

/**
 * WooCommerce REST API: Random sorting for products.
 * 
 * rest_{post_type}_collection_params
 *
 * @param array $params
 * @return array
 */
function flutter_custom_rest_product_collection_params($params)
{
    $params['orderby']['enum'][] = 'rand';
    return $params;
}

function flutter_custom_rest_product_tag_query($args, $request)
{
    return flutter_custom_rest_product_taxomomy_query($args, $request, 'product_tag');
}

function flutter_custom_rest_product_brand_query($args, $request)
{
    return flutter_custom_rest_product_taxomomy_query($args, $request, 'product_brand');
}

function flutter_custom_rest_product_taxomomy_query($args, $request, $taxonomy)
{
    $hide_empty = isset($args['hide_empty']) && $args['hide_empty'] == true;

    // `include` parameter can be an array or comma separated string
    $include = wp_parse_id_list($args['include']);

    // Get product count for all tags, brands related to the requested params (based on category, brand, etc.)
    $terms = get_filtered_term_product_counts($request, $taxonomy, [], $hide_empty);

    // Trick: Use the `include` parameter to fix the API problem below
    // The page = 1 has no visible items (count = 0), the app does not show
    // anything (include loadmore button). If the page = 2 has visible items,
    // cannot do anything in app because it do not show load more button
    foreach ($terms as $key => $term) {
        if ($hide_empty) {
            if ($term['term_count'] > 0) {
                $include[] = $term['term_count_id'];
            }
        } else {
            $include[] = $term['term_count_id'];
        }
    }

    $args['include'] = implode(',', wp_parse_id_list($include));
    return $args;
}

function flutter_custom_rest_product_object_query($args, $request)
{
    $attrs = $request['attributes'];

    if (is_string($attrs) && !empty($attrs)) {
        $attrs = json_decode($attrs, true);
    }

    // Attributes filter.
    if (!empty($attrs)) {
        foreach ($attrs as $attr_key => $attr_value) {
            $args['tax_query'][] = [
                'taxonomy' => $attr_key,
                'field'    => 'term_id',
                'terms'    => explode(',', $attr_value),
            ];
        }
    }

    return $args;
}

function custom_product_category($response, $object, $request)
{
    $id = $response->data['id'];
    $children = get_term_children($id, 'product_cat');

    if (empty($children)) {
        $response->data['has_children'] = false;
    } else {
        $response->data['has_children'] = true;
    }
    return $response;
}

function custom_product_review($response, $object, $request)
{
    if(is_plugin_active('woo-photo-reviews/woo-photo-reviews.php') || is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php')){
        $id = $response->data['id'];
        $image_post_ids = get_comment_meta( $id, 'reviews-images', true );
        $image_arr = array();
        if(!is_string($image_post_ids)){
            foreach( $image_post_ids as $image_post_id ) {
                $image_arr[] = wp_get_attachment_thumb_url( $image_post_id );
            }
        }
        $response->data['images'] = $image_arr;
    }
    return $response;
}
 
function flutter_custom_change_order_response($response, $object, $request)
{
    return customOrderResponse($response, $object, $request);
}

function flutter_custom_change_product_response($response, $object, $request)
{
    return customProductResponse($response, $object, $request);
}

function flutter_custom_change_product_attribute($response, $item, $request)
{
    $taxonomy = wc_attribute_taxonomy_name($item->attribute_name);

    // Get list attribute terms based on attribute.
    $terms = get_filtered_term_product_counts($request, $taxonomy);

    $is_visible = false;
    // Show this attribute if any attribute terms have product quantity > 0
    foreach ($terms as $key => $term) {
        if ($term['term_count'] > 0) {
            $is_visible = true;
            break;
        }
    }

    $response->data['is_visible'] = $is_visible;

    return $response;
}

/// Custom response for product a tag or brand
function flutter_custom_change_product_taxonomy($response, $item, $request)
{
    // There is only a maximum of 1 entry because term_ids is an array of a
    // unique id
    $terms = get_filtered_term_product_counts($request, $item->taxonomy, $item->term_id);

    if (!empty($terms)) {
        $term = $terms[0];
        $count = (int)$term['term_count'];
        $response->data['count'] = $count;
        $response->data['is_visible'] = $count > 0;
    } else {
        $response->data['count'] = 0;
        $response->data['is_visible'] = false;
    }

    return $response;
}

function custom_get_attribute_taxonomy_name( $slug, $product ) {
	// Format slug so it matches attributes of the product.
	$slug       = wc_attribute_taxonomy_slug( $slug );
	$attributes = $product->get_attributes();
	$attribute  = false;

	// pa_ attributes.
	if ( isset( $attributes[ wc_attribute_taxonomy_name( $slug ) ] ) ) {
		$attribute = $attributes[ wc_attribute_taxonomy_name( $slug ) ];
	} elseif ( isset( $attributes[ $slug ] ) ) {
		$attribute = $attributes[ $slug ];
	}

	if ( ! $attribute ) {
		return $slug;
	}

	// Taxonomy attribute name.
	if ( $attribute->is_taxonomy() ) {
		$taxonomy = $attribute->get_taxonomy_object();
		return $taxonomy->attribute_label;
	}

	// Custom product attribute name.
	return $attribute->get_name();
}

function custom_woocommerce_rest_prepare_product_variation_object($response, $object, $request)
{

    global $woocommerce_wpml;

    //update correct product price with tax setting
    $response->data['price'] = wc_get_price_to_display(  $object );
    $response->data['regular_price'] = wc_get_price_to_display(  $object, array( 'price' => $object->get_regular_price() ) );
    $response->data['sale_price'] = wc_get_price_to_display(  $object, array( 'price' => $object->get_sale_price() ) );
    
    $is_purchased = false;
    if (isset($request['user_id'])) {
        $user_id = $request['user_id'];
        $user_data = get_userdata($user_id);
        $user_email = $user_data->user_email;
        $is_purchased = wc_customer_bought_product($user_email, $user_id, $response->data['id']);
    }
    $response->data['is_purchased'] = $is_purchased;
    if (!empty($woocommerce_wpml->multi_currency) && !empty($woocommerce_wpml->settings['currencies_order'])) {

        $price = $response->data['price'];

        foreach ($woocommerce_wpml->settings['currency_options'] as $key => $currency) {
            $rate = (float)$currency["rate"];
            $response->data['multi-currency-prices'][$key]['price'] = $rate == 0 ? $price : sprintf("%.2f", $price * $rate);
        }
    }

    /*Update product price for subscription product*/
    if($object->get_type() == 'subscription_variation'){
        $meta_data = $response->data['meta_data'];
        $sign_up_fee = null;
        foreach ($meta_data as $meta_data_item) {
            if ($meta_data_item->get_data()["key"] == "_subscription_sign_up_fee") {
                $sign_up_fee = $meta_data_item->get_data()["value"];
            }
        }
        if($sign_up_fee != null){
            $response->data['regular_price']= $sign_up_fee;
            $response->data['price']= $sign_up_fee;
        }
    }

    $variation_product = wc_get_product( $response->data['id'] );
    if($variation_product) {
        $_product = wc_get_product( $variation_product->get_parent_id() );
        $attributes = array();

        foreach ( $variation_product->get_variation_attributes() as $attribute_name => $attribute ) {
				$name = str_replace( 'attribute_', '', $attribute_name );

				if ( empty( $attribute ) && '0' !== $attribute ) {
					continue;
				}

				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
					$option_term  = get_term_by( 'slug', $attribute, $name );
					$attributes[] = array(
						'id'     => wc_attribute_taxonomy_id_by_name( $name ),
						'name'   => custom_get_attribute_taxonomy_name( $name, $_product ),
						'option' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
					);
				} else {
					$attributes[] = array(
						'id'     => 0,
						'name'   => custom_get_attribute_taxonomy_name( $name, $_product ),
						'option' => $attribute,
					);
				}
			}
        $response->data['attributes'] = $attributes;
    }
    return $response;
}

// Prepare data before checkout by webview
function flutter_prepare_checkout()
{

    if(empty($_GET) && isset($_SERVER['HTTP_REFERER'])){
		$url_components = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($url_components['query'])) {
            parse_str($url_components['query'], $params);
            if(!empty($params)){
                $_GET = $params;
            }
        }
	}
    
    if (isset($_GET['mobile']) && isset($_GET['code'])) {

        $code = sanitize_text_field($_GET['code']);
        global $wpdb;
        $table_name = $wpdb->prefix . "mstore_checkout";
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE code = %s", $code);
        $item = $wpdb->get_row($sql);
        if ($item) {
            $data = json_decode(urldecode(base64_decode($item->order)), true);
        } else {
            return var_dump("Can't not get the order");
        }

        $shipping = isset($data['shipping']) ? $data['shipping'] : NULL;
        $billing = isset($data['billing']) ? $data['billing'] : $shipping;

        if (isset($data['token'])) {
            // Validate the cookie token
            $userId = validateCookieLogin($data['token']);
            if(!is_wp_error($userId)){
                if (isset($billing)) {
                    if(isset($billing["first_name"]) && !empty($billing["first_name"])){
                        update_user_meta($userId, 'billing_first_name', $billing["first_name"]);
                        update_user_meta($userId, 'shipping_first_name', $billing["first_name"]);
                    }
                    if(isset($billing["last_name"]) && !empty($billing["last_name"])){
                        update_user_meta($userId, 'billing_last_name', $billing["last_name"]);
                        update_user_meta($userId, 'shipping_last_name', $billing["last_name"]);
                    }
                    if(isset($billing["company"]) && !empty($billing["company"])){
                        update_user_meta($userId, 'billing_company', $billing["company"]);
                        update_user_meta($userId, 'shipping_company', $billing["company"]);
                    }
                    if(isset($billing["address_1"]) && !empty($billing["address_1"])){
                        update_user_meta($userId, 'billing_address_1', $billing["address_1"]);
                        update_user_meta($userId, 'shipping_address_1', $billing["address_1"]);
                    }
                    if(isset($billing["address_2"]) && !empty($billing["address_2"])){
                        update_user_meta($userId, 'billing_address_2', $billing["address_2"]);
                        update_user_meta($userId, 'shipping_address_2', $billing["address_2"]);
                    }
                    if(isset($billing["city"]) && !empty($billing["city"])){
                        update_user_meta($userId, 'billing_city', $billing["city"]);
                        update_user_meta($userId, 'shipping_city', $billing["city"]);
                    }
                    if(isset($billing["state"]) && !empty($billing["state"])){
                        update_user_meta($userId, 'billing_state', $billing["state"]);
                        update_user_meta($userId, 'shipping_state', $billing["state"]);
                    }
                    if(isset($billing["postcode"]) && !empty($billing["postcode"])){
                        update_user_meta($userId, 'billing_postcode', $billing["postcode"]);
                        update_user_meta($userId, 'shipping_postcode', $billing["postcode"]);
                    }
                    if(isset($billing["country"]) && !empty($billing["country"])){
                        update_user_meta($userId, 'billing_country', $billing["country"]);
                        update_user_meta($userId, 'shipping_country', $billing["country"]);
                    }
                    if(isset($billing["email"]) && !empty($billing["email"])){
                        update_user_meta($userId, 'billing_email', $billing["email"]);
                        update_user_meta($userId, 'shipping_email', $billing["email"]);
                    }
                    if(isset($billing["phone"]) && !empty($billing["phone"])){
                        update_user_meta($userId, 'billing_phone', $billing["phone"]);
                        update_user_meta($userId, 'shipping_phone', $billing["phone"]);
                    }
                } else {
                    $billing = [];
                    $shipping = [];
    
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
    
                    if (isset($billing["first_name"]) && !isset($shipping["first_name"])) {
                        $shipping = $billing;
                    }
                    if (!isset($billing["first_name"]) && isset($shipping["first_name"])) {
                        $billing = $shipping;
                    }
                }
    
                // Check user and authentication
                $user = get_userdata($userId);
                if ($user && (!is_user_logged_in() || get_current_user_id() != $userId)) {
                    wp_set_current_user($userId, $user->user_login);
                    wp_set_auth_cookie($userId);
    
                    header("Refresh:0");
                }
                cleanupAppointmentCartData($userId);
            }
        } else {
            if (is_user_logged_in()) {
                wp_logout();
                wp_set_current_user(0);
                header("Refresh:0");
            }
        }

        if (is_plugin_active('woocommerce/woocommerce.php') == true) {
            //header("Content-Security-Policy: frame-ancestors 'self' *.yourdomain.com");
            global $woocommerce;
            WC()->session->set('refresh_totals', true);
            WC()->cart->empty_cart();

            $products = $data['line_items'];

            buildCartItemData($products, function($productId, $quantity, $variationId, $attributes, $cart_item_data){
                global $woocommerce;
                $woocommerce->cart->add_to_cart($productId, $quantity, $variationId, $attributes, $cart_item_data);
            });

            if (isset($shipping)) {
                $woocommerce->customer->set_shipping_first_name($shipping["first_name"]);
                $woocommerce->customer->set_shipping_last_name($shipping["last_name"]);
                $woocommerce->customer->set_shipping_company($shipping["company"]);
                $woocommerce->customer->set_shipping_address_1($shipping["address_1"]);
                $woocommerce->customer->set_shipping_address_2($shipping["address_2"]);
                $woocommerce->customer->set_shipping_city($shipping["city"]);
                $woocommerce->customer->set_shipping_state($shipping["state"]);
                $woocommerce->customer->set_shipping_postcode($shipping["postcode"]);
                $woocommerce->customer->set_shipping_country($shipping["country"]);
            }

            if (isset($billing)) {
                $woocommerce->customer->set_billing_first_name($billing["first_name"]);
                $woocommerce->customer->set_billing_last_name($billing["last_name"]);
                $woocommerce->customer->set_billing_company($billing["company"]);
                $woocommerce->customer->set_billing_address_1($billing["address_1"]);
                $woocommerce->customer->set_billing_address_2($billing["address_2"]);
                $woocommerce->customer->set_billing_city($billing["city"]);
                $woocommerce->customer->set_billing_state($billing["state"]);
                $woocommerce->customer->set_billing_postcode($billing["postcode"]);
                $woocommerce->customer->set_billing_country($billing["country"]);
                $woocommerce->customer->set_billing_email($billing["email"]);
                $woocommerce->customer->set_billing_phone($billing["phone"]);
            }

            if (!empty($data['coupon_lines'])) {
                $coupons = $data['coupon_lines'];
                foreach ($coupons as $coupon) {
                    $woocommerce->cart->add_discount($coupon['code']);
                }
            }

            if (!empty($data['shipping_lines'])) {
                $shippingLines = $data['shipping_lines'];
                $shippingMethods = [];
                foreach ($shippingLines as $key => $shippingLine) {
                   if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php') && !empty($shippingLine['meta_data'])) {
                        $seller_meta = array_filter($shippingLine['meta_data'],function($item){
                            return $item['key'] == 'seller_id';
                        });
                        if(!empty($seller_meta)){
                            $shippingMethods[$seller_meta[0]['value']] = $shippingLine['method_id'];
                        }else{
                            $shippingMethods[] = $shippingLine['method_id'];
                        }
                   } else{
                    $shippingMethods[] = $shippingLine['method_id'];
                   }
                }
                WC()->session->set('chosen_shipping_methods', $shippingMethods);
            }
            if (!empty($data['payment_method'])) {
                WC()->session->set('chosen_payment_method', $data['payment_method']);
            }

            if (isset($data['customer_note']) && !empty($data['customer_note'])) {
                $_POST["order_comments"] = sanitize_text_field($data['customer_note']);
                $checkout_fields = WC()->checkout->__get("checkout_fields");
                $checkout_fields["order"] = ["order_comments" => ["type" => "textarea", "class" => [], "label" => "Order notes", "placeholder" => "Notes about your order, e.g. special notes for delivery."]];
                WC()->checkout->__set("checkout_fields", $checkout_fields);
            }
        }
    }

    if (isset($_GET['cookie'])) {
        $cookie = urldecode(base64_decode(sanitize_text_field($_GET['cookie'])));
        $userId = validateCookieLogin($cookie);
        if (!is_wp_error($userId)) {
            $user = get_userdata($userId);
            if ($user !== false) {
                $cookie_elements = explode('|', $cookie);
                if (count($cookie_elements) == 4) {
                    list($username, $expiration, $token, $hmac) = $cookie_elements;
                    $_COOKIE[LOGGED_IN_COOKIE] = $cookie;
                    wp_set_current_user($userId, $user->user_login);
                    wp_set_auth_cookie($userId, true, '', $token);
                }

                if (isset($_GET['order_detail']) && isset($_GET['order_id']) && is_plugin_active('dokan-lite/dokan.php')) {
                    $url = add_query_arg(
                        [
                            'order_id' => $_GET['order_id'],
                            '_wpnonce' => wp_create_nonce('dokan_view_order'),
                        ],
                        dokan_get_navigation_url('orders')
                    );
                    wp_safe_redirect($url);
                    die;
                }

                if (isset($_GET['vendor_admin'])) {
                    global $wp;
                    $request = $wp->request;
                    wp_redirect(esc_url_raw(home_url("/" . $request)));
                    die;
                }
            }
        }
    }
}

// Add product image to order
add_filter('woocommerce_rest_prepare_shop_order_object', 'custom_woocommerce_rest_prepare_shop_order_object', 10, 1);
function custom_woocommerce_rest_prepare_shop_order_object($response)
{
    if (empty($response->data) || empty($response->data['line_items'])) {
        return $response;
    }
    $api = new WC_REST_Products_Controller();
    $req = new WP_REST_Request('GET');
    $line_items = [];
    foreach ($response->data['line_items'] as $item) {
        $product_id = $item['product_id'];
        $req->set_query_params(["id" => $product_id]);
        $res = $api->get_item($req);
        if (is_wp_error($res)) {
            $item["product_data"] = null;
        } else {
            $item["product_data"] = $res->get_data();
        }
        $line_items[] = $item;

    }
    $response->data['line_items'] = $line_items;

    // Get the value
    $bacs_info = get_option( 'woocommerce_bacs_accounts');
    $response->data['bacs_info'] = $bacs_info;
    
    return $response;
}


function mstore_register_order_refund_requested_order_status()
{
    register_post_status('wc-refund-req', array(
        'label' => esc_attr__('Refund Requested'),
        'public' => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list' => true,
        'exclude_from_search' => false,
        'label_count' => _n_noop('Refund requested <span class="count">(%s)</span>', 'Refund requested <span class="count">(%s)</span>')
    ));
}

add_action('init', 'mstore_register_order_refund_requested_order_status');


function mstore_add_custom_order_statuses($order_statuses)
{
    // Create new status array.
    $new_order_statuses = array();
    // Loop though statuses.
    foreach ($order_statuses as $key => $status) {
        // Add status to our new statuses.
        $new_order_statuses[$key] = $status;
        // Add our custom statuses.
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-refund-req'] = esc_attr__('Refund Requested');
        }
    }

    return $new_order_statuses;
}

add_filter('wc_order_statuses', 'mstore_add_custom_order_statuses');


function custom_status_bulk_edit($actions)
{
    // Add order status changes.
    $actions['mark_refund-req'] = __('Change status to refund requested');

    return $actions;
}

add_filter('bulk_actions-edit-shop_order', 'custom_status_bulk_edit', 20, 1);

add_action('rest_api_init', 'get_promptpay_qrcode_routes');
function get_promptpay_qrcode_routes()
{
    register_rest_route('promptpay', '/detail' . '/(?P<id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => function($request){
                $available_payment_methods = WC()->payment_gateways()->payment_gateways();
                $paymentMethod = $available_payment_methods['thai-promptpay-easy'];
                $order = wc_get_order($request['id'] );
                $thank_msg = $paymentMethod->thank_msg;
 $promptpay_id = $paymentMethod->promptpay_id;
 $promptpay_type = $paymentMethod->promptpay_type;
 $promptpay_name = $paymentMethod->promptpay_name;
 $include_price = $paymentMethod->include_price;
                $image_url = get_site_url() . "/wp-content/plugins/thai-promptpay-payment-easy-gateway-plugin/images/promptpay_qrcode/promptpay-qr-l.php?type=$promptpay_type&promptpay_id=$promptpay_id";
 
 if($include_price=='yes'){
 $price = $order->get_total();
 $image_url .= "&price=$price&p=1";
 }


                return  ['thank_msg' => $thank_msg, 'qrcode_url' => $image_url, 'promptpay_id' => $promptpay_id, 'promptpay_name' => $promptpay_name, 'promptpay_type' => $paymentMethod->promptpay_type_name[$promptpay_type]];
            },
            'permission_callback' => function () {
                return true;
            },
        )
    );
}