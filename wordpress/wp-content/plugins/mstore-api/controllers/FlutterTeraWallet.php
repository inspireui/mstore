<?php
require_once(__DIR__ . '/FlutterBase.php');

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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
        }

        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
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
            $_POST['woo_wallet_transfer_note'] = $params['note'];
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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
            }
            wp_set_current_user($user_id);
            $order = wc_get_order($params['order_id']);
            if (($order->get_total('edit') > woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit')) && apply_filters('woo_wallet_disallow_negative_transaction', (woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit') <= 0 || $order->get_total('edit') > woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit')), $order->get_total('edit'), woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit'))) {
                $error = sprintf(__('Your wallet balance is low. Please add %s to proceed with this transaction.', 'woo-wallet'), $order->get_total('edit') - woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit'));
                return parent::sendError("wallet_error", $error, 400);
            }
            $wallet_response = woo_wallet()->wallet->debit(get_current_user_id(), $order->get_total('edit'), apply_filters('woo_wallet_order_payment_description', __('For order payment #', 'woo-wallet') . $order->get_order_number(), $order));

            // Reduce stock levels
            wc_reduce_stock_levels($order_id);

            if ($wallet_response) {
                $order->payment_complete($wallet_response);
                do_action('woo_wallet_payment_processed', $order_id, $wallet_response);
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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
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
            return parent::sendError("invalid_plugin", "You need to install TeraWallet plugin to use this api", 404);
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $cookie = $request->get_header("User-Cookie");
        if (isset($cookie) && $cookie != null) {
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return parent::sendError("no_permission", "You need to login again to refresh cookie", 400);
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
}

new FlutterTeraWallet;