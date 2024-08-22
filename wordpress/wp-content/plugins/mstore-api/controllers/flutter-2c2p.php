<?php
require_once(__DIR__ . '/flutter-base.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Flutterwave
 */

class Flutter2C2P extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_2c2p';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_2c2p_routes'));
    }

    public function register_flutter_2c2p_routes()
    {
        register_rest_route($this->namespace, '/generate_payment_token', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'generate_payment_token'),
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

    public function payment_success()
    {
        if (!is_plugin_active('2c2p_woocommerce/2c2p.php')) {
            return parent::send_invalid_plugin_error("You need to install 2C2P Redirect API for WooCommerce plugin to use this api");
        }

        $objWC_Gateway_2c2p = new WC_Gateway_2c2p();
		$pg_2c2p_setting_values = $objWC_Gateway_2c2p->wc_2c2p_get_setting();

		$secret_key = $pg_2c2p_setting_values['key_secret'];

        $json = file_get_contents('php://input');
        if(strpos($json, 'paymentResponse=') !== false){
            $payment_res = urldecode(str_replace('paymentResponse=', '', $json));
            $data = JWT::decode($payment_res, new Key($secret_key, 'HS256'));
        }else{
            $body = json_decode($json, TRUE);
            $payload = sanitize_text_field($body['payload']);
            $data = JWT::decode($payload, new Key($secret_key, 'HS256'));
            $order_id = $data->invoiceNo;
            $referenceNo = $data->referenceNo;
            $transaction_ref = $data->tranRef;
            $approval_code = $data->approvalCode;
            $transaction_datetime = $data->transactionDateTime;
            $eic = $data->eic;

            $order = wc_get_order($order_id);
            if($order && $data->respCode == '0000'){
                $order->update_status('processing');                                        
                $order->payment_complete();
                $order->add_order_note('2C2P payment transaction successful.<br/>order_id: ' . $order_id . '<br/>transaction_ref: ' . $transaction_ref . '<br/>eci: ' . $eic . '<br/>transaction_datetime: ' . $transaction_datetime . '<br/>approval_code: ' . $approval_code);
            }
        }
        return  true;
    }

    public function generate_payment_token($request)
    {
        if (!is_plugin_active('2c2p_woocommerce/2c2p.php')) {
            return parent::send_invalid_plugin_error("You need to install 2C2P Redirect API for WooCommerce plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['order_id']);
        $amount = sanitize_text_field($body['amount']);
        $currency_code = sanitize_text_field($body['currency_code']);
        $return_url = sanitize_text_field($body['return_url']);
        $backend_return_url = sanitize_text_field($body['backend_return_url']);
       
        return $this->generate_token($order_id, $amount, $currency_code, $return_url, $backend_return_url);
    }

    private function generate_token($order_id, $amount, $currency_code, $return_url, $backend_return_url){

        $objWC_Gateway_2c2p = new WC_Gateway_2c2p();
		$pg_2c2p_setting_values = $objWC_Gateway_2c2p->wc_2c2p_get_setting();

        $merchant_id = $pg_2c2p_setting_values['key_id'];
		$secret_key = $pg_2c2p_setting_values['key_secret'];
		$environment = $pg_2c2p_setting_values['test_mode'];

        if($environment=="demo2"){
            $url = 'https://sandbox-pgw.2c2p.com/payment/4.3/paymentToken';
        }else{
            $url = 'https://pgw.2c2p.com/payment/4.3/paymentToken';
        }

        //generate payment token
        $payload = array(
            "merchantID" => $merchant_id,
            "invoiceNo" => $order_id,
            "description" => "Payment for order ".$order_id,
            "amount" => $amount,
            "locale" => "th",
            "currencyCode" => $currency_code,
            "frontendReturnUrl" => $return_url,
            "backendReturnUrl" => $backend_return_url ?? $domain."/wp-json/api/flutter_2c2p/payment_success"
        );

        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        // Prepare the request data
        $requestData = array(
            'payload' => $jwt,
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode($requestData),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Decode the JSON response
        $data = json_decode($response, true);

        if(isset($data['payload'])){
            return JWT::decode($data['payload'], new Key($secret_key, 'HS256'));
        }
        if(isset($data['respDesc'])){
            return parent::sendError($data['respCode'], $data['respDesc'], 400);
        }
    }
}

new Flutter2C2P;