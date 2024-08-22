<?php
define("ACTIVE_API", "https://active2.inspireui.com/api/v1/validate");
define("DEACTIVE_API", "https://active2.inspireui.com/api/v1/deactive");
define("ACTIVE_TOKEN", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJmb28iOiJiYXIiLCJpYXQiOjE1ODY5NDQ3Mjd9.-umQIC6DuTS_0J0Jj8lcUuUYGjq9OXp3cIM-KquTWX0");

// migrate for old versions
function verifyPurchaseCodeAuto(){
    $is_verified = (get_option('mstore_purchase_code') ==  true || get_option('mstore_purchase_code') ==  "1") && !empty(get_option('mstore_purchase_code_key'))  && empty(get_option('mstore_active_hash_code'));
    if($is_verified){
        verifyPurchaseCode(get_option('mstore_purchase_code_key'));
    }
}

function isPurchaseCodeVerified(){
    return  true;
    // $random_key = get_option('mstore_active_random_key');
    // $hash_code = get_option('mstore_active_hash_code');
    // $code = get_option('mstore_purchase_code_key');
    // return md5('inspire@123%$'.$random_key) == $hash_code && isset($code) && $code != false && strlen($code) > 0;
}

function verifyPurchaseCode($code)
{
    $random_key = wp_generate_password($length = 12, $include_standard_special_chars = false);
    $website = get_home_url();
    $response = wp_remote_post(ACTIVE_API, ["body" => ["token" => ACTIVE_TOKEN, "code" => $code, "url" => $website, "key" => $random_key], 'sslverify'   => false]);
    if (is_wp_error($response)) {
        return $response->get_error_message();
    }
    $statusCode = wp_remote_retrieve_response_code($response);
    $success = $statusCode == 200;
    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body, true);

    delete_option('mstore_purchase_code'); // remove old key to  fix duplicate re-verify 

    if ($success) {
        update_option("mstore_active_random_key", $random_key);
        update_option("mstore_active_hash_code", $body['data']);
        update_option("mstore_purchase_code_key", $code);
    } else {
        delete_option('mstore_purchase_code_key'); // remove old key to  fix duplicate re-verify 
        return $body["message"] ??  $body["error"];
    }
    return $success;
}


function one_signal_push_notification($title = '', $message = '', $user_ids = array()) {   
    if(!is_plugin_active('onesignal-free-web-push-notifications/onesignal.php')){
        return false;
    }

    $onesignal_wp_settings = OneSignal::get_onesignal_settings();
    $app_id = $onesignal_wp_settings['app_id'];
    $api_key = $onesignal_wp_settings['app_rest_api_key'];

    if(empty($app_id) || empty($api_key)){
        return false;
    }

    $content      = array(
        "en" => $message
    );
    $headings = array(
        "en" => $title
    );
	
	$external_ids = array();
	foreach($user_ids as $id){
		$external_ids[] = strval($id);
	}

    $fields = array(
        'app_id' => $app_id,
        'data' => array(
			'title' => $title,
			'message' => $message,
		),
        'priority'=> "10",
        'existing_android_channel_id' => 'high_importance_channel',
        'include_external_user_ids' => $external_ids,
        'contents' => $content,
        'headings' => $headings,
    );
    $bodyAsJson = json_encode($fields);
    $response =  wp_remote_post("https://onesignal.com/api/v1/notifications", array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array("Content-type" => "application/json;charset=UTF-8",
            "Authorization" => "Basic " . $api_key ),
        'body' => $bodyAsJson,
      )
    );
    return wp_remote_retrieve_response_code($response) == 200;
}

function sendNotificationToUser($userId, $orderId, $previous_status, $next_status)
{
    $user = get_userdata($userId);
    $title = get_option("mstore_status_order_title");
    if (!isset($title) || $title == false) {
        $title = "Order Status Changed";
    }
    $message = get_option("mstore_status_order_message");
    if (!isset($message) || $message == false) {
        $message = "Hi {{name}}, Your order: #{{orderId}} changed from {{prevStatus}} to {{nextStatus}}";
    }
    $previous_status_label = wc_get_order_status_name( $previous_status );
    $next_status_label = wc_get_order_status_name( $next_status );
    
    if($user && $user->display_name){
        $message = str_replace("{{name}}", $user->display_name, $message);
    }
    $message = str_replace("{{orderId}}", $orderId, $message);
    $message = str_replace("{{prevStatus}}", $previous_status_label, $message);
    $message = str_replace("{{nextStatus}}", $next_status_label, $message);

    pushNotificationForUser($userId,$title, $message);
}

