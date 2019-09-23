<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');


final class WOOCS_FIXED_COUPON extends WOOCS_FIXED_AMOUNT {
    
    public function __construct() {
        $this->key='_coupon_';
        add_action('woocommerce_coupon_options', array($this, 'add_fixed_coupon_amount'), 9999); 
        add_action('woocommerce_coupon_options_usage_restriction', array($this, 'add_fixed_coupon_restriction_amount'), 9999); 
        add_action('woocommerce_coupon_options_save', array($this, 'save_fixed_coupon'), 9999, 1); 
    }
    public function add_fixed_coupon_amount() {
        global $WOOCS;
        global $post;
        add_action('admin_footer', array($this, 'admin_footer'));
        $data = array();
        $data['currencies'] = $WOOCS->get_currencies();
        $data['default_currency'] = $WOOCS->default_currency;
        $data['is_fixed_enabled'] = $WOOCS->is_fixed_coupon;

        $data['post_id'] = $post->ID;
        $data['type'] = 'amount';

        echo $this->render_html(WOOCS_PATH . 'views/fixed/product_coupon_data.php', $data);
    }

    public function add_fixed_coupon_restriction_amount() {
        global $post;
        global $WOOCS;
        add_action('admin_footer', array($this, 'admin_footer'));
        $data = array();
        $data['currencies'] = $WOOCS->get_currencies();
        $data['default_currency'] = $WOOCS->default_currency;
        $data['is_fixed_enabled'] = $WOOCS->is_fixed_coupon;

        $data['post_id'] = $post->ID;
        $data['type'] = 'restriction';

        echo $this->render_html(WOOCS_PATH . 'views/fixed/product_coupon_restriction_data.php', $data);
    }
    public function save_fixed_coupon($post_id){
        
        if (!current_user_can('manage_options')) {
	    return;
	}
	//***
	global $WOOCS;
	$currencies = $WOOCS->get_currencies();

	//+++
	if (isset($_POST['woocs_fixed_coupon'][$post_id])) {
	    unset($_POST['woocs_fixed_coupon'][0]);
	    unset($_POST['woocs_fixed_coupon']['__POST_ID__']);

	    //clean all data before apply new selected data
	    foreach ($currencies as $code => $curr) {
		delete_post_meta($post_id, '_woocs_amount_coupon_' . $code);
	    }
	    //+++
	    if (is_array($_POST['woocs_fixed_coupon'][$post_id])) {
		foreach ($_POST['woocs_fixed_coupon'][$post_id] as $code => $amount) {
		    //$amount = floatval($amount);
                    $amount= $this->prepare_float_val($amount);
		    if ($amount > 0) {
			update_post_meta($post_id, '_woocs_amount_coupon_' . $code, $amount);
		    } else {
			update_post_meta($post_id, '_woocs_amount_coupon_' . $code, -1);
		    }
		}
	    }

	}
	//+++
	if (isset($_POST['woocs_restriction_min'][$post_id])) {
	    unset($_POST['woocs_restriction_min'][0]);
	    unset($_POST['woocs_restriction_min']['__POST_ID__']);
	    unset($_POST['woocs_restriction_max'][0]);
	    unset($_POST['woocs_restriction_max']['__POST_ID__']);
            
	    //clean all data before apply new selected data
	    foreach ($currencies as $code => $curr) {
		delete_post_meta($post_id, '_woocs_min_spend_coupon_' . $code);
                delete_post_meta($post_id, '_woocs_max_spend_coupon_' . $code);
	    }
	    //+++
	    if (is_array($_POST['woocs_restriction_min'][$post_id])) {
		foreach ($_POST['woocs_restriction_min'][$post_id] as $code => $amount) {

                    $amount= $this->prepare_float_val($amount);                    
		    if ($amount > 0) {
			update_post_meta($post_id, '_woocs_min_spend_coupon_' . $code, $amount);
		    } else {
			update_post_meta($post_id, '_woocs_min_spend_coupon_' . $code, -1);
		    }
		}
	    }
            if (is_array($_POST['woocs_restriction_max'][$post_id])) {
		foreach ($_POST['woocs_restriction_max'][$post_id] as $code => $amount) {

                    $amount= $this->prepare_float_val($amount);                    
		    if ($amount > 0) {
			update_post_meta($post_id, '_woocs_max_spend_coupon_' . $code, $amount);
		    } else {
			update_post_meta($post_id, '_woocs_max_spend_coupon_' . $code, -1);
		    }
		}
	    }

	}
	//+++       
    }
    
}

