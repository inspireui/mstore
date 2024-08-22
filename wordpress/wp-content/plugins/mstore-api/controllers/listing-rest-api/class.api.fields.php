<?php
require_once(__DIR__ . '/mylisting-functions.php');

class FlutterTemplate extends WP_REST_Posts_Controller
{

    protected $_template = 'listable'; // get_template
    protected $_listable = 'listable';
    protected $_listify = 'listify';
    protected $_listingPro = 'listingpro';
    protected $_myListing = 'my listing';
    protected $_jobify = 'jobify';
    protected $_listeo = 'listeo';
    protected $_customPostType = ['job_listing', 'listing']; // all custom post type
    protected $_isListable, $_isListify, $_isMyListing, $_isListingPro, $_isListeo;

    public function __construct()
    {

        $theme = wp_get_theme(get_template());
        $this->_template = strtolower($theme->get('Name'));

        if (strpos($this->_template, $this->_listeo) !== false)
        {
            $this->_isListeo = 1;
        }
        if (strpos($this->_template, $this->_myListing) !== false)
        {
            $this->_isMyListing = 1;
        }
        if (strpos($this->_template, $this->_listingPro) !== false)
        {
            $this->_isListingPro = 1;
        }
        if (strpos($this->_template, $this->_listable) !== false)
        {
            $this->_isListable = 1;
        }
        if (strpos($this->_template, $this->_listify) !== false)
        {
            $this->_isListify = 1;
        }

        if($this->_isListeo != 1 && $this->_isListingPro != 1 && $this->_isListable != 1 && $this->_isListify != 1){
            $this->_isMyListing = 1;
        }

        add_action('init', array(
            $this,
            'add_custom_type_to_rest_api'
        ));
        add_action('rest_api_init', array(
            $this,
            'register_add_more_fields_to_rest_api'
        ));

        if($this->_isListeo){
             add_filter('rest_listing_query', array(
                    $this,
                    'custom_rest_listing_query'
                ), 10, 2);
        }
    }

    /**
     * Add custom type to rest api
     */
    public function add_custom_type_to_rest_api()
    {
        global $wp_post_types, $wp_taxonomies, $post;
        if (isset($wp_post_types['job_listing']))
        {
            $wp_post_types['job_listing']->show_in_rest = true;
            $wp_post_types['job_listing']->rest_base = 'job_listing';
            $wp_post_types['job_listing']->rest_controller_class = 'WP_REST_Posts_Controller';
        }

        //be sure to set this to the name of your taxonomy!
        $taxonomy_name = array(
            'job_listing_category',
            'region',
            'features',
            'job_listing_type',
            'job_listing_region',
            'location',
            'list-tags'
        );
        if (isset($wp_taxonomies))
        {
            foreach ($taxonomy_name as $k => $name):
                if (isset($wp_taxonomies[$name]))
                {
                    $wp_taxonomies[$name]->show_in_rest = true;
                    $wp_taxonomies[$name]->rest_base = $name;
                    $wp_taxonomies[$name]->rest_controller_tclass = 'WP_REST_Terms_Controller';
                }
            endforeach;
        }

    }

    /**
     * Register more field to rest api
     */
    public function register_add_more_fields_to_rest_api()
    {

        // Blog rest api fields
        register_rest_field('post', 'image_feature', array(
            'get_callback' => array(
                $this,
                'get_blog_image_feature'
            ) ,
        ));

        register_rest_field('post', 'author_name', array(
            'get_callback' => array(
                $this,
                'get_blog_author_name'
            ) ,
        ));

        // Get Field Category Custom
        $field_cate = $this->_isListingPro ? 'listing-category' : 'job_listing_category';
        register_rest_field($field_cate, 'term_image', array(
            'get_callback' => array(
                $this,
                'get_term_meta_image'
            ) ,
        ));

        register_rest_field('listing_category', 'term_image', array(
            'get_callback' => array(
                $this,
                'get_term_meta_image'
            ) ,
        ));

        if ($this->_isListable)
        {
            register_rest_field($this->_customPostType, 'author_name', array(
                'get_callback' => array(
                    $this,
                    'get_author_meta'
                ) ,
                'update_callback' => null,
                'schema' => null,
            ));
        }

        // Listing Pro
        if ($this->_isListingPro)
        {
            register_rest_field('lp-reviews', 'author_name', array(
                'get_callback' => array(
                    $this,
                    'get_author_meta'
                ) ,
                'update_callback' => null,
                'schema' => null,
            ));

            register_rest_field($this->_customPostType, 'gallery_images', array(
                'get_callback' => array(
                    $this,
                    'get_post_gallery_images_listingPro'
                ) ,
            ));
        }

        // Listeo
        if ($this->_isListeo)
        {
            register_rest_field($this->_customPostType, 'gallery_images', array(
                'get_callback' => array(
                    $this,
                    'get_post_gallery_images_listeo'
                ) ,
            ));
            register_rest_field($this->_customPostType, 'time_slots', array(
                'get_callback' => array(
                    $this,
                    'get_service_slots'
                ) ,
            ));
            register_rest_field($this->_customPostType, 'gallery_images', array(
                'get_callback' => array(
                    $this,
                    'get_post_gallery_images_listeo'
                ) ,
            ));
            register_rest_route('wp/v2', '/check-availability', array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'check_availability'
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));
            register_rest_route('wp/v2', '/get-slots', array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'check_availability'
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));

