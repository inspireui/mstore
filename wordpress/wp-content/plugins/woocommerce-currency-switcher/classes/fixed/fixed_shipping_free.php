<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

class WOOCS_FIXED_SHIPPING_FREE extends WOOCS_FIXED_AMOUNT {

    protected $key = "";

    public function __construct() {
        $this->key = "_min_shipping_";
        add_filter('woocommerce_shipping_instance_form_fields_free_shipping', array($this, 'add_fixed_free_rate'), 9999, 1);
        add_filter('woocommerce_shipping_free_shipping_instance_settings_values', array($this, 'save_fixed_free_rate'), 9999, 2);
    }

    public function add_fixed_free_rate($fields) {

        global $WOOCS;
        $currencies = $WOOCS->get_currencies();
        $default_currency = $WOOCS->default_currency;
        $is_fixed_enabled = $WOOCS->is_fixed_shipping;

        foreach ($currencies as $code => $data) {
            if ($code == $default_currency) {
                continue;
            }
            $fields['woocs_fixed' . $this->key . $code] = array(
                'title' => sprintf(esc_html__('Minimum order amount in %s', 'woocommerce-currency-switcher'), $code),
                'type' => 'price',
                'placeholder' => esc_html__("auto", 'woocommerce-currency-switcher'),
                'description' => $code,
                'default' => '',
                'desc_tip' => true
            );
        }
        wc_enqueue_js("
        		jQuery( function( $ ) {
                            function wcFreeShippingShowHideMinAmountFieldWOOCS( el ) {
				var form = $( el ).closest( 'form' );
				var minAmountField = $( 'input[id^=woocommerce_free_shipping_woocs_fixed_min_shipping_]', form ).closest( 'tr' );
				if ( 'coupon' === $( el ).val() || '' === $( el ).val() ) {
                                    minAmountField.hide();
				} else {
                                    minAmountField.show();
				}
			}

			$( document.body ).on( 'change', '#woocommerce_free_shipping_requires', function() {
                            wcFreeShippingShowHideMinAmountFieldWOOCS( this );
			});

			// Change while load.
			$( '#woocommerce_free_shipping_requires' ).change();
                            $( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
				if ( 'wc-modal-shipping-method-settings' === target ) {
                                    wcFreeShippingShowHideMinAmountFieldWOOCS( $( '#wc-backbone-modal-dialog #woocommerce_free_shipping_requires', evt.currentTarget ) );
				}
                            } );
			});
	");

        return $fields;
    }

    public function save_fixed_free_rate($options, $method) {
        return $options;
    }

    public function get_value($method_key, $code, $type) {

        $settings = get_option($method_key, null);
        if ($settings == null OR ! is_array($settings)) {
            return -1;
        }
        $array_key = sprintf('woocs_fixed%s%s%s', $type, $this->key, $code);
        if (!isset($settings[$array_key])) {
            return -1;
        }
        return $this->prepare_float_val($settings[$array_key]);
    }

}
