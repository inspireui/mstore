<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WOOCS {

    //http://docs.woothemes.com/wc-apidocs/class-WC_Order.html
    public $storage = null;
    public $cron = NULL;
    public $cron_hook = 'woocs_update_rates_wpcron';
    public $wp_cron_period = DAY_IN_SECONDS;
    public $settings = array();
    public $fixed = NULL;
    public $fixed_coupon = NULL;
    public $fixed_shipping = NULL;
    public $fixed_shipping_free = NULL;
    public $default_currency = 'USD'; //EUR -> set any existed currency here if USD is not exists in your currencies list
    public $current_currency = 'USD'; //EUR -> set any existed currency here if USD is not exists in your currencies list
    public $currency_positions = array();
    public $currency_symbols = array();
    public $is_multiple_allowed = false; //from options
    public $is_fixed_enabled = false; //from options, works if is_multiple_allowed enabled
    public $is_fixed_coupon = false;
    public $is_fixed_shipping = false;
    public $is_fixed_shipping_free = false;
    public $is_fixed_user_role = false;
    public $force_pay_bygeoip_rules = false; //from options, works if is_fixed_enabled enabled
    public $is_geoip_manipulation = true; //from options, works if is_multiple_allowed is NOT enabled
    public $decimal_sep = '.';
    public $thousands_sep = ',';
    public $rate_auto_update = ''; //from options
    public $shop_is_cached = 0;
    public $special_ajax_mode = true;
    private $is_first_unique_visit = false;
    public $no_cents = array('JPY', 'TWD'); //recount price without cents always!!
    public $price_num_decimals = 2;
    public $actualized_for = 0; //created especially for woo >= 2.7 as it not possible to use const WOOCOMMERCE_VERSION in the code at some places
    public $bones = array(
        'reset_in_multiple' => false, //normal is false
        'disable_currency_switching' => false//normal is false. To force the customer to pay in Welcome currency for example, do it by your own logic
    ); //just for some setting for current wp theme adapting - for support only - it is logic hack - be care!!
    public $notes_for_free = true; //dev, displays notes for free version only
    public $statistic = null;

    public function __construct() {
 
        $this->storage = new WOOCS_STORAGE(get_option('woocs_storage', 'transient'));
        $this->statistic = new WOOCS_STATISTIC();

        $this->init_no_cents();
        if (!defined('DOING_AJAX')) {
//we need it if shop uses cache plugin, in such way prices will be redraw by AJAX
            $this->shop_is_cached = get_option('woocs_shop_is_cached', 0);
        }

        //need for woo 2.7
        $this->actualized_for = floatval(get_option('woocs_woo_version', 3.2));

//+++
        add_filter('pre_option_woocommerce_price_num_decimals', array($this, 'woocommerce_price_num_decimals'));

        if (version_compare($this->actualized_for, 3.6, '>=')) {
            add_filter('woocommerce_cart_hash', array($this, 'woocommerce_add_to_cart_hash'), 2, 99999);
        } else {
            add_filter('woocommerce_add_to_cart_hash', array($this, 'woocommerce_add_to_cart_hash'));
        }




        add_action('wp_enqueue_scripts', array($this, 'disable_woo_slider_script'), 100);
//+++
        $currencies = $this->get_currencies();
        if (!empty($currencies) AND is_array($currencies)) {
            foreach ($currencies as $key => $currency) {
                if ($currency['is_etalon']) {
                    $this->default_currency = $key;
                    break;
                }
            }
        }

//+++

        $this->is_geoip_manipulation = get_option('woocs_is_geoip_manipulation', 0);
        $this->is_multiple_allowed = get_option('woocs_is_multiple_allowed', 0);
        if ($this->is_geoip_manipulation) {
            $this->is_multiple_allowed = true;
        }
        $this->is_fixed_enabled = get_option('woocs_is_fixed_enabled', 0);
        $this->is_fixed_coupon = get_option('woocs_is_fixed_coupon', 0);
        $this->is_fixed_shipping = get_option('woocs_is_fixed_shipping', 0);
        $this->is_fixed_shipping_free = get_option('woocs_is_fixed_shipping', 0);
        $this->is_fixed_user_role = get_option('woocs_is_fixed_user_role', 0);

        $this->force_pay_bygeoip_rules = get_option('woocs_force_pay_bygeoip_rules', 0);
        $this->rate_auto_update = get_option('woocs_currencies_rate_auto_update', 'no');

//+++
        $this->currency_positions = array('left', 'right', 'left_space', 'right_space');
        $this->init_currency_symbols();

//+++
        $is_first_activation = (int) get_option('woocs_first_activation', 0);
        if (!$is_first_activation) {
            update_option('woocs_first_activation', 1);
            update_option('woocs_drop_down_view', 'ddslick');
            update_option('woocs_currencies_aggregator', 'yahoo');
            update_option('woocs_aggregator_key', '');
            update_option('woocs_welcome_currency', $this->default_currency);
            update_option('woocs_is_multiple_allowed', 0);
            update_option('woocs_is_fixed_enabled', 0);
            update_option('woocs_is_fixed_shipping', 0);
            update_option('woocs_is_fixed_coupon', 0);
            update_option('woocs_is_fixed_user_role', 0);
            update_option('woocs_force_pay_bygeoip_rules', 0);
            update_option('woocs_is_geoip_manipulation', 0);
            update_option('woocs_collect_statistic', 0);
            update_option('woocs_show_top_button', 0);
            update_option('woocs_show_flags', 1);
            update_option('woocs_special_ajax_mode', 0);
            update_option('woocs_show_money_signs', 1);
            update_option('woocs_customer_signs', '');
            update_option('woocs_customer_price_format', '');
            update_option('woocs_currencies_rate_auto_update', 'no');
            update_option('woocs_rate_auto_update_email', 0);
            update_option('woocs_storage', 'transient');
            update_option('woocs_geo_rules', '');

            update_option('woocs_payments_rule_enabled', '0');
            update_option('woocs_payment_control', '0');
            update_option('woocs_payments_rules', '');

            update_option('woocs_hide_cents', '');
            update_option('woocs_hide_on_front', '');
            update_option('woocs_rate_plus', '');
            update_option('woocs_decimals', array());
            update_option('woocs_price_info', 0);
            update_option('woocs_no_cents', '');
            update_option('woocs_restrike_on_checkout_page', 0);
            update_option('woocs_shop_is_cached', 0);
            update_option('woocs_show_approximate_amount', 0);
            update_option('woocs_show_approximate_price', 0);

            //auto swither
            update_option('woocs_is_auto_switcher', 0);
            update_option('woocs_auto_switcher_skin', 'classic_blocks');
            update_option('woocs_auto_switcher_side', 'left');
            update_option('woocs_auto_switcher_top_margin', '100px');
            update_option('woocs_auto_switcher_color', '#222222');
            update_option('woocs_auto_switcher_hover_color', '#3b5998');
            update_option('woocs_auto_switcher_basic_field', '__CODE__ __SIGN__');
            update_option('woocs_auto_switcher_additional_field', '__DESCR__');
            update_option('woocs_auto_switcher_show_page', '');
            update_option('woocs_auto_switcher_hide_page', '');
            update_option('woocs_auto_switcher_mobile_show', 0);
            update_option('woocs_auto_switcher_roll_px', 90);

            //+++
            $this->reset_currency();
            //***
            update_option('image_default_link_type', 'file'); //http://wordpress.stackexchange.com/questions/9727/link-to-file-url-by-default
        }
//+++
//simple checkout itercept
        if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_checkout') {
            $_REQUEST['woocommerce-currency-switcher'] = $this->escape($this->storage->get_val('woocs_current_currency'));
            $this->current_currency = $this->escape($this->storage->get_val('woocs_current_currency'));
            $_REQUEST['woocs_in_order_currency'] = $this->current_currency;
        }

//paypal query itercept
        if (isset($_REQUEST['mc_currency']) AND ! empty($_REQUEST['mc_currency'])) {
            if (array_key_exists($_REQUEST['mc_currency'], $currencies)) {
                $_REQUEST['woocommerce-currency-switcher'] = $this->escape($_REQUEST['mc_currency']);
            }
        }

//WELCOME USER CURRENCY ACTIVATION
        if (intval($this->storage->get_val('woocs_first_unique_visit')) === 0) {
            $this->is_first_unique_visit = true;
            $this->set_currency($this->get_welcome_currency());
            $this->storage->set_val('woocs_first_unique_visit', 1);
        }

//+++
        if (isset($_REQUEST['woocommerce-currency-switcher'])) {
            if (array_key_exists($_REQUEST['woocommerce-currency-switcher'], $currencies)) {
                $this->storage->set_val('woocs_current_currency', $this->escape($_REQUEST['woocommerce-currency-switcher']));
            } else {
                $this->storage->set_val('woocs_current_currency', $this->default_currency);
            }
        }
//+++
//*** check currency in browser address
        if (isset($_GET['currency']) AND ! empty($_GET['currency'])) {

            if (!$this->is_currency_private($_GET['currency'])) {

                $allow_currency_switching = !$this->bones['disable_currency_switching'];

                //1 issue closing
                if (!get_option('woocs_is_multiple_allowed', 0)) {
                    if (isset($_REQUEST['wc-ajax']) AND ( $_REQUEST['wc-ajax'] == 'get_refreshed_fragments' OR $_REQUEST['wc-ajax'] == 'update_order_review')) {
                        if (isset($_SERVER['REQUEST_URI'])) {
                            if (substr_count($_SERVER['REQUEST_URI'], '/checkout/')) {
                                $allow_currency_switching = false;
                                $this->reset_currency();
                            }
                        }
                    }
                }

                //***

                if (array_key_exists(strtoupper($_GET['currency']), $currencies) AND $allow_currency_switching) {
                    $this->storage->set_val('woocs_current_currency', strtoupper($this->escape($_GET['currency'])));
                    $this->statistic->register_switch(strtoupper($this->escape($_GET['currency'])), strtoupper($this->storage->get_val('woocs_user_country')));
                }
            }
        }
//+++
        if ($this->storage->is_isset('woocs_current_currency')) {
            $this->current_currency = $this->storage->get_val('woocs_current_currency');
        } else {
            $this->current_currency = $this->default_currency;
        }
        $this->storage->set_val('woocs_default_currency', $this->default_currency);
//+++
        //if we want to be paid in the basic currency - not multiple mode
        if (isset($_REQUEST['action']) AND ! get_option('woocs_is_multiple_allowed', 0)) {
            //old code for woocomerce < 2.4, left for comatibility with old versions of woocommerce
            if ($_REQUEST['action'] == 'woocommerce_update_order_review') {
                $this->reset_currency();
            }
        }

//+++ FILTERS
        add_filter('woocommerce_paypal_args', array($this, 'apply_conversion'));
        add_filter('woocommerce_paypal_supported_currencies', array($this, 'enable_custom_currency'), 9999);
        add_filter('woocommerce_currency_symbol', array($this, 'woocommerce_currency_symbol'), 9999);
        add_filter('woocommerce_currency', array($this, 'get_woocommerce_currency'), 9999);
        add_filter('wc_get_template', array($this, 'wc_get_template'), 9999, 5); //from woo 2.7 its nessesary for new order email
//fix woo 3.3.0
        if ($this->is_multiple_allowed) {
            add_action('woocommerce_coupon_loaded', array($this, 'woocommerce_coupon_loaded'), 9999);
        }
//
//main recount hook
        if ($this->is_multiple_allowed) {
            //woo >= v.2.7
            add_filter('woocommerce_product_get_price', array($this, 'raw_woocommerce_price'), 9999, 2);
            //wp-content\plugins\woocommerce\includes\abstracts\abstract-wc-data.php
            //protected function get_prop
            add_filter('woocommerce_product_variation_get_price', array($this, 'raw_woocommerce_price'), 9999, 2);
            add_filter('woocommerce_product_variation_get_regular_price', array($this, 'raw_woocommerce_price'), 9999, 2);
            //for correct currency in preview html
            add_filter('woocommerce_admin_order_preview_line_items', array($this, 'woocommerce_admin_order_preview_line_items'), 9999, 2);
            //comment next code line if on single product page for variable prices you see crossed out price which equal to the regular one,
            //I mean you see 2 same prices (amounts) and one of them is crossed out which by logic should not be visible at all
            //add_filter('woocommerce_product_variation_get_sale_price', array($this, 'raw_woocommerce_price'), 9999, 2);
            //new  function  for sale price
            add_filter('woocommerce_product_variation_get_sale_price', array($this, 'raw_sale_price_filter'), 9999, 2);
        } else {
            add_filter('raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 9999);
        }


        //fix for single page with variables products
        if (version_compare($this->actualized_for, 2.7, '>=') AND $this->is_multiple_allowed) {
            //woo >= v.2.7
            //add_filter('woocommerce_available_variation', array($this, 'woocommerce_available_variation'), 9999, 3);
        }


//+++
        if ($this->is_multiple_allowed) {
//wp-content\plugins\woocommerce\includes\abstracts\abstract-wc-product.php #795
            /* Alda: Had to removed the filter as it is redundant with the woocommerce_get_price hook */
//I back it 07-01-2016 because of it is really need.
//Comment next 2 hooks if double recount is for sale price http://c2n.me/3sCQFkX
            //woo >= v.2.7
            add_filter('woocommerce_product_get_regular_price', array($this, 'raw_woocommerce_price'), 9999, 2);

            //woo >= v.2.7
            add_filter('woocommerce_product_get_sale_price', array($this, 'raw_woocommerce_price_sale'), 9999, 2);

            //***

            add_filter('woocommerce_get_variation_regular_price', array($this, 'raw_woocommerce_price'), 9999, 4);
            add_filter('woocommerce_get_variation_sale_price', array($this, 'raw_woocommerce_price'), 9999, 4);
            add_filter('woocommerce_variation_prices', array($this, 'woocommerce_variation_prices'), 9999, 3);
//***
            add_filter('woocommerce_get_variation_prices_hash', array($this, 'woocommerce_get_variation_prices_hash'), 9999, 3);
        }
//***


        add_filter('woocommerce_price_format', array($this, 'woocommerce_price_format'), 9999);
        add_filter('woocommerce_thankyou_order_id', array($this, 'woocommerce_thankyou_order_id'), 9999);
        add_filter('woocommerce_before_resend_order_emails', array($this, 'woocommerce_before_resend_order_emails'), 1);
        add_filter('woocommerce_email_actions', array($this, 'woocommerce_email_actions'), 10);
        add_action('woocommerce_order_status_completed', array($this, 'woocommerce_order_status_completed'), 1);
        add_action('woocommerce_order_status_completed_notification', array($this, 'woocommerce_order_status_completed_notification'), 1);

        add_filter('woocommerce_package_rates', array($this, 'woocommerce_package_rates'), 9999);

//sometimes woocommerce_product_is_on_sale is works on single page for show OnSale icon for all currencies
//add_filter('woocommerce_product_is_on_sale', array($this, 'woocommerce_product_is_on_sale'), 9999, 2);
//for shop cart
        add_filter('woocommerce_cart_totals_order_total_html', array($this, 'woocommerce_cart_totals_order_total_html'), 9999, 1);
        add_filter('wc_price_args', array($this, 'wc_price_args'), 9999);


//for refreshing mini-cart widget
        add_filter('woocommerce_before_mini_cart', array($this, 'woocommerce_before_mini_cart'), 9999);
        add_filter('woocommerce_after_mini_cart', array($this, 'woocommerce_after_mini_cart'), 9999);

//shipping
        add_filter('woocommerce_shipping_free_shipping_is_available', array($this, 'woocommerce_shipping_free_shipping_is_available'), 999, 3);
        add_filter('woocommerce_shipping_legacy_free_shipping_is_available', array($this, 'woocommerce_shipping_free_shipping_is_available'), 999, 3);

        add_action('woocommerce_order_get_currency', array($this, 'woocommerce_get_order_currency'), 1, 2);
//+++
//+++ AJAX ACTIONS

        add_action('woocommerce_before_calculate_totals', array($this, 'woocs_before_calculate_totals_geoip_fix'));

        add_action('wp_ajax_woocs_save_etalon', array($this, 'save_etalon'));
        add_action('wp_ajax_woocs_get_rate', array($this, 'get_rate'));

        add_action('wp_ajax_woocs_convert_currency', array($this, 'woocs_convert_currency'));
        add_action('wp_ajax_nopriv_woocs_convert_currency', array($this, 'woocs_convert_currency'));

        add_action('wp_ajax_woocs_rates_current_currency', array($this, 'woocs_rates_current_currency'));
        add_action('wp_ajax_nopriv_woocs_rates_current_currency', array($this, 'woocs_rates_current_currency'));

        add_action('wp_ajax_woocs_get_products_price_html', array($this, 'woocs_get_products_price_html'));
        add_action('wp_ajax_nopriv_woocs_get_products_price_html', array($this, 'woocs_get_products_price_html'));

        add_action('wp_ajax_woocs_recalculate_order_data', array($this, 'woocs_recalculate_order_data'));
        add_action('wp_ajax_woocs_all_order_ids', array($this, 'woocs_all_order_ids'));
        add_action('wp_ajax_woocs_recalculate_orders_data', array($this, 'woocs_recalculate_orders_data'));

        add_action('wp_ajax_woocs_set_currency_ajax', array($this, 'woocs_set_currency_ajax'));
        add_action('wp_ajax_nopriv_woocs_set_currency_ajax', array($this, 'woocs_set_currency_ajax'));

//+++

        add_action('woocommerce_settings_tabs_array', array($this, 'woocommerce_settings_tabs_array'), 9999);
        add_action('woocommerce_settings_tabs_woocs', array($this, 'print_plugin_options'), 9999);

        //fix for checkout 14.11.17
        add_action('woocommerce_checkout_process', array($this, 'check_currency_on_checkout'), 1);

//+++
        add_action('widgets_init', array($this, 'widgets_init'));
        add_action('wp_head', array($this, 'wp_head'), 999);
        add_action('body_class', array($this, 'body_class'), 9999);
//***
        add_action('save_post', array($this, 'save_post'), 1);
        add_action('admin_head', array($this, 'admin_head'), 1);

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'admin_init'), 1);
//price formatting on front ***********

        add_action('woocommerce_get_price_html', array($this, 'woocommerce_price_html'), 1, 2);


        add_action('woocommerce_variable_sale_price_html', array($this, 'woocommerce_price_html'), 1, 2);
        add_action('woocommerce_sale_price_html', array($this, 'woocommerce_price_html'), 1, 2);
        add_action('woocommerce_grouped_price_html', array($this, 'woocommerce_price_html'), 1, 2);




//*** additional
//wpo_wcpdf_order_number is -> compatibility for https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/

        add_action('wpo_wcpdf_process_template_order', array($this, 'wpo_wcpdf_process_template_order'), 1, 2);
        add_action('woocs_exchange_value', array($this, 'woocs_exchange_value'), 1);
        //***
        add_filter('woocommerce_checkout_update_order_review', array($this, 'woocommerce_checkout_update_order_review'), 9999);


        //fix  for  calculate shipping with  cost arguments
        add_filter("woocommerce_evaluate_shipping_cost_args", array($this, "woocommerce_fix_shipping_calc"), 10, 3);
        //fix  if current and basic  currencies have different decimsls
        add_filter('wc_get_price_decimals', array($this, 'woocs_fix_decimals'), 999);

//*************************************
        add_shortcode('woocs', array($this, 'woocs_shortcode'));
        add_shortcode('woocs_get_sign_rate', array($this, 'get_sign_rate'));
        add_shortcode('woocs_converter', array($this, 'woocs_converter'));
        add_shortcode('woocs_rates', array($this, 'woocs_rates'));
        add_shortcode('woocs_show_current_currency', array($this, 'woocs_show_current_currency'));
        add_shortcode('woocs_show_custom_price', array($this, 'woocs_show_custom_price'));
        add_shortcode('woocs_geo_hello', array($this, 'woocs_geo_hello'));

        if (get_option('woocs_is_multiple_allowed', 0)) {
            add_action('the_post', array($this, 'the_post'), 1);
            add_action('load-post.php', array($this, 'admin_action_post'), 1);
        }

//+++
        add_action('woocs_update_rates_wpcron', array($this, 'rate_auto_update'), 10);
        $this->cron = new PN_WP_CRON_WOOCS('woocs_rates_wpcron');
        $this->wp_cron_period = (int) $this->get_woocs_cron_schedules($this->rate_auto_update);
        $this->make_rates_auto_update();
//***
        if ($this->is_fixed_enabled OR $this->is_geoip_manipulation) {
            $this->fixed = new WOOCS_FIXED_PRICE();
        }
        if ($this->is_fixed_coupon) {
            $this->fixed_coupon = new WOOCS_FIXED_COUPON();
        }
        if ($this->is_fixed_shipping) {
            $this->fixed_shipping = new WOOCS_FIXED_SHIPPING();
            $this->fixed_shipping_free = new WOOCS_FIXED_SHIPPING_FREE();
        }
        if ($this->is_fixed_user_role) {
            $this->fixed_user_role = new WOOCS_FIXED_USER_ROLE();
        }
        if (get_option('woocs_is_auto_switcher', 0)) {
            $auto_switcher = new WOOCS_AUTO_SWITCHER();
            $auto_switcher->init();
        }
        //for  any notises
        add_action('init', array($this, 'init_style_notice')); //add notice to cleare cache
        //adapt_filter
        add_filter('woocs_convert_price', array($this, 'woocs_convert_price'), 10, 2);
        add_filter('woocs_back_convert_price', array($this, 'woocs_back_convert_price'), 10, 2);
        add_filter('woocs_convert_price_wcdp', array($this, 'woocs_convert_price_wcdp'), 10, 3);

        //payments rule
        if (get_option('woocs_payments_rule_enabled', 0)) {
            add_filter('woocommerce_available_payment_gateways', array($this, 'woocs_filter_gateways'), 10, 1);
        }
        // marketing alert
        add_action('init', array($this, 'init_marketig_woocs'));

        //arder func

        add_action('manage_posts_extra_tablenav', array($this, 'manage_posts_extra_tablenav'), 10, 1);

        //REST API
        //wp-json/woocs/v3/currency
        add_action('rest_api_init', function () {
            register_rest_route('woocs/v3', '/currency', array(
                'methods' => 'GET',
                'callback' => function() {
                    global $WOOCS;
                    return $WOOCS->get_currencies();
                }
            ));
        });
    }

