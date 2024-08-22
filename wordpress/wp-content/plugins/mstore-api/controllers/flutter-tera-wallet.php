<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Tera Wallet
 */

class FlutterTeraWallet extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_tera_wallet';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_tera_wallet_routes'));
    }

    public function register_flutter_tera_wallet_routes()
    {
        register_rest_route($this->namespace, '/transactions', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_transactions'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/balance', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_balance'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/transfer', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'transfer'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/check_recharge', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'check_recharge'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/process_payment', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'process_payment'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/partial_payment', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'partial_payment'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/check_email', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'check_email'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/payment_methods', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_payment_methods'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/payment_settings', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_payment_settings'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
            array(
                'methods' => "POST",
                'callback' => array($this, 'save_payment_settings'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/pending_requests', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_pending_requests'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/approved_requests', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_approved_requests'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/cancelled_requests', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_cancelled_requests'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/submit_request', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'submit_request'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/referrals', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_referrals'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    private function getUserInfo($user_id, &$cachedUsers = [])
    {
        if (!isset($cachedUsers[$user_id])) {
            $user = get_userdata($user_id);
            if ($user) {
                $cachedUsers[$user_id] = array(
                    "id" => $user->ID,
                    "username" => $user->user_login,
                    "nicename" => $user->user_nicename,
                    "email" => $user->user_email,
                    "displayname" => $user->display_name,
                    "firstname" => $user->user_firstname,
                    "lastname" => $user->last_name,
                    "nickname" => $user->nickname,
                    "description" => $user->user_description,
                );
            }
        }
        return $cachedUsers[$user_id];
    }

    public function get_transactions($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $page = isset($request['page']) ? $request['page'] : 0;
            $length = isset($request['length']) ? $request['length'] : 10;
            $page = $page * $length;
            $args = array(
                'limit' => "$page, $length",
                'user_id' => $user_id
            );
            $data = get_wallet_transactions($args);

            $cachedUsers = [];
            foreach ($data as &$item) {
                $item->user = $this->getUserInfo($item->user_id, $cachedUsers);
                $item->created_by = $this->getUserInfo($item->created_by, $cachedUsers);
                unset($item->user_id);
            }

            return $data;
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }


    public function get_balance($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $data = woo_wallet()->wallet->get_wallet_balance($user_id, 'Edit');
            return $data;
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }

    public function transfer($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            $json = file_get_contents('php://input');
            $params = json_decode($json, TRUE);
            $user = get_user_by('email', $params['to']);
            if (!$user) {
                return parent::sendError("user_not_found", "The user is not found", 400);
            }
            wp_set_current_user($user_id);
            $_POST['woo_wallet_transfer_user_id'] = $user->id;
            $_POST['woo_wallet_transfer_amount'] = $params['amount'];
            $_POST['woo_wallet_transfer_note'] = sanitize_text_field($params['note']);
            $_POST['woo_wallet_transfer'] = wp_create_nonce('woo_wallet_transfer');


            include_once(WOO_WALLET_ABSPATH . 'includes/class-woo-wallet-frontend.php');
            return Woo_Wallet_Frontend::instance()->do_wallet_transfer();
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }

    public function check_recharge($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $_POST['woo_wallet_topup'] = wp_create_nonce('woo_wallet_topup');
        include_once(WOO_WALLET_ABSPATH . 'includes/class-woo-wallet-frontend.php');
        $wallet_product = get_wallet_rechargeable_product();

        $check = Woo_Wallet_Frontend::instance()->is_valid_wallet_recharge_amount($params['amount']);

        if ($check['is_valid'] == false) {
            return $check;
        }
        $api = new WC_REST_Products_Controller();
        $req = new WP_REST_Request('GET');
        $req->set_query_params(["id" => $wallet_product->id]);
        $res = $api->get_item($req);
        if (is_wp_error($res)) {
            return $res;
        } else {
            return $res->get_data();
        }
    }

    public function process_payment($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            wp_set_current_user($user_id);
            $order = wc_get_order($params['order_id']);
            if (($order->get_total('edit') > woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit')) && apply_filters('woo_wallet_disallow_negative_transaction', (woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit') <= 0 || $order->get_total('edit') > woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit')), $order->get_total('edit'), woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit'))) {
                $error = sprintf(__('Your wallet balance is low. Please add %s to proceed with this transaction.', 'woo-wallet'), $order->get_total('edit') - woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit'));
                return parent::sendError("wallet_error", $error, 400);
            }
            if($order->get_payment_method() == "wallet"){
				$wallet_response = woo_wallet()->wallet->debit(get_current_user_id(), $order->get_total('edit'), apply_filters('woo_wallet_order_payment_description', __('For order payment #', 'woo-wallet') . $order->get_order_number(), $order));
				if ($wallet_response) {
                    $order->set_transaction_id( $wallet_response );
					do_action( 'woo_wallet_payment_processed', $params['order_id'], $wallet_response );
					$order->save();

                    // Reduce stock levels.
                    wc_reduce_stock_levels( $params['order_id'] );

                    // Complete order payment.
                    $order->payment_complete();
				}else{
                    return parent::sendError("error_debit", "Something went wrong with processing payment please try again.", 400);
                }
			}else{
				$order->payment_complete();
			}

            // Return thankyou redirect
            return array(
                'result' => 'success',
            );
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }

    public function partial_payment($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $order = wc_get_order($params['order_id']);
            if ($order) {
                if ($order->get_customer_id() == $user_id) {
                    wp_set_current_user($user_id);
                    woo_wallet()->wallet->wallet_partial_payment($params['order_id']);
                    return array(
                        'result' => 'success',
                    );
                } else {
                    return parent::sendError("no_permission", "No Permission", 400);
                }
            } else {
                return parent::sendError("not_found", "Order not found", 400);
            }
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }

    public function check_email($request)
    {
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            $user = get_user_by('email', $params['email']);
            if ($user) {
                $avatar = get_user_meta($user->ID, 'user_avatar', true);
                if (!isset($avatar) || $avatar == "" || is_bool($avatar)) {
                    $avatar = get_avatar_url($user->ID);
                } else {
                    $avatar = $avatar[0];
                }
                return array(
                    "id" => $user->ID,
                    "username" => $user->user_login,
                    "nicename" => $user->user_nicename,
                    "email" => $user->user_email,
                    "url" => $user->user_url,
                    "displayname" => $user->display_name,
                    "firstname" => $user->user_firstname,
                    "lastname" => $user->last_name,
                    "nickname" => $user->nickname,
                    "description" => $user->user_description,
                    "avatar" => $avatar,
                );
            } else {
                return parent::sendError("not_found", "The user is not found", 400);
            }
        }

    }

    public function get_payment_methods($request){
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }
        return woo_wallet_withdrawal()->gateways->get_available_gateways();
    }

    public function get_payment_settings($request)
    {
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }
         $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $payment_method = $request['payment_method'];
            $results = [];
            if(isset($payment_method)){
                if('bacs' === $payment_method){
                    $bank_account_details = woo_wallet_withdrawal()->get_bank_account_settings();
                    foreach ($bank_account_details as $account_details){
                        $results[$account_details['name']] = get_user_meta($user_id, '_'.$account_details['name'], true);
                    }
                }
                if('paypal' === $payment_method){
                    $results['woo_wallet_withdrawal_paypal_email'] = get_user_meta($user_id, '_woo_wallet_withdrawal_paypal_email', true);
                }
                return $results;
            }else{
                return parent::sendError("required_params", "payment_method is required", 401);
            }
        }else{
            return parent::sendError("required_login", "Require to login to use this api", 401);
        }
    }

    public function save_payment_settings($request)
    {
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            $payment_method = $params['payment_method'];
            $user = new WP_User($user_id);
            update_user_meta($user_id, '_wallet_withdrawal_method', $payment_method);
            if('bacs' === $payment_method){
                $bank_account_details = woo_wallet_withdrawal()->get_bank_account_settings();
                
                foreach ($bank_account_details as $details){
                    $meta_value = isset($params[$details['name']]) && !empty($params[$details['name']]) ? wc_clean($params[$details['name']]) : '';
                    update_user_meta($user_id, '_'.$details['name'], $meta_value);
                }
                return true;
            }
            if('paypal' === $payment_method){
                $woo_wallet_withdrawal_paypal_email = !empty($params['woo_wallet_withdrawal_paypal_email']) ? wc_clean($params['woo_wallet_withdrawal_paypal_email']) : '';
                update_user_meta($user_id, '_woo_wallet_withdrawal_paypal_email', $woo_wallet_withdrawal_paypal_email);
                return true;
            }
        }
        return false;
    }

    public function get_pending_requests($request){
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $args = array(
                'posts_per_page' => -1,
                'author' => $user_id,
                'post_type' => WOO_Wallet_Withdrawal_Post_Type::$post_type,
                'post_status' => 'ww-pending',
                'suppress_filters' => true
            );
            $withdrawal_requests = get_posts($args);
            if($withdrawal_requests){
                $results = [];
                foreach ($withdrawal_requests as $withdrawal_request){
                    $withdrawal = new Wallet_Withdrawal_Post($withdrawal_request->ID);
                    $item = array();
                    $item['price'] = get_post_meta($withdrawal_request->ID, '_wallet_withdrawal_amount', true);
                    $item['status'] = get_post_status_object(get_post_status($withdrawal_request->ID))->label;
                    $item['time'] = wc_string_to_datetime($withdrawal_request->post_date)->date_i18n(wc_date_format());
                    $item['payment'] = $withdrawal->get_payment_method_title();
                    $results[] = $item;
                }
                return $results;
            }else if (woo_wallet()->settings_api->get_option('_min_withdrawal_limit', '_wallet_settings_withdrawal', 0) > woo_wallet()->wallet->get_wallet_balance($user_id, 'edit')){
                return parent::sendError("error_limit", sprintf(__('Minimum withdrawal limit is %s', 'woo-wallet-withdrawal'), wc_price(woo_wallet()->settings_api->get_option('_min_withdrawal_limit', '_wallet_settings_withdrawal', 0))), 400);
            }else {
                return [];
            }
        }
    }

    public function get_approved_requests($request){
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $args = array(
                'posts_per_page' => -1,
                'author' => $user_id,
                'post_type' => WOO_Wallet_Withdrawal_Post_Type::$post_type,
                'post_status' => 'ww-approved',
                'suppress_filters' => true
            );
            $withdrawal_requests = get_posts($args);
            if($withdrawal_requests){
                $results = [];
                foreach ($withdrawal_requests as $withdrawal_request){
                    $withdrawal = new Wallet_Withdrawal_Post($withdrawal_request->ID);
                    $item = array();
                    $item['price'] = get_post_meta($withdrawal_request->ID, '_wallet_withdrawal_amount', true);
                    $item['status'] = get_post_status_object(get_post_status($withdrawal_request->ID))->label;
                    $item['time'] = wc_string_to_datetime($withdrawal_request->post_date)->date_i18n(wc_date_format());
                    $item['payment'] = $withdrawal->get_payment_method_title();
                    $results[] = $item;
                }
                return $results;
            }else {
                return [];
            }
        }
    }

    public function get_cancelled_requests($request){
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $args = array(
                'posts_per_page' => -1,
                'author' => $user_id,
                'post_type' => WOO_Wallet_Withdrawal_Post_Type::$post_type,
                'post_status' => 'ww-cancelled',
                'suppress_filters' => true
            );
            $withdrawal_requests = get_posts($args);
            if($withdrawal_requests){
                $results = [];
                foreach ($withdrawal_requests as $withdrawal_request){
                    $withdrawal = new Wallet_Withdrawal_Post($withdrawal_request->ID);
                    $item = array();
                    $item['price'] = get_post_meta($withdrawal_request->ID, '_wallet_withdrawal_amount', true);
                    $item['status'] = get_post_status_object(get_post_status($withdrawal_request->ID))->label;
                    $item['time'] = wc_string_to_datetime($withdrawal_request->post_date)->date_i18n(wc_date_format());
                    $item['payment'] = $withdrawal->get_payment_method_title();
                    $results[] = $item;
                }
                return $results;
            }else {
                return [];
            }
        }
    }

    private function validate_withdrawal_request($payment_method, $amount) {
        $response = array('is_valid' => true, 'message' => '');
        $wallet_withdrawal_amount = floatval($amount);
            $wallet_withdrawal_method = $payment_method;
            $transaction_charge = WOO_Wallet_Withdrawal_Payment_gateways::get_gateway_charge($wallet_withdrawal_amount, $wallet_withdrawal_method);
            $args = array(
                'posts_per_page' => -1,
                'author' => get_current_user_id(),
                'post_type' => WOO_Wallet_Withdrawal_Post_Type::$post_type,
                'post_status' => 'ww-pending',
                'suppress_filters' => true
            );
            $withdrawal_requests = get_posts($args);
            if(!$wallet_withdrawal_amount){
                $response = array(
                    'is_valid' => false,
                    'message' => __('Please enter amount.', 'woo-wallet')
                );
            } else if ($wallet_withdrawal_amount + $transaction_charge > woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit')) {
                $response = array(
                    'is_valid' => false,
                    'message' => __('You don\'t have enough balance for this request.', 'woo-wallet')
                );
            } else if (empty($wallet_withdrawal_method)) {
                $response = array(
                    'is_valid' => false,
                    'message' => __('Invalid payment gateway.', 'woo-wallet')
                );
            } else if($withdrawal_requests){
                $response = array(
                    'is_valid' => false,
                    'message' => __('You have a pending withdrawal.', 'woo-wallet')
                );
            } else {
                $response = array(
                    'is_valid' => true,
                    'message' => __('Request submitted successfully.', 'woo-wallet')
                );
            }
        return apply_filters('validate_wallet_withdrawal_request', $response);
    }

    private function process_withdrawal($withdrawal_id, $payment_method, $amount) {
        $wallet_withdrawal_amount = apply_filters('woo_wallet_withdrawal_requested_amount', floatval($amount));
        $wallet_withdrawal_method = $payment_method;
        $transaction_charge = WOO_Wallet_Withdrawal_Payment_gateways::get_gateway_charge($wallet_withdrawal_amount, $wallet_withdrawal_method);
        update_post_meta($withdrawal_id, '_wallet_withdrawal_amount', $wallet_withdrawal_amount);
        update_post_meta($withdrawal_id, '_wallet_withdrawal_currency', get_woocommerce_currency());
        update_post_meta($withdrawal_id, '_wallet_withdrawal_transaction_charge', $transaction_charge);
        update_post_meta($withdrawal_id, '_wallet_withdrawal_method', $wallet_withdrawal_method);
        $withdrawal_transaction_id = woo_wallet()->wallet->debit(get_current_user_id(), ($wallet_withdrawal_amount + $transaction_charge), __('Wallet withdrawal request #', 'woo-wallet-withdrawal') . $withdrawal_id);
        update_wallet_transaction_meta($withdrawal_transaction_id, '_withdrawal_request_id', $withdrawal_id);
        update_post_meta($withdrawal_id, '_wallet_withdrawal_transaction_id', $withdrawal_transaction_id);
        do_action('woo_wallet_withdrawal_update_meta_data', $withdrawal_id);
    }

    public function submit_request($request)
    {
        if (!class_exists('WOO_WALLET_WITHDRAWAL')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet Withdrawal plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            $user = get_userdata($user_id);
            wp_set_current_user($user_id, $user->user_login);

            $payment_method = $params['payment_method'];
            $amount = $params['amount'];

            if('bacs' === $payment_method){
                if(!get_user_meta($user_id, '_bacs_account_name', true) || !get_user_meta($user_id, '_bacs_account_number', true)){
                    return parent::sendError("invalid_bank_settings",'The bank account has not been set up.', 400);
                }
            } else if('paypal' === $payment_method){
                if(!get_user_meta($user_id, '_woo_wallet_withdrawal_paypal_email', true)){
                    return parent::sendError("invalid_paypal_settings",'The paypal email has not been set up.', 400);
                }
            } else if('cashfree' === $payment_method){
                if(!get_user_meta($user_id, '_cashfree_beneid', true)){
                    return parent::sendError("invalid_cashfree_settings",'The cashfree has not been set up.', 400);
                }
            } else if('stripe' === $payment_method){
                if(!get_user_meta($user_id, 'stripe_user_id', true)){
                    return parent::sendError("invalid_stripe_settings",'The stripe has not been set up.', 400);
                }
            }

            $response = $this->validate_withdrawal_request($payment_method, $amount);
            if (!$response['is_valid']) {
                return parent::sendError("invalid_data", $response['message'], 400);
            } else {
                $withdrawal = new Wallet_Withdrawal_Post();
                $withdrawal_id = $withdrawal->create_withdrawal();
                if ($withdrawal_id) {
                    $this->process_withdrawal($withdrawal_id, $payment_method, $amount);
                    /** code for auto withdrawal * */
                    $payment_method_id = $withdrawal->get_payment_method_id();
                    if (woo_wallet_withdrawal()->gateways->payment_gateways[$payment_method_id]->is_enable_auto_withdrawal()) {
                        $withdrawal->approve_withdrawal();
                    }
                    return true;
                } else {
                    return parent::sendError("invalid_data", __('Something went wrong please try again later', 'woo-wallet-withdrawal'), 400);
                }
            }
        }
        return false;
    }

    function get_referrals($request){
        if (!is_plugin_active('woo-wallet/woo-wallet.php')) {
            return parent::send_invalid_plugin_error("You need to install TeraWallet plugin to use this api");
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }

            $actionReferrals = new Action_Referrals();
            $settings = $actionReferrals->settings;
            $referral_handel = apply_filters( 'woo_wallet_referral_handel', 'wwref' );

            $user                   = new WP_User( $user_id );
            $referral_url_by_userid = 'id' === $settings['referal_link'] ? true : false;
            $referral_url           = add_query_arg( $referral_handel, $user->user_login, wc_get_page_permalink( 'myaccount' ) );
            if ( $referral_url_by_userid ) {
                $referral_url = add_query_arg( $referral_handel, $user->ID, wc_get_page_permalink( 'myaccount' ) );
            }
            $referring_visitor = get_user_meta( $user_id, '_woo_wallet_referring_visitor', true ) ? get_user_meta( $user_id, '_woo_wallet_referring_visitor', true ) : 0;
            $referring_signup  = get_user_meta( $user_id, '_woo_wallet_referring_signup', true ) ? get_user_meta( $user_id, '_woo_wallet_referring_signup', true ) : 0;
            $referring_earning = get_user_meta( $user_id, '_woo_wallet_referring_earning', true ) ? get_user_meta( $user_id, '_woo_wallet_referring_earning', true ) : 0;

            return [
                'referral_url' => $referral_url,
                'referral_code' => $referral_url_by_userid ? $user->ID : $user->user_login,
                'referring_visitor' => $referring_visitor,
                'referring_signup' => $referring_signup,
                'referring_earning' => $referring_earning
            ];
        } else {
            return parent::sendError("no_permission", "You need to add User-Cookie in header request", 400);
        }
    }
}

new FlutterTeraWallet;