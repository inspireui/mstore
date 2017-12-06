<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="woocs-admin-preloader"></div>
<div class="subsubsub_section">
    <br class="clear" />

    <?php
    global $WOOCS;

    $welcome_curr_options = array();
    if (!empty($currencies) AND is_array($currencies)) {
        foreach ($currencies as $key => $currency) {
            $welcome_curr_options[$currency['name']] = $currency['name'];
        }
    }
    //+++
    $pd = array();
    $countries = array();
    if (class_exists('WC_Geolocation')) {
        $c = new WC_Countries();
        $countries = $c->get_countries();
        $pd = WC_Geolocation::geolocate_ip();
    }
    //+++
    $options = array(
        array(
            'name' => '',
            'type' => 'title',
            'desc' => '',
            'id' => 'woocs_general_settings'
        ),
        array(
            'name' => __('Drop-down view', 'woocommerce-currency-switcher'),
            'desc' => __('How to display currency switcher drop-down on the front of your site', 'woocommerce-currency-switcher'),
            'id' => 'woocs_drop_down_view',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                'ddslick' => __('ddslick', 'woocommerce-currency-switcher'),
                'chosen' => __('chosen', 'woocommerce-currency-switcher'),
                'chosen_dark' => __('chosen dark', 'woocommerce-currency-switcher'),
                'wselect' => __('wSelect', 'woocommerce-currency-switcher'),
                'no' => __('simple drop-down', 'woocommerce-currency-switcher'),
                'flags' => __('show as flags', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Show flags by default', 'woocommerce-currency-switcher'),
            'desc' => __('Show/hide flags on the front drop-down', 'woocommerce-currency-switcher'),
            'id' => 'woocs_show_flags',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Show money signs', 'woocommerce-currency-switcher'),
            'desc' => __('Show/hide money signs on the front drop-down', 'woocommerce-currency-switcher'),
            'id' => 'woocs_show_money_signs',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Show price info icon', 'woocommerce-currency-switcher'),
            'desc' => __('Show info icon near the price of the product which while its under hover shows prices of products in all currencies', 'woocommerce-currency-switcher'),
            'id' => 'woocs_price_info',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Welcome currency', 'woocommerce-currency-switcher'),
            'desc' => __('In wich currency show prices for first visit of your customer on your site', 'woocommerce-currency-switcher'),
            'id' => 'woocs_welcome_currency',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => $welcome_curr_options,
            'desc_tip' => true
        ),
        array(
            'name' => __('Currency aggregator', 'woocommerce-currency-switcher'),
            'desc' => __('Currency aggregators', 'woocommerce-currency-switcher'),
            'id' => 'woocs_currencies_aggregator',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                //'yahoo' => 'www.finance.yahoo.com',
                'google' => 'www.google.com/finance',
                'ecb' => 'www.ecb.europa.eu',
                'rf' => 'www.cbr.ru - russian centrobank',
                'privatbank' => 'api.privatbank.ua - ukrainian privatbank',
                'bank_polski' => 'Narodowy Bank Polsky',
                'free_converter' => 'The Free Currency Converter',
            ),
            'desc_tip' => true
        ),
        /*
          array(
          'name' => __('CURL for aggregators', 'woocommerce-currency-switcher'),
          'desc' => __('You can use it if aggregators doesn works with file_get_contents because of security reasons. If all is ok leave it No!', 'woocommerce-currency-switcher'),
          'id' => 'woocs_use_curl',
          'type' => 'select',
          'class' => 'chosen_select',
          'css' => 'min-width:300px;',
          'options' => array(
          0 => __('No', 'woocommerce-currency-switcher'),
          1 => __('Yes', 'woocommerce-currency-switcher')
          ),
          'desc_tip' => true
          ),
         */
        array(
            'name' => __('Currency storage', 'woocommerce-currency-switcher'),
            'desc' => __('In some servers there is troubles with sessions, and after currency selecting its reset to welcome currency or geo ip currency. In such case use transient!', 'woocommerce-currency-switcher'),
            'id' => 'woocs_storage',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                'session' => __('session', 'woocommerce-currency-switcher'),
                //'cookie' => __('cookie', 'woocommerce-currency-switcher'),
                'transient' => __('transient', 'woocommerce-currency-switcher')
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Rate auto update', 'woocommerce-currency-switcher'),
            'desc' => __('Currencies rate auto update by wp cron. Use it for your own risk!', 'woocommerce-currency-switcher'),
            'id' => 'woocs_currencies_rate_auto_update',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                'no' => __('no auto update', 'woocommerce-currency-switcher'),
                'hourly' => __('hourly', 'woocommerce-currency-switcher'),
                'twicedaily' => __('twicedaily', 'woocommerce-currency-switcher'),
                'daily' => __('daily', 'woocommerce-currency-switcher'),
                'week' => __('weekly', 'woocommerce-currency-switcher'),
                'month' => __('monthly', 'woocommerce-currency-switcher'),
                'min1' => __('special: each minute', 'woocommerce-currency-switcher'), //for tests
                'min5' => __('special: each 5 minutes', 'woocommerce-currency-switcher'), //for tests
                'min15' => __('special: each 15 minutes', 'woocommerce-currency-switcher'), //for tests
                'min30' => __('special: each 30 minutes', 'woocommerce-currency-switcher'), //for tests
                'min45' => __('special: each 45 minutes', 'woocommerce-currency-switcher'), //for tests
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Email notice about "Rate auto update" results', 'woocommerce-currency-switcher'),
            'desc' => __('After cron done - new currency rates will be sent on the site admin email. ATTENTION: if you not got emails - it is mean that PHP function mail() doesnt work on your server or sending emails by this function is locked.', 'woocommerce-currency-switcher'),
            'id' => 'woocs_rate_auto_update_email',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Hide switcher on checkout page', 'woocommerce-currency-switcher'),
            'desc' => __('Hide switcher on checkout page for any of your reason. Better restrike for users change currency on checkout page in multiple mode.', 'woocommerce-currency-switcher'),
            'id' => 'woocs_restrike_on_checkout_page',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Show approx. amount', 'woocommerce-currency-switcher'),
            'desc' => __('THIS IS AN EXPERIMENTAL FEATURE! Show approximate amount on the checkout and the cart page with currency of user defined by IP in the GeoIp rules tab. Works only with currencies rates data and NOT with fixed prices rules and geo rules.', 'woocommerce-currency-switcher'),
            'id' => 'woocs_show_approximate_amount',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('I am using cache plugin on my site', 'woocommerce-currency-switcher'),
            'desc' => __('Set Yes here ONLY if you are REALLY use cache plugin for your site, for example like Super cache or Hiper cache (doesn matter). + Set "Custom price format", for example: __PRICE__ (__CODE__). After enabling this feature - clean your cache to make it works. It will allow show prices in selected currency on all pages of site. Fee for this feature - additional AJAX queries for products prices redrawing.', 'woocommerce-currency-switcher'),
            'id' => 'woocs_shop_is_cached',
            'type' => 'select',
            'class' => 'chosen_select',
            'css' => 'min-width:300px;',
            'options' => array(
                0 => __('No', 'woocommerce-currency-switcher'),
                1 => __('Yes', 'woocommerce-currency-switcher'),
            ),
            'desc_tip' => true
        ),
        array(
            'name' => __('Custom money signs', 'woocommerce-currency-switcher'),
            'desc' => __('Add your money symbols in your shop. Example: $USD,AAA,AUD$,DDD - separated by commas', 'woocommerce-currency-switcher'),
            'id' => 'woocs_customer_signs',
            'type' => 'textarea',
            'std' => '', // WooCommerce < 2.0
            'default' => '', // WooCommerce >= 2.0
            'css' => 'min-width:500px;',
            'desc_tip' => true
        ),
        array(
            'name' => __('Custom price format', 'woocommerce-currency-switcher'),
            'desc' => __('Set your format how to display price on front. Use keys: __CODE__,__PRICE__. Leave it empty to use default format. Example: __PRICE__ (__CODE__)', 'woocommerce-currency-switcher'),
            'id' => 'woocs_customer_price_format',
            'type' => 'text',
            'std' => '', // WooCommerce < 2.0
            'default' => '', // WooCommerce >= 2.0
            'css' => 'min-width:500px;',
            'desc_tip' => true
        ),
        array(
            'name' => __('Prices without cents', 'woocommerce-currency-switcher'),
            'desc' => __('Recount prices without cents everywhere like in JPY and TWD which by its nature have not cents. Use comma. Example: UAH,RUB. Test it for checkout after setup!', 'woocommerce-currency-switcher'),
            'id' => 'woocs_no_cents',
            'type' => 'text',
            'std' => '', // WooCommerce < 2.0
            'default' => '', // WooCommerce >= 2.0
            'css' => 'min-width:500px;',
            'desc_tip' => true
        ),
        array('type' => 'sectionend', 'id' => 'woocs_general_settings')
    );
    ?>


    <div class="section">

        <h3 style="margin-bottom: 1px;"><?php printf(__('WooCommerce Currency Switcher v.%s', 'woocommerce-currency-switcher'), WOOCS_VERSION) ?></h3>
        <i><?php printf(__('Actualized for WooCommerce v.%s.x', 'woocommerce-currency-switcher'), $this->actualized_for) ?></i><br />

        <br />

        <div id="tabs" class="wfc-tabs wfc-tabs-style-shape" >

            <?php if (version_compare(WOOCOMMERCE_VERSION, WOOCS_MIN_WOOCOMMERCE, '<')): ?>

                <b style="color: red;"><?php printf(__("Your version of WooCommerce plugin is too obsolete. Update minimum to %s version to avoid malfunctionality!", 'woocommerce-currency-switcher'), WOOCS_MIN_WOOCOMMERCE) ?></b><br />

            <?php endif; ?>

            <input type="hidden" name="woocs_woo_version" value="<?php echo WOOCOMMERCE_VERSION ?>" />

            <svg class="hidden">
            <defs>
            <path id="tabshape" d="M80,60C34,53.5,64.417,0,0,0v60H80z"/>
            </defs>
            </svg><nav>
                <ul>
                    <li class="tab-current">
                        <a href="#tabs-1">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php _e("Currencies", 'currency-switcher') ?></span>
                        </a>
                    </li><li>
                        <a href="#tabs-2">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php _e("Options", 'currency-switcher') ?></span>
                        </a>
                    </li><li><a href="#tabs-3">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php _e("Advanced", 'currency-switcher') ?></span>
                        </a></li>
                    <?php if ($this->is_use_geo_rules()): ?>
                        <li>
                            <a href="#tabs-4">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php _e("GeoIP rules", 'woocommerce-currency-switcher') ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="#tabs-5">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php _e("Info Help", 'woocommerce-currency-switcher') ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="content-wrap">
                <section id="tabs-1" class="content-current">
                    <div class="wcf-control-section">

                        <div style="display: none;">
                            <div id="woocs_item_tpl"><?php
                                $empty = array(
                                    'name' => '',
                                    'rate' => 0,
                                    'symbol' => '',
                                    'position' => '',
                                    'is_etalon' => 0,
                                    'description' => '',
                                    'hide_cents' => 0
                                );
                                woocs_print_currency($this, $empty);
                                ?>
                            </div>
                        </div>

                        <ul id="woocs_list">
                            <?php
                            if (!empty($currencies) AND is_array($currencies)) {
                                foreach ($currencies as $key => $currency) {
                                    woocs_print_currency($this, $currency);
                                }
                            }
                            ?>
                        </ul><br />


                        <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button button-primary button-large"><?php _e("Read wiki about Currency Active codes  <-  Get right currencies codes here if you are not sure about it!", 'woocommerce-currency-switcher') ?></a>
                    </div>
                </section>
                <section id="tabs-2">
                    <div class="wfc-control-section-xxx">
                        <?php woocommerce_admin_fields($options); ?>
                    </div>
                </section>

                <section id="tabs-3">

                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_multiple_allowed"><?php _e('Is multiple allowed', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip" data-tip="<?php _e('Customer will pay with selected currency (Yes) or with default currency (No).', 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        0 => __('No', 'woocommerce-currency-switcher'),
                                        1 => __('Yes', 'woocommerce-currency-switcher')
                                    );
                                    $woocs_is_multiple_allowed = get_option('woocs_is_multiple_allowed', 0);
                                    ?>
                                    <select name="woocs_is_multiple_allowed" id="woocs_is_multiple_allowed" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Is multiple allowed', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($woocs_is_multiple_allowed, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                            </tr>


                        <!-- <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="woocs_use_geo_rules"><?php _e('Use GeoLocation rules', 'woocommerce-currency-switcher') ?></label>
                                <span class="woocommerce-help-tip" data-tip="<?php _e('Use GeoLocation rules for your currencies. This feature uses native WC_Geolocation php class! Works from woocommerce >= 2.3.0', 'woocommerce-currency-switcher') ?>"></span>
                            <?php
                            if (!empty($pd) AND ! empty($countries) AND isset($countries[$pd['country']])) {
                                echo '<i style="font-size:11px; font-weight:normal;">' . sprintf(__('Your country is: %s', 'woocommerce-currency-switcher'), $countries[$pd['country']]) . '</i>';
                            } else {
                                echo '<i style="color:red;font-size:11px; font-weight:normal;">' . __('Your country is not defined! Troubles with internet connection or GeoIp service.', 'woocommerce-currency-switcher') . '</i>';
                            }
                            ?>
                            </th>
                            <td class="forminp forminp-select">
                            <?php
                            $opts = array(
                                0 => __('No', 'woocommerce-currency-switcher'),
                                1 => __('Yes', 'woocommerce-currency-switcher')
                            );
                            $selected = get_option('woocs_use_geo_rules', 0);
                            ?>
                                <select name="woocs_use_geo_rules" id="woocs_use_geo_rules" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Use GeoLocation rules', 'woocommerce-currency-switcher') ?>">

                            <?php foreach ($opts as $val => $title): ?>
                                                                                                                                            <option value="<?php echo $val ?>" <?php echo selected($selected, $val) ?>><?php echo $title ?></option>
                            <?php endforeach; ?>

                                </select>
                            </td>
                        </tr> -->


                            <tr valign="top" <?php if (!$woocs_is_multiple_allowed): ?>style="display: none;"<?php endif; ?>>
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_fixed_enabled"><?php _e('Individual fixed prices rules for each product', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip" style="margin-top: -4px;" data-tip="<?php _e("You will be able to set FIXED prices for simple and variable products. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        0 => __('No', 'woocommerce-currency-switcher'),
                                        1 => __('Yes', 'woocommerce-currency-switcher')
                                    );
                                    $woocs_is_fixed_enabled = get_option('woocs_is_fixed_enabled', 0);
                                    ?>
                                    <select name="woocs_is_fixed_enabled" id="woocs_is_fixed_enabled" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Enable fixed pricing', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($woocs_is_fixed_enabled, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>

                                    </select>&nbsp;<a href="http://currency-switcher.com/video-tutorials#video_YHDQZG8GS6w" target="_blank" class="button"><?php _e('Watch video instructions', 'woocommerce-currency-switcher') ?></a>
                                </td>
                            </tr>


                            <tr valign="top" <?php if (!$woocs_is_fixed_enabled): ?>style="display: none;"<?php endif; ?>>
                                <th scope="row" class="titledesc">
                                    <label for="woocs_force_pay_bygeoip_rules"><?php _e('Checkout by GeoIP rules', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip" style="margin-top: 12px;" data-tip="<?php _e("Force the customers to pay on checkout page by rules defined in 'GeoIP rules' tab. <b>ATTENTION</b>: this feature has logical sense if you enabled 'Enable fixed pricing' and also installed fixed prices rules in the products for different currencies!", 'woocommerce-currency-switcher') ?>"></span>
                                    <?php
                                    if (!empty($pd) AND ! empty($countries) AND isset($countries[$pd['country']])) {
                                        echo '<i style="font-size:11px; font-weight:normal;">' . sprintf(__('Your country is: %s', 'woocommerce-currency-switcher'), $countries[$pd['country']]) . '</i>';
                                    } else {
                                        echo '<i style="color:red;font-size:11px; font-weight:normal;">' . __('Your country is not defined! Troubles with internet connection or GeoIp service.', 'woocommerce-currency-switcher') . '</i>';
                                    }
                                    ?>

                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        0 => __('No', 'woocommerce-currency-switcher'),
                                        1 => __('Yes', 'woocommerce-currency-switcher')
                                    );
                                    $selected = get_option('woocs_force_pay_bygeoip_rules', 0);
                                    ?>
                                    <select name="woocs_force_pay_bygeoip_rules" id="woocs_force_pay_bygeoip_rules" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('Checkout by GeoIP rules', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($selected, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_geoip_manipulation"><?php _e('Individual GeoIP rules for each product', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip" style="margin-top: -7px;" data-tip="<?php _e("You will be able to set different prices for each product (in BASIC currency) for different countries", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        0 => __('No', 'woocommerce-currency-switcher'),
                                        1 => __('Yes', 'woocommerce-currency-switcher')
                                    );
                                    $selected = get_option('woocs_is_geoip_manipulation', 0);
                                    ?>
                                    <select name="woocs_is_geoip_manipulation" id="woocs_is_geoip_manipulation" style="min-width: 300px;" class="chosen_select enhanced" tabindex="-1" title="<?php _e('GeoIp product price manipulation', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($selected, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                    &nbsp;<a href="http://currency-switcher.com/video-tutorials#video_PZugTH80-Eo" target="_blank" class="button"><?php _e('Watch video instructions', 'woocommerce-currency-switcher') ?></a>
                                    &nbsp;<a href="http://currency-switcher.com/video-tutorials#video_zh_LVqKADBU" target="_blank" class="button"><?php _e('a hint', 'woocommerce-currency-switcher') ?></a>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label><?php _e('Notes*', 'woocommerce-currency-switcher') ?></label>
                                </th>
                                <td class="forminp forminp-select">

                                    <i><?php _e('Native WooCommerce price filter is blind for all data generated by features "Individual fixed prices rules for each product" and "Individual GeoIP rules for each product"!', 'woocommerce-currency-switcher') ?></i>

                                </td>
                            </tr>


                        </tbody>
                    </table>




                </section>

                <?php if ($this->is_use_geo_rules()): ?>
                    <section id="tabs-4">


                        <?php if (version_compare(WOOCOMMERCE_VERSION, '2.3', '<')): ?>

                            <b style="color: red;"><?php _e("GeoIP works from v.2.3 of the WooCommerce plugin and no with minor versions of WooCommerce!!", 'woocommerce-currency-switcher'); ?></b><br />

                        <?php endif; ?>

                        <?php if (empty($pd)): ?>

                            <b style="color: red;"><?php _e("WooCommerce GeoIP functionality doesn't work on your site. Maybe <a href='https://wordpress.org/support/topic/geolocation-not-working-1/?replies=10' target='_blank'>this</a> will help you.", 'woocommerce-currency-switcher'); ?></b><br />

                        <?php endif; ?>
                        <ul>
                            <?php
                            if (!empty($currencies) AND is_array($currencies)) {
                                foreach ($currencies as $key => $currency) {
                                    $rules = array();
                                    if (isset($geo_rules[$key])) {
                                        $rules = $geo_rules[$key];
                                    }
                                    ?>
                                    <li>
                                        <table style="width: 100%;">
                                            <tr>
                                                <td>
                                                    <div style="width: 70px;<?php if ($currency['is_etalon']): ?>color: red;<?php endif; ?>"><strong><?php echo $key ?></strong>:</div>
                                                </td>
                                                <td style="width: 100%;">
                                                    <select name="woocs_geo_rules[<?php echo $currency['name'] ?>][]" multiple="" size="1" style="max-width: 100%;" class="chosen_select">
                                                        <option value="0"></option>
                                                        <?php foreach ($countries as $key => $value): ?>
                                                            <option <?php echo(in_array($key, $rules) ? 'selected=""' : '') ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </section>
                <?php else: ?>
                    <input type="hidden" name="woocs_geo_rules" value="" />
                <?php endif; ?>



                <section id="tabs-5">

                    <ul>
                        <li><a href="http://currency-switcher.com/documentation/" target="_blank" class="button"><?php _e("Documentation", 'woocommerce-currency-switcher') ?></a>
                            &nbsp;<a href="https://currency-switcher.com/get-flags-free/" target="_blank" class="button button-primary"><?php _e("GET FREE FLAGS IMAGES", 'woocommerce-currency-switcher') ?></a>
                            &nbsp;<a href="http://currency-switcher.com/category/faq/" target="_blank" class="button"><?php _e("FAQ", 'woocommerce-currency-switcher') ?></a>
                            &nbsp;<a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button button-primary button-large"><?php _e("Read wiki about Currency Active codes  <-  Get right currencies codes here", 'woocommerce-currency-switcher') ?></a></li>

                        <li><a href="http://currency-switcher.com/i-cant-add-flags-what-to-do/" target="_blank" class="button"><?php _e("I cant add flags! What to do?", 'woocommerce-currency-switcher') ?></a>
                            &nbsp;<a href="http://currency-switcher.com/using-geolocation-causes-problems-doesnt-seem-to-work-for-me/" target="_blank" class="button"><?php _e("Using Geolocation causes problems, doesn’t seem to work for me", 'woocommerce-currency-switcher') ?></a>
                            &nbsp;<a href="https://currency-switcher.com/wp-content/uploads/2017/09/woocs-options.png" target="_blank" class="button"><?php _e("The plugin options example screen", 'woocommerce-currency-switcher') ?></a></li>

                        <li>
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/wUoM9EHjnYs" frameborder="0" allowfullscreen></iframe>
                        </li>

                        <li>
                            <a href="https://share.payoneer.com/nav/6I2wmtpBuitGE6ZnmaMXLYlP8iriJ-63OMLi3PT8SRGceUjGY1dvEhDyuAGBp91DEmf8ugfF3hkUU1XhP_C6Jg2" target="_blank"><img src="<?php echo WOOCS_LINK ?>/img/100125.png" alt=""></a>
                        </li>



                        <li>
                            <a href="http://codecanyon.net/item/woof-woocommerce-products-filter/11498469?ref=realmag777" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woof_banner.png" /></a>
                        </li>

                        <?php if (!$WOOCS->notes_for_free): ?>
                            <li>
                                <div id="plugin_warning" style="padding: 9px; border: solid red 3px; background: #eee; ">
                                    <div class="plugin_warning_head"><strong style="color: red;">ATTENTION MESSAGE FROM THE PLUGIN AUTHOR TO ALL USERS WHO USES PIRATE VERSION OF THE PLUGIN! IF YOU BOUGHT IT - DO NOT READ AND IGNORE IT!</strong>!<br></div>
                                    <br />
                                    GET YOUR COPY OF THE PLUGIN <em> <span style="text-decoration: underline;"><span style="color: #ff0000;"><strong>ONLY</strong></span></span></em> FROM <a href="http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777" target="_blank"><span style="color: #008000;"><strong>CODECANYON.NET</strong></span></a> OR <span style="color: #008000;"><strong><a href="https://wordpress.org/plugins/woocommerce-currency-switcher/" target="_blank">WORDPRESS.ORG</a></strong></span> IF YOU DO NOT WANT TO BE AN AFFILIATE OF ANY VIRUS SITE.<br>
                                    <br>
                                    <strong>DID YOU CATCH A VIRUS DOWNLOADING THE PLUGIN FROM ANOTHER (PIRATE) SITES<span style="color: #ff0000;">?</span></strong> THIS IS YOUR TROUBLES AND <em>DO NOT WRITE TO SUPPORT THAT GOOGLE DOWN YOUR SITE TO ZERO BECAUSE OF &nbsp;ANY VIRUS</em>!!<br>
                                    <br>
                                    <strong><span style="color: #ff0000;">REMEMBER</span></strong> - if somebody suggesting YOU premium version of the plugin for free - think twenty times before installing it ON YOUR SITE, as it can be trap for it! <strong>DOWNLOAD THE PLUGIN ONLY FROM OFFICIAL SITES TO AVOID THE COLLAPSE OF YOUR BUSINESS</strong>.<br>
                                    <br>
                                    <strong style="color: #ff0000;">Miser pays twice</strong>!<br>
                                    <br>
                                    P.S. Reason of this warning text - emails from the users! Be care!!
                                </div>
                            </li>
                        <?php endif; ?>

                    </ul>
                </section>



            </div>
        </div>






    </div>
    <br />


    <b style="color:red;"><?php _e('Hint', 'woocommerce-currency-switcher'); ?>:</b>&nbsp;<?php _e('To update all currencies rates by one click - press radio button of the default currency and then press "Save changes" button!', 'woocommerce-currency-switcher'); ?><br />


    <?php if ($WOOCS->notes_for_free): ?>
        <hr />

        <div style="font-style: italic;">In the free version of the plugin <b>you can operate with 2 ANY currencies only</b>. If you want more currencies and features you can make upgrade to the premium version of the plugin</div><br />

        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <h3><?php _e("Get the full version of the plugin from Codecanyon", 'woocommerce-currency-switcher') ?>:</h3>
                    <a href="http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woocs_banner.png" alt="<?php _e("full version of the plugin", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>
                <td style="width: 50%;">
                    <h3><?php _e("Get WooCommerce Products Filter", 'woocommerce-currency-switcher') ?>:</h3>
                    <a href="http://codecanyon.net/item/woof-woocommerce-products-filter/11498469?ref=realmag777" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woof_banner.png" alt="<?php _e("WOOF", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>
            </tr>
        </table>
    <?php endif; ?>


    <div class="info_popup" style="display: none;"></div>

</div>


<script type="text/javascript">
    (function ($, window) {

        'use strict';

        $.fn.wfcTabs = function (options) {

            if (!this.length)
                return;

            return this.each(function () {

                var $this = $(this);

                ({
                    init: function () {
                        this.tabsNav = $this.children('nav');
                        this.items = $this.children('.content-wrap').children('section');
                        this._show();
                        this._initEvents();
                    },
                    _initEvents: function () {
                        var self = this;
                        this.tabsNav.on('click', 'a', function (e) {
                            e.preventDefault();
                            self._show($(this));
                        });
                    },
                    _show: function (element) {

                        if (element == undefined) {
                            this.firsTab = this.tabsNav.find('li').first();
                            this.firstSection = this.items.first();

                            if (!this.firsTab.hasClass('tab-current')) {
                                this.firsTab.addClass('tab-current');
                            }

                            if (!this.firstSection.hasClass('content-current')) {
                                this.firstSection.addClass('content-current');
                            }
                        }

                        var $this = $(element),
                                $to = $($this.attr('href'));

                        if ($to.length) {
                            $this.parent('li').siblings().removeClass().end().addClass('tab-current');
                            $to.siblings().removeClass().end().addClass('content-current');
                        }

                    }

                }).init();

            });
        }

    })(jQuery, window);

    jQuery('.wfc-tabs').wfcTabs();
    jQuery(function () {

        jQuery.fn.life = function (types, data, fn) {
            jQuery(this.context).on(types, this.selector, data, fn);
            return this;
        };

        // jQuery("#tabs").tabs();

        jQuery('body').append('<div id="woocs_buffer" style="display: none;"></div>');

        jQuery("#woocs_list").sortable();


        jQuery('.woocs_is_etalon').life('click', function () {
            jQuery('.woocs_is_etalon').next('input[type=hidden]').val(0);
            jQuery('.woocs_is_etalon').prop('checked', 0);
            jQuery(this).next('input[type=hidden]').val(1);
            jQuery(this).prop('checked', 1);
            //instant save
            var currency_name = jQuery(this).parent().find('input[name="woocs_name[]"]').val();
            if (currency_name.length) {
                woocs_show_stat_info_popup('Loading ...');
                var data = {
                    action: "woocs_save_etalon",
                    currency_name: currency_name
                };
                jQuery.post(ajaxurl, data, function (request) {
                    try {
                        request = jQuery.parseJSON(request);
                        jQuery.each(request, function (index, value) {
                            var elem = jQuery('input[name="woocs_name[]"]').filter(function () {
                                return this.value.toUpperCase() == index;
                            });

                            if (elem) {
                                jQuery(elem).parent().find('input[name="woocs_rate[]"]').val(value);
                                jQuery(elem).parent().find('input[name="woocs_rate[]"]').text(value);
                            }
                        });

                        woocs_hide_stat_info_popup();
                        woocs_show_info_popup('Save changes please!', 1999);
                    } catch (e) {
                        woocs_hide_stat_info_popup();
                        alert('Request error! Try later or another agregator!');
                    }
                });
            }

            return true;
        });


        jQuery('.woocs_flag_input').life('change', function ()
        {
            jQuery(this).next('a.woocs_flag').find('img').attr('src', jQuery(this).val());
        });

        jQuery('.woocs_flag').life('click', function ()
        {
            var input_object = jQuery(this).prev('input[type=hidden]');
            window.send_to_editor = function (html)
            {
                woocs_insert_html_in_buffer(html);
                var imgurl = jQuery('#woocs_buffer').find('a').eq(0).attr('href');
                woocs_insert_html_in_buffer("");
                jQuery(input_object).val(imgurl);
                jQuery(input_object).trigger('change');
                tb_remove();
            };
            tb_show('', 'media-upload.php?post_id=0&type=image&TB_iframe=true');

            return false;
        });

        jQuery('.woocs_finance_yahoo').life('click', function () {
            var currency_name = jQuery(this).parent().find('input[name="woocs_name[]"]').val();
            console.log(currency_name);
            var _this = this;
            jQuery(_this).parent().find('input[name="woocs_rate[]"]').val('loading ...');
            var data = {
                action: "woocs_get_rate",
                currency_name: currency_name
            };
            jQuery.post(ajaxurl, data, function (value) {
                jQuery(_this).parent().find('input[name="woocs_rate[]"]').val(value);
            });

            return false;
        });

        //loader
        jQuery(".woocs-admin-preloader").fadeOut("slow");

    });


    function woocs_insert_html_in_buffer(html) {
        jQuery('#woocs_buffer').html(html);
    }
    function woocs_get_html_from_buffer() {
        return jQuery('#woocs_buffer').html();
    }

    function woocs_show_info_popup(text, delay) {
        jQuery(".info_popup").text(text);
        jQuery(".info_popup").fadeTo(400, 0.9);
        window.setTimeout(function () {
            jQuery(".info_popup").fadeOut(400);
        }, delay);
    }

    function woocs_show_stat_info_popup(text) {
        jQuery(".info_popup").text(text);
        jQuery(".info_popup").fadeTo(400, 0.9);
    }


    function woocs_hide_stat_info_popup() {
        window.setTimeout(function () {
            jQuery(".info_popup").fadeOut(400);
        }, 500);
    }



</script>

<?php

function woocs_print_currency($_this, $currency) {
    global $WOOCS;
    ?>
    <li>
        <input class="help_tip woocs_is_etalon" data-tip="<?php _e("Set etalon main currency. This should be the currency in which the price of goods exhibited!", 'woocommerce-currency-switcher') ?>" type="radio" <?php checked(1, $currency['is_etalon']) ?> />
        <input type="hidden" name="woocs_is_etalon[]" value="<?php echo $currency['is_etalon'] ?>" />
        <input type="text" value="<?php echo $currency['name'] ?>" name="woocs_name[]" class="woocs-text woocs-currency" placeholder="<?php _e("Exmpl.: USD,EUR", 'woocommerce-currency-switcher') ?>" />
        <select class="woocs-drop-down woocs-symbol" name="woocs_symbol[]">
            <?php foreach ($_this->currency_symbols as $symbol) : ?>
                <option value="<?php echo md5($symbol) ?>" <?php selected(md5($currency['symbol']), md5($symbol)) ?>><?php echo $symbol; ?></option>
            <?php endforeach; ?>
        </select>
        <select class="woocs-drop-down woocs-position" name="woocs_position[]">
            <option value="0"><?php _e("Select symbol position", 'woocommerce-currency-switcher'); ?></option>
            <?php foreach ($_this->currency_positions as $position) : ?>
                <option value="<?php echo $position ?>" <?php selected($currency['position'], $position) ?>><?php echo str_replace('_', ' ', $position); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="woocs_decimals[]" class="woocs-drop-down woocs-decimals">
            <?php
            $woocs_decimals = array(
                -1 => __("Decimals", 'woocommerce-currency-switcher'),
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
                7 => 7,
                8 => 8
            );
            if (!isset($currency['decimals'])) {
                $currency['decimals'] = 2;
            }
            ?>
            <?php foreach ($woocs_decimals as $v => $n): ?>
                <option <?php if ($currency['decimals'] == $v): ?>selected=""<?php endif; ?> value="<?php echo $v ?>"><?php echo $n ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" style="width: 100px;" value="<?php echo $currency['rate'] ?>" name="woocs_rate[]" class="woocs-text woocs-rate" placeholder="<?php _e("exchange rate", 'woocommerce-currency-switcher') ?>" />
        <button class="button woocs_finance_yahoo help_tip" data-tip="<?php _e("Press this button if you want get currency rate from the selected aggregator above!", 'woocommerce-currency-switcher') ?>"><?php _e("finance", 'woocommerce-currency-switcher'); ?>.<?php echo get_option('woocs_currencies_aggregator', 'yahoo') ?></button>
        <select name="woocs_hide_cents[]" class="woocs-drop-down" <?php if (in_array($currency['name'], $WOOCS->no_cents)): ?>disabled=""<?php endif; ?>>
            <?php
            $woocs_hide_cents = array(
                0 => __("Show cents on front", 'woocommerce-currency-switcher'),
                1 => __("Hide cents on front", 'woocommerce-currency-switcher')
            );
            if (in_array($currency['name'], $WOOCS->no_cents)) {
                $currency['hide_cents'] = 1;
            }
            $hide_cents = 0;
            if (isset($currency['hide_cents'])) {
                $hide_cents = $currency['hide_cents'];
            }
            ?>
            <?php foreach ($woocs_hide_cents as $v => $n): ?>
                <option <?php if ($hide_cents == $v): ?>selected=""<?php endif; ?> value="<?php echo $v ?>"><?php echo $n ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" value="<?php echo $currency['description'] ?>" name="woocs_description[]" style="width: 250px;" class="woocs-text" placeholder="<?php _e("description", 'woocommerce-currency-switcher') ?>" />
        <?php
        $flag = WOOCS_LINK . 'img/no_flag.png';
        if (isset($currency['flag']) AND ! empty($currency['flag'])) {
            $flag = $currency['flag'];
        }
        ?>
        <input type="hidden" value="<?php echo $flag ?>" class="woocs_flag_input" name="woocs_flag[]" />
        <a href="#" class="woocs_flag help_tip" data-tip="<?php _e("Click to select the flag", 'woocommerce-currency-switcher'); ?>"><img src="<?php echo $flag ?>" style="vertical-align: middle; max-width: 50px;" alt="<?php _e("Flag", 'woocommerce-currency-switcher'); ?>" /></a>
        &nbsp;<a href="#" class="help_tip" data-tip="<?php _e("drag and drope", 'woocommerce-currency-switcher'); ?>"><img style="width: 22px; vertical-align: middle;" src="<?php echo WOOCS_LINK ?>img/move.png" alt="<?php _e("move", 'woocommerce-currency-switcher'); ?>" /></a>
    </li>
    <?php
}