//for normal shippng update if to change currency
    public function woocommerce_add_to_cart_hash($hash, $cart = null) {
        //return "";
        return md5(json_encode(WC()->cart->get_cart_for_session()) . $this->current_currency);
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }

//global $wp;            return;

        wp_enqueue_script('jquery');

//+++
        load_plugin_textdomain('woocommerce-currency-switcher', false, dirname(plugin_basename(__FILE__)) . '/languages');


//filters
        add_filter('plugin_action_links_' . WOOCS_PLUGIN_NAME, array($this, 'plugin_action_links'));
        add_filter('woocommerce_currency_symbol', array($this, 'woocommerce_currency_symbol'), 9999);

//***
//set default cyrrency for wp-admin of the site
        if (is_admin() AND ! is_ajax()) {
            $this->current_currency = $this->default_currency;
        } else {
//if we are in the a product backend and loading its variations
            if (is_ajax() AND ( isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_load_variations')) {
                $this->current_currency = $this->default_currency;
            }
        }

        if (is_ajax()) {
            $actions = false;
//code for manual order adding
            if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_add_order_item') {
                $actions = true;
            }

            if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_save_order_items') {
                $actions = true;
            }

            if (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'woocommerce_calc_line_taxes') {
                $actions = true;
            }
//***
            if ($actions AND current_user_can('edit_shop_orders')) {
                $this->current_currency = $this->default_currency;
//check if is order has installed currency
                $currency = get_post_meta($_REQUEST['order_id'], '_order_currency', TRUE);
                if (!empty($currency)) {
                    $this->current_currency = $currency;
                }
            }
        }

        if (is_ajax() AND isset($_REQUEST['action'])
                AND $_REQUEST['action'] == 'woocommerce_mark_order_status'
                AND isset($_REQUEST['status']) AND $_REQUEST['status'] == 'completed'
                AND isset($_REQUEST['order_id'])
        ) {
            $currency = get_post_meta($_REQUEST['order_id'], '_order_currency', true);
            if (!empty($currency)) {
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->current_currency = $currency;
            }
        }



//if we want to be paid in the basic currency - not multiple mode
        if (!get_option('woocs_is_multiple_allowed', 0)) {


//compatibility for WC_Gateway_PayPal_Express_AngellEYE
            if (isset($_GET['wc-api']) AND isset($_GET['pp_action']) AND isset($_GET['use_paypal_credit'])) {
                if ($_GET['pp_action'] == 'expresscheckout') {
                    $this->reset_currency();
                }
            }
        }


        if ($this->force_pay_bygeoip_rules) {
            if ((is_checkout() OR is_checkout_pay_page()) AND ! isset($_GET['key'])) {
                $this->force_pay_bygeoip_rules();
            }
        }

//***
//Show Approx. data info
        if ($this->is_use_geo_rules() AND get_option('woocs_show_approximate_amount', 0) AND ( isset(WC()->cart)/* AND WC()->cart->subtotal > 0 */)) {

            add_filter('woocommerce_cart_total', array($this, 'woocommerce_cart_total'), 9999, 1);

            add_filter('woocommerce_cart_item_price', array($this, 'woocommerce_cart_item_price'), 9999, 3);
            add_filter('woocommerce_cart_item_subtotal', array($this, 'woocommerce_cart_item_subtotal'), 9999, 3);
            add_filter('woocommerce_cart_subtotal', array($this, 'woocommerce_cart_subtotal'), 9999, 3);

            add_filter('woocommerce_cart_totals_taxes_total_html', array($this, 'woocommerce_cart_totals_taxes_total_html'), 9999, 1);
            add_filter('woocommerce_cart_tax_totals', array($this, 'woocommerce_cart_tax_totals'), 9999, 2);
            add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'woocommerce_cart_shipping_method_full_label'), 9999, 2);
        }
        if (apply_filters('woocs_cut_cart_price_format', true)) {
            add_action('woocommerce_cart_item_price', array($this, 'woocs_woocommerce_cart_price_html'), 99999, 2);
            add_filter('woocommerce_cart_item_subtotal', array($this, 'woocs_woocommerce_cart_price_html'), 99999, 2);
            add_filter('woocommerce_cart_subtotal', array($this, 'woocs_woocommerce_cart_price_html'), 99999, 2);
            add_filter('woocommerce_cart_total', array($this, 'woocs_woocommerce_cart_price_html'), 99999, 2);
        }

        //woo version control for enabling right functionality after migration from woo 2.6.x to 3.x.x
        if ($this->actualized_for !== floatval(WOOCOMMERCE_VERSION)) {
            update_option('woocs_woo_version', WOOCOMMERCE_VERSION);
        }

        //***
        //SHOW BUTTON ON THE TOP OF ADMIN PANEL
        add_action('admin_bar_menu', function($wp_admin_bar) {
            if (current_user_can('manage_options')) {
                if (get_option('woocs_show_top_button', 0)) {
                    $args = array(
                        'id' => 'woocs-btn',
                        'title' => __('WOOCS Currency Options', 'woocommerce-currency-switcher'),
                        'href' => admin_url('admin.php?page=wc-settings&tab=woocs'),
                        'meta' => array(
                            'class' => 'wp-admin-bar-woocs-btn',
                            'title' => 'WOOCS - WooCommerce Currency Switcher'
                        )
                    );
                    $wp_admin_bar->add_node($args);
                }
            }
        }, 250);

        //***
        //https://wordpress.org/support/topic/currency-symbol-display-incorrectly/#post-9714451
        add_filter('woocommerce_general_settings', function($data) {
            foreach ($data as $k => $d) {
                if (isset($d['id'])) {
                    if (in_array($d['id'], ['woocommerce_currency', 'woocommerce_price_num_decimals', 'woocommerce_currency_pos'])) {
                        unset($data[$k]);
                    }

                    if ($d['id'] === 'pricing_options') {
                        $data[$k]['desc'] = sprintf(__('The following options affect how prices are displayed on the frontend. WooCommerce Currency Switcher is activated. Set default currency %s please.', 'woocommerce-currency-switcher'), '<a href="' . admin_url('admin.php?page=wc-settings&tab=woocs') . '">' . __('here', 'woocommerce-currency-switcher') . '</a>');
                    }
                }
            }

            return $data;
        });
    }

    public function make_rates_auto_update($reset = false) {
        if ($this->rate_auto_update != 'no' AND ! empty($this->rate_auto_update)) {
            if ($this->wp_cron_period) {
                if ($reset) {
                    $this->cron->reset($this->cron_hook, $this->wp_cron_period);
                }

                $this->woocs_wpcron_init();
            }
        }
    }

    public function woocs_wpcron_init($remove = false) {
        if ($remove) {
            $this->cron->remove($this->cron_hook);
            return;
        }

        if ($this->wp_cron_period) {
            if (!$this->cron->is_attached($this->cron_hook, $this->wp_cron_period)) {
                $this->cron->attach($this->cron_hook, time(), $this->wp_cron_period);
            }

            $this->cron->process();
        }
    }

    public function get_woocs_cron_schedules($key = '') {
        $schedules = array(
            'min15' => 15 * MINUTE_IN_SECONDS,
            'min30' => 30 * MINUTE_IN_SECONDS,
            'min45' => 45 * MINUTE_IN_SECONDS,
            'hourly' => HOUR_IN_SECONDS,
            'twicedaily' => HOUR_IN_SECONDS * 12,
            'daily' => DAY_IN_SECONDS,
            'week' => WEEK_IN_SECONDS,
            'month' => WEEK_IN_SECONDS * 4,
            'min1' => MINUTE_IN_SECONDS,
        );
//print_r($schedules);
        if (!empty($key) AND isset($schedules[$key])) {
            return (int) $schedules[$key];
        } else {
            return NULL;
        }

        return $schedules;
    }

    public function get_currency_price_num_decimals($currency, $val = 2) {
        $currencies = $this->get_currencies();
        if (isset($currencies[$currency]['decimals'])) {
            $val = $currencies[$currency]['decimals'];
        }
        return intval($val);
    }

    public function woocommerce_price_num_decimals($default) {
        $this->price_num_decimals = $this->get_currency_price_num_decimals($this->current_currency);

        return $this->price_num_decimals;
    }

    public function body_class($classes) {
        $classes[] = 'currency-' . strtolower($this->current_currency);
        return $classes;
    }

    public function init_currency_symbols() {

        $this->currency_symbols = array_values($this->get_symbols_set());
        $this->currency_symbols['------'] = '--------'; //just divider

        $this->currency_symbols = apply_filters('woocs_currency_symbols', array_merge($this->currency_symbols, $this->get_customer_signs()));
    }

    private function init_no_cents() {
        $no_cents = get_option('woocs_no_cents', '');
        $currencies = $this->get_currencies();
//***
        if (!empty($currencies) AND is_array($currencies)) {
            $currencies = array_keys($currencies);
            $currencies = array_map('strtolower', $currencies);
            if (!empty($no_cents)) {
                $no_cents = explode(',', $no_cents);
                if (!empty($no_cents) AND is_array($no_cents)) {
                    foreach ($no_cents as $value) {
                        if (in_array(strtolower($value), $currencies)) {
                            $this->no_cents[] = $value;
                        }
                    }
                }
            }
        }

        return $this->no_cents;
    }