function trackOrderStatusChanged($id, $previous_status, $next_status)
{
    $order = wc_get_order($id);
    $userId = $order->get_customer_id();
    sendNotificationToUser($userId, $id, $previous_status, $next_status);
    $status = $order->get_status();
    sendNewOrderNotificationToDelivery($id, $status);
}

function _pushNotification($user_id, $title, $message, $meta_key){
    $is_notification_on = $meta_key == 'mstore_manager_device_token' || $meta_key == 'mstore_delivery_device_token';
    if (is_plugin_active('onesignal-free-web-push-notifications/onesignal.php')) {
        _pushNotificationOneSignal($title,$message, $user_id, $is_notification_on);
    } else {
        $deviceToken = get_user_meta($user_id, $meta_key, true);
        if (isset($deviceToken) && $deviceToken != false) {
            _pushNotificationFirebase($user_id,$title, $message, $deviceToken, $is_notification_on);
        }
    }
}

function pushNotificationForDeliveryBoy($user_id, $title, $message){
    _pushNotification($user_id, $title, $message, 'mstore_delivery_device_token');
}

function pushNotificationForVendor($user_id, $title, $message){
    _pushNotification($user_id, $title, $message, 'mstore_manager_device_token');
}

function pushNotificationForUser($user_id, $title, $message){
     _pushNotification($user_id, $title, $message, 'mstore_device_token');
}

function sendNewOrderNotificationToDelivery($order_id, $status)
{
    global $wpdb;
    $title = "Order notification";
    $statusLabel = wc_get_order_status_name( $status );
    $message = "The order #{$order_id} has been {$statusLabel}";
    if (is_plugin_active('wc-frontend-manager-delivery/wc-frontend-manager-delivery.php')) {
        if ($status == 'cancelled' || $status == 'refunded') {
            $sql = "SELECT `{$wpdb->prefix}wcfm_delivery_orders`.delivery_boy FROM `{$wpdb->prefix}wcfm_delivery_orders`";
            $sql .= " WHERE 1=1";
            $sql .= " AND order_id = %s";
            $sql .= " AND is_trashed = 0";
            $sql .= " AND delivery_status = 'pending'";
            $sql = $wpdb->prepare($sql, $order_id);
            $result = $wpdb->get_results($sql);

            foreach ($result as $item) {
                pushNotificationForDeliveryBoy($item->delivery_boy, $title, $message);
            }
        }

    }

    if (is_plugin_active('delivery-drivers-for-woocommerce/delivery-drivers-for-woocommerce.php')) {
        $order = wc_get_order($order_id);
        $driver_id = $order->get_meta('ddwc_driver_id');
        if ($driver_id) {
            global $WCFM, $wpdb;
            // include upgrade-functions for maybe_create_table;
            if (!function_exists('maybe_create_table')) {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            }
            $table_name = $wpdb->prefix . 'delivery_woo_notification';
            $sql = "CREATE TABLE " . $table_name . "(
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            message text NOT NULL,
            order_id text NOT NULL,
            delivery_boy text NOT NULL,
            created datetime NOT NULL,
            UNIQUE KEY id (id)
            );";
            maybe_create_table($table_name, $sql);
            pushNotificationForDeliveryBoy($driver_id, $title, $message);
            $wpdb->insert($table_name, array(
                'message' => $message,
                'order_id' => $order_id,
                'delivery_boy' => $driver_id,
                'created' => current_time('mysql')
            ));
        }
    }
}

function sendNewOrderNotificationToVendor($order_seller_id, $order_id)
{
    $user = get_userdata($order_seller_id);
    $title = get_option("mstore_new_order_title");
    if (!isset($title) || $title == false) {
        $title = "New Order";
    }
    $message = get_option("mstore_new_order_message");
    if (!isset($message) || $message == false) {
        $message = "Hi {{name}}, Congratulations, you have received a new order! ";
    }
    $message = str_replace("{{name}}", $user->display_name, $message);
    pushNotificationForUser($order_seller_id, $title, $message);//push notification to vendor who logged in on FluxStore MV
    if (!is_plugin_active('onesignal-free-web-push-notifications/onesignal.php')) {//fix duplicate notification if onesignal
        pushNotificationForVendor($order_seller_id, $title, $message);//push notification to vendor who logged in on FluxStore Manager
    }
    if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
        wcfm_message_on_new_order($order_id);
    }
}

