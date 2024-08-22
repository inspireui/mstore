<?php
require_once(__DIR__ . '/flutter-base.php');
/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package Flutterwave
 */

class FlutterCCAvenue extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_cc_avenue';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_cc_avenue_routes'));
    }

    public function register_flutter_cc_avenue_routes()
    {
        register_rest_route($this->namespace, '/checkout', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'checkout'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function checkout()
    {
        if (!is_plugin_active('ccavanue-woocommerce-payment-getway/index.php')) {
            return parent::send_invalid_plugin_error("You need to install CCAvenue Payment Gateway for WooCommerce plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $body = json_decode($json, TRUE);
        $order_id = sanitize_text_field($body['order_id']);
        $redirect_url = sanitize_text_field($body['redirect_url']);
        $cancel_url = sanitize_text_field($body['cancel_url']);

        $available_payment_methods = WC()->payment_gateways->payment_gateways();
        if (!isset($available_payment_methods['ccavenue'])) {
            return parent::send_invalid_plugin_error("You need to install CCAvenue Payment Gateway for WooCommerce plugin to use this api");
        }
        
        $paymentMethod = $available_payment_methods['ccavenue'];

        global $woocommerce;
        $order = wc_get_order($order_id);
        $order_id = $order_id.'_'.date("ymds");
			
		$post_data = get_post_meta($order_id,'_post_data',true);
		update_post_meta($order_id,'_post_data',array());
			
        if($order -> billing_address_1 && $order -> billing_country && $order -> billing_state && $order -> billing_city && $order -> billing_postcode)
        {	
            $country = wc()->countries -> countries [$order -> billing_country];
            $state = $order -> billing_state;
            $city = $order -> billing_city;
            $zip = $order -> billing_postcode;
            $phone = $order->billing_phone;
            $billing_address_1 = trim($order -> billing_address_1, ',');
        }else{
            $billing_address_1 = $paymentMethod->default_add1;
            $country = $paymentMethod->default_country;
            $state = $paymentMethod->default_state;
            $city = $paymentMethod->default_city;
            $zip = $paymentMethod->default_zip;
            $phone = $paymentMethod->default_phone;
        }
        
        $the_order_total = $order->order_total;
        $currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;
        if($paymentMethod->enable_currency_conversion=='yes')
        {
            $the_order_total = $this->currency_convert($currency, 'INR', $the_order_total);		
        }
        $ccavenue_args = array(
            'merchant_id'      => $paymentMethod -> merchant_id,
            'amount'           => $the_order_total,
            'order_id'         => $order_id,
            'redirect_url'     => $redirect_url,
            'cancel_url'       => $cancel_url,
            'billing_name'     => $order -> billing_first_name .' '. $order -> billing_last_name,
            'billing_address'  => $billing_address_1,
            'billing_country'  => $country,
            'billing_state'    => $state,
            'billing_city'     => $city,
            'billing_zip'      => $zip,
            'billing_tel'      => $phone,
            'billing_email'    => $order -> billing_email,
            'delivery_name'    => $order -> shipping_first_name .' '. $order -> shipping_last_name,
            'delivery_address' => $order -> shipping_address_1,
            'delivery_country' => $order -> shipping_country,
            'delivery_state'   => $order -> shipping_state,
            'delivery_tel'     => '',
            'delivery_city'    => $order -> shipping_city,
            'delivery_zip'     => $order -> shipping_postcode,
            'language'         => 'EN',
            'currency'         => $currency,
            
            'payment_option'	=> $post_data['payment_option'],
            'card_type'		 	=> $post_data['card_type'],
            'card_name' 		=> $post_data['card_name'],
            'data_accept' 		=> $post_data['data_accept'],
            'card_number' 		=> $post_data['card_number'],
            'expiry_month' 		=> $post_data['expiry_month'],
            'expiry_year' 		=> $post_data['expiry_year'],
            'cvv_number' 		=> $post_data['cvv_number'],
            'issuing_bank' 		=> $post_data['issuing_bank'],
            );
            
        /*-------------------------------*/
        foreach($ccavenue_args as $param => $value) {
         $paramsJoined[] = "$param=$value";
        }
        $merchant_data   = implode('&', $paramsJoined);
        //echo $merchant_data;
        $encrypted_data = nilesh_encrypt($merchant_data, $paymentMethod->working_key);
       return ['liveurl' => $paymentMethod -> liveurl, 'encrypted_data' => $encrypted_data, 'access_code' => $paymentMethod->access_code];
    }

    /*
    ccavenue functions
    */
    /**
     * Encrypts with a bit more complexity
     *
     * @since 1.1.2
     */
    function nilesh_encrypt($plainText,$key)
    {
        $encryptionMethod = "AES-128-CBC";
        $secretKey = nilesh_hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = openssl_encrypt($plainText, $encryptionMethod, $secretKey, OPENSSL_RAW_DATA, $initVector);
        return bin2hex($encryptedText);
    }

    function nilesh_decrypt($encryptedText,$key)
    {
        $encryptionMethod     = "AES-128-CBC";
        $secretKey         = nilesh_hextobin(md5($key));
        $initVector         =  pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText      = nilesh_hextobin($encryptedText);
        $decryptedText         =  openssl_decrypt($encryptedText, $encryptionMethod, $secretKey, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

    function nilesh_pkcs5_pad ($plainText, $blockSize)
    {
        $pad = $blockSize - (strlen($plainText) % $blockSize);
        return $plainText . str_repeat(chr($pad), $pad);
    }

    function nilesh_hextobin($hexString) 
    { 
        $length = strlen($hexString); 
        $binString="";   
        $count=0; 
        while($count<$length) 
        {       
            $subString =substr($hexString,$count,2);           
            $packedString = pack("H*",$subString); 
            if ($count==0)
            {
                $binString=$packedString;
            } 
                
            else 
            {
                $binString.=$packedString;
            } 
                
            $count+=2; 
        } 
        return $binString; 
    }
    
    function nilesh_debug($what){
        echo '<pre>';
        print_r($what);
        echo '</pre>';
    }

    /*currency convertor API*/
    function currency_convert($currency_from,$currency_to,$currency_input)
    {
        if ($currency_from != $currency_to)
        {
            $from_Currency = urlencode($currency_from);
            $to_Currency = urlencode($currency_to);
            $variable=$from_Currency."_".$to_Currency;
            $get = file_get_contents("https://free.currconv.com/api/v7/convert?q=".$variable."&compact=ultra&apiKey=7bedb773b0b9f2362607");
            $get = json_decode($get);
            $converted_currency = (isset($get->$variable) ? ($get->$variable*$currency_input) : $currency_input);
            return $converted_currency;
        }
        else
        {
            return $currency_input;
        }
    }
}

new FlutterCCAvenue;