//for auto rate update sheduler
    public function rate_auto_update() {
        $currencies = $this->get_currencies();
//***
        $_REQUEST['no_ajax'] = TRUE;
        $request = array();
        foreach ($currencies as $key => $currency) {
            if ($currency['is_etalon'] == 1) {
                continue;
            }
            $_REQUEST['currency_name'] = $currency['name'];
            $request[$key] = (float) $this->get_rate();
        }
//*** checking and assigning data
        foreach ($currencies as $key => $currency) {
            if ($currency['is_etalon'] == 1) {
                continue;
            }
            if (isset($request[$key]) AND ! empty($request[$key]) AND $request[$key] > 0) {
                $currencies[$key]['rate'] = $request[$key];
            }
        }

        //***
        static $email_is_sent = false;
        if (isset($_REQUEST['woocs_cron_running']) AND ! $email_is_sent) {
            if (get_option('woocs_rate_auto_update_email', 0)) {
                $message = sprintf(esc_html__('<strong>Base currency of the site is: %s</strong>', 'woocommerce-currency-switcher'), $this->default_currency);
                $message .= '<br /><br /><ul>';
                foreach ($currencies as $code => $curr) {
                    if ($code == $this->default_currency) {
                        continue;
                    }

                    $message .= '<li><b>' . $code . '</b>: <i>' . $curr['rate'] . '</i><br /><br /></li>';
                }
                $message .= '</ul>';
                //***
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                mail(get_bloginfo('admin_email'), 'Currency rates updated on ' . get_bloginfo('name'), $message, $headers);
            }

            $email_is_sent = true;
        }
        //***

        update_option('woocs', $currencies);
    }

    public function init_geo_currency() {
        $done = false;
        if (!class_exists('WC_Geolocation')) {
            return false;
        }
        $pd = WC_Geolocation::geolocate_ip();
        $this->storage->set_val('woocs_user_country', $pd['country']);
        //***
        if ($this->is_use_geo_rules()) {
            $rules = $this->get_geo_rules();
            if (apply_filters("woocs_geobone_ip", true)) {
                if (intval($this->storage->get_val('woocs_first_unique_geoip')) === 0) {
                    $this->is_first_unique_visit = true;
                    $this->storage->set_val('woocs_first_unique_geoip', 1);
                }
            }
            $is_allowed = $this->is_first_unique_visit AND function_exists('wc_clean') AND function_exists('wp_validate_redirect');
            if ($is_allowed) {

                if (isset($pd['country']) AND ! empty($pd['country'])) {

                    if (!empty($rules)) {
                        foreach ($rules as $curr => $countries) {
                            if (!empty($countries) AND is_array($countries)) {
                                foreach ($countries as $country) {
                                    if ($country == $pd['country']) {
                                        $this->set_currency($curr);
                                        $done = true;
                                        break(2);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $done;
    }

    public function get_currency_by_country($country_code) {
        $rules = $this->get_geo_rules();
        if (!empty($rules)) {
            foreach ($rules as $currency => $countries) {
                if (!empty($countries) AND is_array($countries)) {
                    foreach ($countries as $country) {
                        if ($country == $country_code) {
                            return $currency;
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * Show action links on the plugin screen
     */
    public function plugin_action_links($links) {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=woocs') . '">' . esc_html__('Settings', 'woocommerce-currency-switcher') . '</a>',
            '<a target="_blank" href="' . esc_url('https://currency-switcher.com/codex/') . '">' . esc_html__('Documentation', 'woocommerce-currency-switcher') . '</a>'
                ), $links);

        return $links;
    }

    public function widgets_init() {
        require_once WOOCS_PATH . 'classes/widgets/widget-woocs-selector.php';
        require_once WOOCS_PATH . 'classes/widgets/widget-currency-rates.php';
        require_once WOOCS_PATH . 'classes/widgets/widget-currency-converter.php';
        register_widget('WOOCS_SELECTOR');
        register_widget('WOOCS_RATES');
        register_widget('WOOCS_CONVERTER');

        //overides woocs slider js
        wp_register_script('wc-price-slider_33', WOOCS_LINK . 'js/price-slider_33.js', array('jquery', 'jquery-ui-slider', 'wc-jquery-ui-touchpunch'), WOOCS_VERSION);
    }

    public function admin_enqueue_scripts() {
        if (isset($_GET['tab']) AND $_GET['tab'] == 'woocs') {
            wp_enqueue_style('woocommerce-currency-switcher-options', WOOCS_LINK . 'css/options.css', array(), WOOCS_VERSION);
        }
    }

    public function admin_head() {
        if (isset($_GET['woocs_reset'])) {
            delete_option('woocs');
        }

        if (isset($_GET['page']) AND isset($_GET['tab'])) {
            if ($_GET['page'] == 'wc-settings'/* AND $_GET['tab'] == 'woocs' */) {
                wp_enqueue_script('woocs-admin', WOOCS_LINK . 'js/admin.js', array('jquery'), WOOCS_VERSION);
            }
        }
        //orders
        global $typenow;
        if (function_exists("wc_get_order_types") AND in_array($typenow, wc_get_order_types('order-meta-boxes'), true)) {
            wp_enqueue_script('woocs-orders-script', WOOCS_LINK . 'js/orders.js', array('jquery'), WOOCS_VERSION);
        }
        wp_enqueue_style('woocs-admin-style', WOOCS_LINK . 'css/admin.css', array(), WOOCS_VERSION);
    }

    /**
     * Process the new bulk actions for changing order currency to default.
     */
    public function shop_order_bulk_actions() {
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();

        //print_r($action);exit;
    }

    public function admin_init() {
        if (get_option('woocs_is_multiple_allowed', 0)) {
            add_meta_box('woocs_order_metabox', esc_html__('WOOCS Order Info', 'woocommerce-currency-switcher'), array($this, 'woocs_order_metabox'), 'shop_order', 'side', 'default');
        }
    }

//for orders hook
    public function save_post($order_id) {
        if (current_user_can('edit_shop_orders')) {
            if (!empty($_POST)) {
                global $post;
                if (is_object($post)) {
                    if (($post->post_type == 'shop_order' || $post->post_type == 'shop_subscription') AND isset($_POST['woocs_order_currency'])) {
                        $currencies = $this->get_currencies();
                        $currencies_keys = array_keys($currencies);
                        $currency = $this->escape($_POST['woocs_order_currency']);
                        if (in_array($currency, $currencies_keys)) {

//changing order currency
                            update_post_meta($order_id, '_order_currency', $currency);

                            update_post_meta($order_id, '_woocs_order_rate', $currencies[$currency]['rate']);
                            //wc_add_order_item_meta($order_id, '_woocs_order_rate', $currencies[$currency]['rate'], true);

                            update_post_meta($order_id, '_woocs_order_base_currency', $this->default_currency);
                            //wc_add_order_item_meta($order_id, '_woocs_order_base_currency', $this->default_currency, true);

                            update_post_meta($order_id, '_woocs_order_currency_changed_mannualy', time());
                            //wc_add_order_item_meta($order_id, '_woocs_order_currency_changed_mannualy', time(), true);
                        }
                    }
                }
            }
        }
    }

//for orders hook
    public function the_post($post) {
        if (is_object($post) AND $post->post_type == 'shop_order') {
            $currency = get_post_meta($post->ID, '_order_currency', true);
            if (!empty($currency)) {
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->current_currency = $currency;
            }
        }

        return $post;
    }

//for orders hook
    public function admin_action_post() {
        if (isset($_GET['post'])) {
            $post_id = $_GET['post'];
            $post = get_post($post_id);
            if (is_object($post) AND $post->post_type == 'shop_order') {
                $currency = get_post_meta($post->ID, '_order_currency', true);
                if (!empty($currency)) {
                    $_REQUEST['woocs_in_order_currency'] = $currency;
                    $this->current_currency = $currency;
                }
            }
        }
    }

    public function woocs_order_metabox($post) {
        $data = array();
        $data['post'] = $post;
        $data['order'] = new WC_Order($post->ID);
        wp_enqueue_script('woocs-meta-script', WOOCS_LINK . 'js/meta-box.js', array('jquery'), WOOCS_VERSION);
        echo $this->render_html(WOOCS_PATH . 'views/woocs_order_metabox.php', $data);
    }

    public function wp_head() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        //*** if the site is visited for the first time lets execute geo ip conditions
        $this->init_geo_currency();
        //***
        wp_enqueue_script('jquery');
        wp_enqueue_script('wc-price-slider_33');
        wp_add_inline_script('wc-price-slider_33', $this->init_js_properties(), 'before');
        //$currencies = $this->get_currencies();

        if ($this->get_drop_down_view() == 'ddslick') {
            wp_enqueue_script('jquery.ddslick.min', WOOCS_LINK . 'js/jquery.ddslick.min.js', array('jquery'), WOOCS_VERSION);
        }

        if ($this->get_drop_down_view() == 'chosen' OR $this->get_drop_down_view() == 'chosen_dark') {
            wp_enqueue_script('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'), WOOCS_VERSION);
            wp_enqueue_style('chosen-drop-down', WOOCS_LINK . 'js/chosen/chosen.min.css', array(), WOOCS_VERSION);
            //dark chosen
            if ($this->get_drop_down_view() == 'chosen_dark') {
                wp_enqueue_style('chosen-drop-down-dark', WOOCS_LINK . 'js/chosen/chosen-dark.css', array(), WOOCS_VERSION);
            }
        }

        if ($this->get_drop_down_view() == 'wselect') {
            wp_enqueue_script('woocs_wselect', WOOCS_LINK . 'js/wselect/wSelect.min.js', array('jquery'), WOOCS_VERSION);
            wp_enqueue_style('woocs_wselect', WOOCS_LINK . 'js/wselect/wSelect.css', array(), WOOCS_VERSION);
        }

        //+++

        wp_enqueue_style('woocommerce-currency-switcher', WOOCS_LINK . 'css/front.css', array(), WOOCS_VERSION);
        wp_enqueue_script('woocommerce-currency-switcher', WOOCS_LINK . 'js/front.js', array('jquery'), WOOCS_VERSION);
        if (isset($_GET['currency'])) {
            wp_add_inline_script('woocommerce-currency-switcher', $this->init_js_footer());
        }
        //+++
        //if customer paying pending order from the front
        //checkout/order-pay/1044/?pay_for_order=true&key=order_55b764a4b7990
        if (isset($_GET['pay_for_order']) AND is_checkout_pay_page()) {
            if ($_GET['pay_for_order'] == 'true' AND isset($_GET['key'])) {
                $order_id = wc_get_order_id_by_order_key($_GET['key']);
                $currency = get_post_meta($order_id, '_order_currency', TRUE);
                $this->set_currency($currency);
            }
        }

        //+++
        //if we want to be paid in the basic currency - not multiple mode and in is_geoip_manipulation
        if (!get_option('woocs_is_multiple_allowed', 0)) {
            if (is_checkout() OR is_checkout_pay_page()) {
                $this->reset_currency();
            }
        }


        //logic hack for some cases when shipping for example is wrong in
        //non multiple mode but customer doesn work allow pay in user selected currency
        if ($this->is_multiple_allowed) {
            if ((is_checkout() OR is_checkout_pay_page()) AND $this->bones['reset_in_multiple']) {
                $this->reset_currency();
            }
        }



        if ($this->force_pay_bygeoip_rules) {
            if ((is_checkout() OR is_checkout_pay_page()) AND ! isset($_GET['key'])) {
                $this->force_pay_bygeoip_rules();
            }
        }
    }

    public function init_js_properties() {
        $currencies = $this->get_currencies();
        ob_start();
        ?>
        var woocs_is_mobile = <?php echo (int) wp_is_mobile() ?>;
        var woocs_special_ajax_mode = <?php echo (int) get_option('woocs_special_ajax_mode', 0) ?>;
        var woocs_drop_down_view = "<?php echo $this->get_drop_down_view(); ?>";
        var woocs_current_currency = <?php echo json_encode((isset($currencies[$this->current_currency]) ? $currencies[$this->current_currency] : $currencies[$this->default_currency])) ?>;
        var woocs_default_currency = <?php echo json_encode($currencies[$this->default_currency]) ?>;
        var woocs_array_of_get = '{}';
        <?php if (!empty($_GET)): ?>
            <?php
//sanitization of $_GET array
            $sanitized_get_array = array();
            foreach ($_GET as $key => $value) {
                $sanitized_get_array[$this->escape($key)] = $this->escape($value);
            }
            ?>
            woocs_array_of_get = '<?php echo str_replace("\\", "\\\\", str_replace("'", "", json_encode($sanitized_get_array))); ?>';
        <?php endif; ?>

        woocs_array_no_cents = '<?php echo json_encode($this->no_cents); ?>';

        var woocs_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var woocs_lang_loading = "<?php esc_html_e('loading', 'woocommerce-currency-switcher') ?>";
        var woocs_shop_is_cached =<?php echo (int) $this->shop_is_cached ?>;
        <?php
        return ob_get_clean();
    }

    public function init_js_footer() {
        ob_start();
        ?>
        jQuery( document ).ready(function() {
        try {
        jQuery(function () {
        try {
        //https://wordpress.org/support/topic/wrong-cookie-leads-to-ajax-request-on-every-page/
        jQuery.cookie('woocommerce_cart_hash', '', {path: '/'});
        } catch (e) {
        console.log(e);
        }
        });
        } catch (e) {
        console.log(e);
        }
        });
        <?php
        return ob_get_clean();
    }

    public function woocommerce_checkout_update_order_review() {
        if (!get_option('woocs_is_multiple_allowed', 0)) {
            $this->reset_currency();
        }
        $this->force_pay_bygeoip_rules();
    }

    public function woocommerce_settings_tabs_array($tabs) {
        $tabs['woocs'] = esc_html__('Currency', 'woocommerce-currency-switcher');
        return $tabs;
    }

    public function print_plugin_options() {

        if (isset($_POST['woocs_name']) AND ! empty($_POST['woocs_name'])) {
            $result = array();
            update_option('woocs_drop_down_view', $this->escape($_POST['woocs_drop_down_view']));
            update_option('woocs_currencies_aggregator', $this->escape($_POST['woocs_currencies_aggregator']));
            update_option('woocs_aggregator_key', $this->escape($_POST['woocs_aggregator_key']));

            update_option('woocs_welcome_currency', $this->escape($_POST['woocs_welcome_currency']));
//***
            update_option('woocs_is_multiple_allowed', (int) $_POST['woocs_is_multiple_allowed']);
            update_option('woocs_is_geoip_manipulation', (int) $_POST['woocs_is_geoip_manipulation']);
            update_option('woocs_collect_statistic', (int) $_POST['woocs_collect_statistic']);
            update_option('woocs_show_top_button', (int) $_POST['woocs_show_top_button']);
            update_option('woocs_is_fixed_user_role', (int) $_POST['woocs_is_fixed_user_role']);
            if ((int) $_POST['woocs_is_multiple_allowed']) {
                update_option('woocs_is_fixed_enabled', (int) $_POST['woocs_is_fixed_enabled']);
                update_option('woocs_is_fixed_coupon', (int) $_POST['woocs_is_fixed_coupon']);
                update_option('woocs_is_fixed_shipping', (int) $_POST['woocs_is_fixed_shipping']);
                if ((int) $_POST['woocs_is_fixed_enabled']) {
                    update_option('woocs_force_pay_bygeoip_rules', (int) $_POST['woocs_force_pay_bygeoip_rules']);
                } else {
                    update_option('woocs_force_pay_bygeoip_rules', 0);
                }
            } else {
                update_option('woocs_is_fixed_enabled', 0);
                update_option('woocs_is_fixed_coupon', 0);
                update_option('woocs_is_fixed_shipping', 0);
                update_option('woocs_force_pay_bygeoip_rules', 0);
            }
//***
            update_option('woocs_customer_signs', $this->escape($_POST['woocs_customer_signs']));
            update_option('woocs_customer_price_format', $this->escape($_POST['woocs_customer_price_format']));
            update_option('woocs_currencies_rate_auto_update', $this->escape($_POST['woocs_currencies_rate_auto_update']));
            update_option('woocs_rate_auto_update_email', (int) $_POST['woocs_rate_auto_update_email']);
            update_option('woocs_payments_rule_enabled', (int) $_POST['woocs_payments_rule_enabled']);
            update_option('woocs_show_flags', (int) $_POST['woocs_show_flags']);
            update_option('woocs_special_ajax_mode', (int) $_POST['woocs_special_ajax_mode']);
            update_option('woocs_show_money_signs', (int) $_POST['woocs_show_money_signs']);
            //update_option('woocs_use_curl', (int) $_POST['woocs_use_curl']);
            update_option('woocs_storage', $this->escape($_POST['woocs_storage']));
            //auto swither
            if (isset($_POST['woocs_is_auto_switcher'])) {
                update_option('woocs_is_auto_switcher', (int) $_POST['woocs_is_auto_switcher']);
                if ((int) $_POST['woocs_is_auto_switcher']) {
                    update_option('woocs_auto_switcher_skin', $this->escape($_POST['woocs_auto_switcher_skin']));
                    update_option('woocs_auto_switcher_side', $this->escape($_POST['woocs_auto_switcher_side']));
                    update_option('woocs_auto_switcher_top_margin', $this->escape($_POST['woocs_auto_switcher_top_margin']));
                    update_option('woocs_auto_switcher_color', $this->escape($_POST['woocs_auto_switcher_color']));
                    update_option('woocs_auto_switcher_hover_color', $this->escape($_POST['woocs_auto_switcher_hover_color']));
                    update_option('woocs_auto_switcher_basic_field', $this->escape($_POST['woocs_auto_switcher_basic_field']));
                    update_option('woocs_auto_switcher_additional_field', $this->escape($_POST['woocs_auto_switcher_additional_field']));
                    update_option('woocs_auto_switcher_show_page', $this->escape($_POST['woocs_auto_switcher_show_page']));
                    update_option('woocs_auto_switcher_hide_page', $this->escape($_POST['woocs_auto_switcher_hide_page']));
                    update_option('woocs_auto_switcher_mobile_show', $this->escape($_POST['woocs_auto_switcher_mobile_show']));
                    update_option('woocs_auto_switcher_roll_px', $this->escape($_POST['woocs_auto_switcher_roll_px']));
                }
            }
            //+++
            if (isset($_POST['woocs_geo_rules'])) {
                $woocs_geo_rules = array();
                if (!empty($_POST['woocs_geo_rules'])) {
                    foreach ($_POST['woocs_geo_rules'] as $curr_key => $countries) {
                        $woocs_geo_rules[$this->escape($curr_key)] = array();
                        if (!empty($countries)) {
                            foreach ($countries as $curr) {
                                $woocs_geo_rules[$this->escape($curr_key)][] = $this->escape($curr);
                            }
                        }
                    }
                }
                update_option('woocs_geo_rules', $woocs_geo_rules);
            } else {
                update_option('woocs_geo_rules', '');
            }

            //+++
            if (isset($_POST['woocs_payment_control'])) {
                update_option('woocs_payment_control', (int) $this->escape($_POST['woocs_payment_control']));
            } else {
                update_option('woocs_payment_control', 0);
            }

            if (isset($_POST['woocs_payments_rules'])) {
                $woocs_payments_rules = array();
                if (!empty($_POST['woocs_payments_rules'])) {
                    foreach ($_POST['woocs_payments_rules'] as $payment_key => $currencies) {
                        $woocs_payments_rules[$this->escape($payment_key)] = array();
                        if (!empty($currencies)) {
                            foreach ($currencies as $curr) {
                                $woocs_payments_rules[$this->escape($payment_key)][] = $this->escape($curr);
                            }
                        }
                    }
                }
                update_option('woocs_payments_rules', $woocs_payments_rules);
            } else {
                update_option('woocs_payments_rules', '');
            }


            update_option('woocs_hide_cents', (int) $_POST['woocs_hide_cents']);
            update_option('woocs_hide_on_front', (int) $_POST['woocs_hide_on_front']);
            update_option('woocs_rate_plus', (float) $_POST['woocs_rate_plus']);
            update_option('woocs_price_info', (int) $_POST['woocs_price_info']);
            update_option('woocs_no_cents', $this->escape($_POST['woocs_no_cents']));
            update_option('woocs_restrike_on_checkout_page', (int) $_POST['woocs_restrike_on_checkout_page']);
            update_option('woocs_show_approximate_amount', (int) $_POST['woocs_show_approximate_amount']);
            update_option('woocs_show_approximate_price', (int) $_POST['woocs_show_approximate_price']);
            update_option('woocs_shop_is_cached', (int) $_POST['woocs_shop_is_cached']);
            update_option('woocs_woo_version', WOOCOMMERCE_VERSION);
//***
            $cc = '';
            foreach ($_POST['woocs_name'] as $key => $name) {
                if (!empty($name)) {
                    $symbol = $this->escape($_POST['woocs_symbol'][$key]); //md5 encoded

                    foreach ($this->currency_symbols as $s) {
                        if (md5($s) == $symbol) {
                            $symbol = $s;
                            break;
                        }
                    }

                    $result[strtoupper($name)] = array(
                        'name' => $name,
                        'rate' => floatval($_POST['woocs_rate'][$key]),
                        'symbol' => $symbol,
                        'position' => (in_array($this->escape($_POST['woocs_position'][$key]), $this->currency_positions) ? $this->escape($_POST['woocs_position'][$key]) : $this->currency_positions[0]),
                        'is_etalon' => (int) $_POST['woocs_is_etalon'][$key],
                        'hide_cents' => (int) @$_POST['woocs_hide_cents'][$key],
                        'hide_on_front' => (int) @$_POST['woocs_hide_on_front'][$key],
                        'rate_plus' => (float) @$_POST['woocs_rate_plus'][$key],
                        'decimals' => (int) @$_POST['woocs_decimals'][$key],
                        'description' => $this->escape($_POST['woocs_description'][$key]),
                        'flag' => $this->escape($_POST['woocs_flag'][$key]),
                    );

                    /*
                      if ($_POST['woocs_rate'][$key] == 1) {
                      $cc = $name;
                      }
                     */

                    //https://wordpress.org/support/topic/option-woocommerce_currency-is-not-updated-after-changes/
                    if (intval($_POST['woocs_is_etalon'][$key])) {
                        $cc = $name;
                    }
                }
            }

            update_option('woocs', $result);
            if (!empty($cc)) {
//set default currency for all woocommerce system
                update_option('woocommerce_currency', $cc);
            }
            $this->init_currency_symbols();
//***
            $this->rate_auto_update = $this->escape($_POST['woocs_currencies_rate_auto_update']);
            $this->wp_cron_period = $this->get_woocs_cron_schedules($this->rate_auto_update);
            $this->woocs_wpcron_init(true);
            $this->make_rates_auto_update(true);
//$this->reset_currency();
            //*****

            wp_redirect(admin_url('admin.php?page=wc-settings&tab=woocs'));
        }
//+++

        wp_enqueue_script('media-upload');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');

        wp_enqueue_script('woocommerce-currency-switcher-options', WOOCS_LINK . 'js/options.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), WOOCS_VERSION);

        $args = array();
        $args['currencies'] = $this->get_currencies(true);
        if ($this->is_use_geo_rules()) {
            $args['geo_rules'] = $this->get_geo_rules();
        }

        //***

        echo $this->render_html(WOOCS_PATH . 'views/plugin_options.php', $args);
    }

    public function get_drop_down_view() {
        return apply_filters('woocs_drop_down_view', get_option('woocs_drop_down_view', 'ddslick'));
    }

    public function get_currencies($suppress_filters = false) {

        $currencies = get_option('woocs', array());

        if (empty($currencies) OR ! is_array($currencies) OR count($currencies) < 2) {
            $currencies = $this->prepare_default_currencies();
        }

        if (!$suppress_filters) {
            $currencies = apply_filters('woocs_currency_data_manipulation', $currencies);
        }
        
         if (count($currencies) > 2) {
            $currencies = array_slice($currencies, 0, 2);
        }

        return $currencies;
    }

    public function get_geo_rules() {
        return get_option('woocs_geo_rules', array());
    }

    public function is_use_geo_rules() {
        //$is = get_option('woocs_use_geo_rules', 0);
        $is = true; //from v.2.1.8 always enabled
        $isset = class_exists('WC_Geolocation');

        return ($is && $isset);
    }

//need for paypal currencies supporting
    public function enable_custom_currency($currency_array) {
//https://developer.paypal.com/docs/classic/api/currency_codes/
//includes\gateways\paypal\class-wc-gateway-paypal.php => woo func
//function is_valid_for_use() =>Check if this gateway is enabled and available in the user's country
        $currency_array[] = 'usd';
        $currency_array[] = 'aud';
        $currency_array[] = 'brl';
        $currency_array[] = 'cad';
        $currency_array[] = 'czk';
        $currency_array[] = 'dkk';
        $currency_array[] = 'eur';
        $currency_array[] = 'hkd';
        $currency_array[] = 'huf';
        $currency_array[] = 'ils';
        $currency_array[] = 'jpy';
        $currency_array[] = 'myr';
        $currency_array[] = 'mxn';
        $currency_array[] = 'nok';
        $currency_array[] = 'nzd';
        $currency_array[] = 'php';
        $currency_array[] = 'pln';
        $currency_array[] = 'gbp';
        $currency_array[] = 'rub';
        $currency_array[] = 'sgd';
        $currency_array[] = 'sek';
        $currency_array[] = 'chf';
        $currency_array[] = 'twd';
        $currency_array[] = 'thb';
        $currency_array[] = 'try';
        $currency_array = array_map('strtoupper', $currency_array);
        return $currency_array;
    }

    public function woocommerce_currency_symbol($currency_symbol) {
        global $wp_query;
        if (!isset($wp_query)) {
            if (is_order_received_page() || is_account_page()) {
                if (apply_filters('woocs_currency_symbol_on_order', false)) {
                    return $currency_symbol;
                }
            }
        }
        $currencies = $this->get_currencies();

        if (!isset($currencies[$this->current_currency])) {
            $this->reset_currency();
        }

        return $currencies[$this->current_currency]['symbol'];
    }

    public function get_woocommerce_currency() {
        return $this->current_currency;
    }

    //work in for multiple mode only from woocommerce 2.4
    //wp-content\plugins\woocommerce\includes\class-wc-product-variable.php #303
    public function woocommerce_variation_prices($prices_array) {
        $current_currency = $this->current_currency;

        if (in_array($current_currency, $this->no_cents)/* OR $currencies[$this->current_currency]['hide_cents'] == 1 */) {
            $precision = 0;
        } else {
            if ($current_currency != $this->default_currency) {
                $precision = $this->get_currency_price_num_decimals($current_currency, $this->price_num_decimals);
            } else {
                $precision = $this->get_currency_price_num_decimals($this->default_currency, $this->price_num_decimals);
            }
        }

        //***

        if (!empty($prices_array) AND is_array($prices_array)) {
            //remove sale prices if they equal to regular prices
            foreach ($prices_array['regular_price'] as $key => $value) {
                if ($value === $prices_array['sale_price'][$key]) {
                    
                }
            }

            //***
            foreach ($prices_array as $key => $values) {
                if (!empty($values)) {
                    foreach ($values as $product_id => $price) {

                        $type = 'regular';
                        if ($key === 'sale_price') {//OR $key === 'price') {
                            $type = 'sale';
                        }

                        $is_price_custom = false;

                        if ($this->is_fixed_enabled AND $this->fixed->is_exists($product_id, $current_currency, $type)) {
                            $tmp = number_format(floatval($this->fixed->get_value($product_id, $current_currency, $type)), $precision, $this->decimal_sep, '');

                            if ((int) $tmp !== -1) {
                                if (wc_tax_enabled()) {
                                    $tmp = $this->woocs_calc_tax_price(wc_get_product($product_id), $tmp);
                                }
                                $prices_array[$key][$product_id] = $tmp;
                                $is_price_custom = true;
                                if ($type == 'sale') {
                                    $prices_array['price'][$product_id] = $tmp;
                                }
                            }
                        }

                        if ($this->is_geoip_manipulation AND ! $is_price_custom) {
                            $product = (object) array('id' => $product_id);
                            $price = $this->_get_product_geo_price($product, $price);
                        }
                        if ($this->is_fixed_user_role) {
                            $regular_price_tmp = floatval($this->fixed_user_role->get_value($product_id, '', 'regular'));
                            $sale_price_tmp = floatval($this->fixed_user_role->get_value($product_id, '', 'sale'));
                            if ((int) $regular_price_tmp !== -1 OR (int) $sale_price_tmp !== -1) {
                                $price = $regular_price_tmp;
                                if ((int) $sale_price_tmp !== -1 AND $sale_price_tmp < $regular_price_tmp) {
                                    $price = $sale_price_tmp;
                                }
                                $is_price_custom = false;
                            }
                        }

                        if (!$is_price_custom) { {
                                if (wc_tax_enabled()) {
                                    $price = $this->woocs_calc_tax_price(wc_get_product($product_id), $price);
                                }
                                $prices_array[$key][$product_id] = apply_filters('woocs_woocommerce_variation_prices', number_format(floatval($this->woocs_exchange_value(floatval($price))), $precision, $this->decimal_sep, ''));
                            }
                        }
                    }
                }
            }
        }

        //*** lets sort arrays by values to avoid wrong price displaying on the front
        if (!empty($prices_array) AND is_array($prices_array)) {
            foreach ($prices_array as $key => $arrvals) {
                asort($arrvals);
                $prices_array[$key] = $arrvals;
            }
        }
        //***
        //another way displaing of price range is not correct
        if (empty($prices_array['sale_price'])) {
            if (isset($prices_array['regular_price'])) {
                $prices_array['price'] = $prices_array['regular_price'];
            }
        }
        //***
        return $prices_array;
    }

    public function woocommerce_variation_prices_regular($price, $variant, $product) {
        return $price;
    }

    public function woocommerce_variation_prices_sale($price, $variant, $product) {
        return $price;
    }

    public function woocommerce_variation_prices_fix_3_3($price, $product_id, $type) { //fix 3.3.3
        $is_empty = $this->fixed->is_empty($product_id, $this->current_currency, $type);
        $is_exists = $this->fixed->is_exists($product_id, $this->current_currency, $type);

        if ($is_exists AND ! $is_empty) {
            return floatval($this->fixed->get_value($product_id, $this->current_currency, $type));
        }

        return $price;
    }

    public function woocommerce_get_variation_prices_hash($price_hash, $product, $display) {
        //***
        return "";
    }

    public function raw_woocommerce_price($price, $product = NULL, $min_max = NULL, $display = NULL) {

        if (isset($_REQUEST['woocs_block_price_hook'])) {
            return $price;
        }

        //***

        if (isset($_REQUEST['woocs_raw_woocommerce_price_currency'])) {
            $this->current_currency = $_REQUEST['woocs_raw_woocommerce_price_currency'];
        }
        $currencies = $this->get_currencies();

        if (in_array($this->current_currency, $this->no_cents)/* OR $currencies[$this->current_currency]['hide_cents'] == 1 */) {
            $precision = 0;
        } else {
            if ($this->current_currency != $this->default_currency) {
                $precision = $this->get_currency_price_num_decimals($this->current_currency, $this->price_num_decimals);
            } else {
                $precision = $this->get_currency_price_num_decimals($this->default_currency, $this->price_num_decimals);
            }
        }

//***
        $is_price_custom = false;
        if ($this->is_fixed_enabled) {
            if ($this->is_multiple_allowed AND $product !== NULL AND is_object($product)) {

                //if (isset($product->variation_id))
                if ($product->is_type('variation')) {

                    $tmp_val = $this->_get_product_fixed_price($product, 'variation', $price, $precision);
                } elseif($product->is_type('variable')){
                    $tmp_val=-1;
                }else  {
                    $tmp_val = $this->_get_product_fixed_price($product, 'single', $price, $precision);
                }
            
                if ((int) $tmp_val !== -1) {
                    $price = apply_filters('woocs_fixed_raw_woocommerce_price', $tmp_val, $product, $price);
                    $is_price_custom = true;
                }
            }
        }
        //***
        if ($this->is_geoip_manipulation AND ! $is_price_custom) {
            if ($product !== NULL) {
                try {
                    $product_emulator = (object) array('id' => $product->get_id());
                } catch (Exception $e) {
                    
                }

                $price = $this->_get_product_geo_price($product_emulator, $price);
            }
        }
        if ($this->is_fixed_user_role AND $product !== NULL) {
            if ($product->is_type('variation')) {

                $tmp_val = $this->_get_product_fixed_user_role_price($product, 'variation', $price, $precision);
            } elseif($product->is_type('variable')){
                    $tmp_val=-1;
            } else {
                $tmp_val = $this->_get_product_fixed_user_role_price($product, 'single', $price, $precision);
            }

            if ((int) $tmp_val !== -1) {
                $price = $tmp_val;
                $is_price_custom = false;
            }
        }

        //***

        if (!$is_price_custom) {
            if ($this->current_currency != $this->default_currency) {
                //Edited this line to set default convertion of currency
                if (isset($currencies[$this->current_currency]) AND $currencies[$this->current_currency] != NULL) {
                    $price = number_format(floatval((float) $price * (float) $currencies[$this->current_currency]['rate']), $precision, $this->decimal_sep, '');
                } else {
                    $price = number_format(floatval((float) $price * (float) $currencies[$this->default_currency]['rate']), $precision, $this->decimal_sep, '');
                }
            }
        }
        return apply_filters('woocs_raw_woocommerce_price', $price);

//some hints for price rounding
//http://stackoverflow.com/questions/11692770/rounding-to-nearest-50-cents
    }

    //fix for only woo>=2.7 when multiple mode is activated and price is not sale - price still crossed out
    public function raw_woocommerce_price_sale($price, $product = NULL) {
        
        if (!$this->is_multiple_allowed) {
            return $this->raw_woocommerce_price($price, $product);
        }

        if ($this->is_multiple_allowed) {
            if ($product !== NULL) {
                if ($product->get_sale_price('edit') > 0) {
                    return $this->raw_woocommerce_price($price, $product);
                }
            }
        }
    }

    //+++++++++++++++++++++++++++++ START: USES ONLY FOR WOO > 2.7 AS FIX ON THE CHEKOUT FOR VARIABLE PRODUCTS ++++++++++++++++++++++++++++++++
    //works only in multiple allowed mode
    public function woocommerce_cart_product_subtotal($product_subtotal, $product, $quantity, $cart) {

        if ($product->post_type == 'product_variation') {

            $product_subtotal = $this->wc_price($product->get_price() * $quantity);
        }

        return $product_subtotal;
    }

    public function woocommerce_cart_product_price($price, $product) {

        if ($product->post_type == 'product_variation') {
            $price = $this->wc_price($product->get_price());
        }

        return $price;
    }

    public function woocommerce_cart_subtotal2($cart_subtotal, $compound, $cart) {

        if (!empty($cart) AND isset($cart->cart_contents)) {
            if (!empty($cart->cart_contents)) {
                $cart_subtotal = 0;
                foreach ($cart->cart_contents as $ci) {
                    if ($ci['variation_id'] > 0) {
                        $cart_subtotal += $this->woocs_exchange_value($ci['line_total']);
                    } else {
                        $cart_subtotal += $ci['line_total'];
                    }
                }
            }
        }

        return $this->wc_price($cart_subtotal, false);
    }

    public function woocommerce_cart_contents_total2($cart_contents_total) {
        return 101;
    }

    //+++++++++++++++++++++++++++++ FIINISH: USES ONLY FOR WOO > 2.7 AS FIX ON THE CHEKOUT FOR VARIABLE PRODUCTS ++++++++++++++++++++++++++++++++
    //for tooltip
    private function _get_min_max_variation_prices($product, $current_currency) {
        $currencies = $this->get_currencies();
        $prices_array = $product->get_variation_prices();
        $var_products_ids = array_keys($prices_array['regular_price']);
        $prices_array = array();
        if (!empty($var_products_ids)) {
            foreach ($var_products_ids as $var_prod_id) {

                $is_price_custom = false;
                $regular_price = (float) get_post_meta($var_prod_id, '_regular_price', true);
                $sale_price = (float) get_post_meta($var_prod_id, '_sale_price', true);

                //+++

                if ($this->is_fixed_enabled) {
                    $type = 'regular';
                    $fixed_regular_price = -1;
                    $fixed_sale_price = -1;

                    if ($this->fixed->is_exists($var_prod_id, $current_currency, $type)) {
                        $tmp = $this->fixed->get_value($var_prod_id, $current_currency, $type);
                        if ((int) $tmp !== -1) {
                            $fixed_regular_price = $tmp;
                        }
                    }

                    $type = 'sale';
                    if ($this->fixed->is_exists($var_prod_id, $current_currency, $type)) {
                        $tmp = $this->fixed->get_value($var_prod_id, $current_currency, $type);
                        if ((int) $tmp !== -1) {
                            $fixed_sale_price = $tmp;
                        }
                    }

                    if ((int) $fixed_sale_price !== -1) {
                        $prices_array[] = $fixed_sale_price;
                        $is_price_custom = true;
                    } else {
                        if ((int) $fixed_regular_price !== -1) {
                            $prices_array[] = $fixed_regular_price;
                            $is_price_custom = true;
                        }
                    }
                }


                if ($this->is_geoip_manipulation AND ! $is_price_custom) {
                    $product = (object) array('id' => $var_prod_id);
                    $regular_price = floatval($this->_get_product_geo_price($product, $regular_price));
                    $sale_price = floatval($this->_get_product_geo_price($product, $sale_price));
                    //echo $regular_price . '~~~' . $sale_price . '+++';
                }
                if ($this->is_fixed_user_role) {

                    $regular_price_tmp = floatval($this->fixed_user_role->get_value($var_prod_id, '', 'regular'));
                    $sale_price_tmp = floatval($this->fixed_user_role->get_value($var_prod_id, '', 'sale'));
                    if ((int) $regular_price_tmp !== -1 OR (int) $sale_price_tmp !== -1) {
                        $regular_price = $regular_price_tmp;
                        $sale_price = $sale_price_tmp;
                        $is_price_custom = false;
                    }
                }

                if (!$is_price_custom) {
                    $regular_price = floatval($currencies[$current_currency]['rate'] * $regular_price);
                    $sale_price = floatval($currencies[$current_currency]['rate'] * $sale_price);

                    if ($sale_price > 0) {
                        $prices_array[] = $sale_price;
                    } else {
                        $prices_array[] = $regular_price;
                    }
                }
            }
        }

        //***

        if (!empty($prices_array)) {
            foreach ($prices_array as $key => $value) {
                if (floatval($value) <= 0) {
                    unset($prices_array[$key]);
                }
            }

            //echo '<pre>' . print_r($prices_array, true) . '</pre>';
            if (!empty($prices_array)) {
                return array('min' => min($prices_array), 'max' => max($prices_array));
            }
        }


        return array();
    }

    //$product_type - single, variation - $product->id, $product->variation_id
    public function _get_product_fixed_price($product, $product_type, $price, $precision = 2, $type = NULL) {

        $product_id = $product->get_id();

        //***
        if (!$type) {
            $type = $this->fixed->get_price_type($product, $price);
        }

        $is_empty = $this->fixed->is_empty($product_id, $this->current_currency, $type);
        $is_exists = $this->fixed->is_exists($product_id, $this->current_currency, $type);

        //if sale field is empty BUT regular not, in such case price exists and it is regular
        if ($type == 'sale' AND $is_empty) {
            $type = 'regular';
            $is_exists = $this->fixed->is_exists($product_id, $this->current_currency, $type);
            $is_empty = $this->fixed->is_empty($product_id, $this->current_currency, $type);
        }

        if ($is_exists AND ! $is_empty) {
            return number_format(floatval($this->fixed->get_value($product_id, $this->current_currency, $type)), $precision, $this->decimal_sep, '');
        }


        return -1;
    }

    public function _get_product_fixed_user_role_price($product, $product_type, $price, $precision = 2, $type = NULL) {

        $product_id = $product->get_id();

       if (!$type) {
            $type = $this->fixed_user_role->get_price_type($product, $price);
        }
        $currency = "";

        $is_empty = $this->fixed_user_role->is_empty($product_id, $currency, $type);
        $is_exists = $this->fixed_user_role->is_exists($product_id, $currency, $type);

        if ($type == 'sale' AND $is_empty) {
            $type = 'regular';
            $is_exists = $this->fixed_user_role->is_exists($product_id, $currency, $type);
            $is_empty = $this->fixed_user_role->is_empty($product_id, $currency, $type);
        }


        if ($is_exists AND ! $is_empty) {
            return number_format(floatval($this->fixed_user_role->get_value($product_id, $this->current_currency, $type)), $precision, $this->decimal_sep, '');
        }


        return -1;
    }

    private function _get_product_geo_price($product, $price, $type = NULL, $is_array = false) {
        $is_price_custom = false;
        if ($product !== NULL AND is_object($product)) {
            if (method_exists($product, 'get_id')) {
                $product_id = $product->get_id();
            } else {
                $product_id = $product->id;
            }

            if (!$type) {
                $type = $this->fixed->get_price_type($product, $price);
            }

            $product_geo_data = $this->fixed->get_product_geo_data($product_id);

            if (isset($product_geo_data[$type . '_price_geo'])) {
                if (!empty($product_geo_data[$type . '_price_geo'])) {
                    $user_country = $this->storage->get_val('woocs_user_country');
                    //$user_currency = $this->get_currency_by_country($country);
                    if (!empty($user_country)) {
                        if (!empty($product_geo_data['price_geo_countries'])) {
                            $price_key = '';
                            foreach ($product_geo_data['price_geo_countries'] as $block_key => $countries_codes) {
                                if (!empty($countries_codes) AND is_array($countries_codes)) {
                                    foreach ($countries_codes as $country_code) {
                                        if ($country_code === $user_country) {
                                            $price_key = $block_key;
                                            break(2);
                                        }
                                    }
                                }
                            }

                            //***

                            if (isset($product_geo_data[$type . '_price_geo'][$price_key])) {
                                $price = $product_geo_data[$type . '_price_geo'][$price_key];
                                $is_price_custom = true;
                            }
                        }
                    }
                }
            }
        }

        if ($is_array) {
            return array($price, $is_price_custom);
        }

        return $price;
    }

    public function get_welcome_currency() {
        return get_option('woocs_welcome_currency');
    }

    public function get_customer_signs() {
        $signs = array();
        $data = get_option('woocs_customer_signs', '');
        if (!empty($data)) {
            $data = explode(',', $data);
            if (!empty($data) AND is_array($data)) {
                $signs = $data;
            }
        }
        return $signs;
    }

    public function get_checkout_page_id() {
        return (int) get_option('woocommerce_checkout_page_id');
    }

    public function force_pay_bygeoip_rules() {
        //$use_geo_rules = get_option('woocs_use_geo_rules', 0);
        $use_geo_rules = true;
        if ($this->is_multiple_allowed AND $this->force_pay_bygeoip_rules AND $use_geo_rules) {
            $country = $this->storage->get_val('woocs_user_country');
            $user_currency = $this->get_currency_by_country($country);
            if (!empty($user_currency)) {
                //$user_currency is empty its mean that current country is not in geo ip rules
                $this->set_currency($user_currency);
            }
            do_action('woocs_force_pay_bygeoip_rules', $country, $user_currency, $this->current_currency);
        }
    }

    public function woocommerce_price_format() {
        $currencies = $this->get_currencies();
        $currency_pos = 'left';
        if (isset($currencies[$this->current_currency])) {
            $currency_pos = $currencies[$this->current_currency]['position'];
        }
        $format = '%1$s%2$s';
        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }

        return apply_filters('woocs_price_format', $format, $currency_pos);
    }

//[woocs]
    public function woocs_shortcode($args) {
        if (empty($args)) {
            $args = array();
        }

        $args['shortcode_params'] = $args;
        return $this->render_html(WOOCS_PATH . 'views/shortcodes/woocs.php', $args);
    }

//[woocs_converter exclude="GBP,AUD" precision=2]
    public function woocs_converter($args) {
        if (empty($args)) {
            $args = array();
        }
        return $this->render_html(WOOCS_PATH . 'views/shortcodes/woocs_converter.php', $args);
    }

//[woocs_rates exclude="GBP,AUD" precision=2]
    public function woocs_rates($args) {
        if (empty($args)) {
            $args = array();
        }
        return $this->render_html(WOOCS_PATH . 'views/shortcodes/woocs_rates.php', $args);
    }

//[woocs_show_current_currency text="" currency="" flag=1 code=1]
    public function woocs_show_current_currency($atts) {
        $currencies = $this->get_currencies();
        extract(shortcode_atts(array(
            'text' => esc_html__('Current currency is:', 'woocommerce-currency-switcher'),
            'currency' => $this->current_currency,
            'flag' => 1,
            'code' => 1,
                        ), $atts));

        $args = array();
        $args['currencies'] = $currencies;
        $args['text'] = $text;
        $args['currency'] = $currency;
        $args['flag'] = $flag;
        $args['code'] = $code;
        return $this->render_html(WOOCS_PATH . 'views/shortcodes/woocs_show_current_currency.php', $args);
    }

//[woocs_show_custom_price value=20] -> value should be in default currency
    public function woocs_show_custom_price($atts) {
        extract(shortcode_atts(array('value' => 0), $atts));
        $currencies = $this->get_currencies();
        $convert = true;
        $_REQUEST['woocs_show_custom_price'] = TRUE;
        $wc_price = $this->wc_price($value, $convert, array('currency' => $currencies[$this->current_currency]['name']));
        unset($_REQUEST['woocs_show_custom_price']);
        return $wc_price;
    }

    //for geo ip demo
    public function woocs_geo_hello($atts = '') {
        $pd = array();
        $countries = array();
        $text = '';
        if (class_exists('WC_Geolocation')) {
            $c = new WC_Countries();
            $countries = $c->get_countries();
            $pd = WC_Geolocation::geolocate_ip();
        }
        if (!empty($pd) AND ! empty($countries)) {
            $text = '<span class="woocs_geo_hello">' . sprintf(esc_html__('Your country is: %s. (defined by woocommerce GeoIP functionality)', 'woocommerce-currency-switcher'), $countries[$pd['country']]) . '</span>';
        } else {
            $text = '<i style="color:red; font-size:18px; font-weight:normal;">' . esc_html__('Your country is not defined! Troubles with GeoIp service.', 'woocommerce-currency-switcher') . '</i>';
        }

        return $text;
    }

//http://stackoverflow.com/questions/6918623/curlopt-followlocation-cannot-be-activated
    private function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public function get_currency_freebase_id($currency_code) {
        $freebase_ids = array(
            "AED" => "/m/02zl8q",
            "AFN" => "/m/019vxc",
            "ALL" => "/m/01n64b",
            "AMD" => "/m/033xr3",
            "ANG" => "/m/08njbf",
            "AOA" => "/m/03c7mb",
            "ARS" => "/m/024nzm",
            "AUD" => "/m/0kz1h",
            "AWG" => "/m/08s1k3",
            "AZN" => "/m/04bq4y",
            "BAM" => "/m/02lnq3",
            "BBD" => "/m/05hy7p",
            "BDT" => "/m/02gsv3",
            "BGN" => "/m/01nmfw",
            "BHD" => "/m/04wd20",
            "BIF" => "/m/05jc3y",
            "BMD" => "/m/04xb8t",
            "BND" => "/m/021x2r",
            "BOB" => "/m/04tkg7",
            "BRL" => "/m/03385m",
            "BSD" => "/m/01l6dm",
            "BTC" => "/m/05p0rrx",
            "BWP" => "/m/02nksv",
            "BYN" => "/m/05c9_x",
            "BZD" => "/m/02bwg4",
            "CAD" => "/m/0ptk_",
            "CDF" => "/m/04h1d6",
            "CHF" => "/m/01_h4b",
            "CLP" => "/m/0172zs",
            "CNY" => "/m/0hn4_",
            "COP" => "/m/034sw6",
            "CRC" => "/m/04wccn",
            "CUC" => "/m/049p2z",
            "CUP" => "/m/049p2z",
            "CVE" => "/m/06plyy",
            "CZK" => "/m/04rpc3",
            "DJF" => "/m/05yxn7",
            "DKK" => "/m/01j9nc",
            "DOP" => "/m/04lt7_",
            "DZD" => "/m/04wcz0",
            "EGP" => "/m/04phzg",
            "ETB" => "/m/02_mbk",
            "EUR" => "/m/02l6h",
            "FJD" => "/m/04xbp1",
            "GBP" => "/m/01nv4h",
            "GEL" => "/m/03nh77",
            "GHS" => "/m/01s733",
            "GMD" => "/m/04wctd",
            "GNF" => "/m/05yxld",
            "GTQ" => "/m/01crby",
            "GYD" => "/m/059mfk",
            "HKD" => "/m/02nb4kq",
            "HNL" => "/m/04krzv",
            "HRK" => "/m/02z8jt",
            "HTG" => "/m/04xrp0",
            "HUF" => "/m/01hfll",
            "IDR" => "/m/0203sy",
            "ILS" => "/m/01jcw8",
            "INR" => "/m/02gsvk",
            "IQD" => "/m/01kpb3",
            "IRR" => "/m/034n11",
            "ISK" => "/m/012nk9",
            "JMD" => "/m/04xc2m",
            "JOD" => "/m/028qvh",
            "JPY" => "/m/088n7",
            "KES" => "/m/05yxpb",
            "KGS" => "/m/04k5c6",
            "KHR" => "/m/03_m0v",
            "KMF" => "/m/05yxq3",
            "KRW" => "/m/01rn1k",
            "KWD" => "/m/01j2v3",
            "KYD" => "/m/04xbgl",
            "KZT" => "/m/01km4c",
            "LAK" => "/m/04k4j1",
            "LBP" => "/m/025tsrc",
            "LKR" => "/m/02gsxw",
            "LRD" => "/m/05g359",
            "LSL" => "/m/04xm1m",
            "LYD" => "/m/024xpm",
            "MAD" => "/m/06qsj1",
            "MDL" => "/m/02z6sq",
            "MGA" => "/m/04hx_7",
            "MKD" => "/m/022dkb",
            "MMK" => "/m/04r7gc",
            "MOP" => "/m/02fbly",
            "MRO" => "/m/023c2n",
            "MUR" => "/m/02scxb",
            "MVR" => "/m/02gsxf",
            "MWK" => "/m/0fr4w",
            "MXN" => "/m/012ts8",
            "MYR" => "/m/01_c9q",
            "MZN" => "/m/05yxqw",
            "NAD" => "/m/01y8jz",
            "NGN" => "/m/018cg3",
            "NIO" => "/m/02fvtk",
            "NOK" => "/m/0h5dw",
            "NPR" => "/m/02f4f4",
            "NZD" => "/m/015f1d",
            "OMR" => "/m/04_66x",
            "PAB" => "/m/0200cp",
            "PEN" => "/m/0b423v",
            "PGK" => "/m/04xblj",
            "PHP" => "/m/01h5bw",
            "PKR" => "/m/02svsf",
            "PLN" => "/m/0glfp",
            "PYG" => "/m/04w7dd",
            "QAR" => "/m/05lf7w",
            "RON" => "/m/02zsyq",
            "RSD" => "/m/02kz6b",
            "RUB" => "/m/01hy_q",
            "RWF" => "/m/05yxkm",
            "SAR" => "/m/02d1cm",
            "SBD" => "/m/05jpx1",
            "SCR" => "/m/01lvjz",
            "SDG" => "/m/08d4zw",
            "SEK" => "/m/0485n",
            "SGD" => "/m/02f32g",
            "SLL" => "/m/02vqvn",
            "SOS" => "/m/05yxgz",
            "SRD" => "/m/02dl9v",
            "SSP" => "/m/08d4zw",
            "STD" => "/m/06xywz",
            "SZL" => "/m/02pmxj",
            "THB" => "/m/0mcb5",
            "TJS" => "/m/0370bp",
            "TMT" => "/m/0425kx",
            "TND" => "/m/04z4ml",
            "TOP" => "/m/040qbv",
            "TRY" => "/m/04dq0w",
            "TTD" => "/m/04xcgz",
            "TWD" => "/m/01t0lt",
            "TZS" => "/m/04s1qh",
            "UAH" => "/m/035qkb",
            "UGX" => "/m/04b6vh",
            "USD" => "/m/09nqf",
            "UYU" => "/m/04wblx",
            "UZS" => "/m/04l7bl",
            "VEF" => "/m/021y_m",
            "VND" => "/m/03ksl6",
            "XAF" => "/m/025sw2b",
            "XCD" => "/m/02r4k",
            "XOF" => "/m/025sw2q",
            "XPF" => "/m/01qyjx",
            "YER" => "/m/05yxwz",
            "ZAR" => "/m/01rmbs",
            "ZMW" => "/m/0fr4f"
        );
        $freebase_id = '';
        if ($currency_code && isset($freebase_ids[$currency_code])) {
            $freebase_id = $freebase_ids[$currency_code];
        }

        return $freebase_id;
    }

//ajax
    public function get_rate() {
        $is_ajax = true;
        if (isset($_REQUEST['no_ajax'])) {
            $is_ajax = false;
        }

//***
//http://en.wikipedia.org/wiki/ISO_4217
        $mode = get_option('woocs_currencies_aggregator', 'free_converter');

        $request = "";
        //$woocs_use_curl = (int) get_option('woocs_use_curl', 0);
        $woocs_use_curl = 1;
        switch ($mode) {
            case 'yahoo':
//***
                $date = current_time('timestamp', true);
                $yql_query_url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . $this->default_currency . $this->escape($_REQUEST['currency_name']) . '=X?symbol=' . $this->default_currency . $this->escape($_REQUEST['currency_name']) . '%3DX&period1=' . ( $date - 60 * 86400 ) . '&period2=' . $date . '&interval=1d&includePrePost=false&events=div%7Csplit%7Cearn&lang=en-US&region=US&corsDomain=finance.yahoo.com';
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($yql_query_url);
                } else {
                    $res = file_get_contents($yql_query_url);
                }
                //$yql_query_url="http://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+EURGBP&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
//***
                $data = json_decode($res, true);
                $result = isset($data['chart']['result'][0]['indicators']['quote'][0]['open']) ? $data['chart']['result'][0]['indicators']['quote'][0]['open'] : ( isset($data['chart']['result'][0]['meta']['previousClose']) ? array($data['chart']['result'][0]['meta']['previousClose']) : array() );

                if (count($result) && is_array($result)) {
                    $request = end($result);
                }
                break;

            case 'google':
                //$amount = urlencode(1);
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));
                if ($to_Currency == $from_Currency) {
                    $request = 1;
                    break;
                }
                $url = 'https://www.google.com/async/currency_update?yv=2&async=source_amount:1,source_currency:' . $from_Currency . ',target_currency:' . $to_Currency . ',chart_width:270,chart_height:94,lang:en,country:vn,_fmt:jspb';
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $html = $this->file_get_contents_curl($url);
                } else {
                    $html = file_get_contents($url);
                }

                if ($html) {
                    preg_match('/CurrencyUpdate\":\[\[(.+?)\,/', $html, $matches);

                    if (count($matches) > 0) {
                        $request = isset($matches[1]) ? $matches[1] : 1;
                    } else {
                        $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                    }
                }

                break;

            case 'privatbank':
                //https://api.privatbank.ua/#p24/exchange
                $url = 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5'; //4,5

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = json_decode($res, true);
                $rates = array();

                //var_dump($currency_data);
                if (!empty($currency_data)) {
                    foreach ($currency_data as $c) {
                        if ($c['base_ccy'] == 'UAH') {
                            $rates[$c['ccy']] = floatval($c['sale']);
                        }
                    }
                }


                //***

                if (!empty($rates)) {

                    if ($this->default_currency != 'UAH') {
                        if ($_REQUEST['currency_name'] != 'UAH') {
                            if (isset($_REQUEST['currency_name'])) {
                                $request = floatval($rates[$this->default_currency] / ($rates[$this->escape($_REQUEST['currency_name'])]));
                            } else {
                                $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                            }
                        } else {
                            $request = 1 / (1 / $rates[$this->default_currency]);
                        }
                    } else {
                        if ($_REQUEST['currency_name'] != 'UAH') {
                            $request = 1 / $rates[$_REQUEST['currency_name']];
                        } else {
                            $request = 1;
                        }
                    }
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                //***

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }


                break;



            case 'ecb':
                $url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = simplexml_load_string($res);
                $rates = array();
                if (empty($currency_data->Cube->Cube)) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                    break;
                }



                foreach ($currency_data->Cube->Cube->Cube as $xml) {
                    $att = (array) $xml->attributes();
                    $rates[$att['@attributes']['currency']] = floatval($att['@attributes']['rate']);
                }


                //***

                if (!empty($rates)) {

                    if ($this->default_currency != 'EUR') {
                        if ($_REQUEST['currency_name'] != 'EUR') {
                            if (isset($_REQUEST['currency_name'])) {
                                $request = floatval($rates[$this->escape($_REQUEST['currency_name'])] / $rates[$this->default_currency]);
                            } else {
                                $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                            }
                        } else {
                            //$request = $rates[$this->default_currency];
                            $request = 1 / $rates[$this->default_currency];
                        }
                    } else {
                        if ($_REQUEST['currency_name'] != 'EUR') {
                            //$request = 1 / $rates[$_REQUEST['currency_name']];
                            $request = $rates[$_REQUEST['currency_name']];
                        } else {
                            $request = 1;
                        }
                    }
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                //***

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }


                break;
            case 'free_ecb':
//***           https://api.exchangeratesapi.io/latest?base=USD&symbols=GBP
                $ex_currency = $this->escape($_REQUEST['currency_name']);
                $query_url = 'https://api.exchangeratesapi.io/latest?base=' . $this->default_currency . '&symbols=' . $ex_currency;
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($query_url);
                } else {
                    $res = file_get_contents($query_url);
                }
//***
                $data = json_decode($res, true);
                $request = isset($data['rates'][$ex_currency]) ? $data['rates'][$ex_currency] : 0;

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
            case 'micro':
                //https://ratesapi.io/api/latest?base=USD&symbols=INR
                $ex_currency = $this->escape($_REQUEST['currency_name']);
                $query_url = 'https://ratesapi.io/api/latest?base=' . $this->default_currency . '&symbols=' . $ex_currency;
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($query_url);
                } else {
                    $res = file_get_contents($query_url);
                }
//***
                $data = json_decode($res, true);
                $request = isset($data['rates'][$ex_currency]) ? $data['rates'][$ex_currency] : 0;

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;

            case 'rf':
                //http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=21/08/2015
                $xml_url = 'http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req='; //21/08/2015
                $date = date('d/m/Y');
                $xml_url .= $date;
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($xml_url);
                } else {
                    $res = file_get_contents($xml_url);
                }
//***
                $xml = simplexml_load_string($res) or die("Error: Cannot create object");
                $xml = $this->object2array($xml);
                $rates = array();
                $nominal = array();
//***
                if (isset($xml['Valute'])) {
                    if (!empty($xml['Valute'])) {
                        foreach ($xml['Valute'] as $value) {
                            $rates[$value['CharCode']] = floatval(str_replace(',', '.', $value['Value']));
                            $nominal[$value['CharCode']] = $value['Nominal'];
                        }
                    }
                }
//***
                if (!empty($rates)) {
                    if ($this->default_currency != 'RUB') {
                        if ($_REQUEST['currency_name'] != 'RUB') {
                            if (isset($_REQUEST['currency_name'])) {
                                $request = $nominal[$this->escape($_REQUEST['currency_name'])] * floatval($rates[$this->default_currency] / $rates[$this->escape($_REQUEST['currency_name'])] / $nominal[$this->escape($this->default_currency)]);
                            } else {
                                $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                            }
                        } else {
                            if ($nominal[$this->default_currency] == 10) {
                                $request = (1 / (1 / $rates[$this->default_currency])) / $nominal[$this->default_currency];
                            } else {
                                $request = 1 / (1 / $rates[$this->default_currency]);
                            }
                        }
                    } else {
                        if ($_REQUEST['currency_name'] != 'RUB') {
                            $request = $nominal[$this->escape($_REQUEST['currency_name'])] / $rates[$_REQUEST['currency_name']];
                        } else {
                            $request = 1;
                        }
                    }
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                //***

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                break;


            case 'bank_polski':
                //http://api.nbp.pl/en.html
                $url = 'http://api.nbp.pl/api/exchangerates/tables/A'; //A,B

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = json_decode($res, TRUE);
                $rates = array();
                if (!empty($currency_data[0])) {
                    foreach ($currency_data[0]['rates'] as $c) {
                        $rates[$c['code']] = floatval($c['mid']);
                    }
                }

                //***

                if (!empty($rates)) {

                    if ($this->default_currency != 'PLN') {
                        if ($_REQUEST['currency_name'] != 'PLN') {
                            if (isset($_REQUEST['currency_name'])) {
                                $request = floatval($rates[$this->default_currency] / ($rates[$this->escape($_REQUEST['currency_name'])]));
                            } else {
                                $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                            }
                        } else {
                            $request = 1 / (1 / $rates[$this->default_currency]);
                        }
                    } else {
                        if ($_REQUEST['currency_name'] != 'PLN') {
                            $request = 1 / $rates[$_REQUEST['currency_name']];
                        } else {
                            $request = 1;
                        }
                    }
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                //***

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }


                break;
            case 'free_converter':
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));
                $query_str = sprintf("%s_%s", $from_Currency, $to_Currency);
                $key = get_option('woocs_aggregator_key', '');
                if (!$key) {
                    $request = esc_html__("Please use the API key", 'woocommerce-currency-switcher');
                    break;
                }
                $url = "http://free.currencyconverterapi.com/api/v3/convert?q={$query_str}&compact=y&apiKey={$key}";

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = json_decode($res, true);

                if (!empty($currency_data[$query_str]['val'])) {
                    $request = $currency_data[$query_str]['val'];
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                //***

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
            case 'fixer':
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));

                $key = get_option('woocs_aggregator_key', '');
                if (!$key) {
                    $request = esc_html__("Please use the API key", 'woocommerce-currency-switcher');
                    break;
                }
                $url = "http://data.fixer.io/api/latest?base={$from_Currency}&symbolst={$to_Currency}&access_key={$key}";

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = json_decode($res, true);

                $request = isset($currency_data['rates'][$to_Currency]) ? $currency_data['rates'][$to_Currency] : 0;

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
            case 'currencylayer':
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));

                $key = get_option('woocs_aggregator_key', '');
                if (!$key) {
                    $request = esc_html__("Please use the API key", 'woocommerce-currency-switcher');
                    break;
                }

                $url = "http://apilayer.net/api/live?source={$from_Currency}&currencies={$to_Currency}&access_key={$key}&format=1";

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = json_decode($res, true);

                $rates = isset($currency_data['quotes']) ? $currency_data['quotes'] : 0;
                $request = isset($rates[$from_Currency . $to_Currency]) ? $rates[$from_Currency . $to_Currency] : 0;
                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
            case 'openexchangerates':
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));

                $key = get_option('woocs_aggregator_key', '');
                if (!$key) {
                    $request = esc_html__("Please use the API key", 'woocommerce-currency-switcher');
                    break;
                }

                $url = "https://openexchangerates.org/api/latest.json?base={$from_Currency}&symbolst={$to_Currency}&app_id={$key}";

                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }

                $currency_data = json_decode($res, true);

                $request = isset($currency_data['rates'][$to_Currency]) ? $currency_data['rates'][$to_Currency] : 0;

                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
            case 'cryptocompare':
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));
                //https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=BTC
                $query_str = sprintf("?fsym=%s&tsyms=%s", $from_Currency, $to_Currency);
                $url = "https://min-api.cryptocompare.com/data/price" . $query_str;
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }
                $currency_data = json_decode($res, true);
                if (!empty($currency_data[$to_Currency])) {
                    $request = $currency_data[$to_Currency];
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                //***
                if (!$request) {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
            case 'xe':
                $amount = urlencode(1);
                $from_Currency = urlencode($this->default_currency);
                $to_Currency = urlencode($this->escape($_REQUEST['currency_name']));
                //http://www.xe.com/currencyconverter/convert/?Amount=1&From=ZWD&To=CUP
                $url = "http://www.xe.com/currencyconverter/convert/?Amount=1&From=" . $from_Currency . "&To=" . $to_Currency;
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $html = $this->file_get_contents_curl($url);
                } else {
                    $html = file_get_contents($url);
                }
                //test converterresult-toAmount
                preg_match_all('/<span class=\'uccResultAmount\'>(.*?)<\/span>/s', $html, $matches);
                if (isset($matches[1][0])) {
                    $request = floatval(str_replace(",", "", $matches[1][0]));
                } else {
                    $request = sprintf(esc_html__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }

                break;
             case 'ron':
                 // thank you, Maleabil
                $url = 'https://www.bnr.ro/nbrfxrates.xml';
                if (function_exists('curl_init') AND $woocs_use_curl) {
                    $res = $this->file_get_contents_curl($url);
                } else {
                    $res = file_get_contents($url);
                }
                $currency_data = simplexml_load_string($res);
                $rates = array();
                if (empty($currency_data->Body->Cube)) {
                    $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                    break;
                }
                foreach ($currency_data->Body->Cube->Rate as $xml) {
                    $att = (array) $xml->attributes();
                    $final['rate'] = (string) $xml;
                    $rates[$att['@attributes']['currency']] = floatval($final['rate']);
                }
                //***
                if (!empty($rates)) {
                    if ($this->default_currency != 'RON') {
                        if ($_REQUEST['currency_name'] != 'RON') {
                            if (isset($_REQUEST['currency_name'])) {
                                $request =1/ floatval($rates[$this->escape($_REQUEST['currency_name'])] / $rates[$this->default_currency]);
                            } else {
                                $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                            }
                        } else {
                            $request = 1 * ($rates[$this->default_currency]);
                        }
                    } else {
                        if ($_REQUEST['currency_name'] != 'RON') {
                            if ($rates[$_REQUEST['currency_name']] < 1) {
                                $request = 1 / $rates[$_REQUEST['currency_name']];
                            } else {
                                $request = $rates[$_REQUEST['currency_name']];
                            }
                        } else {
                            $request = 1;
                        }
                    }
                } else {
                    $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                //***

                if (!$request) {
                    $request = sprintf(__("no data for %s", 'woocommerce-currency-switcher'), $this->escape($_REQUEST['currency_name']));
                }
                break;
                
            default:
                break;
        }


//***
        if ($is_ajax) {
            echo $request;
            exit;
        } else {
            return $request;
        }
    }

    private function object2array($object) {
        return @json_decode(@json_encode($object), 1);
    }

//ajax
    public function save_etalon() {
        if (!is_ajax() OR ! current_user_can('manage_options')) {
//we need it just only for ajax update
            return "";
        }
//$this->make_rates_auto_update(true);
        $currencies = $this->get_currencies();
        $currency_name = $this->escape($_REQUEST['currency_name']);
        foreach ($currencies as $key => $currency) {
            if ($currency['name'] == $currency_name) {
                $currencies[$key]['is_etalon'] = 1;
            } else {
                $currencies[$key]['is_etalon'] = 0;
            }
        }
        update_option('woocs', $currencies);
//+++ get curr updated values back
        $request = array();
        $this->default_currency = strtoupper($this->escape($_REQUEST['currency_name']));
        $_REQUEST['no_ajax'] = TRUE;
        foreach ($currencies as $key => $currency) {
            if ($currency_name != $currency['name']) {
                $_REQUEST['currency_name'] = $currency['name'];
                $request[$key] = $this->get_rate();
            } else {
                $request[$key] = 1;
            }
        }

        echo json_encode($request);
        exit;
    }

//order data registration
    public function woocommerce_thankyou_order_id($order_id) {
        $currencies = $this->get_currencies();
//+++
        update_post_meta($order_id, '_woocs_order_rate', $currencies[$this->current_currency]['rate']);
        //wc_add_order_item_meta($order_id, '_woocs_order_rate', $currencies[$this->current_currency]['rate'], true);

        update_post_meta($order_id, '_woocs_order_base_currency', $this->default_currency);
        //wc_add_order_item_meta($order_id, '_woocs_order_base_currency', $this->default_currency, true);

        update_post_meta($order_id, '_woocs_order_currency_changed_mannualy', 0);
        // wc_add_order_item_meta($order_id, '_woocs_order_currency_changed_mannualy', 0, true);

        return $order_id;
    }

    public function woocommerce_cart_totals_order_total_html($output) { {
            return $output;
        }
//experimental feature. Do not use it.
//***
        $value = "&nbsp;(";
//***
        $currencies = $this->get_currencies();
        $amount = WC()->cart->total / $currencies[$this->current_currency]['rate'];
//***
        $cc = $this->current_currency;
        $this->current_currency = $this->default_currency;
        $value .= esc_html__('Total in basic currency: ', 'woocommerce-currency-switcher') . $this->wc_price($amount, false, array('currency' => $this->default_currency));
        $this->current_currency = $cc;
        $value .= ")";
        return $output . $value;
    }

    public function wc_price_args($default_args) {
        if (in_array($this->current_currency, $this->no_cents)) {
            $default_args['decimals'] = 0;
        }
        return $default_args;
    }

//***************************** email actions

    public function woocommerce_email_actions($email_actions) {
        $_REQUEST['woocs_order_emails_is_sending'] = 1;
        if (isset($_REQUEST['woocs_in_order_currency'])) {
            $this->current_currency = $_REQUEST['woocs_in_order_currency'];
            //$this->default_currency = $_REQUEST['woocs_in_order_currency'];
        } else {
            global $post;
            if (is_object($post) AND $post->post_type == 'shop_order') {
                //processing button pressed in: wp-admin/edit.php?post_type=shop_order
                $currency = get_post_meta($post->ID, '_order_currency', true);
                if (!empty($currency)) {
                    $_REQUEST['woocs_in_order_currency'] = $currency;
                    $this->current_currency = $currency;
                }
            } else {
                //processing button pressed in: wp-admin/post.php?post=1170&action=edit - inside of order by drop-down on the left
                if (isset($_POST['order_status']) AND isset($_POST['post_ID'])) {
                    $currency = get_post_meta((int) $_POST['post_ID'], '_order_currency', true);
                    //echo $currency;exit;
                    if (!empty($currency)) {
                        $_REQUEST['woocs_in_order_currency'] = $currency;
                        $this->current_currency = $currency;
                    }
                }
            }
        }



        return $email_actions;
    }

    public function woocommerce_before_resend_order_emails($order) {
        $order_id = 0;
        if (method_exists($order, 'get_id')) {
            $order_id = $order->get_id();
        } else {
            $order_id = $order->id;
        }

        $currency = get_post_meta($order_id, '_order_currency', true);
        if (!empty($currency)) {
            $_REQUEST['woocs_in_order_currency'] = $currency;
            $this->current_currency = $currency;
            $this->default_currency = $currency;
        }
    }

//when admin complete order
    public function woocommerce_order_status_completed($order_id) {
        if (get_option('woocs_is_multiple_allowed', 0)) {
            $currency = get_post_meta($order_id, '_order_currency', true);
            if (!empty($currency)) {
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->default_currency = $currency;
            }
        }
    }

//wp-content\plugins\woocommerce\includes\class-wc-emails.php
//public static function init_transactional_emails()
//public static function send_transactional_email()
    public function woocommerce_order_status_completed_notification($args) {
        if (get_option('woocs_is_multiple_allowed', 0)) {
            $order_id = $args;
            $currency = get_post_meta($order_id, '_order_currency', true);
            if (!empty($currency)) {
                $_REQUEST['woocs_in_order_currency'] = $currency;
                $this->default_currency = $currency;
                $this->current_currency = $currency;
            }
        }
    }

    public function render_html($pagepath, $data = array()) {
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }

    public function get_sign_rate($atts) {
        $sign = strtoupper($atts['sign']);
        $currencies = $this->get_currencies();
        $rate = 0;
        if (isset($currencies[$sign])) {
            $rate = $currencies[$sign]['rate'];
        }

        return $rate;
    }

//for hook woocommerce_paypal_args
    function apply_conversion($paypal_args) {
        if (in_array($this->current_currency, $this->no_cents)) {
            $paypal_args['currency_code'] = $this->current_currency;
            foreach ($paypal_args as $key => $value) {
                if (strpos($key, 'amount_') !== false) {
                    $paypal_args[$key] = number_format($value, 0, $this->decimal_sep, '');
                } else {
                    if (strpos($key, 'tax_cart') !== false) {
                        $paypal_args[$key] = number_format($value, 0, $this->decimal_sep, '');
                    }
                }
            }
        }

        return $paypal_args;
    }

    public function woocommerce_price_html($price_html, $product) {

        static $customer_price_format = -1;
        if ($customer_price_format === -1) {
            $customer_price_format = get_option('woocs_customer_price_format', '__PRICE__');
        }

        if (empty($customer_price_format)) {
            $customer_price_format = '__PRICE__';
        }

//***
        $currencies = $this->get_currencies();


        $product_id = $product->get_id();


//+++
        if (!empty($customer_price_format)) {
            $txt = '<span class="woocs_price_code" data-product-id="' . $product_id . '">' . $customer_price_format . '</span>';
            $txt = str_replace('__PRICE__', $price_html, $txt);
            $price_html = str_replace('__CODE__', $this->current_currency, $txt);
            $price_html = apply_filters('woocs_price_html_tail', $price_html);
        }


//hide cents on front as html element
        if (!in_array($this->current_currency, $this->no_cents)) {
            $sep = wc_get_price_decimal_separator();
            $zeros = str_repeat('[0-9]', $this->get_currency_price_num_decimals($this->current_currency));
            if ($currencies[$this->current_currency]['hide_cents'] == 1) {
                $price_html = preg_replace("/\\{$sep}{$zeros}/", '', $price_html);
            }
        }

//add additional info in price html
        if (get_option('woocs_price_info', 0) AND ! (is_admin() AND ! isset($_REQUEST['get_product_price_by_ajax'])) AND ! isset($_REQUEST['hide_woocs_price_info_list'])) {
            $info = "<ul class='woocs_price_info_list'>";
            $current_currency = $this->current_currency;

            foreach ($currencies as $сurr) {

                if (isset($сurr['hide_on_front']) AND $сurr['hide_on_front']) {
                    continue;
                }

                if ($сurr['name'] == $current_currency) {
                    continue;
                }
                $this->current_currency = $сurr['name'];

                $value = (float) $product->get_price('edit') * (float) $currencies[$сurr['name']]['rate'];

                $precision = $this->get_currency_price_num_decimals($сurr['name'], $this->price_num_decimals);
                $value = number_format($value, $precision, $this->decimal_sep, '');

                //***

                $product_type = '';

                $product_type = $product->get_type();

                if ($product_type == 'variable') {


                    $min_value = $product->get_variation_price('min', true) * $currencies[$сurr['name']]['rate'];
                    $max_value = $product->get_variation_price('max', true) * $currencies[$сurr['name']]['rate'];

                    //***
                    $min_max_values = $this->_get_min_max_variation_prices($product, $сurr['name']);
                    if (!empty($min_max_values)) {

                        $min_value = $min_max_values['min'] /* $currencies[$сurr['name']]['rate'] */;
                        $max_value = $min_max_values['max'] /* $currencies[$сurr['name']]['rate'] */;
                    }
                    if (wc_tax_enabled()) {
                        $min_value = $this->woocs_calc_tax_price($product, $min_value);
                        $max_value = $this->woocs_calc_tax_price($product, $max_value);
                    }
                    //+++
                    $_REQUEST['woocs_wc_price_convert'] = FALSE;

                    $var_price = "";
                    $var_price1 = $this->wc_price($min_value, array('currency' => $сurr['name']), false, $product, $precision);
                    $var_price2 = $this->wc_price($max_value, array('currency' => $сurr['name']), false, $product, $precision);
                    if ($var_price1 == $var_price2) {
                        $var_price = $var_price1;
                    } else {
                        $var_price = sprintf("%s - %s", $var_price1, $var_price2);
                    }

                    unset($_REQUEST['woocs_wc_price_convert']);
                    $info .= "<li><b>" . $сurr['name'] . "</b>: " . $var_price . "</li>";
                } elseif ($product_type == 'grouped') {

                    $child_ids = $product->get_children();
                    $prices = array();
                    foreach ($child_ids as $prod_id) {
                        $product1 = wc_get_product($prod_id);
                        $product_type1 = $product1->get_type();
                        if ($product_type1 == 'variable') {

                            $min_value = $product1->get_variation_price('min', true) * $currencies[$сurr['name']]['rate'];
                            $max_value = $product1->get_variation_price('max', true) * $currencies[$сurr['name']]['rate'];
                            //***
                            $min_max_values = $this->_get_min_max_variation_prices($product1, $сurr['name']);
                            if (!empty($min_max_values)) {

                                $min_value = $min_max_values['min'] /* $currencies[$сurr['name']]['rate'] */;
                                $max_value = $min_max_values['max'] /* $currencies[$сurr['name']]['rate'] */;
                            }
                            if (wc_tax_enabled()) {
                                $prices[] = $this->woocs_calc_tax_price($product1, $min_value);
                                $prices[] = $this->woocs_calc_tax_price($product1, $max_value);
                            } else {
                                $prices[] = $min_value;
                                $prices[] = $max_value;
                            }
                        } else {

                            if ($this->is_fixed_enabled AND $this->is_multiple_allowed) {
                                $type = 'sale';
                                $is_empty = $this->fixed->is_empty($prod_id, $сurr['name'], $type);
                                $is_exists = $this->fixed->is_exists($prod_id, $сurr['name'], $type);

                                if ($type == 'sale' AND $is_empty) {
                                    $type = 'regular';
                                    $is_exists = $this->fixed->is_exists($prod_id, $сurr['name'], $type);
                                    $is_empty = $this->fixed->is_empty($prod_id, $сurr['name'], $type);
                                }

                                if ($is_exists AND ! $is_empty) {
                                    $special_convert = true;
                                    $is_price_custom = true;
                                    if (floatval($this->fixed->get_value($prod_id, $сurr['name'], $type)) > 0) {

                                        if (wc_tax_enabled()) {
                                            $prices[] = $this->woocs_calc_tax_price($product1, floatval($this->fixed->get_value($prod_id, $сurr['name'], $type)));
                                        } else {
                                            $prices[] = floatval($this->fixed->get_value($prod_id, $сurr['name'], $type));
                                        }
                                    }
                                } else {
                                    if (wc_tax_enabled()) {
                                        $prices[] = $this->woocs_calc_tax_price($product1, $product1->get_price('edit') * $currencies[$сurr['name']]['rate']);
                                    } else {
                                        $prices[] = $product1->get_price('edit') * $currencies[$сurr['name']]['rate'];
                                    }
                                }
                            } else {
                                if (wc_tax_enabled()) {
                                    $prices[] = $this->woocs_calc_tax_price($product1, $product1->get_price('edit') * $currencies[$сurr['name']]['rate']);
                                } else {
                                    $prices[] = $product1->get_price('edit') * $currencies[$сurr['name']]['rate'];
                                }
                            }
                        }
                    }
                    asort($prices);
                    $_REQUEST['woocs_wc_price_convert'] = FALSE;
                    $var_price = "";
                    $var_price1 = $this->wc_price(array_shift($prices), array('currency' => $сurr['name']), false, $product, $precision);
                    $var_price2 = $this->wc_price(array_pop($prices), array('currency' => $сurr['name']), false, $product, $precision);

                    if ($var_price1 == $var_price2) {
                        $var_price = $var_price1;
                    } else {
                        $var_price = sprintf("%s - %s", $var_price1, $var_price2);
                    }

                    $info .= "<li><b>" . $сurr['name'] . "</b>: " . $var_price . "</li>";
                } else {
                    //print_r($product);
                    if (wc_tax_enabled()) {
                        $value = $this->woocs_calc_tax_price($product, $value);
                    }
                    $info .= "<li><span>" . $сurr['name'] . "</span>: " . $this->wc_price($value, false, array('currency' => $сurr['name']), $product, $precision) . "</li>";
                }
            }
            $this->current_currency = $current_currency;
            $info .= "</ul>";
            $info = '<div class="woocs_price_info"><span class="woocs_price_info_icon"></span>' . $info . '</div>';
            $add_icon = strripos($price_html, $info);
            if ($add_icon === false) {
                $price_html .= $info;
            }
        }

        //add approx in price html
        if (get_option('woocs_show_approximate_price', 0) AND ! is_admin()) {
            $price_html = $this->woocs_add_approx_to_price($price_html, $product);
        }

        
        return $price_html;
    }

    public function woocommerce_coupon_get_discount_amount($discount, $discounting_amount, $cart_item, $single, $coupon) {
        if ($this->is_multiple_allowed) {
            if (is_object($coupon) AND method_exists($coupon, 'is_type')) {
                if (!$coupon->is_type(array('percent_product', 'percent'))) {
                    $discount = $this->woocs_exchange_value(floatval($discount));
                }
            }
        }

        return $discount;
    }

    public function woocommerce_coupon_validate_minimum_amount($is, $coupon) {

        if ($this->current_currency != $this->default_currency AND get_option('woocs_is_multiple_allowed', 0)) {
            $currencies = $this->get_currencies();
            //convert amount into basic currency amount
            $cart_amount = $this->back_convert(WC()->cart->get_displayed_subtotal(), $currencies[$this->current_currency]['rate']);
            return $coupon->get_minimum_amount() > $cart_amount;
        }

        return $is;
    }

    public function woocommerce_coupon_validate_maximum_amount($is, $coupon) {

        if ($this->current_currency != $this->default_currency AND get_option('woocs_is_multiple_allowed', 0)) {
            $currencies = $this->get_currencies();
            //convert amount into basic currency amount
            $cart_amount = $this->back_convert(WC()->cart->get_displayed_subtotal(), $currencies[$this->current_currency]['rate']);
            return $coupon->get_maximum_amount() < $cart_amount;
        }

        return $is;
    }

    public function woocommerce_coupon_error($err, $err_code, $coupon) {
        if ($this->current_currency != $this->default_currency) {
            $currencies = $this->get_currencies();

            $rate = 1;
            if (get_option('woocs_is_multiple_allowed', 0)) {
                $rate = $currencies[$this->current_currency]['rate'];
            }

            switch ($err_code) {
                case 112:

                    $amount = $coupon->get_maximum_amount() * $rate;
                    $err = sprintf(esc_html__('The maximum spend for this coupon is %s.', 'woocommerce-currency-switcher'), wc_price($amount));
                    break;

                case 108:
                    $amount = $coupon->get_minimum_amount() * $rate;
                    $err = sprintf(esc_html__('The minimum spend for this coupon is %s.', 'woocommerce-currency-switcher'), wc_price($amount));
                    break;

                default:
                    break;
            }
        }

        return $err;
    }

//wp filter for values which is in basic currency and no possibility do it automatically
    public function woocs_exchange_value($value) {
        $currencies = $this->get_currencies();
        $value = floatval($value) * floatval($currencies[$this->current_currency]['rate']);
        $precision = $this->get_currency_price_num_decimals($this->current_currency, $this->price_num_decimals);
        $value = number_format($value, $precision, $this->decimal_sep, '');
        return $value;
    }

//set it to default
    public function reset_currency() {
        $this->set_currency('');
    }

    public function set_currency($currency = '') {
        if (empty($currency)) {
            $currency = $this->default_currency;
        }

        $this->current_currency = $currency;
        $this->storage->set_val('woocs_current_currency', $currency);
    }

//compatibility for https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/stats/
//hook commented, wpo_wcpdf_process_template_order uses for this
    public function wpo_wcpdf_order_number($order_id) {
//set order currency instead selected on the front
        $currency = get_post_meta($order_id, '_order_currency', TRUE);
        if (!empty($currency)) {
            $this->current_currency = $currency;
        }

        return $order_id;
    }

//https://wordpress.org/support/topic/multi-currency-on-invoices?replies=8
    public function wpo_wcpdf_process_template_order($template_type, $order_id) {
        if (!empty($order_id) AND is_numeric($order_id)) {
            $currency = get_post_meta($order_id, '_order_currency', TRUE);
            if (!empty($currency)) {
                $this->current_currency = $currency;
            }
        }
    }

//***

    public function woocommerce_get_order_currency($order_currency, $order) {

        if (!is_ajax() AND ! is_admin() AND is_object($order)) {
            $order_id = 0;
            if (method_exists($order, 'get_id')) {
                $order_id = $order->get_id();
            } else {
                $order_id = $order->id;
            }

            $currency = get_post_meta($order_id, '_order_currency', TRUE);
            if (!empty($currency)) {
                $this->current_currency = $currency;
            }
        }

        return $order_currency;
    }

    public function woocommerce_view_order($order_id) {

        if (!is_ajax() AND ! is_admin()) {
            $currency = get_post_meta($order_id, '_order_currency', TRUE);
            if (!empty($currency)) {
                $this->current_currency = $currency;
            }
        }

        return $order_id;
    }

    public function woocommerce_package_rates($rates) {
        $currencies = $this->get_currencies();
        $new_version = false;
        $new_version = true;

//***
        if ($this->is_multiple_allowed) {
            if ($this->current_currency != $this->default_currency) {
                $currencies = $this->get_currencies();
                foreach ($rates as $rate) {

                    $value = $rate->cost * $currencies[$this->current_currency]['rate'];
                    if ($this->is_fixed_shipping) {//is fixed shipping cost
                        $is_empty = $this->fixed_shipping->is_empty($rate->id, $this->current_currency, '');
                        $is_exist = $this->fixed_shipping->is_exists($rate->id, $this->current_currency, '');
                        if (!$is_empty AND $is_exist) {
                            $value = $this->fixed_shipping->get_value($rate->id, $this->current_currency, '');
                        }
                    }
                    $precision = $this->get_currency_price_num_decimals($this->current_currency, $this->price_num_decimals);
                    $rate->cost = number_format(floatval($value), $precision, $this->decimal_sep, '');
//VAT values for another currency in the shipping
//https://wordpress.org/support/topic/vat-values-are-not-switched-to-another-currency-for-shipping

                    if (isset($rate->taxes)) {
                        $taxes = $rate->taxes;
                        if (!empty($taxes)) {
                            $new_tax = array();
                            if ($this->is_fixed_shipping AND ! $is_empty AND $is_exist AND $value) {
                                if (wc_tax_enabled() AND ! WC()->customer->is_vat_exempt() AND is_array($rate->taxes)) {
                                    $new_tax = WC_Tax::calc_shipping_tax($value, WC_Tax::get_shipping_tax_rates());
                                }
                            } else {
                                foreach ($taxes as $order => $tax) {
                                    $value_tax = $tax * $currencies[$this->current_currency]['rate'];
                                    $sum = number_format(floatval($value_tax), $precision, $this->decimal_sep, '');
                                    if ($new_version) {
                                        $new_tax[$order] = $sum;
                                    } else {
                                        $rate->taxes[$order] = $sum;
                                    }
                                }
                            }
                            if ($new_version) {
                                $rate->set_taxes($new_tax);
                            }
                        }
                    }
                }
            }
        }

        return $rates;
    }

    public function wcml_raw_price_amount($value) {
        return $this->woocs_exchange_value($value);
    }

//ajax
    public function woocs_convert_currency() {
        $currencies = $this->get_currencies();
        $v = $currencies[$_REQUEST['to']]['rate'] / $currencies[$_REQUEST['from']]['rate'];
        if (in_array($_REQUEST['to'], $this->no_cents)) {
            $_REQUEST['precision'] = 0;
        }
        $value = number_format($v * $_REQUEST['amount'], intval($_REQUEST['precision']), $this->decimal_sep, '');


        wp_die($value);
    }

//for refreshing mini-cart widget
    public function woocommerce_before_mini_cart() {
        $_REQUEST['woocs_woocommerce_before_mini_cart'] = 'mini_cart_refreshing';
        WC()->cart->calculate_totals();
    }

//for refreshing mini-cart widget
    public function woocommerce_after_mini_cart() {
        unset($_REQUEST['woocs_woocommerce_before_mini_cart']);
    }

//ajax
    public function woocs_rates_current_currency() {
        wp_die(do_shortcode('[woocs_rates exclude="' . $this->escape($_REQUEST['exclude']) . '" precision="' . $this->escape($_REQUEST['precision']) . '" current_currency="' . $this->escape($_REQUEST['current_currency']) . '"]'));
    }

//log test data while makes debbuging
    public function log($string) {
        $handle = fopen(WOOCS_PATH . 'log.txt', 'a+');
        $string .= PHP_EOL;
        fwrite($handle, $string);
        fclose($handle);
    }

    public function wc_price($price, $convert = true, $args = array(), $product = NULL, $decimals = -1) {
        if (!isset($_REQUEST['woocs_wc_price_convert'])) {
            $_REQUEST['woocs_wc_price_convert'] = true;
        }
        extract(apply_filters('wc_price_args', wp_parse_args($args, array(
            'ex_tax_label' => false,
            'currency' => '',
            'decimal_separator' => $this->decimal_sep,
            'thousand_separator' => $this->thousands_sep,
            'decimals' => $decimals,
            'price_format' => $this->woocommerce_price_format()
        ))));

        if ($decimals < 0) {
            $decimals = $this->get_currency_price_num_decimals($currency, $this->price_num_decimals);
        }

//***
        $currencies = $this->get_currencies();
        if (isset($currencies[$currency])/* AND !isset($_REQUEST['woocs_show_custom_price']) */) {
            if ($currencies[$currency]['hide_cents']) {
                $decimals = 0;
            }
        }

//***
        $negative = $price < 0;
        $special_convert = false;
        $is_price_custom = false;
        try {
            if ($product !== NULL AND is_object($product)) {

                $product_id = $product->get_id();

                //***
                if ($this->is_multiple_allowed) {
                    if ($this->is_fixed_enabled) {
                        //$type = $this->fixed->get_price_type($product, $price);
                        $type = 'sale';

                        $is_empty = $this->fixed->is_empty($product_id, $currency, $type);
                        $is_exists = $this->fixed->is_exists($product_id, $currency, $type);

                        if ($type == 'sale' AND $is_empty) {
                            $type = 'regular';
                            $is_exists = $this->fixed->is_exists($product_id, $currency, $type);
                            $is_empty = $this->fixed->is_empty($product_id, $currency, $type);
                        }

                        if ($is_exists AND ! $is_empty) {
                            $special_convert = true;
                            $is_price_custom = true;
                            if (floatval($this->fixed->get_value($product_id, $currency, $type)) > 0) {
                                $price = floatval($this->fixed->get_value($product_id, $currency, $type));
                                if (wc_tax_enabled()) {
                                    $price = $this->woocs_calc_tax_price($product, $price);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            
        }

        //***

        if ($this->is_geoip_manipulation AND ! $is_price_custom AND ! is_null($product)) {
            $product_geo_price_data = $this->_get_product_geo_price($product, $price, 'sale', true);
            $price = $product_geo_price_data[0];
            $is_price_custom = true;

            $product_type = 'simple';

            $product_type = $product->get_type();

            if ($product_type == 'variable') {
                if ($product_geo_price_data[1]) {
                    $is_price_custom = false;
                }
            } else {
                if ($product_geo_price_data[1]) {
                    $price = $this->raw_woocommerce_price(floatval($negative ? $price * -1 : $price));
                    if (wc_tax_enabled()) {
                        $price = $this->woocs_calc_tax_price($product, $price);
                    }
                }
            }
        }
        if ($this->is_fixed_user_role AND ! is_null($product)) {
            $type = 'sale';
            $currency = "";
            $product_id = $product->get_id();
            $is_empty = $this->fixed_user_role->is_empty($product_id, $currency, $type);
            $is_exists = $this->fixed_user_role->is_exists($product_id, $currency, $type);

            if ($type == 'sale' AND $is_empty) {
                $type = 'regular';
                $is_exists = $this->fixed_user_role->is_exists($product_id, $currency, $type);
                $is_empty = $this->fixed_user_role->is_empty($product_id, $currency, $type);
            }

            if ($is_exists AND ! $is_empty) {
                $is_price_custom = true;
                $is_price_custom = false;
                if (floatval($this->fixed_user_role->get_value($product_id, $currency, $type)) > 0) {
                    $price = floatval($this->fixed_user_role->get_value($product_id, $currency, $type));
                    $price = $this->raw_woocommerce_price(floatval($negative ? $price * -1 : $price));
                    if (wc_tax_enabled()) {
                        $price = $this->woocs_calc_tax_price($product, $price);
                    }
                }
            }
        }


        //***

        if ($convert AND $_REQUEST['woocs_wc_price_convert'] AND ! $is_price_custom) {
            $price = $this->raw_woocommerce_price(floatval($negative ? $price * -1 : $price));
        }

        //***

        $price = apply_filters('formatted_woocommerce_price', number_format($price, $decimals, $decimal_separator, $thousand_separator), $price, $decimals, $decimal_separator, $thousand_separator);


        if (apply_filters('woocommerce_price_trim_zeros', false) AND $decimals > 0) {
            $price = wc_trim_zeros($price);
        }

        $formatted_price = ( $negative ? '-' : '' ) . sprintf($price_format, get_woocommerce_currency_symbol($currency), $price);
        $return = '<span class="woocs_amount">' . $formatted_price . '</span>';

        if ($ex_tax_label && wc_tax_enabled()) {
            $return .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
        }

        //***

        return apply_filters('wc_price', $return, $price, $args);
    }

    public function woocommerce_available_variation($variation_data, $product, $variation) {

        $_REQUEST['woocs_woocommerce_available_variation_is'] = TRUE;
        add_filter('raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 9999);
        $variation = wc_get_product($variation->get_id());

        // See if prices should be shown for each variation after selection.
        $show_variation_price = apply_filters('woocommerce_show_variation_price', $variation->get_price() === "" || $product->get_variation_sale_price('min') !== $product->get_variation_sale_price('max') || $product->get_variation_regular_price('min') !== $product->get_variation_regular_price('max'), $product, $variation);

        $_REQUEST['hide_woocs_price_info_list'] = true;
        $variation_data = apply_filters('woocs_woocommerce_available_variation', array_merge($variation->get_data(), array(
            'attributes' => $variation->get_variation_attributes(),
            'image' => wc_get_product_attachment_props($variation->get_image_id()),
            'weight_html' => wc_format_weight($variation->get_weight()),
            'dimensions_html' => wc_format_dimensions($variation->get_dimensions(false)),
            'price_html' => $show_variation_price ? '<span class="price">' . $variation->get_price_html() . '</span>' : '',
            'availability_html' => wc_get_stock_html($variation),
            'variation_id' => $variation->get_id(),
            'variation_is_visible' => $variation->variation_is_visible(),
            'variation_is_active' => $variation->variation_is_active(),
            'is_purchasable' => $variation->is_purchasable(),
            'display_price' => wc_get_price_to_display($variation),
            'display_regular_price' => wc_get_price_to_display($variation, array('price' => $variation->get_regular_price())),
            'dimensions' => wc_format_dimensions($variation->get_dimensions(false)),
            'min_qty' => $variation->get_min_purchase_quantity(),
            'max_qty' => 0 < $variation->get_max_purchase_quantity() ? $variation->get_max_purchase_quantity() : '',
            'backorders_allowed' => $variation->backorders_allowed(),
            'is_in_stock' => $variation->is_in_stock(),
            'is_downloadable' => $variation->is_downloadable(),
            'is_virtual' => $variation->is_virtual(),
            'is_sold_individually' => $variation->is_sold_individually() ? 'yes' : 'no',
            'variation_description' => $variation->get_description(),
                )), $product, $variation);

        unset($_REQUEST['hide_woocs_price_info_list']);
        unset($_REQUEST['woocs_woocommerce_available_variation_is']);
        remove_filter('raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 9999);

        return apply_filters('woocs_woocommerce_available_variation', $variation_data, $product, $variation);
    }

//woo hook
    public function woocommerce_product_is_on_sale($value, $product) {
        $is_sale = false;
        $sale_price = $product->sale_price;
        $regular_price = $product->regular_price;
        $price = $product->price;

//***
//https://www.skyverge.com/blog/get-a-list-of-woocommerce-sale-products/
        if ($product->product_type == 'variable') {
            
        } else {
            if ($sale_price !== $regular_price AND ( $price === $sale_price)) {
                $is_sale = true;
            }
        }


        return $is_sale;
    }

//woo hook
//wp-content\plugins\woocommerce\includes\shipping\free-shipping\class-wc-shipping-free-shipping.php #192
    public function woocommerce_shipping_free_shipping_is_available($is_available, $package, $this_shipping = null) {
        global $woocommerce;
        $currencies = $this->get_currencies(); {
            $has_coupon = false;
            $has_met_min_amount = false;

            if (in_array($this_shipping->requires, array('coupon', 'either', 'both'))) {
                if ($coupons = WC()->cart->get_coupons()) {
                    foreach ($coupons as $code => $coupon) {
                        if ($coupon->is_valid() && $coupon->get_free_shipping()) {
                            $has_coupon = true;
                            break;
                        }
                    }
                }
            }

            if (in_array($this_shipping->requires, array('min_amount', 'either', 'both'))) {
                $total = WC()->cart->get_displayed_subtotal();
                if ($this->is_multiple_allowed) {
                    if ($this->current_currency != $this->default_currency) {
                        $total = (float) $this->back_convert($total, $currencies[$this->current_currency]['rate']);
                        //$amount = $amount + $amount * 0.001; //correction because of cents
                    }
                }
                if ('incl' === WC()->cart->tax_display_cart) {
                    $total = round($total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals());
                } else {
                    $total = round($total - WC()->cart->get_discount_total(), wc_get_price_decimals());
                }
                $min_amount = $this_shipping->min_amount;

                if ($this->is_fixed_shipping) {//is fixed shipping min_amount
                    $is_empty = $this->fixed_shipping_free->is_empty($this_shipping->get_instance_option_key(), $this->current_currency, '');
                    $is_exist = $this->fixed_shipping_free->is_exists($this_shipping->get_instance_option_key(), $this->current_currency, '');
                    if (!$is_empty AND $is_exist) {
                        $min_amount = $this->fixed_shipping_free->get_value($this_shipping->get_instance_option_key(), $this->current_currency, '');
                        if ($this->current_currency != $this->default_currency) {
                            $min_amount = (float) $this->back_convert($min_amount, $currencies[$this->current_currency]['rate']);
                            //$amount = $amount + $amount * 0.001; //correction because of cents
                        }
                    }
                }
                if ($total >= $min_amount) {
                    $has_met_min_amount = true;
                }
            }

            switch ($this_shipping->requires) {
                case 'min_amount' :
                    $is_available = $has_met_min_amount;
                    break;
                case 'coupon' :
                    $is_available = $has_coupon;
                    break;
                case 'both' :
                    $is_available = $has_met_min_amount && $has_coupon;
                    break;
                case 'either' :
                    $is_available = $has_met_min_amount || $has_coupon;
                    break;
                default :
                    $is_available = true;
                    break;
            }
        }

//***

        return $is_available;
    }

//ajax
//for price redrawing on front if site using cache plugin functionality
    public function woocs_get_products_price_html() {
        $result = array();
        if (isset($_REQUEST['products_ids'])) {
//***
            $this->init_geo_currency();
//***
            $_REQUEST['get_product_price_by_ajax'] = 1;

            $products_ids = $_REQUEST['products_ids'];
//***
            if (!empty($products_ids) AND is_array($products_ids)) {
                foreach ($products_ids as $p_id) {
                    $product = wc_get_product($p_id);
                    if (is_object($product)) {
                        $result[$p_id] = $product->get_price_html();
                    }
                }
            }
        }
//***
        $data = array();
        $data['ids'] = $result;
        $data['current_currency'] = $this->current_currency;
        wp_die(json_encode($data));
    }

//count amount in basic currency from any currency
    public function back_convert($amount, $rate, $precision = 4) {
        if (!$rate OR $rate == 0) {
            $rate = 1;
        }
        return number_format((1 / $rate) * $amount, $precision, '.', '');
    }

//recalculation order to basic currency data if order is in any another currency
    public function recalculate_order($order_id) {

        $order_currency = get_post_meta($order_id, '_order_currency', true);
//lets avoid recalculation for order which is already in
        if ($order_currency == $this->default_currency OR empty($order_currency)) {
            return;
        }
        $decimals = $this->get_currency_price_num_decimals($order_currency, $this->price_num_decimals);

//***
        $currencies = $this->get_currencies();
        $_woocs_order_rate = get_post_meta($order_id, '_woocs_order_rate', true);
        if (empty($_woocs_order_rate)) {
            $_woocs_order_rate = $currencies[$order_currency]['rate'];
        }
//***

        update_post_meta($order_id, '_woocs_order_currency', $this->default_currency);
        update_post_meta($order_id, '_order_currency', $this->default_currency);

        update_post_meta($order_id, '_woocs_order_base_currency', $this->default_currency);
        //  wc_update_order_item_meta($order_id, '_woocs_order_base_currency', $this->default_currency);

        update_post_meta($order_id, '_woocs_order_rate', 1);
        // wc_update_order_item_meta($order_id, '_woocs_order_rate', 1);

        update_post_meta($order_id, '_woocs_order_currency_changed_mannualy', time());
        //wc_add_order_item_meta($order_id, '_woocs_order_currency_changed_mannualy', time(), true);
//***

        $_order_shipping = get_post_meta($order_id, '_order_shipping', true);
        update_post_meta($order_id, '_order_shipping', $this->back_convert($_order_shipping, $_woocs_order_rate));

        $_order_total = get_post_meta($order_id, '_order_total', true);
        update_post_meta($order_id, '_order_total', $this->back_convert($_order_total, $_woocs_order_rate));

        $_refund_amount = get_post_meta($order_id, '_refund_amount', true);
        update_post_meta($order_id, '_refund_amount', $this->back_convert($_refund_amount, $_woocs_order_rate));

        $_cart_discount_tax = get_post_meta($order_id, '_cart_discount_tax', true);
        update_post_meta($order_id, '_cart_discount_tax', $this->back_convert($_cart_discount_tax, $_woocs_order_rate));

        $_order_tax = get_post_meta($order_id, '_order_tax', true);
        update_post_meta($order_id, '_order_tax', $this->back_convert($_order_tax, $_woocs_order_rate));

        $_order_shipping_tax = get_post_meta($order_id, '_order_shipping_tax', true);
        update_post_meta($order_id, '_order_shipping_tax', $this->back_convert($_order_shipping_tax, $_woocs_order_rate));

        $_cart_discount = get_post_meta($order_id, '_cart_discount', true);
        update_post_meta($order_id, '_cart_discount', $this->back_convert($_cart_discount, $_woocs_order_rate));

//***

        global $wpdb;
        $data_sql = array(
            array(
                'val' => $order_id,
                'type' => 'int',
            ),
        );
        $get_items_sql = $this->woocs_prepare("SELECT order_item_id,order_item_type FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d ", $data_sql);
        $line_items = $wpdb->get_results($get_items_sql, ARRAY_N);
        if (!empty($line_items) AND is_array($line_items)) {
            foreach ($line_items as $v) {
                $order_item_id = $v[0];
                $order_item_type = $v[1];

                switch ($order_item_type) {
                    case 'line_item':

                        $amount = wc_get_order_item_meta($order_item_id, '_line_subtotal', true);
                        wc_update_order_item_meta($order_item_id, '_line_subtotal', $this->back_convert($amount, $_woocs_order_rate, $decimals));

                        $amount = wc_get_order_item_meta($order_item_id, '_line_total', true);
                        wc_update_order_item_meta($order_item_id, '_line_total', $this->back_convert($amount, $_woocs_order_rate, $decimals));

                        $amount = wc_get_order_item_meta($order_item_id, '_line_subtotal_tax', true);
                        wc_update_order_item_meta($order_item_id, '_line_subtotal_tax', $this->back_convert($amount, $_woocs_order_rate, $decimals));

                        $amount = wc_get_order_item_meta($order_item_id, '_line_tax', true);
                        wc_update_order_item_meta($order_item_id, '_line_tax', $this->back_convert($amount, $_woocs_order_rate, $decimals));

                        $_line_tax_data = wc_get_order_item_meta($order_item_id, '_line_tax_data', true);
                        if (!empty($_line_tax_data) AND is_array($_line_tax_data)) {
                            foreach ($_line_tax_data as $key => $values) {
                                if (!empty($values)) {
                                    if (is_array($values)) {
                                        foreach ($values as $k => $value) {
                                            if (is_numeric($value)) {
                                                $_line_tax_data[$key][$k] = $this->back_convert($value, $_woocs_order_rate, $decimals);
                                            }
                                        }
                                    } else {
                                        if (is_numeric($values)) {
                                            $_line_tax_data[$key] = $this->back_convert($values, $_woocs_order_rate, $decimals);
                                        }
                                    }
                                }
                            }
                        }

                        wc_update_order_item_meta($order_item_id, '_line_tax_data', $_line_tax_data);

                        break;

                    case 'shipping':
                        $amount = wc_get_order_item_meta($order_item_id, 'cost', true);
                        wc_update_order_item_meta($order_item_id, 'cost', $this->back_convert($amount, $_woocs_order_rate, $decimals));


                        $taxes = wc_get_order_item_meta($order_item_id, 'taxes', true);

                        if (!empty($taxes) AND is_array($taxes)) {
                            foreach ($taxes as $key => $values) {
                                if (!empty($values)) {
                                    if (is_array($values)) {
                                        foreach ($values as $k => $value) {
                                            if (is_numeric($value)) {
                                                $taxes[$key][$k] = $this->back_convert($value, $_woocs_order_rate, $decimals);
                                            }
                                        }
                                    } else {
                                        if (is_numeric($values)) {
                                            $taxes[$key] = $this->back_convert($values, $_woocs_order_rate, $decimals);
                                        }
                                    }
                                }
                            }
                        }

                        wc_update_order_item_meta($order_item_id, 'taxes', $taxes);

                        break;

                    case 'tax':
                        $amount = wc_get_order_item_meta($order_item_id, 'tax_amount', true);
                        wc_update_order_item_meta($order_item_id, 'tax_amount', $this->back_convert($amount, $_woocs_order_rate, 3));

                        $amount = wc_get_order_item_meta($order_item_id, 'shipping_tax_amount', true);
                        wc_update_order_item_meta($order_item_id, 'shipping_tax_amount', $this->back_convert($amount, $_woocs_order_rate, $decimals));

                        break;

                    default:
                        break;
                }
            }
        }

//***

        $order = new WC_Order($order_id);
        $refunds = $order->get_refunds();

        if (!empty($refunds)) {
            foreach ($refunds as $refund) {
                $post_id = 0;

                if (method_exists($refund, 'get_id')) {
                    $post_id = $refund->get_id();
                } else {
                    $post_id = $refund->id;
                }

                $amount = get_post_meta($post_id, '_refund_amount', true);
                update_post_meta($post_id, '_refund_amount', $this->back_convert($amount, $_woocs_order_rate, $decimals));
                $amount = get_post_meta($post_id, '_order_total', true);
                update_post_meta($post_id, '_order_total', $this->back_convert($amount, $_woocs_order_rate, $decimals));
                update_post_meta($post_id, '_order_currency', $this->default_currency);
            }
        }
    }

//ajax
    public function woocs_recalculate_order_data() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $this->recalculate_order((int) $_REQUEST['order_id']);
        wp_die('done');
    }

    public function woocs_recalculate_orders_data() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $orders = array();
        if (isset($_POST['order_ids'])) {
            $orders = $_POST['order_ids'];
        } else {
            return;
        }
        foreach ($orders as $id) {
            $this->recalculate_order((int) $id);
        }
        wp_die('done');
    }

//***************** BEGIN ADDITIONAL INFO HTML ON THE CHECKOUT+CART ***************
//only attach some info in html
//wp-content\plugins\woocommerce\templates\cart\cart.php
    public function woocommerce_cart_item_price($product_price, $cart_item, $cart_item_key) {
        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
        $currencies = $this->get_currencies();

        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {
            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);

//***
            $back_convert = true;
            if ($user_currency == $this->default_currency) {
                $back_convert = false;
            }
            if ($this->is_multiple_allowed) {
                $back_convert = true;
            }
            if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                $back_convert = false;
            }
//***
            $_price = $cart_item['line_total']; //or  ["line_subtotal"]
            //fix tax
            if ('incl' === WC()->cart->tax_display_cart) {
                $_price = $cart_item['line_total'] + $cart_item['line_tax'];
            }


            if ($back_convert) {
                $cart_price = $this->back_convert($_price, $currencies[$tmp_curr_currency]['rate']) / $cart_item['quantity'];
            } else {
                $cart_price = $_price / $cart_item['quantity'];
            }
            if ($this->is_fixed_enabled) {
                //$cart_price=$cart_item['data']->get_price()/$currencies[$this->current_currency]['rate'];
            }
            $wc_price = $this->wc_price($cart_price, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));
            $product_price .= $this->get_cart_item_price_html($wc_price);


            $this->set_currency($tmp_curr_currency);
        }

        return $product_price;
    }

//wp-content\plugins\woocommerce\templates\cart\cart.php
    public function woocommerce_cart_item_subtotal($product_subtotal, $cart_item, $cart_item_key) {
        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
        $currencies = $this->get_currencies();

        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {
            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);

            $cart_amount = $cart_item['line_subtotal'];
            //fix tax
            if ('incl' === WC()->cart->tax_display_cart) {
                $cart_amount = $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];
            }
//***
            $back_convert = true;
            if ($user_currency == $this->default_currency) {
                $back_convert = false;
            }
            if ($this->is_multiple_allowed) {
                $back_convert = true;
            }
            if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                $back_convert = false;
            }
//***

            if ($back_convert) {
                $cart_amount = $this->back_convert($cart_amount, $currencies[$tmp_curr_currency]['rate']);
            }


            $wc_price = $this->wc_price($cart_amount, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));
            $product_subtotal .= $this->get_cart_item_price_html($wc_price);


            $this->set_currency($tmp_curr_currency);
        }

        return $product_subtotal;
    }