function wcfm_message_on_new_order($order_id)
{
    global $WCFM, $wpdb;
    if (get_post_meta($order_id, '_wcfm_new_order_notified', true)) return;
    $author_id = -2;
    $author_is_admin = 1;
    $author_is_vendor = 0;
    $message_to = 0;
    $order = wc_get_order($order_id);

    // Admin Notification
    $wcfm_messages = sprintf(__('You have received an Order <b>#%s</b>', 'wc-frontend-manager'), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>');
    $WCFM->wcfm_notification->wcfm_send_direct_message($author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, 'order', apply_filters('wcfm_is_allow_order_notification_email', false));

    $order_vendors = array();
    foreach ($order->get_items() as $item_id => $item) {
        if (version_compare(WC_VERSION, '4.4', '<')) {
            $product = $order->get_product_from_item($item);
        } else {
            $product = $item->get_product();
        }
        $product_id = 0;
        if (is_object($product)) {
            $product_id = $item->get_product_id();
        }
        if ($product_id) {
            $author_id = -1;
            $message_to = wcfm_get_vendor_id_by_post($product_id);

            if ($message_to) {
                if (apply_filters('wcfm_is_allow_itemwise_notification', true)) {
                    $wcfm_messages = sprintf(__('You have received an Order <b>#%s</b> for <b>%s</b>', 'wc-frontend-manager'), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', get_the_title($product_id));
                } elseif (!in_array($message_to, $order_vendors)) {
                    $wcfm_messages = sprintf(__('You have received an Order <b>#%s</b>', 'wc-frontend-manager'), '<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>');
                } else {
                    continue;
                }
                $wcfm_messages = apply_filters('wcfm_new_order_vendor_notification_message', $wcfm_messages, $order_id, $message_to);
                $WCFM->wcfm_notification->wcfm_send_direct_message($author_id, $message_to, $author_is_admin, $author_is_vendor, $wcfm_messages, 'order', apply_filters('wcfm_is_allow_order_notification_email', false));
                $order_vendors[$message_to] = $message_to;
                do_action('wcfm_after_new_order_vendor_notification', $message_to, $product_id, $order_id);
            }
        }
    }

    update_post_meta($order_id, '_wcfm_new_order_notified', 'yes');
}

function trackNewOrder($order_id)
{
    $seller_ids = getSellerIdsByOrderId($order_id);
    if (empty($seller_ids)) {
        return;
    }
    foreach ($seller_ids as $vendor_id) {
        sendNewOrderNotificationToVendor($vendor_id, $order_id);
    }
}

function getAddOns($categories)
{
    $addOns = [];
    if (is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php')) {
        $addOnGroup = WC_Product_Addons_Groups::get_all_global_groups();
        foreach ($addOnGroup as $addOn) {
            $cateIds = array_keys($addOn["restrict_to_categories"]);
            if (count($cateIds) == 0) {
                $addOns = array_merge($addOns, $addOn["fields"]);
                break;
            }
            $isSupported = false;
            foreach ($categories as $cate) {
                if (in_array($cate["id"], $cateIds)) {
                    $isSupported = true;
                    break;
                }
            }
            if ($isSupported) {
                $addOns = array_merge($addOns, $addOn["fields"]);
            }
        }
    }

    return $addOns;
}

function deactiveMStoreApi()
{
    $website = get_home_url();
    $code = get_option('mstore_purchase_code_key');
    $response = wp_remote_post(DEACTIVE_API . "?token=" . ACTIVE_TOKEN, ["body" => ["code" => $code, "website" => $website]]);
    $statusCode = wp_remote_retrieve_response_code($response);
    $success = $statusCode == 200;
    if ($success) {
        delete_option("mstore_purchase_code_key");
    } else {
        $body = wp_remote_retrieve_body($response);
        if(is_array(json_decode($body, true))){
            $body = json_decode($body, true);
            return $body["error"];
        }else{
            return  $body;
        }
    }
    return $success;
}

function parseMetaDataForBookingProduct($product)
{
    if (is_plugin_active('woocommerce-appointments/woocommerce-appointments.php')) {
        //add meta_data to $_POST to use for booking product
        $meta_data = [];
        foreach ($product["meta_data"] as $key => $value) {
            if ($value["key"] == "staff_ids" && isset($value["value"])) {
                $staffs = is_array($value["value"]) ? $value["value"] : json_decode($value["value"], true);
                if (count($staffs) > 0) {
                    $meta_data["wc_appointments_field_staff"] = sanitize_text_field($staffs[0]);
                }
            } elseif ($value["key"] == "product_id") {
                $meta_data["add-to-cart"] = sanitize_text_field($value["value"]);
            } else {
                $meta_data[$value["key"]] = sanitize_text_field($value["value"]);
            }
        }
        $_POST = $meta_data;
    }
}

function isPHP8()
{
    return version_compare(phpversion(), '8.0.0') >= 0;
}

function addQRCodeUrlToMetaResponse($response){
    if (is_plugin_active('yith-woocommerce-barcodes-premium/init.php')) {
        $barcode = YITH_Barcode::get($response->data['id']);
        $barcode_img_path = get_post_meta( $response->data['id'], '_ywbc_barcode_filename', false );
        if($barcode && $barcode_img_path){
            $barcode_value    = $barcode->get_display_value();
            $barcode_protocol = $barcode->get_protocol();
            $barcode_src = apply_filters(
                'yith_ywbc_barcode_src',
                $barcode_img_path ? esc_url(  YITH_YWBC()->get_public_file_path( $barcode ) ) : 'data:image/png;base64,' . $barcode->image,
                $barcode_value,
                $barcode_protocol,
                'default'
            );
            $meta_data = $response->data['meta_data'];
            $meta_data[] = new WC_Meta_Data(
                array(
                    'key'   =>'_ywbc_barcode_image_url',
                    'value' => $barcode_src,
                )
            );
            $response->data['meta_data'] = $meta_data;
        }
    }
    return $response;
}

function addYITHBadgeToMetaResponse($response, $product){
    if (function_exists( 'yith_wcbm_get_product_badges' )) {
       // Add badge to product.
        $badges_to_show = yith_wcbm_get_product_badges( $product );
        $badges_to_show = apply_filters( 'yith_wcbm_badges_to_show_on_product', $badges_to_show, $product );

        $badges = array();

        foreach ( $badges_to_show as $badge_id ) {
            $badge_id = yith_wcbm_wpml_translate_badge_id( $badge_id );
            $badge    = yith_wcbm_get_badge_object( $badge_id );

            if ( $badge && $badge->is_enabled() ) {
                $data = $badge->get_data();
                // If badge is image, get image url.
                if ( $badge->get_type() === 'image' ) {
                    $image_url = $badge->get_image_url();
                    if (strpos($image_url, 'http') == false) {
                        $is_https = strpos(home_url(), 'https') == true ;
                        $image_url = ($is_https ? 'https:' : 'http:').$image_url;
                     }
                    $data['image_url'] = $image_url;
                }
                $badges[] = $data;
            }
        }

        $meta_data = $response->data['meta_data'];
        $meta_data[] = array(
                'id'    => '_yith_wcbm_badges',
                'key'   => '_yith_wcbm_badges',
                'value' => $badges,
        );

        $hide_on_single = get_option( 'yith-wcbm-hide-on-single-product', 'no' ) === 'yes';
        $meta_data[] = array(
            'id'    => '_yith-wcbm-hide-on-single-product',
            'key'   => '_yith-wcbm-hide-on-single-product',
            'value' => $hide_on_single,
        );
        
        $response->data['meta_data'] = $meta_data;
    }
    return $response;
}

function customProductResponse($response, $object, $request)
{
    global $woocommerce_wpml;

    // Will load the product variations if this request is for a specific
    // product by ID
    $is_detail_api = isset($request->get_params()['id']);

    // The `is_all_data` can be String, Boolean or null. So it can be wrong if
    // check 'false' == true
    if (filter_var($request['is_all_data'], FILTER_VALIDATE_BOOLEAN)) {
        $is_detail_api = true;
    }

    $is_purchased = false;
    if (isset($request['user_id'])) {
        $user_id = $request['user_id'];
        $user_data = get_userdata($user_id);
        if ($user_data) {
            $user_email = $user_data->user_email;
            $is_purchased = wc_customer_bought_product($user_email, $user_id, $response->data['id']);
        }
    }
    $response->data['is_purchased'] = $is_purchased;

    //update correct product price with tax setting
    $response->data['price'] = wc_get_price_to_display(  $object );
    $response->data['regular_price'] = wc_get_price_to_display(  $object, array( 'price' => $object->get_regular_price() ) );
    if($object->get_sale_price() != ""){
		$response->data['sale_price'] = wc_get_price_to_display(  $object, array( 'price' => $object->get_sale_price() ) );
	}else{
		$response->data['sale_price'] = null;
	}
    
    if (!empty($woocommerce_wpml->multi_currency) && !empty($woocommerce_wpml->settings['currencies_order'])) {

        $type = $response->data['type'];
        $price = floatval($response->data['price']);

        foreach ($woocommerce_wpml->settings['currency_options'] as $key => $currency) {
            $rate = (float)$currency["rate"];
            $response->data['multi-currency-prices'][$key]['price'] = $rate == 0 ? $price : sprintf("%.2f", $price * $rate);
        }
    }

    $product = wc_get_product($response->data['id']);

    /* Update price for product variant */
    if ($product && $product->is_type('variable')) {
        $prices = $product->get_variation_prices();
        if (!empty($prices['price'])) {
            $response->data['price'] = current($prices['price']);
            $response->data['regular_price'] = current($prices['regular_price']);
            $response->data['sale_price'] = current($prices['sale_price']);
            $response->data['min_price'] = wc_get_price_to_display(  $product, array( 'price' => $product->get_variation_price() ) );
            $response->data['max_price'] = wc_get_price_to_display(  $product, array( 'price' => $product->get_variation_price('max') ) );
            
            if(!$response->data['min_price']){
                $response->data['min_price'] = '0';
            }
            if(!$response->data['max_price']){
                $response->data['max_price'] = '0';
            }
            //convert to string
            $response->data['min_price'] = strval($response->data['min_price']);
            $response->data['max_price'] = strval($response->data['max_price']);

            if($is_detail_api){
				$variations = $response->data['variations'];
				$controller = new WC_REST_Product_Variations_V2_Controller();
				$variation_arr = array();
                foreach($variations as $variation_id){
					$variation = new WC_Product_Variation($variation_id);
                    $variation_data = $controller->prepare_object_for_response($variation, $request)->get_data();
					$variation_arr[] = $variation_data;
				}
                $response->data['variation_products'] = $variation_arr;
            }
            
        }
    }

    if($product) {
        $attributes = $product->get_attributes();
        $attributesData = [];
        foreach ($attributes as $key => $attr) {
            if(!is_string($attr)){
                $check = $attr->is_taxonomy();
                if ($check) {
                    $taxonomy = $attr->get_taxonomy_object();
                    $label = $taxonomy->attribute_label;
                } else {
                    $label = $attr->get_name();
                }
                $attrOptions = wc_get_product_terms($response->data['id'], $attr["name"]);
                $attrOptions = empty($attrOptions) ? array_map(function ($v){
                    return ['name'=>$v, 'slug' => $v];
                },$attr["options"]) : $attrOptions;
                $attributesData[] = array_merge($attr->get_data(), ["label" => $label, "name" => urldecode($key)], ['options' =>$attrOptions]);
            }
        }
        $response->data['attributesData'] = $attributesData;
    }
    
    /* Product Add On */
    if(class_exists('WC_Product_Addons_Helper')){
        $add_ons_list =  [];
        $product_addons = WC_Product_Addons_Helper::get_product_addons( $response->data['id'], false );
        //$add_ons_list  = count($addOns) == 0 ? $product_addons : array_merge($product_addons, $addOns);
        $add_ons_list  = array_map(function($item){
            if($item['type']  == 'file_upload' && !array_key_exists('options',$item)){
                $item['options'] = [['label'=>'','price'=>'','image'=>'','price_type'=>'']];
            }
            return $item;
        },$product_addons);

        $add_ons_exists = false;

        $meta_data = $response->data['meta_data'];
        $new_meta_data = [];
        foreach ($meta_data as $meta_data_item) {
            if ($meta_data_item->get_data()["key"] == "_product_addons") {
                $add_ons_exists = true;
                $meta_data_item->__set("value", $add_ons_list);
                $meta_data_item->apply_changes();
            }
            $new_meta_data[] = $meta_data_item;
        }
        if(!$add_ons_exists && count($add_ons_list) > 0){
            $new_meta_data[] = new WC_Meta_Data(
                array(
                    'key'   =>'_product_addons',
                    'value' => $add_ons_list,
                )
            );
        }
        $response->data['meta_data'] = $new_meta_data;
    }
    

    /* Product Booking */
    if (is_plugin_active('woocommerce-appointments/woocommerce-appointments.php')) {
        $terms = wp_get_post_terms($response->data['id'], 'product_type');
        if ($terms != false && count($terms) > 0 && $terms[0]->name == 'appointment') {
            $response->data['type'] = 'appointment';
            $response->data['appointment_duration'] = $product->get_duration();
            $response->data['appointment_duration_unit'] = $product->get_duration_unit();
        }
    }

    /*Update product price for subscription product*/
    if($product && ($product->is_type('subscription') || $product->is_type('variable-subscription'))){
        $meta_data = $response->data['meta_data'];
        $sign_up_fee = null;
        foreach ($meta_data as $meta_data_item) {
            if ($meta_data_item->get_data()["key"] == "_subscription_sign_up_fee") {
                $sign_up_fee = $meta_data_item->get_data()["value"];
            }
        }
        if($sign_up_fee != null){
            $response->data['regular_price']= $sign_up_fee;
            $response->data['price']= $sign_up_fee;
        }
    }
    
    /* TeraWallet */
    if ( class_exists( 'WooWallet' ) ) {
        $response->data['is_wallet_product'] =  get_option( '_woo_wallet_recharge_product' ) == (string) $response->data['id'];
    }
    
    /* YITH WooCommerce Barcodes and QR Codes Premium */
    $response = addQRCodeUrlToMetaResponse($response);

    /* YITH WooCommerce Badge Management Premium */
    $response = addYITHBadgeToMetaResponse($response, $product);

    $blackListKeys = ['yoast_head','yoast_head_json','_links'];
    $response->data = array_diff_key($response->data,array_flip($blackListKeys));
    return $response;
}

/// Clone from wp-content/plugins/woocommerce-brands/includes/widgets/class-wc-widget-brand-nav.php
function get_filtered_term_product_counts($request, $taxonomy, $term_ids = [], $hide_empty = true)
{
    global $wpdb;

    if (!isset($term_ids) || empty($term_ids)) {
        $term_ids = wp_list_pluck(get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => $hide_empty,
        )), 'term_id');
    }

    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }

    $tax_query  = array();
    $meta_query = array();

    $category = sanitize_text_field($request['category']);
    if (isset($category) && $category) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'terms' => explode(',', $category),
        );
    }

    $brand = sanitize_text_field($request['brand']);
    if (isset($brand) && $brand) {
        $tax_query[] = array(
            'taxonomy' => 'product_brand',
            'terms' => explode(',', $brand),
        );
    }

    $tag = sanitize_text_field($request['tag']);
    if (isset($tag) && $tag) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'terms' => explode(',', $tag),
        );
    }

    $meta_query      = new WP_Meta_Query($meta_query);
    $tax_query       = new WP_Tax_Query($tax_query);
    $meta_query_sql  = $meta_query->get_sql('post', $wpdb->posts, 'ID');
    $tax_query_sql   = $tax_query->get_sql($wpdb->posts, 'ID');

    // Generate query
    $query           = array();
    $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
    $query['from']   = "FROM {$wpdb->posts}";
    $query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];
    $query['where']   = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'
			" . $tax_query_sql['where'] . $meta_query_sql['where'] . "
			AND terms.term_id IN (" . implode(',', array_map('absint', $term_ids)) . ")
		";
    $query['group_by'] = "GROUP BY terms.term_id";
    $query             = apply_filters('woocommerce_get_filtered_term_product_counts_query', $query);
    $query             = implode(' ', $query);

    $term_counts = $wpdb->get_results($query, ARRAY_A);

    $exist_terms_ids = array_column($term_counts, 'term_count_id');

    // In some cases, the `term_counts` result may be missing some terms contained in `$term_ids`.
    $missing_term_ids = array_diff($term_ids, $exist_terms_ids);

    // Add missing terms to result with term_count = 0
    $result = $term_counts;
    foreach ($missing_term_ids as $id) {
        $result[] = [
            'term_count' => 0,
            'term_count_id' => $id
        ];
    }

    return $result;
}

