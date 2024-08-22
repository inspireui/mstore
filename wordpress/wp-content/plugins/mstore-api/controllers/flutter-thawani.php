<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package PayStack
 */

class FlutterThawani extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_thawani';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_thawani_routes'));
    }

    public function register_flutter_thawani_routes()
    {
        register_rest_route($this->namespace, '/order_success', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'update_order_success'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function update_order_success($request)
    {

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['order_id']);
        $order = wc_get_order($order_id);
        $thawani_gateway = WC()->payment_gateways->payment_gateways()['thawani_gw'];
        if(isset($thawani_gateway)){
            $order->update_status('wc-'.$thawani_gateway->settings['status'], __('payment Success', 'thawani'));
            update_post_meta($order_id, 'thawani_session', sanitize_text_field($body['session_token']));
            return ['success' => true];
        }else{
            return ['message' => 'thawani_gw not found'];
        }
    }
}

new FlutterThawani;