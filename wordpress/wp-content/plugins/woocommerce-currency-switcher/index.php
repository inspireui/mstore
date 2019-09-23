<?php

/*
  Plugin Name: WOOCS - WooCommerce Currency Switcher
  Plugin URI: https://currency-switcher.com/
  Description: Currency Switcher for WooCommerce that allows to the visitors and customers on your woocommerce store site switch currencies and optionally apply selected currency on checkout
  Author: realmag777
  Version: 1.3.0
  Requires at least: WP 4.1.0
  Tested up to: WP 5.2
  Text Domain: woocommerce-currency-switcher
  Domain Path: /languages
  Forum URI: https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/
  Author URI: https://pluginus.net/
  WC requires at least: 2.6.0
  WC tested up to: 3.7
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


//disable REST api for non-woocs requests
if (isset($_SERVER['SCRIPT_URI'])) {
    $uri = parse_url(trim($_SERVER['SCRIPT_URI']));
    $uri = explode('/', trim($uri['path'], ' /'));

    if ($uri[0] === 'wp-json') {
        $allow = ['woocs'];
        if (!in_array($uri[1], $allow)) {
            return;
        }
    }
}


if (defined('DOING_AJAX')) {
    if (isset($_REQUEST['action'])) {
        //do not recalculate refund amounts when we are in order backend
        if ($_REQUEST['action'] == 'woocommerce_refund_line_items') {
            return;
        }

        if (isset($_REQUEST['order_id']) AND $_REQUEST['action'] == 'woocommerce_load_order_items') {
            return;
        }
    }
}

define('WOOCS_VERSION', '1.3.0');
define('WOOCS_MIN_WOOCOMMERCE', '2.6');
define('WOOCS_PATH', plugin_dir_path(__FILE__));
define('WOOCS_LINK', plugin_dir_url(__FILE__));
define('WOOCS_PLUGIN_NAME', plugin_basename(__FILE__));

//classes
include_once WOOCS_PATH . 'classes/storage.php';
include_once WOOCS_PATH . 'classes/cron.php';
include_once WOOCS_PATH . 'classes/alert.php';
include_once WOOCS_PATH . 'classes/fixed/fixed_amount.php';
include_once WOOCS_PATH . 'classes/fixed/fixed_price.php';
include_once WOOCS_PATH . 'classes/statistic.php';

//26-08-2019
class WOOCS_STARTER {

    private $support_time = 1519919054; // Date of the old < 3.3 version support
    private $default_woo_version = 2.9;
    private $actualized = 0.0;
    private $version_key = "woocs_woo_version";
    private $_woocs = null;

    public function __construct() {
        if (time() > $this->support_time) {
            $this->default_woo_version = 3.6;
        }
        $this->actualized = floatval(get_option($this->version_key, $this->default_woo_version));
    }

    public function update_version() {
        if (defined('WOOCOMMERCE_VERSION') AND ( $this->actualized !== floatval(WOOCOMMERCE_VERSION))) {
            update_option('woocs_woo_version', WOOCOMMERCE_VERSION);
        }
    }

    public function get_actual_obj() {
        if ($this->_woocs != null) {
            return $this->_woocs;
        }
        if (version_compare($this->actualized, '3.3', '<')) {
            include_once WOOCS_PATH . 'classes/woocs_before_33.php';
        } else {
            include_once WOOCS_PATH . 'classes/woocs_after_33.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_coupon.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_shipping.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_shipping_free.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_user_role.php';
            include_once WOOCS_PATH . 'classes/auto_switcher.php';
        }
        $this->_woocs = new WOOCS();
        return $this->_woocs;
    }

}

//+++
if (isset($_GET['P3_NOCACHE'])) {
    //stupid trick for that who believes in P3
    return;
}

//+++
//fix 28-08-2018 because of long id which prevent js script working
function woocs_short_id($smth) {
    return substr(md5($smth), 1, 7);
}

//+++
$WOOCS_STARTER = new WOOCS_STARTER();

$WOOCS = $WOOCS_STARTER->get_actual_obj();
$GLOBALS['WOOCS'] = $WOOCS;
add_action('init', array($WOOCS, 'init'), 1);

//****
//rate + interes
add_filter('woocs_currency_data_manipulation', function ($currencies) {
    foreach ($currencies as $key => $value) {
        if (isset($value['rate_plus'])) {
            $currencies[$key]['rate'] = floatval($value['rate']) + floatval($value['rate_plus']);
        }
    }

    return $currencies;
}, 1, 1);

//hide WOOCS meta in the order
add_filter('woocommerce_order_item_get_formatted_meta_data', function ($formatted_meta) {
    foreach ($formatted_meta as $key => $meta) {
        if (in_array($meta->key, ['_woocs_order_rate', '_woocs_order_base_currency', '_woocs_order_currency_changed_mannualy'])) {
            unset($formatted_meta[$key]);
        }
    }
    return $formatted_meta;
}, 10, 1);