function getLangCodeFromConfigFile ($file) {
    return str_replace('config_', '', str_replace('.json', '',$file));
}

function generateCookieByUserId($user_id, $seconds = 1209600){
    $expiration = time() + 365 * DAY_IN_SECONDS;
    $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
    return $cookie;
}

function validateCookieLogin($cookie){
    if(isset($cookie) && strlen($cookie) > 0){
        $userId = wp_validate_auth_cookie($cookie, 'logged_in');
        if($userId == false){
            return new WP_Error("expired_cookie", "Your session has expired. Please logout and login again.", array('status' => 401));
        }else{
            return $userId;
        }
    }else{
        return new WP_Error("invalid_login", "Cookie is required", array('status' => 401));
    }
}

function checkWhiteListAccounts ($user_id) {
    $whiteList = array('vendor@demo.com', 'delivery_demo', 'demo');
    $user_info = get_userdata($user_id);
    return in_array($user_info->user_email, $whiteList) || in_array($user_info->user_login, $whiteList);
}

function checkIsAdmin($user_id){
    $user = get_userdata( $user_id );
    $user_roles = $user->roles;
    $is_admin = in_array( 'administrator', $user_roles, true );
    return $is_admin;
}

function upload_image_from_mobile($image, $count, $user_id)
{
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    $imgdata = $image;
    $imgdata = trim($imgdata);
    $imgdata = str_replace('data:image/png;base64,', '', $imgdata);
    $imgdata = str_replace('data:image/jpg;base64,', '', $imgdata);
    $imgdata = str_replace('data:image/jpeg;base64,', '', $imgdata);
    $imgdata = str_replace('data:image/gif;base64,', '', $imgdata);
    $imgdata = str_replace(' ', '+', $imgdata);
    $imgdata = base64_decode($imgdata);
    $f = finfo_open();
    $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
    $type_file = explode('/', $mime_type);
    $avatar = time() . '_' . $count . '.' . $type_file[1];

    $uploaddir = wp_upload_dir();
    $myDirPath = $uploaddir["path"];
    $myDirUrl = $uploaddir["url"];

    file_put_contents($uploaddir["path"] . '/' . $avatar, $imgdata);

    $filename = $myDirUrl . '/' . basename($avatar);
    $wp_filetype = wp_check_filetype(basename($filename), null);
    $uploadfile = $uploaddir["path"] . '/' . basename($filename);

    $attachment = array(
        "post_mime_type" => $wp_filetype["type"],
        "post_title" => preg_replace("/\.[^.]+$/", "", basename($filename)),
        "post_content" => "",
        "post_author" => $user_id,
        "post_status" => "inherit",
        'guid' => $myDirUrl . '/' . basename($filename),
    );

    $attachment_id = wp_insert_attachment($attachment, $uploadfile);
    $attach_data = apply_filters('wp_generate_attachment_metadata', $attachment, $attachment_id, 'create');
    // $attach_data = wp_generate_attachment_metadata($attachment_id, $uploadfile);
    wp_update_attachment_metadata($attachment_id, $attach_data);
    return $attachment_id;
}

