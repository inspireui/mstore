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
    protected $namespace_v3 = 'wc/v3/flutter';
    private $whilelist = ['id','name','slug', 'permalink','date_created','date_created_gmt','date_modified','date_modified_gmt','type','status','featured','catalog_visibility','description','short_description','sku','price','regular_price','sale_price','date_on_sale_from','date_on_sale_from_gmt','date_on_sale_to','date_on_sale_to_gmt','price_html','on_sale','purchasable','total_sales','virtual','downloadable','downloads','download_limit','download_expiry','external_url','button_text','tax_status','tax_class','manage_stock','stock_quantity','stock_status','backorders','backorders_allowed','backordered','sold_individually','weight','dimensions','shipping_required','shipping_taxable','shipping_class','shipping_class_id','reviews_allowed','average_rating','rating_count','related_ids','upsell_ids','cross_sell_ids','parent_id','purchase_note','categories','tags','images','attributes','default_attributes','variations','grouped_products','menu_order','meta_data','store','attributesData', 'variation_products'];
    private $metaDataWhilelist = ['wc_appointments_','_aftership_', '_wcfmd_','_orddd_','_minmax_product_','product_id','order_id','staff_ids','_video_url','_woofv_video_embed','_product_addons','_wholesale_price','_have_wholesale_price'];
    private $supportedLayouts = ["fourColumn","threeColumn","twoColumn","staggered","saleOff","card","listTile","largeCardHorizontalListItems","largeCard","simpleVerticalListItems","simpleList"];
    private $unSupportedVerticalLayouts = ["menu","menuCustom"];
    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_routes'));
        add_filter('wp_rest_cache/allowed_endpoints', array($this, 'wprc_add_flutter_endpoints'));
    }

    /**
     * Register the flutter caching endpoints so they will be cached.
     */
    function wprc_add_flutter_endpoints($allowed_endpoints)
    {
        if (!isset($allowed_endpoints[$this->namespace]) || !in_array('cache', $allowed_endpoints[$this->namespace])) {
            $allowed_endpoints[$this->namespace][] = 'cache';
            $allowed_endpoints[$this->namespace][] = 'category/cache';
            $allowed_endpoints[$this->namespace][] = 'widgets/cache';
        }
        return $allowed_endpoints;
    }

    public function register_flutter_routes()
    {
        $cache = array(
            'args' => array(),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_home_data'),
                'permission_callback' => array($this, 'flutter_get_items_permissions_check'),
            ),
        );
        register_rest_route($this->namespace, '/cache', $cache);
        register_rest_route($this->namespace_v3, '/cache', $cache);

        $categoryCache = array(
            'args' => array(),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_category_data'),
                'permission_callback' => array($this, 'flutter_get_items_permissions_check'),
            ),
        );
        register_rest_route($this->namespace, '/category/cache', $categoryCache);
        register_rest_route($this->namespace_v3, '/category/cache', $categoryCache);

        $widgetCache = array(
            'args' => array(),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_widgets_data'),
                'permission_callback' => array($this, 'flutter_get_items_permissions_check'),
            ),
        );
        register_rest_route($this->namespace, '/widgets/cache', $widgetCache);
        register_rest_route($this->namespace_v3, '/widgets/cache', $widgetCache);
    }

    public function flutter_get_items_permissions_check()
    {
        return isPurchaseCodeVerified();
    }

    private function get_config_file_path($lang){
        if (!isset($lang)) {
            $configs = FlutterUtils::get_all_json_files();
            if (!empty($configs)) {
                return FlutterUtils::get_json_file_path($configs[0]);
            } else {
                return new WP_Error("existed_config", "Config file hasn't been uploaded yet.", array('status' => 400));
            }
        } else {
            return FlutterUtils::get_json_file_path("config_" . $lang . ".json");
        }
    }

    private function arrayWhitelist($array, $whitelist) {
        $results = [];
        for ($i=0; $i < count($array); $i++) { 
            $results[] = array_intersect_key(
                $array[$i], 
                array_flip($whitelist)
            );
        }
        return $results;	
    }

    private function arrayMetaDataWhitelist($array) {
        return array_values(array_filter($array, function($v, $k) {
            foreach ($this->metaDataWhilelist as $whilelist) {
                $key = is_array($v) ? $v['key'] : $v->__get('key');
                if (strpos($key, $whilelist) !== false) {
                    return true;
                }
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Get Home Data for caching
     *
     * @param object $request
     *
     * @return json
     */
    public function get_home_data($request)
    {
        $lang = sanitize_text_field($request["lang"]);
        $homeCache  =  FlutterUtils::get_home_cache_path($lang);
        if($request["reset"]  == "false" && file_exists($homeCache)){
            $fileContent = file_get_contents($homeCache);
            return  json_decode($fileContent, true);
        }

        $api = new WC_REST_Products_Controller();
        $path = $this->get_config_file_path($lang);
        if(is_wp_error($path)){
            return $path;
        }
        if (file_exists($path)) {
            $fileContent = file_get_contents($path);
            $array = json_decode($fileContent, true);

            //get products for horizontal layout
            $countDataLayout = 0;
            $results = [];
            $horizontalLayout = $array["HorizonLayout"];
            foreach ($horizontalLayout as $layout) {
                if (in_array($layout['layout'], $this->supportedLayouts)) {
                    if($countDataLayout <  4){
                        $layout["data"] = $this->getProductsByLayout($layout, $api, $request);
                        $countDataLayout += 1;
                    }
                    $results[] = $layout;
                } else {
                    if (isset($layout["items"]) && count($layout["items"]) > 0) {
                        $items = [];
                        foreach ($layout["items"] as $item) {
                            if($countDataLayout <  4 && array_key_exists('layout', $item) && in_array($item['layout'], $this->supportedLayouts)){
                                $item["data"] = $this->getProductsByLayout($item, $api, $request);
                                $countDataLayout += 1;
                            }
                            
                            $items[] = $item;
                        }
                        $layout["items"] = $items;
                    }
                    $results[] = $layout;
                }
            }
            $array['HorizonLayout'] = $results;

            //get products for vertical layout
            if (isset($array["VerticalLayout"])) {
                $layout = $array["VerticalLayout"];
                if (!in_array($layout['layout'], $this->unSupportedVerticalLayouts)) {
                    if($countDataLayout <  4){
                        $layout["data"] = $this->getProductsByLayout($layout, $api, $request);
                        $countDataLayout += 1;
                    }
                    $array['VerticalLayout'] = $layout;
                }
            }

            //save data to  cache file
            file_put_contents($homeCache, json_encode($array));

            return $array;
        } else {
            return new WP_Error("existed_config", "Config file hasn't been uploaded yet.", array('status' => 400));
        }
    }

    function getProductsByLayout($layout, $api, $request)
    {
        $category = $layout['category'] ?? null;
        $tag = $layout['tag'] ?? null;
        $order = $layout['order'] ?? 'desc';
        $orderby = $layout['orderby'] ?? 'date';
        $include = $layout['include'] ?? null;
        $featured = $layout['featured'] ?? null;
        $onSale = $layout['onSale'] ?? null;
        if ($category == '-1') $category = null;
        if ($tag == '-1') $tag = null;
        
        $params = array('order' => $order, 'orderby' => $orderby, 'status' => 'publish');
        if ($category != null) {
            $params['category'] = $category;
        }
        if ($tag != null) {
            $params['tag'] = $tag;
        }
        if ($featured == true) {
            $params['featured'] = $featured;
        }
        if ((isset($layout["layout"]) && $layout["layout"] == "saleOff") || $onSale == true) {
			$params['include'] = [];
            $params['on_sale'] = true;
        } else if ($include != null && is_string($include)) {
            $params['include'] = explode(',', $include);
        }
        $limit = get_option("mstore_limit_product");
        $limit = (!isset($limit) || $limit == false) ? 10 : $limit;
        $limit = isset($layout['limit']) && is_int($layout['limit']) ? $layout['limit'] : $limit;
        $params['per_page'] = $limit;
        $params['page'] = 0;
        $params['is_all_data'] = $request->get_param('is_all_data') ?? false;
	
        if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
            $wcfmmp_radius_lat = $request->get_param('wcfmmp_radius_lat');
            $wcfmmp_radius_lng = $request->get_param('wcfmmp_radius_lng');
            $wcfmmp_radius_range = $request->get_param('wcfmmp_radius_range');
            if ($wcfmmp_radius_lat && $wcfmmp_radius_lng && $wcfmmp_radius_range) {
                $params['wcfmmp_radius_lat'] = $wcfmmp_radius_lat;
                $params['wcfmmp_radius_lng'] = $wcfmmp_radius_lng;
                $params['wcfmmp_radius_range'] = $wcfmmp_radius_range;
                $request->set_query_params($params);
                $helper = new FlutterWCFMHelper();
                return $helper->flutter_get_wcfm_products($request);
            }
        }
        
        $request->set_query_params($params);

        $response = $api->get_items($request);
        $products = $response->get_data();

        $items = [];
        foreach ($products as $item) {
            if($item['catalog_visibility'] !== 'hidden'){
                $items[] = $item;
            }
        }
        $items = $this->arrayWhitelist($items, $this->whilelist);
        foreach ($items as &$value) {
            if(isset($value['meta_data'])){
                $value['meta_data'] =  $this->arrayMetaDataWhitelist($value['meta_data']);
            }
        }
        return $items;
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
            $ids = explode(",", $ids);
        } else {
            $ids = [];
        }

        if (count($ids) > 0) {
            $results = [];
            foreach ($ids as $id) {
                $results[$id] = $this->getProductsByLayout(["category" => $id], $api, $request);
            }
            return $results;
        } else {
            return new WP_Error("empty_ids", "categoryIds is empty", array('status' => 400));
        }
    }

    public function get_widgets_data($request)
    {
        $api = new WC_REST_Products_Controller();
        $path = $this->get_config_file_path(sanitize_text_field($request["lang"]));
        if(is_wp_error($path)){
            return $path;
        }

        if (file_exists($path)) {
            $fileContent = file_get_contents($path);
            $array = json_decode($fileContent, true);

            if (isset($array["Widgets"])) {
                $layout = $array["Widgets"];
                if (isset($layout['category']) || isset($layout['tag'])) {
                    $layout["data"] = $this->getProductsByLayout($layout, $api, $request);
                }
                return $layout;
            } else {
                return new WP_Error("invalid_format", "The config file doesn't have 'Widgets' property", array('status' => 400));
            }
        } else {
            return new WP_Error("existed_config", "Config file hasn't been uploaded yet.", array('status' => 400));
        }
    }

}

new FlutterHome;