<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Store Locator
 * Plugin: https://yithemes.com/themes/plugins/yith-store-locator-wordpress/
 */

class FlutterStoreLocator extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_store_locator';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_store_locator_routes'));
    }

    public function register_flutter_store_locator_routes()
    {
        register_rest_route($this->namespace, '/stores', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_stores'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        register_rest_route($this->namespace, '/stores'.'/(?P<id>[\d]+)'.'/products', array(
            'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type' => 'integer',
                ),
            ),
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_products'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function get_stores($request)
    {
        if (!is_plugin_active('yith-store-locator-for-wordpress/init.php')) {
            return parent::send_invalid_plugin_error("You need to install YITH Store Locator for WordPress & WooCommerce plugin to use this api");
        }
        $params = [];
        if(isset($request["latitude"]) && isset($request["longitude"])){
            $params['latitude'] = $request["latitude"];
            $params['longitude'] = $request["longitude"];
        }
         $params['show_all'] = $request["show_all"] == true;
        if(isset($request["radius"])){
            $params['filters'] = ['radius' => [$request["radius"]]];
        }

        $results = [];
		delete_transient( 'yith_sl_stores' );
        $stores = yith_sl_get_stores($params);
	
        //customize for YITH WooCommerce Checkout Manager
        $fields = get_option( 'ywccp_fields_shipping_options', array() );
        function find_field($slug, $fields){
            foreach ($fields as $key => $value) {
                if($value['condition_value'] == $slug.'|pick-up'){
                    return $value['label'];
                }
            }
            return "";
        }
        //
        
        for ( $i = 0; $i < count($stores); $i++ ){
            $store = YITH_Store_Locator_Store( $stores[ $i ]['id'] );
			if($store->get_image()){
				preg_match( '@src="([^"]+)"@' , $store->get_image(), $match );
				$src = array_pop($match);
			}else{
				$src = null;
			}
            $shipping_address = find_field($stores[ $i ]['slug'], $fields);
            $results[] = [
                'id' => $store->get_id(),
                'name' => $store->get_name(),
                'slug' => $stores[ $i ]['slug'],
                'shipping_address' => $shipping_address,
                'description' => $store->get_description(),
                'image' => $src,
                'address' => trim(preg_replace("/^(<br \/>)/", "", trim($store->get_full_address()), 1)),
                'link' => $store->get_store_name_link(),
                'direction_link' => $store->get_direction_link(),
                'marker_icon' => $store->get_marker_icon(),
                'latitude' => $store->get_prop('latitude'),
                'longitude' => $store->get_prop('longitude'),
                'phone' => $store->get_prop('phone'),
                'mobile_phone' => $store->get_prop('mobile_phone'),
                'fax' => $store->get_prop('fax'),
                'email' => $store->get_prop('email'),
                'website' => $store->get_prop('website'),
            ];
        }

        return $results;
    }

    public function get_products($request)
    {
        if (!is_plugin_active('yith-store-locator-for-wordpress/init.php')) {
            return parent::send_invalid_plugin_error("You need to install YITH Store Locator for WordPress & WooCommerce plugin to use this api");
        }
        $store_id = $request["id"];
        $page = 1;
        $per_page = 10;
        if (isset($request['page'])) {
            $page = sanitize_text_field($request['page']);
            if(!is_numeric($page)){
                $page = 1;
            }
        }
        if (isset($request['per_page'])) {
            $per_page = sanitize_text_field($request['per_page']);
            if(!is_numeric($per_page)){
                $per_page = 10;
            }
        }
        $offset = ($page - 1) * $per_page;

        //get product ids
        global $wpdb;
		$results  = $wpdb->get_results( $wpdb->prepare( 'SELECT product_id FROM ' . $wpdb->prefix . 'yith_sl_stores_relationship WHERE store_id=%s LIMIT %d OFFSET %d', $store_id, $per_page, $offset ) ); //phpcs:ignore.
		$products = array();
		foreach ( $results as $result ) {
			$products[] = $result->product_id;
		}

        if(count($products) > 0){
            $controller = new CUSTOM_WC_REST_Products_Controller();
            $req = new WP_REST_Request('GET');
            $params = array('status' =>'published', 'include' => $products, 'page'=>1, 'per_page'=>$limit);
            $req->set_query_params($params);
            $response = $controller->get_items($req);

            return $response;
        }else{
            return [];
        }
        
    }
}

new FlutterStoreLocator;