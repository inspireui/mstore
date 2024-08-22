<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package PayTm
 */

class FlutterPayTm extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_paytm';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_paytm_routes'));
    }

    public function register_flutter_paytm_routes()
    {
        register_rest_route($this->namespace, '/generate_txn_token', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'generate_txn_token'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function generate_txn_token($request)
    {
        if (!is_plugin_active('paytm-payments/woo-paytm.php')) {
            return parent::send_invalid_plugin_error("You need to install Paytm WooCommerce Payment Gateway plugin to use this api");
        }
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $params = ['order_id'=>sanitize_text_field($body['order_id']), 'amount'=>sanitize_text_field($body['amount']), 'cust_id'=>sanitize_text_field($body['cust_id'])];
        $payTm = new WC_paytm();
        return  $payTm->blinkCheckoutSend($params);
    }
}

new FlutterPayTm;