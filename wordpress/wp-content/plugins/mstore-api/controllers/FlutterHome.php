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
        return get_option('mstore_purchase_code') === "1";
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
        $api = new WC_REST_Products_Controller();
        $lang = $request["lang"];
        $uploads_dir = wp_upload_dir();
        if (!isset($lang)) {
            $folder = trailingslashit($uploads_dir["basedir"]) . "/2000/01";
            $files = scandir($folder);
            $configs = [];
            foreach ($files as $file) {
                if (strpos($file, "config") !== false && strpos($file, ".json") !== false) {
                    $configs[] = $file;
                }
            }
            if (!empty($configs)) {
                $path = $uploads_dir["basedir"] . "/2000/01/" . $configs[0];
            } else {
                return new WP_Error("existed_config", "Config file hasn't been uploaded yet.", array('status' => 400));
            }
        } else {
            $path = $uploads_dir["basedir"] . "/2000/01/config_" . $lang . ".json";
        }

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
                } else {
                    if (isset($layout["items"]) && count($layout["items"]) > 0) {
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
            if (isset($array["VerticalLayout"])) {
                $layout = $array["VerticalLayout"];
                if (isset($layout['category'])) {
                    $layout["data"] = $this->getProductsByLayout($layout, $api, $request);
                    $array['VerticalLayout'] = $layout;
                }
            }

            return $array;
        } else {
            return new WP_Error("existed_config", "Config file hasn't been uploaded yet.", array('status' => 400));
        }
    }

    function getProductsByLayout($layout, $api, $request)
    {
        if ((!isset($layout['category']) && !isset($layout['tag'])) || (isset($layout['category']) && ($layout['category'] == null || $layout['category'] == "-1")) || (isset($layout['tag']) && ($layout['tag'] == null || $layout['tag'] == "-1"))) {
            return [];
        }
        $params = array('order' => 'desc', 'orderby' => 'date', 'status' => 'publish');
        if (isset($layout['category'])) {
            $params['category'] = $layout['category'];
        }
        if (isset($layout['tag'])) {
            $params['tag'] = $layout['tag'];
        }
        if (isset($layout['feature'])) {
            $params['feature'] = $layout['feature'];
        }
        if (isset($layout["layout"]) && $layout["layout"] == "saleOff") {
            $params['on_sale'] = "true";
        }
        $limit = get_option("mstore_limit_product");
        $limit = (!isset($limit) || $limit == false) ? 10 : $limit;
        $params['per_page'] = $limit;
        $params['page'] = 0;

        $request->set_query_params($params);

        $response = $api->get_items($request);
        $products = $response->get_data();
        return $products;
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
        $lang = $request["lang"];
        $uploads_dir = wp_upload_dir();
        if (!isset($lang)) {
            $folder = trailingslashit($uploads_dir["basedir"]) . "/2000/01";
            $files = scandir($folder);
            $configs = [];
            foreach ($files as $file) {
                if (strpos($file, "config") !== false && strpos($file, ".json") !== false) {
                    $configs[] = $file;
                }
            }
            if (!empty($configs)) {
                $path = $uploads_dir["basedir"] . "/2000/01/" . $configs[0];
            } else {
                return new WP_Error("existed_config", "Config file hasn't been uploaded yet.", array('status' => 400));
            }
        } else {
            $path = $uploads_dir["basedir"] . "/2000/01/config_" . $lang . ".json";
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
