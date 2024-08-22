<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package MyFatoorah
 */

class FlutterMyFatoorah extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_myfatoorah';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_myfatoorah_routes'));
    }

    public function register_flutter_myfatoorah_routes()
    {
        register_rest_route($this->namespace, '/myfatoorah_complete', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'myfatoorah_complete'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

    }

    public function myfatoorah_complete($request)
    {
        if (!is_plugin_active('myfatoorah-woocommerce/myfatoorah-woocommerce.php')) {
            return parent::send_invalid_plugin_error("You need to install MyFatoorah – WooCommerce plugin to use this api");
        }
        $_GET['oid'] = base64_encode(sanitize_text_field($request['orderId']));
        $_GET['paymentId'] = $request['paymentId'];
        $value = do_action( 'woocommerce_api_myfatoorah_complete' );

        return true;
    }
}

new FlutterMyFatoorah;