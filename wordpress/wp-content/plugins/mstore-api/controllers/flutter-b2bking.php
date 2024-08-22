<?php
require_once(__DIR__ . '/flutter-base.php');

/*
 * Base REST Controller for flutter
 *
 * @since 1.4.0
 *
 * @package B2BKing
 */

class FlutterB2BKing extends FlutterBaseController
{
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'api/flutter_b2bking';

    /**
     * Register all routes releated with stores
     *
     * @return void
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_flutter_b2bking_routes'));
    }

    public function register_flutter_b2bking_routes()
    {
        register_rest_route($this->namespace, '/roles', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_roles'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/register_fields', array(
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_register_fields'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/register', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'register'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/product/(?P<id>[\d]+)/tiered_price', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                ),
            ),
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_tiered_price'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));

        register_rest_route($this->namespace, '/product/(?P<id>[\d]+)/info_table', array(
            'args' => array(
                'id' => array(
                    'description' => __('Unique identifier for the resource.', 'woocommerce'),
                    'type' => 'integer',
                ),
            ),
            array(
                'methods' => "GET",
                'callback' => array($this, 'get_info_table'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
        
        register_rest_route($this->namespace, '/send_quote', array(
            array(
                'methods' => "POST",
                'callback' => array($this, 'send_quote'),
                'permission_callback' => function () {
                    return parent::checkApiPermission();
                }
            ),
        ));
    }

    public function get_roles($request)
    {
        if (!class_exists('B2bking')) {
            return parent::send_invalid_plugin_error("You need to install B2BKing Core plugin to use this api");
        }

        $roles = get_posts([
            'post_type' => 'b2bking_custom_role',
              'post_status' => 'publish',
              'numberposts' => -1,
              'orderby' => 'menu_order',
              'order' => 'ASC',
              'meta_query'=> array(
                  'relation' => 'AND',
                array(
                    'key' => 'b2bking_custom_role_status',
                    'value' => 1
                ),
            )
        ]);

        $results = array();
        foreach ($roles as $role){
            $approval_required = get_post_meta($role->ID,'b2bking_custom_role_approval',true);
            $results[] = ['ID' => $role->ID, 'name' => $role->post_title, 'role' => 'role_'.$role->ID, 'approval_required' => $approval_required != 'automatic'];
        }
        return $results;
    }

    public function get_register_fields($request)
    {
        if (!class_exists('B2bking')) {
            return parent::send_invalid_plugin_error("You need to install B2BKing Core plugin to use this api");
        }

        $fields = array();
        $custom_fields = get_posts([
            'post_type' => 'b2bking_custom_field',
              'post_status' => 'publish',
              'numberposts' => -1,
              'orderby' => 'menu_order',
              'order' => 'ASC',
              'meta_query'=> array(
                  'relation' => 'AND',
                array(
                    'key' => 'b2bking_custom_field_status',
                    'value' => 1
                ),
            )
        ]);

        foreach ($custom_fields as $custom_field){
            $field_type = get_post_meta($custom_field->ID, 'b2bking_custom_field_field_type', true);
			$field_label = get_post_meta(apply_filters( 'wpml_object_id', $custom_field->ID, 'post', true ), 'b2bking_custom_field_field_label', true);
			$field_placeholder = get_post_meta(apply_filters( 'wpml_object_id', $custom_field->ID, 'post', true ), 'b2bking_custom_field_field_placeholder', true);
			$required = get_post_meta($custom_field->ID, 'b2bking_custom_field_required', true);
            $role = get_post_meta($custom_field->ID, 'b2bking_custom_field_registration_role', true);
			if ($role !== 'multipleroles'){
				$role_class = esc_attr($role);
			} else {
				$role_class = get_post_meta($custom_field->ID, 'b2bking_custom_field_multiple_roles', true);
			}
            $fields[] = ['ID' => $custom_field->ID,'label' =>$field_label, 'placeholder' => $field_placeholder, 'type' => $field_type, 'required' => $required == '1', 'role' => $role_class];
        }
        return $fields;
    }

    function get_field_value($field_id, $custom_fields){
        $result = null;
        foreach ($custom_fields as $key => $value) {
            if($field_id == $value['id']){
                $result = $value['value'];
                break;
            }
        }
        return $result;
    }

    public function is_rest_api_request_custom($is_rest_api_request){
        return false;
    }

    public function register()
    {
        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);
        $email = sanitize_text_field(filter_input(INPUT_POST, 'email')); 
        $password = sanitize_text_field(filter_input(INPUT_POST, 'password')); 
        $register_role = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_registration_roles_dropdown')); 
        
        if (!class_exists('B2bking')) {
            return parent::send_invalid_plugin_error("You need to install B2BKing Core plugin to use this api");
        }

        //Check role
        $roles = get_posts([
            'post_type' => 'b2bking_custom_role',
              'post_status' => 'publish',
              'numberposts' => -1,
              'orderby' => 'menu_order',
              'order' => 'ASC',
              'meta_query'=> array(
                  'relation' => 'AND',
                array(
                    'key' => 'b2bking_custom_role_status',
                    'value' => 1
                ),
            )
        ]);
        $valid_role = false;
        $results = array();
        foreach ($roles as $role){
            if('role_'.$role->ID == $register_role){
                $valid_role = true;
                break;
            }
        }
        if($valid_role == false){
            return parent::sendError('required', 'role is incorrect', 400);
        }

        //Check custom fields
        $custom_fields = get_posts([
            'post_type' => 'b2bking_custom_field',
              'post_status' => 'publish',
              'numberposts' => -1,
              'orderby' => 'menu_order',
              'order' => 'ASC',
              'meta_query'=> array(
                  'relation' => 'AND',
                array(
                    'key' => 'b2bking_custom_field_status',
                    'value' => 1
                ),
            )
        ]);
       
        $err_msg = null;
        foreach ($custom_fields as $custom_field){
            $field_type = get_post_meta($custom_field->ID, 'b2bking_custom_field_field_type', true);
			$required = get_post_meta($custom_field->ID, 'b2bking_custom_field_required', true);
            $role = get_post_meta($custom_field->ID, 'b2bking_custom_field_registration_role', true);
            if ($required == '1') {
                $check_required = false;
                if($role == 'allroles'){
                    $check_required = true;
                }else if($role === 'multipleroles'){
                    $field_roles = get_post_meta($custom_field->ID, 'b2bking_custom_field_multiple_roles', true);
                    $check_required = in_array($register_role, explode(',', $field_roles));
                }else{
                    if($register_role == $role){
                        $check_required = true;
                    }
                }

                if($check_required){
                    $value = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_custom_field_'.$custom_field->ID)); 
                    if($value == null || $value == ''){
                        $field_label = get_post_meta(apply_filters( 'wpml_object_id', $custom_field->ID, 'post', true ), 'b2bking_custom_field_field_label', true);
                        $err_msg = $field_label.' is required.';
                        break;
                    }
                }
            }
        }
        if($err_msg != null){
            return parent::sendError('required', $err_msg, 400);
        }

        add_filter('is_rest_api_request', array($this, 'is_rest_api_request_custom'), 10);
        
        $user_params = array();
        $user_params["user_email"] = sanitize_email($email);
        $user_params["user_login"] = sanitize_email($email);
        $user_params["user_pass"] = $password;

        $user_id = wp_insert_user($user_params);

        remove_filter('is_rest_api_request', array($this, 'is_rest_api_request_custom'), 10);

        if (is_wp_error($user_id)) {
            return parent::sendError($user_id->get_error_code(), $user_id->get_error_message(), 400);
        }

        return true;
    }

    function get_tiered_price($request){
        $cookie = $request->get_header("User-Cookie");
        $post_id = $request->get_param( 'id' );
        $product = wc_get_product($post_id);
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            
        } else {
            $user_id = 0;
        }

        wp_set_current_user($user_id);
        $user_id = b2bking()->get_top_parent_account($user_id);

        //init WC()->cart to get correct product price
        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

            WC()->session = new $session_class();
            WC()->session->init();
        }
        if (null === WC()->cart) {
            WC()->cart = new WC_Cart();
        }
        if (null === WC()->customer) {
            WC()->customer = new WC_Customer(get_current_user_id(), true);
        }

        $currentusergroupidnr = apply_filters('b2bking_b2b_group_for_pricing', b2bking()->get_user_group($user_id), $user_id);
        
        $is_b2b_user = get_user_meta( $user_id, 'b2bking_b2buser', true );
        if ( apply_filters('b2bking_tiered_table_discount_uses_sale_price', $product->is_on_sale() ) ) {
            $original_user_price = get_post_meta($product->get_id(),'_sale_price',true);
               
            if ($is_b2b_user === 'yes'){
                // Search if there is a specific price set for the user's group
                $b2b_price = b2bking()->tofloat(get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
                                    
                if (!empty($b2b_price)){
                    $original_user_price = $b2b_price;
                }
            }
        } else {
            $original_user_price = get_post_meta($product->get_id(),'_regular_price',true);

            if ($is_b2b_user === 'yes'){
                // Search if there is a specific price set for the user's group
                $b2b_price = b2bking()->tofloat(get_post_meta($product->get_id(), 'b2bking_regular_product_price_group_'.$currentusergroupidnr, true ));
                                    
                if (!empty($b2b_price)){
                    $original_user_price = $b2b_price;
                }
            }
        }
        // adjust price for tax
        $original_user_price = b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $original_user_price ) ); // get sale price

        $price_tiers = get_post_meta($post_id, 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true);

        $user_price = array();
        $grpriceexists = 'no';


        // if didn't find anything as a price tier + user does not have group price, give regular price tiers
        // if no tiers AND no group price exists, get B2C tiered pricing
        if ($currentusergroupidnr){
            $grregprice = get_post_meta($post_id, 'b2bking_regular_product_price_group_'.$currentusergroupidnr, true );
            $grsaleprice = get_post_meta($post_id, 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
            if (!empty($grregprice) && b2bking()->tofloat($grregprice) !== 0){
                $grpriceexists = 'yes';	
            }
            if (!empty($grsaleprice) && b2bking()->tofloat($grsaleprice) !== 0){
                $grpriceexists = 'yes';	
            }
        }


        if (empty($price_tiers) && $grpriceexists === 'no'){
            $price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_b2c', true );
        }

        // apply percentage instead of final prices (optiinally)
        $price_tiers = b2bking()->convert_price_tiers($price_tiers, $product);

        if (!empty($price_tiers) && strlen($price_tiers) > 1) {
            $price_tiers_array = explode(';', $price_tiers);
            $price_tiers_array = array_filter($price_tiers_array);

            // need to order this array by the first number (elemnts of form 1:5, 2:5, 6:5)
            $helper_array = array();							
            foreach ($price_tiers_array as $index=> $pair){
                $pair_array = explode(':', $pair);
                $helper_array[$pair_array[0]] = b2bking()->tofloat($pair_array[1], 4);
            }
            ksort($helper_array);
            $tired_prices = array();
            foreach ($helper_array as $index=>$value){
                $now_price = $value;
                $discount = ($original_user_price-$now_price)/$original_user_price*100;
                $tired_prices[] = ['quantity' => $index, 'discount' => round($discount), 'price'=>round($now_price)];
            }
            return $tired_prices;
        }
        return [];
    }

    function get_info_table($request){
        $cookie = $request->get_header("User-Cookie");
        $post_id = $request->get_param( 'id' );
        if (isset($cookie) && $cookie != null) {
            $user_id = validateCookieLogin($cookie);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            
        } else {
            $user_id = 0;
        }
        
        $results = [];

        $is_enabled = get_post_meta($post_id, 'b2bking_show_information_table', true);
		if ($is_enabled === 'no' && !apply_filters('b2bking_show_information_table_all', false)){
            return $results;
        }

        $user_id = b2bking()->get_top_parent_account($user_id);
        $currentusergroupidnr = b2bking()->get_user_group($user_id);
        $customrows = get_post_meta($post_id, 'b2bking_product_customrows_group_'.$currentusergroupidnr, true);

        // if didn't find anything as a price tier, give regular price tiers
        if (empty($customrows)){
            if (apply_filters('b2bking_information_table_apply_regular_all', true)){
                $customrows = get_post_meta($post_id, 'b2bking_product_customrows_group_b2c', true);
            }
        }

        if (!empty($customrows) || apply_filters('b2bking_show_information_table_all', false)){
            $customrows = str_replace('&amp;', '&', $customrows);

            $rows_array = explode(';',$customrows);
            $rows_array = apply_filters('b2bking_information_table_content_rows', $rows_array);
            foreach ($rows_array as $row){
                $row_values = explode (':', $row, 2);
                if (!empty($row_values[0]) && !empty($row_values[1])){
                    $results[] = ['label' => $row_values[0], 'text' => $row_values[1]];
                }
            }
        }

        return $results;
    }

    public function send_quote()
    {
        if (!class_exists('B2bking')) {
            return parent::send_invalid_plugin_error("You need to install B2BKing Core plugin to use this api");
        }

        $json = file_get_contents('php://input');
        $params = json_decode($json, TRUE);

        do_action('b2bkingrequestquotecart');
    }
}

new FlutterB2BKing;