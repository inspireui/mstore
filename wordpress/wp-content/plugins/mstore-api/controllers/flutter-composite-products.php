<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Composite Products
 * Plugin: https://yithemes.com/themes/plugins/yith-woocommerce-composite-products/
 */

class FlutterCompositeProducts extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_composite_products';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_composite_products_routes'));
    }

    public function register_flutter_composite_products_routes()
    {
        register_rest_route($this->namespace, '/product'.'/(?P<id>[\d]+)'.'/components', array(
            'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type' => 'integer',
                ),
            ),
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_components'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function get_components($request)
    {
        if (!class_exists('YITH_WCP')) {
            return parent::send_invalid_plugin_error("You need to install YITH Composite Products for WooCommerce plugin to use this api");
        }
        $product_id = $request["id"];
        $components = get_post_meta( $product_id, '_ywcp_component_data_list' );
        $results = [];
        foreach ($components as $item) {
            foreach ($item as $key => &$component) {
                $productIds = [];
                if ($component["option_type"] == "product") {
                    $productIds = $component["option_type_product_id_values"];
                }
                if ($component["option_type"] == "product_categories" || $component["option_type"] == "product_tags") {
                    $args = YITH_WCP()->getProductsQueryArgs( $product_id, $component);
                    $query = new WP_Query( $args );
                    $productIds = array_map(function($post){
                        return $post->ID;
                    },$query->posts);
                }

                if(count($productIds) > 0){
                    $controller = new CUSTOM_WC_REST_Products_Controller();
                    $req = new WP_REST_Request('GET');
                    $params = array('status' =>'published', 'include' => $productIds, 'page'=>1, 'per_page'=>count($productIds));
                    $req->set_query_params($params);
                    $pRes = $controller->get_items($req);
                    $products = $pRes->get_data();
                }else{
                    $products = [];
                }
                $component['option_type_products'] = $products;
                $component['id'] = $key;
                $results[] = $component;
            }
        }
        
        return $results;
    }

}

new FlutterCompositeProducts;