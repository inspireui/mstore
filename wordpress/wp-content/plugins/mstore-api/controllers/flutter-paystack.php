<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package PayStack
 */

class FlutterPayStack extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_paystack';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_paystack_routes'));
    }

    public function register_flutter_paystack_routes()
    {
        register_rest_route($this->namespace, '/initialize_transaction', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'initialize_transaction'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/verify_paystack_transaction', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'verify_paystack_transaction'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function initialize_transaction($request)
    {
        if (!is_plugin_active('woo-paystack/woo-paystack.php')) {
            return parent::send_invalid_plugin_error("You need to install Paystack WooCommerce Payment Gateway plugin to use this api");
        }
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['order_id']);

        $payStack = new WC_Gateway_Paystack();

        $order        = wc_get_order( $order_id );
		$email        = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
		$amount       = $order->get_total() * 100;
		$txnref       = $order_id . '_' . time();
		$currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;
		$callback_url = WC()->api_request_url( 'WC_Gateway_Paystack' );

		$paystack_params = array(
			'amount'       => (int)$amount,
			'email'        => $email,
			'currency'     => $currency,
			'reference'    => $txnref,
			'callback_url' => $callback_url,
		);

		if ( $payStack->split_payment ) {

			$paystack_params['subaccount'] = $payStack->subaccount_code;
			$paystack_params['bearer']     = $payStack->charges_account;

			if ( empty( $payStack->transaction_charges ) ) {
				$paystack_params['transaction_charge'] = '';
			} else {
				$paystack_params['transaction_charge'] = $payStack->transaction_charges * 100;
			}
		}

		$paystack_params['metadata']['custom_fields'] = $payStack->get_custom_fields( $order_id );
		$paystack_params['metadata']['cancel_action'] = wc_get_cart_url();

		update_post_meta( $order_id, '_paystack_txn_ref', $txnref );

		$paystack_url = 'https://api.paystack.co/transaction/initialize/';

		$headers = array(
			'Authorization' => 'Bearer ' . $payStack->secret_key,
			'Content-Type'  => 'application/json',
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
			'body'    => json_encode( $paystack_params ),
		);

		$request = wp_remote_post( $paystack_url, $args );
        $paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
			return $paystack_response->data;
		} else {
			return parent::sendError("invalid_data", $paystack_response->message, 400);
		}
    }

    public function verify_paystack_transaction($request)
    {
        if (!is_plugin_active('woo-paystack/woo-paystack.php')) {
            return parent::send_invalid_plugin_error("You need to install Paystack WooCommerce Payment Gateway plugin to use this api");
        }
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $_REQUEST['reference'] = sanitize_text_field($body['reference']);
        $_REQUEST['paystack_txnref'] = sanitize_text_field($body['reference']);

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

        $payStack = new WC_Gateway_Paystack();
        return $payStack->verify_paystack_transaction();
    }
}

new FlutterPayStack;