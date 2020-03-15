<?php

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package home
 */

class FlutterHome extends WP_REST_Controller
{
        /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'wc/v2/flutter';//prefix must be wc/ or wc- to reuse check permission function in woo commerce


    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_routes'));
        add_filter( 'wp_rest_cache/allowed_endpoints', array($this, 'wprc_add_flutter_endpoints'));
    }

    /**
     * Register the flutter caching endpoints so they will be cached.
     */
    function wprc_add_flutter_endpoints( $allowed_endpoints ) {
        if ( ! isset( $allowed_endpoints[ $this->namespace ] ) || ! in_array( 'cache', $allowed_endpoints[ $this->namespace ] ) ) {
            $allowed_endpoints[ $this->namespace ][] = 'cache';
            $allowed_endpoints[ $this->namespace ][] = 'category/cache';
        }
        return $allowed_endpoints;
    }

    public function register_flutter_routes()
    {
        register_rest_route($this->namespace, '/cache', array(
            'args'=>array(),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_home_data'),
                'permission_callback' => array( $this, 'flutter_get_items_permissions_check' ),
            ),
        ));

        register_rest_route($this->namespace, '/category/cache', array(
            'args'=>array(),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_category_data'),
                'permission_callback' => array( $this, 'flutter_get_items_permissions_check' ),
            ),
        ));
    }

    public function flutter_get_items_permissions_check(){
       return wc_rest_check_post_permissions( "product", 'read' );
    }

    /**
     * Get Home Data for caching
     *
     * @param object $request
     *
     * @return json
     */
    public function get_home_data()
    {
        $api = new WC_REST_Products_Controller();
        $request = new WP_REST_Request('GET');
        $path = str_replace('plugins/mstore-api/controllers','uploads',dirname( __FILE__ ))."/2000/01/config.json";
        
        if (file_exists($path)) {
            $fileContent = file_get_contents($path);
            $array = json_decode($fileContent, true);

            //get products for horizontal layout
            $results = [];
            $horizontalLayout = $array["HorizonLayout"];
            foreach ($horizontalLayout as $layout) {
                if (isset($layout['category']) || isset($layout['tag'])) {
                    $layout["data"] = $this->getProductsByLayout($layout, $api, $request);
                    $results[] = $layout;
                }else{
                    if(isset($layout["items"]) && count($layout["items"]) > 0){
                        $items = [];
                        foreach ($layout["items"] as $item) {
                            $item["data"] = $this->getProductsByLayout($item, $api, $request);
                            $items[] = $item;
                        }
                        $layout["items"] = $items;
                    }
                    $results[] = $layout;
                }
            }
            $array['HorizonLayout'] = $results;

            //get products for vertical layout
            $layout = $array["VerticalLayout"];
            if (isset($layout['category'])) {
                $layout["data"] = $this->getProductsByLayout($layout, $api, $request);
                $array['VerticalLayout'] = $layout;
            }
            
            return $array;
        }else{
            return new WP_Error( "existed_config", "Config file hasn't been uploaded yet.", array( 'status' => 400 ) );
        }
    }

    function getProductsByLayout($layout, $api, $request)
    {
        $params = array('order'=> 'desc', 'orderby' => 'date');
        if (isset($layout['category'])) {
            $params['category'] = $layout['category'];
        }
        if (isset($layout['tag'])) {
            $params['tag'] = $layout['tag'];
        }
        if (isset($layout['feature'])) {
            $params['feature'] = $layout['feature'];
        }

        $request->set_query_params($params);

        $response = $api->get_items($request);
        return $response->get_data();
    }
    

    /**
     * Get Category Data for caching
     *
     * @param object $request
     *
     * @return json
     */
    public function get_category_data($request)
    {
        $api = new WC_REST_Products_Controller();
        $ids = $request["categoryIds"];
        if (isset($ids)) {
            $ids = explode(",",$ids);
        }else{
            $ids = [];
        }

        if(count($ids) > 0){
            $results = [];
            foreach ($ids as $id) {
                $results[$id] = $this->getProductsByLayout(["category"=>$id], $api, $request);
            }
            return $results;
        }else{
            return new WP_Error( "empty_ids", "categoryIds is empty", array( 'status' => 400 ) );
        }
    }
    
}

new FlutterHome;