function getSellerIdsByOrderId($order_id){
    $seller_ids = [];
    if (is_plugin_active('dokan-lite/dokan.php')) {
        $order_seller_id = dokan_get_seller_id_by_order($order_id);
        if (isset($order_seller_id) && $order_seller_id != false) {
            $seller_ids[] = $order_seller_id;
        }
    }else if (is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php')) {
        if (function_exists('wcfm_get_vendor_store_by_post')) {
            $order = wc_get_order($order_id);
            if (is_a($order, 'WC_Order')) {
                $items = $order->get_items('line_item');
                if (!empty($items)) {
                    foreach ($items as $order_item_id => $item) {
                        $line_item = new WC_Order_Item_Product($item);
                        $product = $line_item->get_product();
                        $product_id = $line_item->get_product_id();
                        $vendor_id = wcfm_get_vendor_id_by_post($product_id);
                        if (!$vendor_id) continue;
                        if (in_array($vendor_id, $seller_ids)) continue;

                        $store_name = wcfm_get_vendor_store($vendor_id);
                        if ($store_name) {
                            $seller_ids[] = $vendor_id;
                        }
                    }
                }
            }
        }
    }else{
        $users_query = new WP_User_Query( array( 
                    'role' => 'Administrator', 
                    'fields' => 'ID'
                    ));
        $seller_ids = $users_query->get_results();
    }
    return $seller_ids;
}

