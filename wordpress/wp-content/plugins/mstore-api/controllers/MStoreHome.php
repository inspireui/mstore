<?php

/*
 * Base REST Controller for mstore
 *
 * @since 1.4.0
 *
 * @package home
 */

class MStoreHome extends WP_REST_Controller
{
        /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'mstore/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'cache';


    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_mstore_routes'));
        add_filter( 'woocommerce_rest_check_permissions', array($this, 'checkPermissions'),99 );
    }

    public function checkPermissions(){
        return true;
    }

    public function register_mstore_routes()
    {
        register_rest_route($this->namespace, '/cache', array(
            'args'=>array(),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_home_data'),
            ),
        ));
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
        global $json_api;
        $path = dirname(dirname(__FILE__))."/templates/config.json";
        
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
            $json_api->error("Config file hasn't been uploaded yet.");
        }
    }

    function getProductsByLayout($layout, $api, $request)
    {
        if (isset($layout['category'])) {
            $request->set_query_params(array('category'=>$layout['category']));
        }
        if (isset($layout['tag'])) {
            $request->set_query_params(array('tag'=>$layout['tag']));
        }
        if (isset($layout['feature'])) {
            $request->set_query_params(array('featured'=>$layout['feature']));
        }
        
        $response = $api->get_items($request);
        return $response->get_data();
    }
    
}

new MStoreHome;
