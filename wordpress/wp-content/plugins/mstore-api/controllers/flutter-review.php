<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Review
 */

class FlutterReview extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_review';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_review_routes'));
        add_filter('duplicate_comment_id', array($this, 'duplicate_comment_id_callback'), 10, 2);
    }

    public function register_flutter_review_routes()
    {
        register_rest_route($this->namespace, '/products', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_products_to_rate'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    function duplicate_comment_id_callback( $dupe_id, $commentdata ) {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        if(isset($body['comment_meta']) && is_array($body['comment_meta']) && !empty($body['comment_meta']['order_id'])){
            $commentArg = array(
                'post_id' => $commentdata['comment_post_ID'],
                'meta_query'=>[
                    ['key' => 'order_id', 'value' => $body['comment_meta']['order_id']]
                ],
            );
            $comments = get_comments( $commentArg );
            if (count($comments) > 0) {
                return $comments[0]->comment_ID;
            }
            return '';
        }
        return $dupe_id;
    }

    public function get_products_to_rate($request)
    {
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            // GET USER ORDERS (COMPLETED + PROCESSING)
            $customer_orders = wc_get_orders( array(
                'limit' => -1,
                'customer_id' => $user_id,
                'status' => array_values( wc_get_is_paid_statuses() ),
                'return' => 'ids',
            ) );
        
            // LOOP THROUGH ORDERS AND GET PRODUCT IDS
            if ( ! $customer_orders ) return [];
            $product_order_items = array();

            foreach ( $customer_orders as $customer_order_id ) {
                $order = wc_get_order( $customer_order_id );
                $items = $order->get_items();
                foreach ( $items as $item ) {
                    $product_id = $item->get_product_id();

                    $commentArg = array(
                        'post_id' => $product_id,
                        'meta_query'=>[
                            ['key' => 'order_id', 'value' => $customer_order_id]
                        ],
                        'count' => true
                    );
                    $count = get_comments( $commentArg );
                    if($count == 0){
                        $product_order_items[] = ['product_id' => $product_id, 'order' => $order->get_data()];
                    }
                }
            }
            if(count($product_order_items) > 0){
                function get_product_id($v)
                {
                    return($v['product_id']);
                }
                $product_ids = array_unique( array_map("get_product_id",$product_order_items) );
                $controller = new CUSTOM_WC_REST_Products_Controller();
                $req = new WP_REST_Request('GET');
                $params = array('include' => $product_ids, 'page'=>1, 'per_page'=>count($product_ids), 'orderby' => 'id', 'order' => 'ASC');
                $req->set_query_params($params);
                $pRes = $controller->get_items($req);
                $products = $pRes->get_data();

                return array_map(function($v) use ($products) { 
                    foreach ( $products as $product_data ) {
                        if($product_data['id'] == $v['product_id']){
                            $v['product_data'] = $product_data;
                            break;
                        }
                    }
                    return $v;
                 },$product_order_items);
            }
            return [];
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }
}

new FlutterReview;