function sendNotificationForOrderStatusUpdated($order_id, $status)
{
    $seller_ids = getSellerIdsByOrderId($order_id);
    if (empty($seller_ids)) {
        return;
    }

    $title = "Update Order";

    foreach ($seller_ids as $seller_id) {
        $user = get_userdata($seller_id);
        if ($status == 'refund-req') {
            $message = "Hi {{name}}, The order #{{order}} is refunded.";
        }else if($status == 'cancelled'){
            $message = "Hi {{name}}, The order #{{order}} is cancelled.";
        }else{
            $message = "Hi {{name}}, The order #{{order}} is updated.";
        }
        $message = str_replace("{{name}}", $user->display_name, $message);
        $message = str_replace("{{order}}", $order_id, $message);
    
        pushNotificationForVendor($seller_id, $title, $message);
    }
}

function _pushNotificationFirebase($user_id, $title, $message, $deviceToken, $is_notification_on){
    $is_on = $is_notification_on == true || isNotificationEnabled($user_id);
    if($is_on){
        FirebaseMessageHelper::push_notification($title, $message, $deviceToken);
    }
}

function _pushNotificationOneSignal($user_id, $title, $message, $is_notification_on){
    $is_on = $is_notification_on == true || isNotificationEnabled($user_id);
    if($is_on){
        one_signal_push_notification($title,$message,array($user_id));
    }
}