            register_rest_route('wp/v2', '/booking', array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'booking'
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));
            register_rest_route('wp/v2', '/get-bookings', array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_bookings'
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));

            register_rest_route('wp/v2', '/payment', array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_payment_methods'
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));
        }

        // My Listing
        if ($this->_isMyListing)
        {
            /* get listing by tags for case myListing */
            register_rest_route('tags/v1', '/job_listing', array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'get_job_listing_by_tags'
                ) ,
                'args' => array(
                    'tag' => array() ,
                    'page' => array(
                        'validate_callback' => function ($param, $request, $key)
                        {
                            return is_numeric($param);
                        }
                    ) ,
                    'limit' => array(
                        'validate_callback' => function ($param, $request, $key)
                        {
                            return is_numeric($param);
                        }
                    ) ,
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));

            //add address
            register_rest_field('job_listing',
                'newaddress',
                array(
                    'get_callback'  => array($this,'_rest_get_address_data'),
                )
            );

            //add lat
            register_rest_field( 'job_listing',
                'newlat',
                array(
                    'get_callback'  => array($this,'_rest_get_lat_data'),
                )
            );

            register_rest_field( 'job_listing',
                'newlng',
                array(
                    'get_callback'  => array($this,'_rest_get_lng_data'),
                )
            );
        }

        /* --- meta field for gallery image --- */

        register_rest_field($this->_customPostType, 'comments_ratings', array(
            'get_callback' => array(
                $this,
                'get_comments_ratings'
            ) ,
            'update_callback' => null,
            'schema' => null,
        ));

        register_rest_field($this->_customPostType, 'listing_data', array(
            'get_callback' => array(
                $this,
                'get_post_meta_for_api'
            ) ,
            'schema' => null,
        ));

        register_rest_field($this->_customPostType, 'cost', array(
            'get_callback' => array(
                $this,
                'get_cost_for_booking'
            ) ,
            'schema' => null,
        ));

        register_rest_field($this->_customPostType, 'featured_image', array(
            'get_callback' => array(
                $this,
                'get_blog_image_feature'
            ) ,
            'schema' => null,
        ));

        /* Register for custom routes to rest API */
        register_rest_route('wp/v2', '/getRating/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array(
                $this,
                'get_rating'
            ) ,
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route('wp/v2', '/getReviews/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array(
                $this,
                'get_reviews'
            ) ,
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route('wp/v2', '/submitReview', array(
            'methods' => 'POST',
            'callback' => array(
                $this,
                'submitReview'
            ) ,
            'args' => array(
                'post_author' => array(
                    'validate_callback' => function ($param, $request, $key)
                    {
                        return is_numeric($param);
                    }
                ) ,
                'post_title' => array() ,
                'post_content' => array() ,
                'listing_id' => array(
                    'validate_callback' => function ($param, $request, $key)
                    {
                        return is_numeric($param);
                    }
                ) ,
                'rating' => array(
                    'validate_callback' => function ($param, $request, $key)
                    {
                        return is_numeric($param);
                    }
                )
                ),
                'permission_callback' => function () {
                    return true;
                }
        ));
        
        register_rest_route('wp/v2', '/get-nearby-listings', array(
            'methods' => 'GET',
            'callback' => array(
                $this,
                'get_nearby_listings'
            ),
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route('wp/v2/dokan', '/orders', array(
            'methods' => 'GET',
            'callback' => array(
                $this,
                'get_dokan_orders'
            ),
            'permission_callback' => function () {
                return true;
            }
        ));

        register_rest_route('wp/v2', '/get-listing-types', array(
            'methods' => 'GET',
            'callback' => array(
                $this,
                'get_listing_types'
            ),
            'permission_callback' => function () {
                return true;
            }
        ));
    }


    function get_dokan_orders($request)
    {
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $page = isset($request["page"]) ? $request["page"] : 1;
            $page = $page - 1;
            $limit = isset($request["limit"]) ? $request["limit"] : 10;
            $page = $page * $limit;

            global $wpdb;
            $postmeta_tb = $wpdb->prefix . "postmeta";
            $posts_tb = $wpdb->prefix . "posts";
            $dokan_orders_tb = $wpdb->prefix . "dokan_orders";
            $sql = $wpdb->prepare("SELECT $posts_tb.ID FROM $postmeta_tb INNER JOIN $posts_tb ON $postmeta_tb.post_id=$posts_tb.ID INNER JOIN $dokan_orders_tb ON $posts_tb.ID = $dokan_orders_tb.order_id WHERE $postmeta_tb.meta_key = '_customer_user' AND $postmeta_tb.meta_value=%s LIMIT %d OFFSET %d",$user_id,$limit,$page);
            $items = $wpdb->get_results($sql);
            if(empty($items)){
                return [];
            }
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item->ID;
            }
            add_filter( 'woocommerce_rest_check_permissions', '__return_true' );
            $controller = new CUSTOM_WC_REST_Orders_Controller();
            $req = new WP_REST_Request('GET');
            $params = ['include'=>$ids,'page' => 1, 'per_page' => $limit, 'status'=>['any']];
            $req->set_query_params($params);
            $response = $controller->get_items($req);
            remove_filter( 'woocommerce_rest_check_permissions', '__return_true' );
            return $response->get_data();
        }else{
            return new WP_Error("no_permission",  "You need to add User-Cookie in header request", array('status' => 400));
        }
    }

    public function get_listing_types($request){
        if ($this->_isMyListing) {
            $types = get_posts( [
				'post_type' => 'case27_listing_type',
				'posts_per_page' => -1,
			] );
            return  $types;
        } else {
            return new WP_Error("not_found",  "get_listing_types is not implemented", array('status' => 404));
        }
    }

    public function get_nearby_listings($request){
        $current_lat = $request['lat'];
        $current_long = $request['long'];
        $search_location = $request['search_location'];
        $radius = 100; //in km
        if(isset($request['radius'])){
            $radius =  $request['radius'];
        }
        $limit = 10;
        $offset = 0;
        if(isset($request['per_page'])){
            $limit = absint( $request['per_page'] );
        }
        if(isset($request['page'])){
            $offset = absint($request['page']);
            $offset= ($offset -1) * $limit;
        }
        
        $data = array();
        global $wpdb;
        if($this->_isListeo){

            $sql = "SELECT p.*, ";
            $sql .= " (6371 * acos (cos (radians(%f)) * cos(radians(t.lat)) * cos(radians(t.lng) - radians(%f)) + ";
            $sql .= "sin (radians(%f)) * sin(radians(t.lat)))) AS distance FROM (SELECT b.post_id, a.post_status, sum(if(";
            $sql .= "meta_key = '_geolocation_lat', meta_value, 0)) AS lat, sum(if(meta_key = '_geolocation_long', ";
            $sql .= "meta_value, 0)) AS lng FROM {$wpdb->prefix}posts a, {$wpdb->prefix}postmeta b WHERE a.id = b.post_id AND (";
            $sql .= "b.meta_key='_geolocation_lat' OR b.meta_key='_geolocation_long') AND a.post_status='publish' GROUP BY b.post_id) AS t INNER ";
            $sql .= "JOIN {$wpdb->prefix}posts as p on (p.ID=t.post_id) HAVING distance < %f";

            $sql = $wpdb->prepare($sql, $current_lat, $current_long, $current_lat, $radius);
            $posts = $wpdb->get_results($sql);
            $items = (array)($posts);
            // return $items;
            foreach ($items as $item):
                $itemdata = $this->prepare_item_for_response($item, $request);
                $data[] = $this->prepare_response_for_collection($itemdata);
            endforeach;
        }
        if( $this->_isMyListing){
            $listing_type = $request['listing_type'] ?? 'place';
            $bodyReq = ['proximity_units'=>'km','listing_type'=>$listing_type, 'form_data'=>[
                'page'=>$offset / $limit,
                'per_page'=>$limit,
                'search_keywords'=>'',
                'proximity'=>$radius,
                'lat'=>$current_lat,
                'lng'=>$current_long,
                'category'=>'',
                'search_location'=> $search_location ?? '',
                'region'=>'',
                'tags'=>'',
                'sort'=>'nearby'
                ]
            ];
			$posts =  myListingExploreListings($bodyReq);
            $items = (array)($posts);
            foreach ($items as $item):
                $itemdata = $this->prepare_item_for_response($item, $request);
                $data[] = $this->prepare_response_for_collection($itemdata);
            endforeach;

            // $sql = "SELECT p.*, ";
            // $sql .= " (6371 * acos (cos (radians($current_lat)) * cos(radians(t.lat)) * cos(radians(t.lng) - radians($current_long)) + ";
            // $sql .= "sin (radians($current_lat)) * sin(radians(t.lat)))) AS distance FROM (SELECT b.post_id, a.post_status, sum(if(";
            // $sql .= "meta_key = 'geolocation_lat', meta_value, 0)) AS lat, sum(if(meta_key = 'geolocation_long', ";
            // $sql .= "meta_value, 0)) AS lng FROM {$wpdb->prefix}posts a, {$wpdb->prefix}postmeta b WHERE a.id = b.post_id AND (";
            // $sql .= "b.meta_key='geolocation_lat' OR b.meta_key='geolocation_long') AND a.post_status='publish' GROUP BY b.post_id) AS t INNER ";
            // $sql .= "JOIN {$wpdb->prefix}posts as p on (p.ID=t.post_id) HAVING distance < {$radius}";
            // $posts = $wpdb->get_results($sql);
            // $items = (array)($posts);
            // // return $items;
            // foreach ($items as $item):
            //     $itemdata = $this->prepare_item_for_response($item, $request);
            //     $data[] = $this->prepare_response_for_collection($itemdata);
            // endforeach;
        }
        if($this->_isListingPro){
            $args = array(
                'post_type' => 'listing',
                'posts_per_page' => -1,
                'paged' => 1,
                'post_status' => 'publish'
            );
            $posts = query_posts($args);

            $items = (array)($posts);
            foreach ($items as $item):
                $this_lat = listing_get_metabox_by_ID('latitude',$item->ID);
                $this_long = listing_get_metabox_by_ID('longitude',$item->ID);
                if( !empty($this_lat) && !empty($this_long) ){
                    
                    $calDistance = GetDrivingDistance($current_lat, $this_lat, $current_long, $this_long, 'km');
                    if(!empty($calDistance['distance'])){
                        if( $calDistance['distance'] < $radius){
                            $itemdata = $this->prepare_item_for_response($item, $request);
                            $data[] = $this->prepare_response_for_collection($itemdata);
                        }
                    }
                }

            endforeach;
        }
        return $data;
    }
    // Listeo theme functions
    public function get_service_slots($object)
    {
        $slots = [];
        if ( isset($object['_slots_status']) && $object['_slots_status'] == 'on')
        {
            $slots = json_decode($object['_slots']);

        }
        return $slots;
    }

    public function get_payment_methods($object)
    {
        $cookie = $object['cookie'];
        if (!isset($cookie))
        {
            return new WP_REST_Response('You are unauthorized to do this', 401);
        }
        $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
        if (!$user_id)
        {
            return new WP_REST_Response('Invalid request', 401);
        }
        $payment_methods = WC()
            ->payment_gateways
            ->get_available_payment_gateways();
        return array_values($payment_methods);
    }

    public function check_availability($request)
    {
        if (!isset($request['slot']))
        {
            $slot = false;
        }
        else
        {
            $slot = $request['slot'];
        }
        if (isset($request['hour']))
        {
            $data['free_places'] = 1;
        }
        else
        {
            $data['free_places'] = Listeo_Core_Bookings_Calendar::count_free_places($request['listing_id'], $request['date_start'], $request['date_end'], json_encode($slot));
        }
        $multiply = 1;
        if (isset($request['adults'])) $multiply = $request['adults'];
        if (isset($request['tickets'])) $multiply = $request['tickets'];

        $coupon = (isset($request['coupon'])) ? $request['coupon'] : false;
        $services = (isset($request['services'])) ? $request['services'] : false;

        $data['price'] = Listeo_Core_Bookings_Calendar::calculate_price($request['listing_id'], $request['date_start'], $request['date_end'], $multiply, $services, '');
        if (!empty($coupon))
        {
            $data['price_discount'] = Listeo_Core_Bookings_Calendar::calculate_price($request['listing_id'], $request['date_start'], $request['date_end'], $multiply, $services, $coupon);

        }
        // $_slots = $this->update_slots($request);
        return $data;
    }

    public function get_bookings($request)
    {
        $user_id = $request['user_id'];

        $args = array(
            'bookings_author' => $user_id,
            'type' => 'reservation'
        );
        $limit = 10;
        $offset = 0;
        if (isset($request['per_page']) && isset($request['page']))
        {
            $limit = $request['per_page'];
            $offset = $request['page'];
        }
        $bookings = Listeo_Core_Bookings_Calendar::get_newest_bookings($args, $limit, $offset);

        $data = [];

        foreach ($bookings as $booking)
        {
            $item = $booking;
            if (isset($booking['order_id']))
            {
                $order_id = $booking['order_id'];
                $order = wc_get_order($order_id);
                $order_data = $order->get_data();
                $item['order_status'] = $order_data['status'];
            }
            $post_id = $booking['listing_id'];
            $item['featured_image'] = get_the_post_thumbnail_url($post_id);
            $item['title'] = get_the_title($post_id);

            $data[] = $item;

        }

        return $data;
    }

    public function update_slots($request)
    {
        $listing_id = $request['listing_id'];

        $date_end = $request['date_start'];
        $date_start = $request['date_end'];

        $dayofweek = date('w', strtotime($date_start));
        $un_slots = get_post_meta($listing_id, '_slots', true);
        $_slots = Listeo_Core_Bookings_Calendar::get_slots_from_meta($listing_id);
        if ($dayofweek == 0)
        {
            $actual_day = 6;
        }
        else
        {
            $actual_day = $dayofweek - 1;
        }
        $_slots_for_day = $_slots[$actual_day];
        $new_slots = array();

        if (is_array($_slots_for_day) && !empty($_slots_for_day))
        {
            foreach ($_slots_for_day as $key => $slot)
            {
                $places = explode('|', $slot);
                $free_places = $places[1];
                $hours = explode(' - ', $places[0]);
                $hour_start = date("H:i:s", strtotime($hours[0]));
                $hour_end = date("H:i:s", strtotime($hours[1]));
                $date_start = $date_start . ' ' . $hour_start;
                $date_end = $date_end . ' ' . $hour_end;

                $result = Listeo_Core_Bookings_Calendar::get_slots_bookings($date_start, $date_end, array(
                    'listing_id' => $listing_id,
                    'type' => 'reservation'
                ));
                $reservations_amount = count($result);
                $free_places -= $reservations_amount;
                if ($free_places > 0)
                {
                    $new_slots[] = $places[0] . '|' . $free_places;
                }
            }
        }
        return $new_slots;
    }

    static function insert_booking($args)
    {

        global $wpdb;

        $insert_data = array(
            'bookings_author' => $args['bookings_author'],
            'owner_id' => $args['owner_id'],
            'listing_id' => $args['listing_id'],
            'date_start' => date("Y-m-d H:i:s", strtotime($args['date_start'])) ,
            'date_end' => date("Y-m-d H:i:s", strtotime($args['date_end'])) ,
            'comment' => $args['comment'],
            'type' => $args['type'],
            'created' => current_time('mysql')
        );

        if (isset($args['order_id'])) $insert_data['order_id'] = $args['order_id'];
        if (isset($args['expiring'])) $insert_data['expiring'] = $args['expiring'];
        if (isset($args['status'])) $insert_data['status'] = $args['status'];
        if (isset($args['price'])) $insert_data['price'] = $args['price'];

        $wpdb->insert($wpdb->prefix . 'bookings_calendar', $insert_data);

        return $wpdb->insert_id;

    }

    public function booking($object)
    {
        $_user_id = $object['user_id'];
        $user_info = get_user_meta($_user_id);
        $u_data = get_user_by( 'id', $_user_id );

        $first_name = isset($user_info['billing_first_name']) ? $user_info['billing_first_name'][0] : $user_info['first_name'][0];
        $last_name = isset($user_info['billing_last_name']) ? $user_info['billing_last_name'][0] : $user_info['last_name'][0];
        $email = isset($user_info['billing_email']) ? $user_info['billing_email'][0] : $u_data->user_email;
        $billing_address_1 = (isset($user_info['billing_address_1'][0])) ? $user_info['billing_address_1'][0] : false;
        $billing_postcode = (isset($user_info['billing_postcode'][0])) ? $user_info['billing_postcode'][0] : false;
        $billing_city = (isset($user_info['billing_city'][0])) ? $user_info['billing_city'][0] : false;
        $billing_country = (isset($user_info['billing_country'][0])) ? $user_info['billing_country'][0] : false;
    	$billing_phone = isset($user_info['billing_phone'][0]) ? $user_info['billing_phone'][0] : false;

        $data = json_decode($object['value']);
        $date_start = isset($data->date_start) ? $data->date_start : null;
        $date_end = isset($data->date_end) ? $data->date_end : null;
        $adults = isset($data->adults) ? $data->adults : null;
        $tickets = isset($data->tickets) ? $data->tickets : null;
        $listing_id = isset($data->listing_id) ? $data->listing_id : null;
        $slot = isset($data->slot) ? $data->slot : null;
        $_hour_end = isset($data->_hour_end) ? $data->_hour_end : null;
        $_hour = isset($data->_hour) ? $data->_hour : null;
        $services = isset($data->services) ? $data->services : false;
        $comment_services = false;
        $message = '';
        if (!empty($services))
        {

            $currency_abbr = get_option('listeo_currency');
            $currency_postion = get_option('listeo_currency_postion');
            $currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
            $comment_services = array();
            $bookable_services = listeo_get_bookable_services($listing_id);

            $firstDay = new DateTime($date_start);
            $lastDay = new DateTime($date_start . '23:59:59');

            $days_between = $lastDay->diff($firstDay)->format("%a");
            $days_count = ($days_between == 0) ? 1 : $days_between;

            //since 1.3 change comment_service to json
            $countable = array_column($services, 'value');
            if (isset($adults))
            {
                $guests = $adults;
            }
            else if (isset($tickets))
            {
                $guests = $tickets;
            }
            else
            {
                $guests = 1;
            }
            $i = 0;
            foreach ($bookable_services as $key => $service)
            {

                if (in_array(sanitize_title($service['name']) , array_column($services, 'service')))
                {
                    $comment_services[] = array(
                        'service' => $service,
                        'guests' => $adults,
                        'days' => $days_count,
                        'countable' => $countable[$i],
                        'price' => listeo_calculate_service_price($service, $adults, $days_count, $countable[$i])
                    );

                    $i++;
                }

            }

        }
        $listing_meta = get_post_meta($listing_id, '', true);
        $instant_booking = get_post_meta($listing_id, '_instant_booking', true);

        if (get_transient('listeo_last_booking' . $_user_id) == $listing_id . ' ' . $date_start . ' ' . $date_end)
        {
            $message = 'booked';
            return $message;
        }

        set_transient('listeo_last_booking' . $_user_id, $listing_id . ' ' . $date_start . ' ' . $date_end, 60 * 15);

        $listing_meta = get_post_meta($listing_id, '', true);

        $listing_owner = get_post_field('post_author', $listing_id);

        switch ($listing_meta['_listing_type'][0])
        {
            case 'event':
                $comment = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $billing_phone,
                    'message' => $object['message'],
                    'tickets' => $tickets,
                    'service' => $comment_services,
                    'billing_address_1' => $billing_address_1,
                    'billing_postcode' => $billing_postcode,
                    'billing_city' => $billing_city,
                    'billing_country' => $billing_country
                );

                $booking_id = self::insert_booking(array(
                    'owner_id' => $listing_owner,
                    'bookings_author' => $_user_id,
                    'listing_id' => $listing_id,
                    'date_start' => $date_start,
                    'date_end' => $date_start,
                    'comment' => json_encode($comment) ,
                    'type' => 'reservation',
                    'price' => Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, $tickets, $services, '') ,
                ));

                $already_sold_tickets = (int)get_post_meta($listing_id, '_event_tickets_sold', true);
                $sold_now = $already_sold_tickets + $tickets;
                update_post_meta($listing_id, '_event_tickets_sold', $sold_now);

                $status = apply_filters('listeo_event_default_status', 'waiting');
                if ($instant_booking == 'check_on' || $instant_booking == 'on')
                {
                    $status = 'confirmed';
                }
                $changed_status = Listeo_Core_Bookings_Calendar::set_booking_status($booking_id, $status);
            break;
            case 'rental':
                // get default status
                $status = apply_filters('listeo_rental_default_status', 'waiting');
                // count free places
                $free_places = Listeo_Core_Bookings_Calendar::count_free_places($listing_id, $date_start, $date_end);
                if ($free_places > 0)
                {
                    $count_per_guest = get_post_meta($listing_id, "_count_per_guest", true);
                    if ($count_per_guest)
                    {
                        $multiply = 1;
                        if (isset($adults)) $multiply = $adults;
                        $price = Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, $multiply, $services, '');
                    }
                    else
                    {
                        $price = Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, 1, $services, '');
                    }

                    $booking_id = self::insert_booking(array(
                        'owner_id' => $listing_owner,
                        'listing_id' => $listing_id,
                        'bookings_author' => $_user_id,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'comment' => json_encode(array(
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'phone' => $billing_phone,
                            'message' => $object['message'],
                            'adults' => $adults,
                            'service' => $comment_services,
                            'billing_address_1' => $billing_address_1,
                            'billing_postcode' => $billing_postcode,
                            'billing_city' => $billing_city,
                            'billing_country' => $billing_country
                        )) ,
                        'type' => 'reservation',
                        'price' => $price,
                    ));
                    $status = apply_filters('listeo_event_default_status', 'waiting');
                    if ($instant_booking == 'check_on' || $instant_booking == 'on')
                    {
                        $status = 'confirmed';
                    }
                    $changed_status = Listeo_Core_Bookings_Calendar::set_booking_status($booking_id, $status);

                }
                else
                {
                    $message = 'unavailable';
                }
                break;
            case 'service':
                $status = apply_filters('listeo_service_default_status', 'waiting');
                if ($instant_booking == 'check_on' || $instant_booking == 'on')
                {
                    $status = 'confirmed';
                }
                if (!isset($slot))
                {
                    $count_per_guest = get_post_meta($listing_id, "_count_per_guest", true);
                    if ($count_per_guest)
                    {
                        $multiply = 1;
                        if (isset($adults)) $multiply = $adults;
                        $price = Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, $multiply, $services, '');
                    }
                    else
                    {
                        $price = Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, 1, $services, '');
                    }
                    $hour_end = (isset($_hour_end) && !empty($_hour_end)) ? $_hour_end : $_hour;
                    $booking_id = self::insert_booking(array(
                        'bookings_author' => $_user_id,
                        'owner_id' => $listing_owner,
                        'listing_id' => $listing_id,
                        'date_start' => $date_start . ' ' . $_hour . ':00',
                        'date_end' => $date_end . ' ' . $hour_end . ':00',
                        'comment' => json_encode(array(
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'phone' => $billing_phone,
                            'adults' => $adults,
                            'message' => $object['message'],
                            'service' => $comment_services,
                            'billing_address_1' => $billing_address_1,
                            'billing_postcode' => $billing_postcode,
                            'billing_city' => $billing_city,
                            'billing_country' => $billing_country

                        )) ,
                        'type' => 'reservation',
                        'price' => $price,
                    ));

                    $changed_status = Listeo_Core_Bookings_Calendar::set_booking_status($booking_id, $status);

                }
                else
                {
                    $free_places = Listeo_Core_Bookings_Calendar::count_free_places($listing_id, $date_start, $date_end, json_encode($slot));
                    if ($free_places > 0)
                    {
                        $slot = is_array($slot) ?  $slot : json_encode($slot);
                        $hours = explode(' - ', $slot[0]);
                        $hour_start = date("H:i:s", strtotime($hours[0]));
                        $hour_end = date("H:i:s", strtotime($hours[1]));
                        $count_per_guest = get_post_meta($listing_id, "_count_per_guest", true);

                        if ($count_per_guest)
                        {
                            $multiply = 1;
                            if (isset($adults)) $multiply = $adults;
                            $price = Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, $multiply, $services, '');
                        }
                        else
                        {
                            $price = Listeo_Core_Bookings_Calendar::calculate_price($listing_id, $date_start, $date_end, 1, $services, '');
                        }

                        $booking_id = self::insert_booking(array(
                            'bookings_author' => $_user_id,
                            'owner_id' => $listing_owner,
                            'listing_id' => $listing_id,
                            'date_start' => $date_start . ' ' . $hour_start,
                            'date_end' => $date_end . ' ' . $hour_end,
                            'comment' => json_encode(array(
                                'first_name' => $first_name,
                                'last_name' => $last_name,
                                'email' => $email,
                                'phone' => $billing_phone,
                                'adults' => $adults,
                                'message' => $object['message'],
                                'service' => $comment_services,
                                'billing_address_1' => $billing_address_1,
                                'billing_postcode' => $billing_postcode,
                                'billing_city' => $billing_city,
                                'billing_country' => $billing_country

                            )) ,
                            'type' => 'reservation',
                            'price' => $price,
                        ));

                        $status = apply_filters('listeo_service_slots_default_status', 'waiting');
                        if ($instant_booking == 'check_on' || $instant_booking == 'on')
                        {
                            $status = 'confirmed';
                        }

                        $changed_status = Listeo_Core_Bookings_Calendar::set_booking_status($booking_id, $status);

                    }
                    else
                    {
                        $message = 'unavailable';
                    }
                }
                break;
            }

            // when we have database problem with statuses
            if (!isset($changed_status))
            {
                $message = 'error';
            }

            switch ($status)
            {
                case 'waiting':
                    $message = 'waiting';
                break;
                case 'confirmed':
                    $message = 'confirmed';
                break;
                case 'cancelled':
                    $message = 'cancelled';
                break;
            }

            return $message;

        }

        public function get_post_gallery_images_listeo($object)
        {
            $results = [];
            $gallery = get_post_meta($object['id'], '_gallery', true);
            if ($gallery)
            {
                foreach ($gallery as $key => $val)
                {
                    if ($key)
                    {
                        $getVal = get_post_meta($key, '_wp_attached_file', true);
                        if (!empty($getVal))
                        {
                            $results[] = get_bloginfo('url') . '/wp-content/uploads/' . $getVal;
                        }
                    };
                }
            }
            return $results;
        }

        // End of Listeo theme functions
        

        function _rest_get_address_data( $object ) {
            //get the Post Id
            $listing_id = $object['id'];
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}mylisting_locations WHERE listing_id = %s"; //wp_it_job_details is job table
            $sql = $wpdb->prepare($sql, $listing_id);
            $results = $wpdb->get_row($sql);
                if($results) {
                    return $results->address;
            } else return ""; //return nothing 
        }

        function _rest_get_lat_data( $object ) {
            //get the Post Id
            $listing_id = $object['id'];
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}mylisting_locations WHERE listing_id = %s"; //wp_it_job_details is job table
            $sql = $wpdb->prepare($sql, $listing_id);
            $results = $wpdb->get_row($sql);
                if($results) {
                    return $results->lat;
            } else return ""; //return nothing 
        } 

        function _rest_get_lng_data( $object ) {
            //get the Post Id
            $listing_id = $object['id'];
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}mylisting_locations WHERE listing_id = %s"; //wp_it_job_details is job table
            $sql = $wpdb->prepare($sql, $listing_id);
            $results = $wpdb->get_row($sql);
                if($results) {
                    return $results->lng;
            } else return ""; //return nothing 
        }

        // Blog section
        public function get_blog_image_feature($object)
        {
            $image_feature = wp_get_attachment_image_src($object['featured_media'], 'full');
            return is_array($image_feature) && count($image_feature) > 0 ? $image_feature[0] : null;
        }

        public function get_blog_author_name($object)
        {
            $user = get_userdata($object['author']);
            return $user->display_name;
        }

        /* --- - MyListing - ---*/
        public function get_job_listing_by_tags($request)
        {
            $args = ['post_type' => 'job_listing', 'paged' => $request['page'] ? $request['page'] : 1, 'posts_per_page' => $request['limit'] ? $request['limit'] : 10, ];
            if ($request['tag'])
            {
                $args['tax_query'][] = array(
                    'taxonomy' => 'case27_job_listing_tags',
                    'field' => 'term_id',
                    'terms' => explode(',', $request['tag'])
                );
            }
            global $wpdb;
            $posts = query_posts($args);
            $data = array();
            $items = (array)($posts);
            // return $items;
            foreach ($items as $item):
                $itemdata = $this->prepare_item_for_response($item, $request);
                $data[] = $this->prepare_response_for_collection($itemdata);
            endforeach;

            return new WP_REST_Response($data, 200);

        }

        function _rest_get_address_lat_lng_data($object)
        {
            //get the Post Id
            $listing_id = $object['id'];
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}mylisting_locations WHERE listing_id = %s"; //wp_it_job_details is job table
            $sql = $wpdb->prepare($sql, $listing_id);
            $results = $wpdb->get_row($sql);
            $data = [];
            if ($results) {
                $data['address'] = $results->address;
                $data['lat'] = $results->lat;
                $data['lng'] = $results->lng;
            }
            return $data; 
        }

        /* --- - ListingPro - ---*/
        public function get_post_gallery_images_listingPro($object)
        {

            $gallery = get_post_meta($object['id'], 'gallery_image_ids', true);

            $gallery = explode(',', $gallery);
            if ($gallery)
            {
                foreach ($gallery as $value)
                {
                    $getVal = get_post_meta($value, '_wp_attached_file', true);

                    if (!empty($getVal))
                    {
                        $results[] = get_bloginfo('url') . '/wp-content/uploads/' . $getVal;
                    }
                }
            }

            return $results;
        }

        /*- --- - Listable - ---- */

        public function get_author_meta($object)
        {
            $user = get_user_meta($object['post_author']);
            if ($this->_isListingPro)
            {
                $user = get_user_meta($object['author']);
                $user = $user['first_name'][0];
            }
            return $user;

        }
        /* Meta Fields Rest API */
        /**
         * Get term meta images
         * @param $object
         * @param $field_name
         * @param $request
         * @return mixed
         */
        public function get_term_meta_image($object)
        {

            if ($this->_isListable)
            {
                $name = 'pix_term_image';
            }
            elseif ($this->_isListify)
            {
                $name = 'thumbnail_id';
            }
            elseif ($this->_isListingPro)
            {
                $name = 'lp_category_banner';
                return get_term_meta($object['id'], $name, true);
            }
            elseif ($this->_isListeo)
            {
                $name = '_cover';
                $image_id =  get_term_meta($object['id'], $name, true);
                return wp_get_original_image_url($image_id);
            }
            else
            {
                $name = 'image';
            }
            $term_meta_id = get_term_meta($object['id'], $name, true);
            return get_post_meta($term_meta_id, '_wp_attachment_metadata');
        }

        /**
         * Get comment rating
         * @param $object
         * @param $field_name
         * @param $request
         * @return array|bool
         */
        public function get_comments_ratings($object)
        {
            $meta_key = $commentKey = 'pixrating';

            if ($this->_isListify)
            {
                $meta_key = $commentKey = 'rating';
            }
            else if ($this->_isMyListing)
            {
                $meta_key = '_case27_ratings';
                $commentKey = '_case27_post_rating';
            }

            $post_id = isset($object[0]) ? $object[0] : '';
            $decimals = 1;

            if (empty($post_id))
            {
                $post_id = get_the_ID();
            }

            $comments = get_comments(array(
                'post_id' => $post_id,
                // 'meta_key' => $meta_key,
                'status' => 'approve'
            ));

            if (empty($comments))
            {
                return false;
            }

            $total = 0;
            foreach ($comments as $comment)
            {
                $current_rating = get_comment_meta($comment->comment_ID, $commentKey, true);
                $total = $total + (double)$current_rating;
            }

            $average = $total / count($comments);

            return ['totalReview' => count($comments) , 'totalRate' => number_format($average, $decimals) ];
        }

        public function get_reviews(WP_REST_Request $request)
        {
            $post_id = $request['id'];

            if (empty($post_id))
            {
                $post_id = get_the_ID();
            }
            $comments = get_comments(array(
                'post_id' => $post_id
            ));

            $results = [];
            if ($this->_isMyListing)
            {
                $commentKey = '_case27_post_rating';
            }
            else if ($this->_isListeo)
            {
                $commentKey = 'listeo-rating';
            }
            foreach ($comments as & $item)
            {
                $status = wp_get_comment_status($item->comment_ID);
                $countRating = get_comment_meta($item->comment_ID, $commentKey, true);
                $current_rating = get_comment_meta($item->comment_ID, $commentKey, true);
                $results[] = ["id" => $item->comment_ID, "rating" => $countRating, "status" => $status, "author_name" => $item->comment_author, "date" => $item->comment_date, "content" => $item->comment_content, "author_email" => $item->comment_author_email];
            }
            return $results;
        }

        public function submitReview(WP_REST_Request $request)
        {
            if ($this->_isListingPro)
            {
                $post_information = array(
                    'post_author' => $request['post_author'],
                    'post_title' => $request['post_title'],
                    'post_content' => $request['post_content'],
                    'post_type' => 'lp-reviews',
                    'post_status' => 'publish'
                );
                $postID = wp_insert_post($post_information);

                listing_set_metabox('rating', (double)$request['rating'], $postID);
                listing_set_metabox('listing_id', $request['listing_id'], $postID);
                listingpro_set_listing_ratings($postID, $request['listing_id'], $request['rating'], 'add');
                listingpro_total_reviews_add($request['listing_id']);
                return 'Success';
            }

            if ($this->_isListeo || $this->_isMyListing)
            {
                $cookie = $request->get_header("User-Cookie");
                if (isset($cookie) && $cookie != null) {
                    $user_id = validateCookieLogin($cookie);
                    if (is_wp_error($user_id)) {
                        return $user_id;
                    }
                    wp_set_current_user( $user_id );
                }
                $comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
                return $comment;
            }

            return 'Failed';
        }

        /**
         * Get meta for api
         * @param $object
         * @return mixed
         */
        public function get_post_meta_for_api($object)
        {
            $post_id = $object['id'];
            $meta = get_post_meta($post_id);
            foreach ($meta as $k => $item):
                $meta[$k] = get_post_meta($post_id, $k, true);
            endforeach;
            if($this->_isMyListing){
                $meta['_job_description'] = get_the_content($post_id);
                $listing_type = $meta['_case27_listing_type'];
                $listing_type = \MyListing\Src\Listing_Type::get_by_name( $listing_type );
                $meta['_case27_listing_type_name'] = $listing_type->get_name();
            }
            
            if (array_key_exists('_menu', $meta)) {
                $meta['_menu'] = array_map(function($item){
                    if (isset($item['menu_elements']) && !empty($item['menu_elements'])) {
                        $item['menu_elements'] = array_map(function($element){
                            if (isset($element['cover']) && !empty($element['cover'])) {
                                $image = wp_get_attachment_image_src($element['cover'], 'listeo-gallery');
								$thumb = wp_get_attachment_image_src($element['cover'], 'thumbnail');
                                $element['image'] = !empty($image) ? $image[0] : null;
                                $element['thumb'] = !empty($thumb) ? $thumb[0] : null;
                            }
                            return $element;
                        }, $item['menu_elements']);
                    }
                    return $item;
                }, $meta['_menu']);
            }
            
            return $meta;
        }

        /**
         * Get rating
         * @param WP_REST_Request $request
         * @return WP_REST_Response
         */
        public function get_rating(WP_REST_Request $request)
        {
            $name = 'pixrating';
            if ($this->_isListify)
            {
                $name = 'rating';
            }
            elseif ($this->_isMyListing)
            {
                $name = '_case27_post_rating';
            }
            $id = $request['id'];
            $countRating = get_comment_meta($id, $name, true);
            if ($countRating)
            {
                return new WP_REST_Response($countRating, 200);
            }
            return new WP_REST_Response(["status" => 404, "message" => "Not Found"], 404);
        }

        /**
         * Get cost for booking
         * @param $object
         * @param $field_name
         * @param $request
         * @return string|void
         */
        public function get_cost_for_booking($object)
        {
            $currency = get_option('woocommerce_currency');
            if ($currency)
            {
                $product_id = get_post_meta($object['id'], '_products', true);
                if ($this->_isListable)
                {
                    $_product = wc_get_product($product_id[0]);

                    if (!$_product) return;
                    return $currency . ' ' . $_product->get_price();
                }
                elseif ($this->_isListify)
                {
                    $_product = new WC_Product($product_id[0]);
                    return ['currency' => $currency, 'price' => $_product->get_price() , 'merge' => $currency . ' ' . $_product->get_price() ];
                }
                else
                {
                    $price = get_post_meta($object['id'], '_price-per-day', true);
                    return ['currency' => $currency, 'price' => $price, 'merge' => $currency != 'USD' ? $currency . ' ' . $price : $price . ' ' . $currency];
                }
            }
            return [];

        }

        public function protected_title_format()
        {
            return '%s';
        }

        public function prepare_item_for_response($post, $request)
        {
            $GLOBALS['post'] = $post;

            setup_postdata($post);

            $schema = $this->get_item_schema();
            $this->add_additional_fields_schema($schema);
            // Base fields for every post.
            $data = array();
            // echo "<pre>";
            // print_r($post);
            // echo "</pre>";
            // return;
            if (!empty($schema['properties']['id']))
            {
                $data['id'] = $post->ID;
            }

            if (!empty($schema['properties']['date']))
            {
                $data['date'] = $this->prepare_date_response($post->post_date_gmt, $post->post_date);
            }

            if (!empty($schema['properties']['date_gmt']))
            {
                // For drafts, `post_date_gmt` may not be set, indicating that the
                // date of the draft should be updated each time it is saved (see
                // #38883).  In this case, shim the value based on the `post_date`
                // field with the site's timezone offset applied.
                if ('0000-00-00 00:00:00' === $post->post_date_gmt)
                {
                    $post_date_gmt = get_gmt_from_date($post->post_date);
                }
                else
                {
                    $post_date_gmt = $post->post_date_gmt;
                }
                $data['date_gmt'] = $this->prepare_date_response($post_date_gmt);
            }

            if (!empty($schema['properties']['guid']))
            {
                $data['guid'] = array(
                    /** This filter is documented in wp-includes/post-template.php */
                    'rendered' => apply_filters('get_the_guid', $post->guid, $post->ID) ,
                    'raw' => $post->guid,
                );
            }

            if (!empty($schema['properties']['modified']))
            {
                $data['modified'] = $this->prepare_date_response($post->post_modified_gmt, $post->post_modified);
            }

            if (!empty($schema['properties']['modified_gmt']))
            {
                // For drafts, `post_modified_gmt` may not be set (see
                // `post_date_gmt` comments above).  In this case, shim the value
                // based on the `post_modified` field with the site's timezone
                // offset applied.
                if ('0000-00-00 00:00:00' === $post->post_modified_gmt)
                {
                    $post_modified_gmt = date('Y-m-d H:i:s', strtotime($post->post_modified) - (get_option('gmt_offset') * 3600));
                }
                else
                {
                    $post_modified_gmt = $post->post_modified_gmt;
                }
                $data['modified_gmt'] = $this->prepare_date_response($post_modified_gmt);
            }

            if (!empty($schema['properties']['password']))
            {
                $data['password'] = $post->post_password;
            }

            if (!empty($post->distance))
            {

                $data['distance'] = $post->distance;
            }

            $data['pure_taxonomies'] = $this->get_pure_taxonomies();
            $data['listing_data'] = $this->get_post_meta_for_api($data);
            if (!empty($schema['properties']['slug']))
            {
                $data['slug'] = $post->post_name;
            }

            if (!empty($schema['properties']['status']))
            {
                $data['status'] = $post->post_status;
            }

            if (!empty($schema['properties']['type']))
            {
                $data['type'] = $post->post_type;
            }

            if (!empty($schema['properties']['link']))
            {
                $data['link'] = get_permalink($post->ID);
            }

            if (!empty($schema['properties']['title']))
            {

                add_filter('protected_title_format', array(
                    $this,
                    'protected_title_format'
                ));

                $data['title'] = array(
                    'raw' => $post->post_title,
                    'rendered' => get_the_title($post->ID) ,
                );

                remove_filter('protected_title_format', array(
                    $this,
                    'protected_title_format'
                ));
            }
            else
            {
                // case for this is listing pro
                $data['title'] = array(
                    'raw' => $post->post_title,
                    'rendered' => get_the_title($post->ID) ,
                );
            }

            if ($this->_isListeo)
            {
                $gallery = get_post_meta($post->ID, '_gallery', true);
                if ($gallery)
                {
                    foreach ($gallery as $key => $val)
                    {
                        if ($key)
                        {
                            $getVal = get_post_meta($key, '_wp_attached_file', true);
                            if (!empty($getVal))
                            {
                                $results[] = get_bloginfo('url') . '/wp-content/uploads/' . $getVal;
                            }
                        };
                    }
                }
                $data['gallery_images'] = $results;
            }

            if($this->_isListingPro){
                $gallery = get_post_meta($post->ID, 'gallery_image_ids', true);

                $gallery = explode(',', $gallery);
                if ($gallery)
                {
                    foreach ($gallery as $value)
                    {
                        $getVal = get_post_meta($value, '_wp_attached_file', true);
    
                        if (!empty($getVal))
                        {
                            $results[] = get_bloginfo('url') . '/wp-content/uploads/' . $getVal;
                        }
                    }
                }
                $data['gallery_images'] = $results;
            }

            if ($this->_isMyListing) {
                if (!empty($schema['properties']['id'])) {
                    $location = $this->_rest_get_address_lat_lng_data($data);
                    $data['newaddress'] = $location['address'];
                    $data['newlat'] = $location['lat'];
                    $data['newlng'] = $location['lng'];
                }
            }

            $has_password_filter = false;

            if ($this->can_access_password_content($post, $request))
            {
                // Allow access to the post, permissions already checked before.
                add_filter('post_password_required', '__return_false');

                $has_password_filter = true;
            }

            if (!empty($schema['properties']['content']))
            {
                $data['content'] = array(
                    'raw' => $post->post_content,
                    /** This filter is documented in wp-includes/post-template.php */
                    'rendered' => post_password_required($post) ? '' : apply_filters('the_content', $post->post_content) ,
                    'protected' => (bool)$post->post_password,
                );
            }
            else
            {
                // case for this is a listing pro
                $data['content'] = array(
                    'raw' => $post->post_content,
                    /** This filter is documented in wp-includes/post-template.php */
                    'rendered' => post_password_required($post) ? '' : apply_filters('the_content', $post->post_content) ,
                    'protected' => (bool)$post->post_password,
                );
            }

            if (!empty($schema['properties']['excerpt']))
            {
                /** This filter is documented in wp-includes/post-template.php */
                $excerpt = apply_filters('the_excerpt', apply_filters('get_the_excerpt', $post->post_excerpt, $post));
                $data['excerpt'] = array(
                    'raw' => $post->post_excerpt,
                    'rendered' => post_password_required($post) ? '' : $excerpt,
                    'protected' => (bool)$post->post_password,
                );
            }

            if ($has_password_filter)
            {
                // Reset filter.
                remove_filter('post_password_required', '__return_false');
            }

            if (!empty($schema['properties']['author']))
            {
                $data['author'] = (int)$post->post_author;
            }

            $image = wp_get_attachment_image_src((int)get_post_thumbnail_id($post->ID));
            $data['featured_image'] = $image[0];

            if (!empty($schema['properties']['parent']))
            {
                $data['parent'] = (int)$post->post_parent;
            }

            if (!empty($schema['properties']['menu_order']))
            {
                $data['menu_order'] = (int)$post->menu_order;
            }

            if (!empty($schema['properties']['comment_status']))
            {
                $data['comment_status'] = $post->comment_status;
            }

            if (!empty($schema['properties']['ping_status']))
            {
                $data['ping_status'] = $post->ping_status;
            }

            if (!empty($schema['properties']['sticky']))
            {
                $data['sticky'] = is_sticky($post->ID);
            }

            if (!empty($schema['properties']['template']))
            {
                if ($template = get_page_template_slug($post->ID))
                {
                    $data['template'] = $template;
                }
                else
                {
                    $data['template'] = '';
                }
            }

            if (!empty($schema['properties']['format']))
            {
                $data['format'] = get_post_format($post->ID);

                // Fill in blank post format.
                if (empty($data['format']))
                {
                    $data['format'] = 'standard';
                }
            }

            if (!empty($schema['properties']['meta']))
            {
                $data['meta'] = $this
                    ->meta
                    ->get_value($post->ID, $request);

            }

            $taxonomies = wp_list_filter(get_object_taxonomies($this->post_type, 'objects') , array(
                'show_in_rest' => true
            ));

            foreach ($taxonomies as $taxonomy)
            {
                $base = !empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

                if (!empty($schema['properties'][$base]))
                {
                    $terms = get_the_terms($post, $taxonomy->name);
                    $data[$base] = $terms ? array_values(wp_list_pluck($terms, 'term_id')) : array();
                }
            }

            $context = !empty($request['context']) ? $request['context'] : 'view';
            $data = $this->add_additional_fields_to_object($data, $request);
            $data = $this->filter_response_by_context($data, $context);

            // Wrap the data in a response object.
            $response = rest_ensure_response($data);

            $response->add_links($this->prepare_links($post));

            /**
             * Filters the post data for a response.
             *
             * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
             *
             * @since 4.7.0
             *
             * @param WP_REST_Response $response The response object.
             * @param WP_Post          $post     Post object.
             * @param WP_REST_Request  $request  Request object.
             */
            return apply_filters("rest_prepare_job_listing", $response, $post, $request);
        }

        public function get_pure_taxonomies()
        {
            $return = array();
            // Get categories
            $post_categories = wp_get_post_categories($object['id']);
            foreach ($post_categories as $category)
            {
                $return['categories'][] = get_category($category);
            }
            // Get tags
            $post_tags = wp_get_post_tags($object['id']);
            if (!empty($post_tags))
            {
                $return['tags'] = $post_tags;
            }
            // Get taxonomies
            $args = array(
                'public' => true,
                '_builtin' => false
            );
            $output = 'names'; // or objects
            $operator = 'and'; // 'and' or 'or'
            $taxonomies = get_taxonomies($args, $output, $operator);
            foreach ($taxonomies as $key => $taxonomy_name)
            {
                $post_taxonomies = get_the_terms($object['id'], $taxonomy_name);
                if (is_array($post_taxonomies))
                {
                    foreach ($post_taxonomies as $key2 => $post_taxonomy)
                    {
                        $return[$taxonomy_name][] = get_term($post_taxonomy, $taxonomy_name);
                    }
                }
            }
            return $return;
        }

        /**
         * Prepare a response for inserting into a collection.
         *
         * @param WP_REST_Response $response Response object.
         * @return array Response data, ready for insertion into collection data.
         */

        public function prepare_response_for_collection($response)
        {
            if (!($response instanceof WP_REST_Response))
            {
                return $response;
            }

            $data = (array)$response->get_data();
            $server = rest_get_server();

            if (method_exists($server, 'get_compact_response_links'))
            {
                $links = call_user_func(array(
                    $server,
                    'get_compact_response_links'
                ) , $response);
            }
            else
            {
                $links = call_user_func(array(
                    $server,
                    'get_response_links'
                ) , $response);
            }

            if (!empty($links))
            {
                $data['_links'] = $links;
            }

            return $data;
        }

        public function get_job_listing_by_type($request)
        {
            $posts = query_posts(array(
                'meta_key' => '_case27_listing_type',
                'meta_value' => $request['type'],
                'post_type' => 'job_listing',
                'paged' => $request['page'],
                'posts_per_page' => $request['limit']
            ));

            $data = array();
            $items = (array)($posts);

            foreach ($items as $item):
                $itemdata = $this->prepare_item_for_response($item, $request);
                $data[] = $this->prepare_response_for_collection($itemdata);
            endforeach;

            return new WP_REST_Response($data, 200);

        }

        public function custom_rest_listing_query($args, $request){
            $is_featured = $request['featured'] == 'true';
            if($is_featured == true){
             $args['meta_key'] = '_featured';   
             $args['meta_query'] = array( 'key' => '_featured', 'value' => 'on', 'compare' => '=' );
            }
            return $args;
        }
    } // end Class
    

    // class For get case27_job_listing_tags for get All Tags to show in Filter Search
    class TemplateExtendMyListing extends WP_REST_Terms_Controller
    {
        protected $_template = 'listable'; // get_template
        protected $_listable = 'listable';
        protected $_listify = 'listify';
        protected $_myListing = 'my listing';

        protected $_customPostType = ['job_listing']; // all custom post type
        protected $_isListable, $_isListify, $_isMyListing;

        public function __construct()
        {
            global $wp_version;
            if(floatval($wp_version) < 6.0){
                /* extends from parent */
                parent::__construct('job_listing');
            }

            $isChild = strstr(strtolower(wp_get_theme()) , "child");
            if ($isChild == 'child')
            {
                $string = explode(" ", wp_get_theme());
                $this->_template = strtolower($string[0] . ' ' . $string[1]);
            }
            else
            {
                $this->_template = strtolower(wp_get_theme());
            }

            $this->_isListable = $this->_template == $this->_listable ? 1 : 0;
            $this->_isListify = $this->_template == $this->_listify ? 1 : 0;
            $this->_isMyListing = $this->_template == $this->_myListing ? 1 : 0;

            add_action('rest_api_init', array(
                $this,
                'register_add_more_fields_to_rest_api_listing'
            ));
        }

        public function register_add_more_fields_to_rest_api_listing()
        {
            // case for myListing with job_listing_type
            if ($this->_isMyListing)
            {

                register_rest_route('listing/v1', 'case27_job_listing_tags', array(
                    'methods' => 'GET',
                    'callback' => array(
                        $this,
                        'get_case27_job_listing_tags'
                    ) ,
                    'permission_callback' => function () {
                        return true;
                    }
                ));
            }

        }
        public function prepare_item_for_response($item, $request)
        {

            $schema = $this->get_item_schema();
            $data = array();

            if (!empty($schema['properties']['id']))
            {
                $data['id'] = (int)$item->term_id;
            }

            if (!empty($schema['properties']['count']))
            {
                $data['count'] = (int)$item->count;
            }

            if (!empty($schema['properties']['description']))
            {
                $data['description'] = $item->description;
            }

            if (!empty($schema['properties']['link']))
            {
                $data['link'] = get_term_link($item);
            }

            if (!empty($schema['properties']['name']))
            {
                $data['name'] = $item->name;
            }

            if (!empty($schema['properties']['slug']))
            {
                $data['slug'] = $item->slug;
            }

            if (!empty($schema['properties']['taxonomy']))
            {
                $data['taxonomy'] = $item->taxonomy;
            }

            if (!empty($schema['properties']['parent']))
            {
                $data['parent'] = (int)$item->parent;
            }

            if (!empty($schema['properties']['meta']))
            {
                $data['meta'] = $this
                    ->meta
                    ->get_value($item->term_id, $request);
            }

            $context = !empty($request['context']) ? $request['context'] : 'view';
            // $data    = $this->add_additional_fields_to_object( $data, $request );
            $data = $this->filter_response_by_context($data, $context);

            $response = rest_ensure_response($data);

            $response->add_links($this->prepare_links($item));

            /**
             * Filters a term item returned from the API.
             *
             * The dynamic portion of the hook name, `$this->taxonomy`, refers to the taxonomy slug.
             *
             * Allows modification of the term data right before it is returned.
             *
             * @since 4.7.0
             *
             * @param WP_REST_Response  $response  The response object.
             * @param object            $item      The original term object.
             * @param WP_REST_Request   $request   Request used to generate the response.
             */
            return apply_filters("rest_prepare_case27_job_listing_tags", $response, $item, $request);
        }

        public function get_case27_job_listing_tags($request)
        {
            $posts = get_terms(['case27_job_listing_tags']);
            $data = array();
            $items = (array)($posts);
            foreach ($items as $item):
                $itemdata = $this->prepare_item_for_response($item, $request);
                $data[] = $itemdata;
            endforeach;
            $result = [];
            foreach ($data as $item):
                $result[] = $item->data;
            endforeach;

            return new WP_REST_Response($result, 200);
        }

    }

    class TemplateSearch extends FlutterTemplate
    {

        public function __construct()
        {
            /* extends from parent */
            parent::__construct($this->_isListingPro ? 'listing' : 'job_listing');
            add_action('rest_api_init', array(
                $this,
                'register_fields_for_search_advance'
            ));
        }

        /*
         * define for method for search
        */
        public function register_fields_for_search_advance()
        {
            /* get search by tags & categories for case myListing */
            register_rest_route('search/v1', $this->_isListingPro ? 'listing' : 'job_listing', array(
                'methods' => 'GET',
                'callback' => array(
                    $this,
                    'search_by_myParams'
                ) ,
                'args' => array(
                    'tags' => array(
                        'validate_callback' => function ($param, $request, $key)
                        {
                            return is_string($param);
                        }
                    ) ,
                    'categories' => array(
                        // 'validate_callback' => function($param, $request, $key) {
                        // 	return is_string( $param );
                        // }
                        
                    ) ,
                    'type' => array(
                        // 'validate_callback' => function($param, $request, $key) {
                        // 	return is_string( $param );
                        // }
                        
                    ) ,
                    'regions' => array(
                        // 'validate_callback' => function($param, $request, $key) {
                        // 	return is_string( $param );
                        // }
                        
                    ) , // for listify
                    'typeListable' => array() , // for listable
                    'search' => array(
                        // 'validate_callback' => function($param, $request, $key) {
                        // 	return is_string( $param );
                        // }
                        
                    ) ,
                    'author' => array(
                        // 'validate_callback' => function($param, $request, $key) {
                        // 	return is_string( $param );
                        // }
                        
                    ) ,
                    'isGetLocate' => array(
                        'validate_callback' => function ($param, $request, $key)
                        {
                            return is_string($param);
                        }
                    ) ,
                    'lat' => array() ,
                    'long' => array() ,
                    'page' => array(
                        'validate_callback' => function ($param, $request, $key)
                        {
                            return is_numeric($param);
                        }
                    ) ,
                    'limit' => array(
                        'validate_callback' => function ($param, $request, $key)
                        {
                            return is_numeric($param);
                        }
                    ) ,
                ) ,
                'permission_callback' => function () {
                    return true;
                }
            ));

            if ($this->_isMyListing)
            {
                register_rest_route('searchExtends/v1', '/job_listing', array(
                    'methods' => 'GET',
                    'callback' => array(
                        $this,
                        'searchQuery'
                    ) ,
                    'args' => array(

                        'search' => array(
                            'validate_callback' => function ($param, $request, $key)
                            {
                                return is_string($param);
                            }
                        ) ,
                        'page' => array(
                            'validate_callback' => function ($param, $request, $key)
                            {
                                return is_numeric($param);
                            }
                        ) ,
                        'limit' => array(
                            'validate_callback' => function ($param, $request, $key)
                            {
                                return is_numeric($param);
                            }
                        ) ,
                    ) ,
                    'permission_callback' => function () {
                        return true;
                    }
                ));
            }
        }

        public function search_by_myParams($request)
        {
            $args = ['post_type' => $this->_customPostType, 'paged' => $request['page'] ? $request['page'] : 1, 'post_status' => 'publish', 'posts_per_page' => $request['limit'] ? $request['limit'] : 10, ];
            if ($request['tags'])
            {
                $args['tax_query'][] = array(
                    'taxonomy' => 'case27_job_listing_tags',
                    'field' => 'term_id',
                    'terms' => explode(',', $request['tags'])
                );
            }
            if ($request['categories'])
            {
                $args['tax_query'][] = array(
                    'taxonomy' => 'job_listing_category',
                    'field' => 'term_id',
                    'terms' => explode(',', $request['categories']) ,
                );

            }
            if ($request['type'])
            {
                $args['meta_query'] = [['key' => '_case27_listing_type', 'value' => $request['type'], 'compare' => 'LIKE', ]];
            }
            //case for listify
            if ($request['regions'])
            {
                $args['tax_query'][] = array(
                    'taxonomy' => 'job_listing_region',
                    'field' => 'term_id',
                    'terms' => explode(',', $request['regions']) ,
                );
            }
            //case for listable
            if ($request['typeListable'])
            {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'job_listing_type',
                        'field' => 'term_id',
                        'terms' => explode(',', $request['typeListable']) ,
                    ) ,
                );
            }
            if ($request['search'])
            {
                $args['s'] = $request['search'];
            }
            if ($request['author'])
            {
                $args['author'] = $request['author'];
            }

            global $wpdb;
            $posts = query_posts($args);

            if ($request['isGetLocate'])
            {
                $lat = $request['lat'];
                $long = $request['long'];
                $sql = "SELECT p.*, ";
                $sql .= " (6371 * acos (cos (radians(%f)) * cos(radians(t.lat)) * cos(radians(t.lng) - radians(%f)) + ";
                $sql .= "sin (radians(%f)) * sin(radians(t.lat)))) AS distance FROM (SELECT b.post_id, a.post_status, sum(if(";
                $sql .= "meta_key = 'geolocation_lat', meta_value, 0)) AS lat, sum(if(meta_key = 'geolocation_long', ";
                $sql .= "meta_value, 0)) AS lng FROM {$wpdb->prefix}posts a, {$wpdb->prefix}postmeta b WHERE a.id = b.post_id AND (";
                $sql .= "b.meta_key='geolocation_lat' OR b.meta_key='geolocation_long') AND a.post_status='publish' GROUP BY b.post_id) AS t INNER ";
                $sql .= "JOIN {$wpdb->prefix}posts as p on (p.ID=t.post_id)  ORDER BY distance LIMIT 30";
                
                $sql = $wpdb->prepare($sql,$lat,$long,$lat);
                $posts = $wpdb->get_results($sql, OBJECT);
                if ($wpdb->last_error)
                {
                    return 'Error: ' . $wpdb->last_error;
                }
                // return $posts;
                
            }

            $data = array();
            $items = (array)($posts);
            // return $items;
            if (count($items) > 0)
            {
                foreach ($items as $item):
                    $itemdata = $this->prepare_item_for_response($item, $request);
                    $data[] = $this->prepare_response_for_collection($itemdata);
                endforeach;
            }

            return new WP_REST_Response($data, 200);
        }

        public function searchQuery($request)
        {
            $args = ['post_type' => 'job_listing', 'paged' => $request['page'] ? $request['page'] : 1, 'post_status' => 'publish', 'posts_per_page' => $request['limit'] ? $request['limit'] : 10, ];
            if ($request['search'])
            {
                $args['s'] = $request['search'];
            }

            $categories = get_terms(['taxonomy' => 'job_listing_category', 'search' => isset($request['search']) ? $request['search'] : '', ]);

            $args['meta_query'] = [['key' => '_case27_listing_type', 'value' => '', 'compare' => '!=', ]];

            global $wpdb;
            $listings = query_posts($args);

            $data = array();
            $items = (array)($listings);
            // return $items;
            foreach ($items as $item):
                $itemdata = $this->prepare_item_for_response($item, $request);
                $data[] = $this->prepare_response_for_collection($itemdata);
            endforeach;

            $listings_grouped = [];

            foreach ($data as $listing)
            {
                // return $listing['job_listing_category'][0];
                foreach ($listing['job_listing_category'] as $value)
                {
                    $type = get_term_by('id', $value, 'job_listing_category')->name;
                    if (!isset($listings_grouped[$type])) $listings_grouped[$type] = [];

                    $listings_grouped[$type][] = $listing;
                }

            }

            return new WP_REST_Response($listings_grouped, 200);
        }
    }

    new FlutterTemplate;
    new TemplateExtendMyListing;
    new TemplateSearch;