//wp-content\plugins\woocommerce\templates\cart\cart-totals.php
    public function woocommerce_cart_subtotal($cart_subtotal, $compound, $woo) {
        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
        $currencies = $this->get_currencies();

        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {

            $amount = 0;
            if ($compound) {
                $amount = $woo->cart_contents_total + $woo->shipping_total + $woo->get_taxes_total(false, false);
// Otherwise we show cart items totals only (before discount)
            } else {

// Display varies depending on settings
                if ($woo->tax_display_cart == 'excl') {
                    $amount = $woo->subtotal_ex_tax;
                } else {
                    $amount = $woo->subtotal;
                }
            }
//***

            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);

//***
            $back_convert = true;
            if ($user_currency == $this->default_currency) {
                $back_convert = false;
            }
            if ($this->is_multiple_allowed) {
                $back_convert = true;
            }
            if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                $back_convert = false;
            }
//***

            if ($back_convert) {
                $amount = $this->back_convert($amount, $currencies[$tmp_curr_currency]['rate']);
            }

            $wc_price = $this->wc_price($amount, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));
            $cart_subtotal .= $this->get_cart_item_price_html($wc_price);


            $this->set_currency($tmp_curr_currency);
        }

        return $cart_subtotal;
    }

//wp-content\plugins\woocommerce\includes\class-wc-cart.php
    public function woocommerce_cart_total($html_value) {

        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
        $currencies = $this->get_currencies();

//***
        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {

            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);
            $total = WC()->cart->total;

//***
            $back_convert = true;
            if ($user_currency == $this->default_currency) {
                $back_convert = false;
            }
            if ($this->is_multiple_allowed) {
                $back_convert = true;
            }
            if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                $back_convert = false;
            }
