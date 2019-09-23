<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

class WOOCS_FIXED_AMOUNT {

    protected $key = "";

    public function __construct() {
        
    }

    public function admin_footer() {
        wp_enqueue_script('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'), WOOCS_VERSION);
        wp_enqueue_style('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.min.css',array(), WOOCS_VERSION);
        wp_enqueue_script('woocs-fixed', WOOCS_LINK . 'js/fixed.js', array('jquery'), WOOCS_VERSION);
        wp_enqueue_style('woocs-fixed', WOOCS_LINK . 'css/fixed.css',array(), WOOCS_VERSION);
    }

    public function get_value($post_id, $code, $type) {
        return get_post_meta($post_id, '_woocs_' . $type . $this->key . strtoupper($code), true);
    }

    public function is_exists($post_id, $code, $type) {
        $is = false;
        $val = $this->get_value($post_id, $code, $type);
        if (floatval($val) > 0 OR (int) $val === -1) {
            $is = true;
        }
        return $is;
    }

    public function is_empty($post_id, $code, $type) {
        $is = false;
        $val = $this->get_value($post_id, $code, $type);
        if ($val=="" OR (int) $val === -1) {
            $is = true;
        }
        return $is;
    }

    public function prepare_float_val($val) {
        $thousand_sep = wc_get_price_thousand_separator();
        $decimal_sep = wc_get_price_decimal_separator();
        // NOTE: You don't really have to use floatval() here, it just to prove that it a legitimate float value.
        $number = floatval(str_replace($decimal_sep, '.', str_replace($thousand_sep, '', $val)));
        return $number;
    }

    public function prepare_float_to_show($val, $decimals = 2) {
        $decimal_sep = wc_get_price_decimal_separator();
        $number = number_format(floatval($val), $decimals, $decimal_sep, '');
        return $number;
    }

    public function render_html($pagepath, $data = array()) {
        if (isset($data['pagepath'])) {
            unset($data['pagepath']);
        }
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }

}
