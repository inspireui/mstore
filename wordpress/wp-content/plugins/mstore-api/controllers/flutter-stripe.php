<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Stripe
 */

class FlutterStripe extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_stripe';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_stripe_routes'));
    }

    public function register_flutter_stripe_routes()
    {
        register_rest_route($this->namespace, '/payment_intent', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'create_payment_intent'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route(
            $this->namespace,
            '/payment_intent' . '/(?P<id>[\w]+)',
            array(
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'get_payment_intent'),
                    'permission_callback' => function () {
                        return parent::checkApiPermission();
                    }
                ),
            )
        );
    }

    public function create_payment_intent($request)
    {
        if (!is_plugin_active('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Stripe Gateway plugin to use this api");
        }
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['orderId']);
        $capture_method = sanitize_text_field($body['captureMethod']);
        $return_url = sanitize_text_field($body['returnUrl']);
        $email = sanitize_text_field($body['email']);

        $order  = wc_get_order( $order_id );
		if ( is_a( $order, 'WC_Order' ) ) {
			$amount = $order->get_total();
		}
        $currency       = get_woocommerce_currency();

        $params = [
            'amount'               => WC_Stripe_Helper::get_stripe_amount( $amount, strtolower( $currency ) ),
            'currency'             => strtolower( $currency ),
            'payment_method_types' => ['card'],
            'capture_method'       => $capture_method == 'automatic' ? 'automatic' : 'manual',
            'metadata'             => ['order_id'=>$order_id],
            'description'          => $email,
            'receipt_email'        => $email
        ];

        if(isset($body['request3dSecure'])){
            $request_3d_secure = sanitize_text_field($body['request3dSecure']);
            $params['payment_method_options'] = ['card' => ['request_three_d_secure' => $request_3d_secure ?? 'automatic']];
            $params['confirm'] = 'false';
        }
        if(isset($body['payment_method_id'])){
            $payment_method_id = sanitize_text_field($body['payment_method_id']);
            $params['payment_method'] = $payment_method_id;
            $params['confirm'] = 'true';
        }

        $payment_intent = WC_Stripe_API::request(
			$params,
			'payment_intents'
		);

		if ( ! empty( $payment_intent->error ) ) {
            return new WP_Error(400, $payment_intent->error->message, array('status' => 400));
		}

		return [
			'id'            => $payment_intent->id,
			'client_secret' => $payment_intent->client_secret,
		];
    }

    public function get_payment_intent($request)
    {
        if (!is_plugin_active('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php')) {
            return parent::send_invalid_plugin_error("You need to install WooCommerce Stripe Gateway plugin to use this api");
        }
        $parameters = $request->get_params();
        $payment_intent_id = $parameters['id'];
        $response = WC_Stripe_API::request( [], "payment_intents/$payment_intent_id", 'GET' );
        return $response;
    }
}

new FlutterStripe;