//***

            if ($back_convert) {
                $total = $this->back_convert($total, $currencies[$tmp_curr_currency]['rate']);
            }

            $wc_price = $this->wc_price($total, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));
            $html_value .= $this->get_cart_item_price_html($wc_price);

            $this->set_currency($tmp_curr_currency);
        }

        return $html_value;
    }

//wp-content\plugins\woocommerce\includes\class-wc-cart.php
    public function woocommerce_cart_totals_taxes_total_html($html_value) {

        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
        $currencies = $this->get_currencies();

        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {
            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);

            $total = 0;
            $compound = true;
            foreach (WC()->cart->taxes as $key => $tax) {
                if (!$compound && WC_Tax::is_compound($key))
                    continue;
                $total += $tax;
            }
            foreach (WC()->cart->shipping_taxes as $key => $tax) {
                if (!$compound && WC_Tax::is_compound($key))
                    continue;
                $total += $tax;
            }


//***
            $back_convert = true;
            if ($user_currency == $this->default_currency) {
                $back_convert = false;
            }
            if ($this->is_multiple_allowed) {
                $back_convert = true;
            }
            if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                $back_convert = false;
            }
//***

            if ($back_convert) {
                $total = $this->back_convert($total, $currencies[$tmp_curr_currency]['rate']);
            }

            $wc_price = $this->wc_price($total, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));
            $html_value .= $this->get_cart_item_price_html($wc_price);


            $this->set_currency($tmp_curr_currency);
        }


        return $html_value;
    }

    public function woocommerce_cart_tax_totals($tax_totals, $woo) {
//$woo is WC_Cart
        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
        $currencies = $this->get_currencies();

        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {
            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);

            if (!empty($tax_totals)) {
                foreach ($tax_totals as $key => $o) {

                    $amount = $o->amount;

//***
                    $back_convert = true;
                    if ($user_currency == $this->default_currency) {
                        $back_convert = false;
                    }
                    if ($this->is_multiple_allowed) {
                        $back_convert = true;
                    }
                    if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                        $back_convert = false;
                    }
//***

                    if ($back_convert) {
                        $amount = $this->back_convert($amount, $currencies[$tmp_curr_currency]['rate']);
                    }

                    $wc_price = $this->wc_price($amount, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));
                    $o->formatted_amount .= $this->get_cart_item_price_html($wc_price);
                }
            }


            $this->set_currency($tmp_curr_currency);
        }



        return $tax_totals;
    }

    public function woocs_add_approx_to_price($price_html, $product) {
        $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));

        if ($user_currency != $this->current_currency AND ! empty($user_currency)) {
            $info = "";

            $currencies = $this->get_currencies();
            $tmp_curr_currency = $this->current_currency;
            $this->set_currency($user_currency);
            $value = (float) $product->get_price('edit') * (float) $currencies[$user_currency]['rate'];

            $precision = $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals);
            $value = number_format($value, $precision, $this->decimal_sep, '');

            //***

            $product_type = '';

            $product_type = $product->get_type();

            if ($product_type == 'variable') {


                $min_value = $product->get_variation_price('min', true) * $currencies[$user_currency]['rate'];
                $max_value = $product->get_variation_price('max', true) * $currencies[$user_currency]['rate'];

                //***
                $min_max_values = $this->_get_min_max_variation_prices($product, $user_currency);
                if (!empty($min_max_values)) {
                    //echo '<pre>' . print_r($min_max_values, true) . '</pre>';
                    $min_value = $min_max_values['min'] /* $currencies[$сurr['name']]['rate'] */;
                    $max_value = $min_max_values['max'] /* $currencies[$сurr['name']]['rate'] */;
                }
                if (wc_tax_enabled()) {
                    $min_value = $this->woocs_calc_tax_price($product, $min_value);
                    $max_value = $this->woocs_calc_tax_price($product, $max_value);
                }
                //+++
                $_REQUEST['woocs_wc_price_convert'] = FALSE;

                $var_price = "";
                $var_price1 = $this->wc_price($min_value, array('currency' => $user_currency), false, $product, $precision);
                $var_price2 = $this->wc_price($max_value, array('currency' => $user_currency), false, $product, $precision);
                if ($var_price1 == $var_price2) {
                    $var_price = $var_price1;
                } else {
                    $var_price = sprintf("%s - %s", $var_price1, $var_price2);
                }

                unset($_REQUEST['woocs_wc_price_convert']);
                $info .= $var_price;
            } elseif ($product_type == 'grouped') {

                $child_ids = $product->get_children();
                $prices = array();
                foreach ($child_ids as $prod_id) {
                    $product1 = wc_get_product($prod_id);
                    $product_type1 = $product1->get_type();
                    if ($product_type1 == 'variable') {

                        $min_value = $product1->get_variation_price('min', true) * $currencies[$user_currency]['rate'];
                        $max_value = $product1->get_variation_price('max', true) * $currencies[$user_currency]['rate'];
                        //***
                        $min_max_values = $this->_get_min_max_variation_prices($product1, $user_currency);
                        if (!empty($min_max_values)) {
                            $min_value = $min_max_values['min'] /* $currencies[$сurr['name']]['rate'] */;
                            $max_value = $min_max_values['max'] /* $currencies[$сurr['name']]['rate'] */;
                        }
                        if (wc_tax_enabled()) {
                            $prices[] = $this->woocs_calc_tax_price($product1, $min_value);
                            $prices[] = $this->woocs_calc_tax_price($product1, $max_value);
                        } else {
                            $prices[] = $min_value;
                            $prices[] = $max_value;
                        }
                    } else {

                        if ($this->is_fixed_enabled AND $this->is_multiple_allowed) {
                            $type = 'sale';
                            $is_empty = $this->fixed->is_empty($prod_id, $user_currency, $type);
                            $is_exists = $this->fixed->is_exists($prod_id, $user_currency, $type);

                            if ($type == 'sale' AND $is_empty) {
                                $type = 'regular';
                                $is_exists = $this->fixed->is_exists($prod_id, $user_currency, $type);
                                $is_empty = $this->fixed->is_empty($prod_id, $user_currency, $type);
                            }

                            if ($is_exists AND ! $is_empty) {
                                $special_convert = true;
                                $is_price_custom = true;
                                if (floatval($this->fixed->get_value($prod_id, $user_currency, $type)) > 0) {

                                    if (wc_tax_enabled()) {
                                        $prices[] = $this->woocs_calc_tax_price($product1, floatval($this->fixed->get_value($prod_id, $user_currency, $type)));
                                    } else {
                                        $prices[] = floatval($this->fixed->get_value($prod_id, $user_currency, $type));
                                    }
                                }
                            } else {
                                if (wc_tax_enabled()) {
                                    $prices[] = $this->woocs_calc_tax_price($product1, $product1->get_price('edit') * $currencies[$user_currency]['rate']);
                                } else {
                                    $prices[] = $product1->get_price('edit') * $currencies[$user_currency]['rate'];
                                }
                            }
                        } else {
                            if (wc_tax_enabled()) {
                                $prices[] = $this->woocs_calc_tax_price($product1, $product1->get_price('edit') * $currencies[$user_currency]['rate']);
                            } else {
                                $prices[] = $product1->get_price('edit') * $currencies[$user_currency]['rate'];
                            }
                        }
                    }
                }
                asort($prices);
                $_REQUEST['woocs_wc_price_convert'] = FALSE;
                $var_price = "";
                $var_price1 = $this->wc_price(array_shift($prices), array('currency' => $user_currency), false, $product, $precision);
                $var_price2 = $this->wc_price(array_pop($prices), array('currency' => $user_currency), false, $product, $precision);

                if ($var_price1 == $var_price2) {
                    $var_price = $var_price1;
                } else {
                    $var_price = sprintf("%s - %s", $var_price1, $var_price2);
                }

                $info .= $var_price;
            } else {

                if (wc_tax_enabled()) {
                    $value = $this->woocs_calc_tax_price($product, $value);
                }
                $info .= $this->wc_price($value, false, array('currency' => $user_currency), $product, $precision);
            }
            $info = $this->get_cart_item_price_html($info);
            $this->set_currency($tmp_curr_currency);

            //***

            $add_icon = strripos($price_html, $info);
            if ($add_icon === false) {
                $price_html .= $info;
            }
        }

        return $price_html;
    }