function isNotificationEnabled($user_id){
    $is_on = get_user_meta($user_id, "mstore_notification_status", true);
    return  $is_on === "" || $is_on === "on";
}

function getCommissionOrderResponse($responseData, $vendor_id){
    if(is_plugin_active(
        "wc-multivendor-marketplace/wc-multivendor-marketplace.php"
    )){
        global $WCFM;
        global $wpdb;

        $order_id = $responseData['id'];
        $order = wc_get_order($order_id);
        $vendorEarnings = 0;
        $adminFee = 0;

        $is_admin = checkIsAdmin($vendor_id);
        if($is_admin){
            $commission = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_order( $order->get_id() );
            if( $commission ) {
                $vendorEarnings = (float) $commission;
        
                $gross_sales  = (float) $order->get_total();
                $total_refund = (float) $order->get_total_refunded();
                //if( $admin_fee_mode || ( $marketplece == 'dokan' ) ) {
                    $adminFee = $gross_sales - $total_refund - $commission;
                //}
            }
            $responseData["vendor_earnings"] = $vendorEarnings;
            $responseData["admin_fee"] = $adminFee;
        }else{
            $sql = "
                SELECT GROUP_CONCAT(ID) as commission_ids,
                GROUP_CONCAT(item_id) as order_item_ids,
                SUM(commission_amount) as line_total,
                SUM(total_commission) as total_commission,
                SUM(item_total) as item_total,
                SUM(item_sub_total) as item_sub_total,
                    SUM(shipping) as shipping,
                SUM(tax) as tax,
                SUM(	shipping_tax_amount) as shipping_tax_amount,
                SUM(	refunded_amount) as refunded_amount,
                SUM(	discount_amount) as discount_amount
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE order_id = %d
                AND `vendor_id` = %d
                AND `is_refunded` != 1";
                $order_due = $wpdb->get_results( $wpdb->prepare( $sql, $order_id, $vendor_id ) );
                if( !$order_due || !isset( $order_due[0] ) ){
                    $responseData["vendor_earnings"] = 0;
                    $responseData["admin_fee"] = 0;
                    return $responseData;
                }else{
                    $gross_sale_order = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $vendor_id, '', '', $order_id );
                    $total = $order_due[0]->total_commission;
                    $responseData["vendor_earnings"] = $total;
                    $responseData["admin_fee"] = $gross_sale_order - $total;
                }
        }
    }else{
        $responseData["vendor_earnings"] = 0;
        $responseData["admin_fee"] = 0;
    }
    return $responseData;
}

