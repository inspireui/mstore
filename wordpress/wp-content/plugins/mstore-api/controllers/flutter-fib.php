<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package FIB
 * https://documenter.getpostman.com/view/18377702/UVCB93tc#intro
 */

class FlutterFIB extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_fib';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_fib_routes'));
    }

    public function register_flutter_fib_routes()
    {

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

    public function payment_success($request)
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);

        $order = wc_get_order( sanitize_text_field($body['order_id']) );
        $order->payment_complete();
        $order->add_order_note('FIB payment successful.<br/>Payment ID: '.sanitize_text_field($body['payment_id']));
        return  true;
    }
}

new FlutterFIB;