//wp-content\plugins\woocommerce\includes\wc-cart-functions.php
    public function woocommerce_cart_shipping_method_full_label($label, $method) {
//$woo is WC_Cart
        if ($method->cost > 0) {
            $user_currency = $this->get_currency_by_country($this->storage->get_val('woocs_user_country'));
            $currencies = $this->get_currencies();

            if ($user_currency != $this->current_currency AND ! empty($user_currency)) {
                $tmp_curr_currency = $this->current_currency;
                $this->set_currency($user_currency);

                if (WC()->cart->tax_display_cart == 'excl') {
                    $amount = $method->cost;
                } else {
                    $amount = $method->cost + $method->get_shipping_tax();
                }

//***
                $back_convert = true;
                if ($user_currency == $this->default_currency) {
                    $back_convert = false;
                }
                if ($this->is_multiple_allowed) {
                    $back_convert = true;
                }
                if (!$this->is_multiple_allowed AND ( $user_currency !== $this->default_currency)) {
                    $back_convert = false;
                }
//***

                if ($back_convert) {
                    $amount = $this->back_convert($amount, $currencies[$tmp_curr_currency]['rate']);
                }

                $wc_price = $this->wc_price($amount, true, array('decimals' => $this->get_currency_price_num_decimals($user_currency, $this->price_num_decimals)));

                $label .= $this->get_cart_item_price_html($wc_price);

                $this->set_currency($tmp_curr_currency);
            }
        }

        return $label;
    }

    private function get_cart_item_price_html($wc_price) {
        $html = '<div class="woocs_cart_item_price">';
        $html .= apply_filters('woocs_get_approximate_amount_text', sprintf(esc_html__('(Approx. %s)', 'woocommerce-currency-switcher'), $wc_price), $wc_price);
        $html .= '</div>';
        return $html;
    }

