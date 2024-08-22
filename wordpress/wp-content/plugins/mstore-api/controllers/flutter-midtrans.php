<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Midtrans
 */

class FlutterMidtrans extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_midtrans';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_midtrans_routes'));
    }

    public function register_flutter_midtrans_routes()
    {
        register_rest_route($this->namespace, '/generate_snap_token', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'generate_snap_token'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/payment_success', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'payment_success'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function generate_snap_token($request)
    {
        if (!is_plugin_active('midtrans-woocommerce/midtrans-gateway.php')) {
            return parent::send_invalid_plugin_error("You need to install Midtrans WooCommerce Payment Gateway plugin to use this api");
        }
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        if($body['currency'] != 'IDR'){
             $options  =  get_option( 'woocommerce_midtrans_settings');
             if ($options && $options['to_idr_rate']) {
                $params = array(
                    'transaction_details' => array(
                        'order_id' => sanitize_text_field($body['order_id']),
                        'gross_amount' => floatval(sanitize_text_field($body['amount']))*intval($options['to_idr_rate']),
                    )
                );
             }
        }
        if (!isset($params)) {
            $params = array(
                'transaction_details' => array(
                    'order_id' => sanitize_text_field($body['order_id']),
                    'gross_amount' => sanitize_text_field($body['amount']),
                )
            );
        }
        require_once ABSPATH . 'wp-content/plugins/midtrans-woocommerce/midtrans-gateway.php';
        $order = wc_get_order( sanitize_text_field($body['order_id']) );
        $snapResponse = WC_Midtrans_API::createSnapTransactionHandleDuplicate( $order, $params, 'midtrans' );
        return  $snapResponse;
    }

    public function payment_success($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $order = wc_get_order( sanitize_text_field($body['order_id']) );
        $order->payment_complete();
        $order->add_order_note('Midtrans payment successful.<br/>Transaction ID: '.sanitize_text_field($body['transaction_id']));
        return  true;
    }
}

new FlutterMidtrans;