function customOrderResponse($response, $object, $request)
{
    /* YITH WooCommerce Barcodes and QR Codes Premium */
    $response = addQRCodeUrlToMetaResponse($response);

    if(function_exists( 'wcfm_is_order_delivered' ) ) {
        $is_order_delivered = wcfm_is_order_delivered( $response->data['id'] );
        $response->data['delivery_status'] = $is_order_delivered ? 'delivered' : 'pending';
    }
    
    return $response;
}

function cleanupAppointmentCartData($customer_id) {
    if(class_exists( 'WC_Appointments' )){
        $appointment_ids = WC_Appointment_Data_Store::get_appointment_ids_by(
			[
				'status'           => [ 'in-cart', 'was-in-cart' ],
				'object_id'   => $customer_id,
				'object_type' => 'customer',
			]
		);

		if ( $appointment_ids ) {
			foreach ( $appointment_ids as $appointment_id ) {
				wp_trash_post( $appointment_id );
			}
		}
    }	
}

function buildCartItemData($products, $callback){
    foreach ($products as $product) {
                $productId = absint($product['product_id']);

                $quantity = $product['quantity'];
                $variationId = isset($product['variation_id']) ? $product['variation_id'] : "";

                $attributes = [];
                if (isset($product["meta_data"])) {
                    foreach ($product["meta_data"] as $item) {
                        if($item["value"] != null){
                            $attributes[strtolower($item["key"])] = $item["value"];
                        }
                    }
                }

                if (isset($product['addons'])) {
                    $addons = array();
                    foreach ($product['addons'] as $key => $value) {
                        if(is_array($value)){
                            $addons[$key] = array_map(function($val){
                                return sanitize_title($val);
                            },$value);
                        }else if (is_string($value)) {
                            $addons[$key] = sanitize_title($value);
                        }else{
                            $addons[$key] = $value;
                        }
                    }
                    $_POST = $addons;
                }
                
                // Check the product variation
                if (!empty($variationId)) {
                    $productVariable = new WC_Product_Variable($productId);
                    $listVariations = $productVariable->get_available_variations();
                    foreach ($listVariations as $vartiation => $value) {
                        if ($variationId == $value['variation_id']) {
                            $attributes = array_merge($value['attributes'], $attributes);
                            $callback($productId, $quantity, $variationId, $attributes, array());
                        }
                    }
                } else {
                    parseMetaDataForBookingProduct($product);
                    $cart_item_data = array();
                    if (is_plugin_active('woo-wallet/woo-wallet.php')) {
                        $wallet_product = get_wallet_rechargeable_product();
                        if ($wallet_product->get_id() == $productId) {
                            $cart_item_data['recharge_amount'] = $product['total'];
                        }
                    }
                    if(isset($product['ywgc_amount'])){
                        $cart_item_data['ywgc_amount'] = $product['ywgc_amount'];
                        $cart_item_data['ywgc_product_id'] = $productId;
                    }
                    $callback($productId, $quantity, 0, $attributes, $cart_item_data);
                }
            }
}
?>