//***************** END ADDITIONAL INFO HTML ON THE CHECKOUT+CART ***************
//custom code for Woocommerce Advanced Shipping by http://jeroensormani.com/ in multiple mode
    public function woocommerce_cart_get_taxes($taxes, $woo_cart) {
        if ($this->is_multiple_allowed AND $this->current_currency != $this->default_currency) {
            $currencies = $this->get_currencies();
            if (!empty($woo_cart->shipping_taxes)) {
//as it recounted twice - down it!
                foreach ($woo_cart->shipping_taxes as $key => $value) {
                    $woo_cart->shipping_taxes[$key] = $value * $currencies[$this->current_currency]['rate'];
                }
            }
// Merge
            foreach (array_keys($woo_cart->taxes + $woo_cart->shipping_taxes) as $key) {
                $taxes[$key] = ( isset($woo_cart->shipping_taxes[$key]) ? $woo_cart->shipping_taxes[$key] : 0 ) + ( isset($woo_cart->taxes[$key]) ? $woo_cart->taxes[$key] : 0 );
            }
        }
        return $taxes;
    }

//class-wc-cart.php -> public function calculate_totals()
    public function woocommerce_after_calculate_totals($woo_cart) {
        if ($this->is_multiple_allowed AND $this->current_currency != $this->default_currency AND is_ajax()) {
            if (isset($_POST['billing_address_1'])) {
                $currencies = $this->get_currencies();
                if (!empty($woo_cart->shipping_taxes)) {
//as it recounted twice - down it!
                    foreach ($woo_cart->shipping_taxes as $key => $value) {
                        $woo_cart->shipping_taxes[$key] = $value * $currencies[$this->current_currency]['rate'];
                    }
                }
// Merge
                foreach (array_keys($woo_cart->taxes + $woo_cart->shipping_taxes) as $key) {
                    $woo_cart->taxes[$key] = ( isset($woo_cart->shipping_taxes[$key]) ? $woo_cart->shipping_taxes[$key] : 0 ) + ( isset($woo_cart->taxes[$key]) ? $woo_cart->taxes[$key] : 0 );
                }

//***

                $total = $woo_cart->total;
                $currencies = $this->get_currencies();
                if (!empty($woo_cart->shipping_taxes)) {
//as it recounted twice - down it!
                    foreach ($woo_cart->shipping_taxes as $key => $value) {
                        $total = $total - ($value / $currencies[$this->current_currency]['rate'] - $value);
                    }
                }

                $woo_cart->total = $total;
            }
        }
    }

    public function escape($value) {
        return sanitize_text_field(esc_html($value));
    }

    public function wc_get_template($located, $template_name, $args, $template_path, $default_path) {
        if (isset($args['order'])) {
            if (is_object($args['order']) AND ! is_null($args['order'])) {
                $order = $args['order'];
                if (substr($template_name, 0, 6) === 'emails') {
                    if (method_exists($order, 'get_currency')) {
                        $this->set_currency($order->get_currency());
                    }
                }
            }
        }

        return $located;
    }

    public function woocommerce_fix_shipping_calc($arg, $sum, $_this) {
        $rate = 1;
        if ($this->is_multiple_allowed) {
            $currencies = $this->get_currencies();
            $rate = $currencies[$this->current_currency]['rate'];
            if (!$rate OR $rate == 0) {
                $rate = 1;
            }
            $arg['cost'] = $arg['cost'] / $rate;
        }
        return $arg;
    }

    public function woocs_fix_decimals($code) {
        global $wp_filter;
        $functions = debug_backtrace();
        foreach ($functions as $funcs) {
            if ($funcs['function'] == 'add_rate') {
                $decimal = 2;
                $decimal = $this->get_currency_price_num_decimals($this->default_currency);
                return $decimal;
            }
        }
        return $code;
    }

    //Thank you @jonathanmoorebcsorg !!!
    //https://wordpress.org/support/topic/variations-show-bogus-sale-price/
    public function raw_sale_price_filter($price, $product = NULL) {
        return ($price == '') ? '' : $this->raw_woocommerce_price($price, $product);
    }

    function woocs_woocommerce_cart_price_html($price_html, $product = null) {

        static $customer_price_format = -1;
        if ($customer_price_format === -1) {
            $customer_price_format = get_option('woocs_customer_price_format', '__PRICE__');
        }
        $currencies = $this->get_currencies();
        if (empty($customer_price_format)) {
            $customer_price_format = '__PRICE__';
        }
        if (!empty($customer_price_format)) {
            $txt = '<span class="woocs_special_price_code" >' . $customer_price_format . '</span>';
            $txt = str_replace('__PRICE__', $price_html, $txt);
            $price_html = str_replace('__CODE__', $this->current_currency, $txt);
            $price_html = apply_filters('woocs_price_html_tail', $price_html);
        }

        return $price_html;
    }

    //notices functions
    public function init_style_notice() {
        if (is_admin()) {
            global $wp_version;
            if (version_compare($wp_version, '4.2', '>=') && (int) get_option('woocs_first_activation', 0) == 1) {
                $alert = (array) get_option('woocs_alert_notice', array());
                if (empty($alert['woocommerce-currency-switcher'])) {
                    add_action('admin_notices', array($this, 'woocs_alert'));
                    add_action('network_admin_notices', array($this, 'woocs_alert'));
                    add_action('wp_ajax_woocs_dismiss_alert', array($this, 'woocs_dismiss_alert'));
                }
                //enqueue admin/js/updates.js
            }
        }
    }

    function woocs_alert() {

        $alert = get_option('woocs_alert_rev', 0);
        if (intval($alert) === 0) {
            update_option('woocs_alert_rev', time());
            delete_option('woocs_alert_notice');
        }

        //***

        if (intval(get_option('woocs_alert_rev')) + 86400 * 14 <= time()) {//14 days
            if (isset($_GET['tab']) AND $_GET['tab'] == 'woocs') {
                $support_link = 'https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/';
                ?>
                <div class="notice notice-warning is-dismissible" id="woocs_alert_notice" data-nonce="<?php echo json_encode(wp_create_nonce('woocs_dissmiss_alert')) ?>">
                    <p class="plugin-card-woocommerce-currency-switcher">
                        <?php printf(__("Hello! Looks like you using <b>WooCommerce Currency Switcher</b> for some time and I hope this software helped you with your business. If you happy with the plugin functionality and like Currency Switcher - rate please WOOCS with 5-stars, also share your opinion and ideas with us. Thank you!<br /> P.S. If you have troubles you can always ask %s about help. Thank you!", 'woocommerce-currency-switcher'), "<a href='{$support_link}' target='_blank'>" . __('support', 'woocommerce-currency-switcher') . "</a>") ?>
                    </p>

                    <hr />

                    <?php
                    $link = 'https://codecanyon.net/downloads#item-8085217';
                    if ($this->notes_for_free) {
                        $link = 'https://wordpress.org/support/plugin/woocommerce-currency-switcher/reviews/#new-post';
                    }
                    ?>


                    <table style="width: 100%; margin-bottom: 7px;">
                        <tr>
                            <td style="width: 33%; text-align: center;">
                                <a href="<?= $link ?>" target="_blank" class="woocs-panel-button dashicons-before dashicons-star-filled">&nbsp;<?php echo __('Write marvellous review about WOOCS features', 'woocommerce-currency-switcher') ?></a>
                            </td>

                            <td style="width: 33%; text-align: center;">
                                <a href="javascript: jQuery('#woocs_alert_notice .notice-dismiss').trigger('click');void(0);" class="button button-large dashicons-before dashicons-thumbs-up">&nbsp;<?php echo __('It is done!', 'woocommerce-currency-switcher') ?></a>
                            </td>

                            <td style="width: 33%; text-align: center;">
                                <a href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" target="_blank" class="woocs-panel-button dashicons-before dashicons-hammer"><?php echo __('WooCommerce Currency Switcher SUPPORT', 'woocommerce-currency-switcher') ?></a>
                            </td>
                        </tr>
                    </table>


                </div>

                <?php
                wp_enqueue_script('woocs-alert', WOOCS_LINK . 'js/alert.js', array('jquery'), WOOCS_VERSION);
            }
        }
    }

    function woocs_dismiss_alert() {
        check_ajax_referer('woocs_dissmiss_alert', 'sec');
        $alert = (array) get_option('woocs_alert_notice', array());
        $alert[$_POST['alert']] = 1;

        add_option('woocs_alert_notice', $alert, '', 'no');
        update_option('woocs_alert_notice', $alert);

        exit;
    }

    public function woocs_prepare($query, $args) {
        if (is_null($query)) {
            return;
        }
        $sql_val = array();

        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = preg_replace('|(?<!%)%f|', '%F', $query); // Force floats to be locale unaware
        $query = preg_replace('|(?<!%)%s|', "'%s'", $query); // quote the strings, avoiding escaped strings like %%s
        if (!is_array($args)) {
            $args = array('val' => $args, 'type' => 'string');
        }
        foreach ($args as $item) {

            if (!is_array($item) OR ! isset($item['val'])) {
                continue;
            }
            if (!isset($item['type'])) {
                $item['type'] = 'string';
            }
            $sql_val[] = $this->woocs_escape_sql($item['type'], $item['val']);
        }
        return @vsprintf($query, $sql_val);
    }

    public function woocs_escape_sql($type, $value) {
        switch ($type) {
            case'string':
                global $wpdb;
                return $wpdb->_real_escape($value);
                break;
            case'int':
                return intval($value);
                break;
            case'float':
                return floatval($value);
                break;
            default :
                global $wpdb;
                return $wpdb->_real_escape($value);
        }
    }

    public function check_currency_on_checkout() {
        if (!$this->is_multiple_allowed) {
            $curr_curr = $this->default_currency;
            $this->current_currency = $curr_curr;
            $this->storage->set_val('woocs_current_currency', $curr_curr);
        }
    }

    //compatibilites
    function woocs_convert_price($amount, $is_cond_multi = false) {
        if (get_option('woocs_is_multiple_allowed', 0) AND $is_cond_multi) {
            return $this->woocs_exchange_value($amount);
        } elseif ($is_cond_multi == false) {
            return $this->woocs_exchange_value($amount);
        }
        return $amount;
    }

    function woocs_back_convert_price($amount, $is_cond_multi = false) {
        $currencies = $this->get_currencies();
        $curr_currency = $this->current_currency;
        if (get_option('woocs_is_multiple_allowed', 0) AND $is_cond_multi) {
            return $this->back_convert($amount, $currencies[$curr_currency]['rate'], $currencies[$curr_currency]['decimals']);
        } elseif ($is_cond_multi == false) {
            return $this->back_convert($amount, $currencies[$curr_currency]['rate'], $currencies[$curr_currency]['decimals']);
        }
        return $amount;
    }

    function woocs_convert_price_wcdp($amount, $is_cond_multi = false, $method) {
        if ($method != 'discount__amount') {
            return $amount;
        }
        if (get_option('woocs_is_multiple_allowed', 0) AND $is_cond_multi) {
            return $this->woocs_exchange_value($amount);
        } elseif ($is_cond_multi == false) {
            return $this->woocs_exchange_value($amount);
        }
        return $amount;
    }

    //fix woo 3.3.0
    function woocommerce_coupon_loaded($coupon) {

        if (!$this->is_multiple_allowed OR $this->current_currency == $this->default_currency) {
            return $coupon;
        }
        $convert = false;
        $prices = array();
        $count_id = $coupon->get_id();
        $prices['amount'] = $coupon->get_amount();
        $prices['min_spend'] = $coupon->get_minimum_amount();
        $prices['max_spend'] = $coupon->get_maximum_amount();
        if (!$coupon->is_type('percent_product') AND ! $coupon->is_type('percent')) {
            $convert = true;
        }
        //convert
        foreach ($prices as $key => $val) {
            if (!('amount' == $key AND ! $convert)) {
                $prices[$key] = $this->woocs_exchange_value($val);
            }
            if ($this->is_fixed_coupon) {//fixed coupon
                if ($this->fixed_coupon->is_exists($count_id, $this->current_currency, $key)) {
                    $tmp_amount = floatval($this->fixed_coupon->get_value($count_id, $this->current_currency, $key));
                    if ((int) $tmp_amount !== -1) {
                        $prices[$key] = $tmp_amount;
                    }
                }
                if ((float) $prices[$key] === 0.0) {
                    $prices[$key] == "";
                }
            }
        }
        //+++
        $coupon->set_minimum_amount($prices['min_spend']);
        $coupon->set_maximum_amount($prices['max_spend']);
        $coupon->set_amount($prices['amount']);

        return $coupon;
    }

    //fix woo 3.3.0
    function woocs_calc_tax_price($product, $price) {
        if ($product AND $product->is_taxable()) {
            return wc_get_price_to_display($product, array("qty" => 1, "price" => $price));
        } else {
            return $price;
        }
    }

    function woocs_before_calculate_totals_geoip_fix() {
        if ($this->force_pay_bygeoip_rules) {
            if (isset($_SERVER['REQUEST_URI'])) {
                if (substr_count($_SERVER['REQUEST_URI'], '/checkout/')) {
                    $this->force_pay_bygeoip_rules();
                }
            }
        }
    }

    public function disable_woo_slider_script() {
        wp_dequeue_script('wc-price-slider');
    }

    public function prepare_default_currencies() {

        $default = array(
            'USD' => array(
                'name' => 'USD',
                'rate' => 1,
                'symbol' => '&#36;',
                'position' => 'right',
                'is_etalon' => 0,
                'description' => 'USA dollar',
                'hide_cents' => 0,
                'hide_on_front' => 0,
                'flag' => '',
            ),
        );
        $wc_currency = get_option('woocommerce_currency');

        switch ($wc_currency) {
            case 'USD':
                $default['EUR'] = array(
                    'name' => 'EUR',
                    'rate' => 0.89,
                    'symbol' => '&euro;',
                    'position' => 'left_space',
                    'is_etalon' => 0,
                    'description' => 'European Euro',
                    'hide_cents' => 0,
                    'hide_on_front' => 0,
                    'flag' => '',
                );
                $default['USD']['is_etalon'] = 1;
                break;
            case 'EUR':
                $default['EUR'] = array(
                    'name' => 'EUR',
                    'rate' => 1,
                    'symbol' => '&euro;',
                    'position' => 'left_space',
                    'is_etalon' => 1,
                    'description' => 'European Euro',
                    'hide_cents' => 0,
                    'hide_on_front' => 0,
                    'flag' => '',
                );
                $default['USD']['rate'] = 1.15;
                break;
            default :
                $default[$wc_currency] = array(
                    'name' => $wc_currency,
                    'rate' => 1,
                    'symbol' => $this->get_default_currency_symbol($wc_currency),
                    'position' => 'left_space',
                    'is_etalon' => 1,
                    'description' => '',
                    'hide_cents' => 0,
                    'hide_on_front' => 0,
                    'flag' => '',
                );
                $default['USD']['rate'] = 1.15;
                $default['USD']['description'] = esc_html__('change the rate and this description to the right values', 'woocommerce-currency-switcher');
                break;
        }

        return $default;
    }

    //just need it to set default data after the plugin installing
    public function get_default_currency_symbol($currency) {
        $symbols = $this->get_symbols_set();
        return isset($symbols[$currency]) ? $symbols[$currency] : '&#36;';
    }

    public function get_symbols_set() {
        return array(
            'USD' => '&#36;',
            'EUR' => '&euro;',
            'GBP' => '&pound;',
            'UAH' => '&#1075;&#1088;&#1085;.',
            'RUB' => '&#1088;&#1091;&#1073;.',
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => 'Afl.',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BYN' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GEL' => '&#x10da;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'MDL',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STD' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UGX' => 'UGX',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'CFA',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK'
        );
    }

    public function woocs_all_order_ids() {
        $query_args = array(
            'post_type' => wc_get_order_types(),
            'post_status' => array_keys(wc_get_order_statuses()),
            'posts_per_page' => 999999999999,
        );
        $order_ids = array();
        $all_orders = get_posts($query_args);
        foreach ($all_orders as $order) {
            $order_ids[] = $order->ID;
        }
        die(json_encode($order_ids));
    }

    public function woocommerce_admin_order_preview_line_items($items, $order) {
        if ($this->is_multiple_allowed) {
            $order_currency = get_post_meta($order->get_id(), '_order_currency', true);
            if ($order_currency AND $this->current_currency != $order_currency) {
                $this->set_currency($order_currency);
            }
        } else {
            $this->set_currency($this->default_currency);
        }

        return $items;
    }

    function woocs_filter_gateways($gateway_list) {
        global $WOOCS;
        if (is_checkout() OR is_checkout_pay_page()) {
            $exclude = get_option('woocs_payments_rules', array());
            if (!is_array($exclude)) {
                $exclude = array();
            }
            foreach ($exclude as $gateway_key => $currencies) {
                $behavior = true;
                $behavior = in_array($WOOCS->current_currency, $currencies);
                if (get_option('woocs_payment_control', 0)) {
                    $behavior = !$behavior;
                }

                if (isset($gateway_list[$gateway_key]) AND $behavior) {
                    unset($gateway_list[$gateway_key]);
                }
            }
        }
        return $gateway_list;
    }

    function manage_posts_extra_tablenav($width) {
        global $typenow;
        if (get_option('woocs_is_multiple_allowed', 0)) {
            if (in_array($typenow, wc_get_order_types('order-meta-boxes'), true) AND $width == 'top') {
                ?>
                <a href="javascript:woocs_recalculate_all_orders_data();void(0);" class="button woocs_recalculate_all_orders_curr_button"><?php esc_html_e("Recalculate all orders", 'woocommerce-currency-switcher') ?>&nbsp;<img class="help_tip" data-tip="WOOCS: <?php esc_html_e('Recalculate all orders with basic currency. Recommended test this option on the clone of your site! Read the documentation of the plugin about it!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /><img class="woocs_ajax_preload" src="<?php echo WOOCS_LINK ?>/img/loading_large.gif" height="18" width="18" /></a>
                <?php
            }
        }
    }

    public function woocs_set_currency_ajax() {
        if (isset($_REQUEST['currency']) AND ! $this->is_currency_private($_REQUEST['currency'])) {
            $currency = sanitize_text_field($_REQUEST['currency']);
            $this->set_currency($currency);
            $this->statistic->register_switch(strtoupper($this->escape($currency)), strtoupper($this->storage->get_val('woocs_user_country')));
        }
    }

    public function init_marketig_woocs() {
        $alert = new WOOCS_ADV();
        $alert->init();
    }

    public function is_currency_private($currency) {
        $currencies = $this->get_currencies();
        return isset($currencies[$currency]['hide_on_front']) AND $currencies[$currency]['hide_on_front'];
    }

}
