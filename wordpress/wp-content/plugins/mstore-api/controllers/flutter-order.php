<?php

class CUSTOM_WC_REST_Orders_Controller extends WC_REST_Orders_Controller
{

    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_order';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_woo_routes'));
    }

    public function register_flutter_woo_routes()
    {
        register_rest_route($this->namespace, '/create', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_new_order'),
                'permission_callback' => array($this, 'custom_create_item_permissions_check'),
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
            ),
            'schema' => array($this, 'get_public_item_schema'),
        ));

        //some reasons can't use PUT method
        register_rest_route(
            $this->namespace,
            '/update' . '/(?P<id>[\d]+)',
            array(
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type' => 'integer',
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'update_item'),
                    'permission_callback' => array($this, 'custom_create_item_permissions_check'),
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
		
		register_rest_route(
            $this->namespace,
            '/update' . '/(?P<id>[\d]+)',
            array(
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type' => 'integer',
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'update_item'),
                    'permission_callback' => array($this, 'custom_create_item_permissions_check'),
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );

        //some reasons can't use DELETE method
        register_rest_route(
            $this->namespace,
            '/delete' . '/(?P<id>[\d]+)',
            array(
                'args' => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type' => 'integer',
                    ),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'new_delete_pending_order'),
                    'permission_callback' => array($this, 'custom_delete_item_permissions_check'),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
    }

    function custom_create_item_permissions_check($request)
    {
        $cookie = $request->get_header("User-Cookie");
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return false;
            }
            $params["customer_id"] = $user_id;
            wp_set_current_user($user_id);
            $request->set_body_params($params);
            return true;
        } else {
            $params["customer_id"] = 0;
            $request->set_body_params($params);
            return true;
        }
    }

    function custom_delete_item_permissions_check($request)
    {
        $cookie = $request->get_header("User-Cookie");
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return false;
            }
            $order = wc_get_order($request['id'] );
            if($order != false){
                return  $order->get_customer_id() == 0 || $order->get_customer_id() == $user_id;
            }
        } 
        return false;
    }

    function create_new_order($request)
    {
        $params = $request->get_body_params();
        if (isset($params['fee_lines']) && count($params['fee_lines']) > 0) {
            $fee_name = $params['fee_lines'][0]['name'];
            if ($fee_name == 'Via Wallet') {
                if (is_plugin_active('woo-wallet/woo-wallet.php')) {
                    $balance = woo_wallet()->wallet->get_wallet_balance($params["customer_id"], 'Edit');
                    $total = $params['fee_lines'][0]['total'];
                    if (floatval($balance) < floatval($total) * (-1)) {
                        return new WP_Error("invalid_wallet", "The wallet is not enough to checkout", array('status' => 400));
                    }
                }
            }
        }
        if (isset($params['payment_method']) && $params['payment_method'] == 'wallet' && isset($params['total'])) {
            if (is_plugin_active('woo-wallet/woo-wallet.php')) {
                $balance = woo_wallet()->wallet->get_wallet_balance($params["customer_id"], 'Edit');
                if (floatval($balance) < floatval($params['total'])) {
                    return new WP_Error("invalid_wallet", "The wallet is not enough to checkout", array('status' => 400));
                }
            }
        }

        /*** Fix: can not save all meta_data if they have same key ***/
        $has_change = false;
        if (isset($params['line_items']) && count($params['line_items']) > 0) {
            $line_items = array();
            foreach ($params['line_items'] as $key => $value) {
               if (isset($value['meta_data']) && count($value['meta_data']) > 0){
                $meta_data = array();
                $keys = array();
                foreach ($value['meta_data'] as $k => $v) {
                    $keys[] = $v['key'];
                    $count = array_count_values($keys)[$v['key']];
                    if ($count > 1) {
                        $has_change = true;
                        $meta_data[] = ['key'=>$v['key'].' '.$count, 'value'=>$v['value']];
                    }else{
                        $meta_data[] = $v;
                    }
                }
                $value['meta_data'] = $meta_data;
                $line_items[] = $value;
               }
            }
            $params['line_items'] = $line_items;
        }
        if($has_change){
            $request = new WP_REST_Request( $request->get_method() );
		    $request->set_body_params( $params );
        }
        /************************/

        $response = $this->create_item($request);
        if(is_wp_error($response)){
            return $response;
        }
		$data = $response->get_data();

        // Send the customer invoice email.
       	$order = wc_get_order( $data['id'] );
        if($order->get_payment_method() == 'cod' || $order->has_status( array( 'processing', 'completed' ) )){
            WC()->payment_gateways();
            WC()->shipping();
            WC()->mailer()->customer_invoice( $order );
            WC()->mailer()->emails['WC_Email_New_Order']->trigger( $order->get_id(), $order, true );
            add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
            WC()->mailer()->emails['WC_Email_New_Order']->trigger( $order->get_id(), $order, true );
        }

        //add order note if payment method is tap
        if (isset($params['payment_method']) && $params['payment_method'] == 'tap' && isset($params['transaction_id'])) {
            $order->payment_complete();
            $order->add_order_note('Tap payment successful.<br/>Tap ID: '.$params['transaction_id']);
        }
		
        //update order type for wholesale
        if (class_exists('WooCommerceWholeSalePrices')) {
            global $wc_wholesale_prices;
            $wc_wholesale_prices->wwp_order->add_order_type_meta_to_wc_orders($data['id']);
        }
        
        //add order to wcfm_marketplace_orders table to show order on the vendor dashboard
        if(class_exists('WCFMmp')) {
            do_action('wcfm_manual_order_processed', $data['id'], $order, $order);
        }
        
        return  $response;
    }

    function new_delete_pending_order($request){
        add_filter( 'woocommerce_rest_check_permissions', '__return_true' );
        $response = $this->delete_item($request);
        remove_filter( 'woocommerce_rest_check_permissions', '__return_true' );
        return $response;
    }
}

new CUSTOM_WC_REST_Orders_Controller();