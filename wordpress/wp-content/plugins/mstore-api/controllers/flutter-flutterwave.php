<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Flutterwave
 */

class FlutterFlutterwave extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_flutterwave';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_flutterwave_routes'));
    }

    public function register_flutter_flutterwave_routes()
    {
        register_rest_route($this->namespace, '/flw_verify_payment', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'flw_verify_payment'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function flw_verify_payment($request)
    {
        if (!is_plugin_active('rave-woocommerce-payment-gateway/woocommerce-rave.php')) {
            return parent::send_invalid_plugin_error("You need to install Flutterwave WooCommerce plugin to use this api");
        }
        $_GET['txref'] = sanitize_text_field($request['txref']);

        $flutterwave = new FLW_WC_Payment_Gateway();
        if (defined('WC_ABSPATH')) {
            // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
            include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
            include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
            include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
        }
        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

            WC()->session = new $session_class();
            WC()->session->init();
        }
        if (null === WC()->cart) {
            WC()->cart = new WC_Cart();
        }
        return $flutterwave->flw_verify_payment();
    }
}

new FlutterFlutterwave;