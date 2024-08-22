<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package PayStack
 */

class FlutterExpressPay extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_expresspay';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_expresspay_routes'));
    }

    public function register_flutter_expresspay_routes()
    {
        register_rest_route($this->namespace, '/card_checkout', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'card_checkout'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/verify_payment', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'verify_payment'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function verify_payment($request)
    {

        if (!is_plugin_active('woo-web-payment-getaway/web-payment-gateway.php')) {
            return parent::send_invalid_plugin_error("You need to install ShahbandrPay plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['order_id']);
        $transaction_id = sanitize_text_field($body['transaction_id']);

        $options  = get_option( 'woocommerce_shahbandrpay_settings');
        $password = $options['password'];
        $secret   = $options['secret'];
        $new_order_status = !empty($options['new_order_status']) ? $options['new_order_status'] : 'processing';

        $hash = sha1(md5(strtoupper($transaction_id . $password)));
        $url = 'https://pay.expresspay.sa/api/v1/payment/status';

        $main_json = [
            "merchant_key" => $secret,
            "payment_id" => $transaction_id,
            "hash" => $hash
        ];

        $getter = curl_init($url); //init curl
        curl_setopt($getter, CURLOPT_POST, 1); //post
        curl_setopt($getter, CURLOPT_POSTFIELDS, json_encode($main_json)); //json
        curl_setopt($getter, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); //header
        curl_setopt($getter, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($getter);

        $response = json_decode($result, true);

        if ( $response['status'] == 'settled' ) {
            $order = wc_get_order($order_id);
            update_post_meta( $order_id, 'trans_id', $transaction_id );
            update_post_meta( $order_id, 'trans_date', $response['date'] );

            $order->update_status( $new_order_status, 'ShahbandrPay successfully paid');
            $order->add_order_note( 'ShahbandrPay successfully paid' );
            return ['success' => true];
        } else {
            return ['message' => $response['reason'] ?? 'expresspay error'];
        }
    }

    public function card_checkout($request)
    {
        if (!is_plugin_active('woo-web-payment-getaway/web-payment-gateway.php')) {
            return parent::send_invalid_plugin_error("You need to install ShahbandrPay plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['order_id']);
        $card_number = sanitize_text_field($body['card_number']);
        $card_exp = sanitize_text_field($body['card_exp']);
        $card_cvc = sanitize_text_field($body['card_cvc']);
        $return_url = sanitize_text_field($body['return_url']);


        $options  = get_option( 'woocommerce_shahbandrpay_settings');
        $password = $options['password'];
        $secret   = $options['secret'];

        global $woocommerce;
    
        $order = new WC_Order($order_id);
        $user = $order->get_user();
        $user_id = $order->get_user_id();
        $currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;

        $action_adr = 'https://api.expresspay.sa/post';
        $customerName = '';
        if(mb_detect_encoding($order->get_billing_first_name()) !== 'UTF-8' && mb_detect_encoding($order->get_billing_last_name()) !== 'UTF-8') {
            $customerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        }
        $email =  $order->get_billing_email() ? $order->get_billing_email() : $user->email;

        if ($customerName == '') {
            $customer = array(
                'email' => $email
                );
        } else {
            $customer = array(
                'name' => $customerName,
                'email' => $email
            );
        }
        
        $billing_address = array(
            'country' => $order->get_billing_country() ? $order->get_billing_country() : 'NA',
            'state' => $order->get_billing_state() ? $order->get_billing_state() : 'NA',
            'city' => $order->get_billing_city() ? $order->get_billing_city() : 'NA',
            'address' => $order->get_billing_address_1() ? $order->get_billing_address_1() : 'NA',
            'zip' => $order->get_billing_postcode() ? $order->get_billing_postcode() : '12271',
            'phone' => $order->get_billing_phone() ? $order->get_billing_phone() : '',
            'email' => $email
        );

        $amount = number_format($order->get_total(), 2, '.', '');

        $order_json = array(
            'number' => "$order_id",
            'description' => __('Payment Order # ', 'woocommerce') . $order_id . __(' in the store ', 'woocommerce') . home_url('/'),
            'amount' => $amount,
            'currency' => $currency,
        );
        
        $card_number = str_replace(" ","",$card_number);
        if ($card_exp) {
            $exp_array = explode('/', $card_exp);
            $month = str_replace(" ", "",$exp_array[0]);
            $year = str_replace(" ", "",'20'.$exp_array[1]);
        } else {
            $month = '';
            $year = '';
        }
        
        $hash = md5(strtoupper(strrev($email).$password.strrev(substr($card_number,0,6).substr($card_number,-4))));

        $data = [
            'action'            => 'SALE',
            'client_key'        => $secret,
            'order_id'          => 'ORDER-' . $order_id . time(),
            'order_amount'      => $amount,
            'order_currency'    => $currency,
            'order_description' => __('Product Order # ', 'woocommerce') . $order_id,
            'card_number'       => $card_number,
            'card_exp_month'    => $month,
            'card_exp_year'     => $year,
            'card_cvv2'         => $card_cvc,
            'payer_first_name'  => $order->get_billing_first_name(),
            'payer_last_name'   => $order->get_billing_last_name(),
            'payer_address'     => $billing_address['address'],
            'payer_country'     => $billing_address['country'],
            'payer_city'        => $billing_address['city'],
            'payer_zip'         => $billing_address['zip'],
            'payer_email'       => $billing_address['email'],
            'payer_phone'       => $billing_address['phone'],
            'payer_ip'          => '123.123.123.123',
            'term_url_3ds'      => $return_url,
            'hash'              => $hash,
        ];


        $fields = "";
        foreach ($data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }
        $getter = curl_init($action_adr);
        curl_setopt($getter, CURLOPT_POST, 1);
        curl_setopt($getter, CURLOPT_POSTFIELDS, rtrim($fields, '&'));
        curl_setopt($getter, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));
        curl_setopt($getter, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($getter);
        $httpcode = curl_getinfo($getter, CURLINFO_HTTP_CODE);

        $response = json_decode($result, true);

        if ($httpcode != 200) {
            $errors = '';
            foreach($response['errors'] as $value){
                $errors .= $value['error_code'] . ' : ' .$value['error_message'].'<br>';
            }
            return parent::sendError("invalid_payment", $errors, 400);
        }

        if ($response['result'] == 'SUCCESS' && $response['status'] == 'SETTLED') {
                    
            $order->payment_complete($order_id);
            $order->update_status($new_order_status, 'ShahbandrPay successfully paid');
            $order->add_order_note( 'ShahbandrPay successfully paid' );
 
            update_post_meta( $order_id, 'trans_id', $response['trans_id'] );
            update_post_meta( $order_id, 'trans_date', $response['trans_date'] );
            update_post_meta( $order_id, 'trans_hash', $hash );

            return array(
                'success'   => true,
            );
        }elseif($response['result'] == 'REDIRECT' && $response['status'] == 'REDIRECT' ){
            $order->update_status('on-hold', 'Awaiting 3-D Secure Payment');
            update_post_meta( $order_id, 'trans_id', $response['trans_id'] );
            update_post_meta( $order_id, 'trans_date', $response['trans_date'] );
            update_post_meta( $order_id, 'trans_hash', $hash );

            $body   = $response['redirect_params']['body'];
            $url    = $response['redirect_url'];
            $method = $response['redirect_method'];
            
            return array(
                'body' => $response['redirect_params']['body'],
                'url' => $response['redirect_url'],
                'method' => $response['redirect_method'],
                'trans_id' => $response['trans_id']
            );
        }
        else {
            return parent::sendError("invalid_payment", 'Please try again.', 400);
        }
    }
}

new FlutterExpressPay;