<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
if (!function_exists('woocs_price_options'))
{

    function woocs_price_options($post_id, $curr_code, $value_regular = '', $value_sale = '')
    {
        ?>
        <li id="woocs_li_<?php echo $post_id ?>_<?php echo $curr_code ?>">
            <div class="woocs_price_col">
                <p class="form-field form-row _regular_price_field">
                    <label for="woocs_regular_<?php echo $post_id ?>_<?php echo $curr_code ?>"><?php _e('Regular price', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo $curr_code ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_regular_price[<?php echo $post_id ?>][<?php echo $curr_code ?>]" id="woocs_regular_<?php echo $post_id ?>_<?php echo $curr_code ?>" value="<?php echo($value_regular > 0 ? $value_regular : '') ?>" placeholder="<?php _e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
            </div>
            <div class="woocs_price_col">
                <p class="form-field form-row _sale_price_field">
                    <label for="woocs_sale_<?php echo $post_id ?>_<?php echo $curr_code ?>"><?php _e('Sale price', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo $curr_code ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_sale_price[<?php echo $post_id ?>][<?php echo $curr_code ?>]" id="woocs_sale_<?php echo $post_id ?>_<?php echo $curr_code ?>" value="<?php echo($value_sale > 0 ? $value_sale : '') ?>" placeholder="<?php _e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
            </div>
            <div class="woocs_price_col">
                <p class="form-row">
                    <a href="javascript:woocs_remove_li_product_price(<?php echo $post_id ?>,'<?php echo $curr_code ?>',false);void(0);" class="button"><?php _e('Remove', 'woocommerce-currency-switcher') ?></a>
                </p>
            </div>
        </li>
        <?php
    }

}

//***

if (!function_exists('woocs_price_options_geo'))
{

    function woocs_price_options_geo($post_id, $index, $countries_selected, $value_regular = '', $value_sale = '')
    {
        ?>
        <li id="woocs_li_geo_<?php echo $post_id ?>_<?php echo $index ?>">
            <div class="woocs_price_col">
                <p class="form-field form-row _regular_price_field">
                    <label for="woocs_regular_geo_<?php echo $post_id ?>_<?php echo $index ?>"><?php _e('Regular price', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo get_woocommerce_currency_symbol(); ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_regular_price_geo[<?php echo $post_id ?>][<?php echo $index ?>]" id="woocs_regular_geo_<?php echo $post_id ?>_<?php echo $index ?>" value="<?php echo($value_regular > 0 ? $value_regular : '') ?>" placeholder="<?php _e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
            </div>
            <div class="woocs_price_col">
                <p class="form-field form-row _sale_price_field">
                    <label for="woocs_sale_geo_<?php echo $post_id ?>_<?php echo $index ?>"><?php _e('Sale price', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo get_woocommerce_currency_symbol(); ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_sale_price_geo[<?php echo $post_id ?>][<?php echo $index ?>]" id="woocs_sale_geo_<?php echo $post_id ?>_<?php echo $index ?>" value="<?php echo($value_sale > 0 ? $value_sale : '') ?>" placeholder="<?php _e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
            </div>            
            <div class="woocs_price_col">
                <p class="form-row">
                    <a href="javascript:woocs_remove_li_product_price(<?php echo $post_id ?>,'<?php echo $index ?>', true);void(0);" class="button"><?php _e('Remove', 'woocommerce-currency-switcher') ?></a>
                </p>
            </div>
            <div style="clear: both;">
                <p class="form-row">
                    <?php $c = new WC_Countries(); ?>
                    <select name="woocs_price_geo_countries[<?php echo $post_id ?>][<?php echo $index ?>][]" multiple="" size="1" style="width: 80%;" <?php if ($index !== '__INDEX__'): ?>class="chosen_select"<?php endif; ?> data-placeholder="<?php _e('select some countries', 'woocommerce-currency-switcher') ?>">
                        <option value="0"></option>
                        <?php foreach ($c->get_countries() as $key => $value): ?>
                            <option <?php echo(in_array($key, $countries_selected) ? 'selected=""' : '') ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </div>
        </li>
        <?php
    }

}
?>

<div class="woocs_multiple_simple_panel options_group pricing woocommerce_variation" style="<?php if ($type == 'simple'): ?>display: none;<?php endif; ?>">

    <ul class="woocs_tab_navbar">
        <?php if ($is_fixed_enabled): ?>
            <li><a href="javascript:woocs_open_tab('woocs_tab_fixed',<?php echo $post_id ?>);void(0)" id="woocs_tab_fixed_btn_<?php echo $post_id ?>" class="woocs_tab_button button"><?php _e('The product fixed prices rules', 'woocommerce-currency-switcher') ?></a></li>
        <?php endif; ?>

        <?php if ($is_geoip_manipulation): ?>
            <li><a href="javascript:woocs_open_tab('woocs_tab_geo',<?php echo $post_id ?>);void(0)" id="woocs_tab_geo_btn_<?php echo $post_id ?>" class="woocs_tab_button button"><?php _e('The product custom GeoIP rules', 'woocommerce-currency-switcher') ?></a></li>
        <?php endif; ?>
    </ul>

    <input type="hidden" name="woocs_regular_price[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_sale_price[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_regular_price_geo[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_sale_price_geo[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_price_geo_countries[<?php echo $post_id ?>]" value="" />

    <!---------------------------------------------------------------->

    <?php if ($is_fixed_enabled): ?>
        <div id="woocs_tab_fixed_<?php echo $post_id ?>" class="woocs_tab">
            <h4><?php _e('WOOCS - the product <b>fixed</b> prices', 'woocommerce-currency-switcher') ?><img class="help_tip" data-tip="<?php _e('Here you can set FIXED price for the product for any currency you want. In the case of empty price field recounting by rate will work!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></h4>
            <select class="select short" id="woocs_multiple_simple_select_<?php echo $post_id ?>">
                <?php foreach ($currencies as $code => $curr): ?>
                    <?php
                    if ($code === $default_currency OR $this->is_exists($post_id, $code, 'regular'))
                    {
                        continue;
                    }
                    ?>
                    <option value="<?php echo $code ?>"><?php echo $code ?></option>
                <?php endforeach; ?>
            </select>
            &nbsp;<a href="javascript:woocs_add_product_price(<?php echo $post_id ?>);void(0);" class="button"><?php _e('Add', 'woocommerce-currency-switcher') ?></a>
            &nbsp;<a href="javascript:woocs_add_all_product_price(<?php echo $post_id ?>);void(0);" class="button"><?php _e('Add all', 'woocommerce-currency-switcher') ?></a>
            <br />
            <br />
            <hr style="clear: both; overflow: hidden;" />
            <ul id="woocs_multiple_simple_list_<?php echo $post_id ?>">
                <?php
                foreach ($currencies as $code => $curr)
                {
                    if ($this->is_exists($post_id, $code, 'regular'))
                    {
                        woocs_price_options($post_id, $code, $this->get_value($post_id, $code, 'regular'), $this->get_value($post_id, $code, 'sale'));
                    }
                }
                ?>
            </ul>
            <div id="woocs_multiple_simple_tpl">
                <?php woocs_price_options('__POST_ID__', '__CURR_CODE__') ?>
            </div>
        </div>
    <?php endif; ?>

    <!---------------------------------------------------------------->

    <?php if ($is_geoip_manipulation): ?>
        <div id="woocs_tab_geo_<?php echo $post_id ?>" class="woocs_tab">
            <h4><?php _e('WOOCS - the product custom GeoIP rules', 'woocommerce-currency-switcher') ?><img class="help_tip" data-tip="<?php _e('Here you can set prices in the basic currency for different countries, and recount will be done relatively of this values. ATTENTION: fixed price for currencies has higher priority!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></h4>

            <a href="javascript: woocs_add_group_geo(<?php echo $post_id ?>);void(0);" class="button"><?php _e('Add group', 'woocommerce-currency-switcher') ?></a>

            <pre>
                <?php //print_r($product_geo_data); ?>
            </pre>

            <ul id="woocs_multiple_simple_list_geo_<?php echo $post_id ?>">
                <?php
                if (!empty($product_geo_data) AND ! empty($product_geo_data['price_geo_countries']))
                {
                    foreach ($product_geo_data['price_geo_countries'] as $index => $countries_selected)
                    {
                        if ($index == 0)
                        {
                            continue;
                        }

                        woocs_price_options_geo($post_id, $index, (array) $countries_selected, $product_geo_data['regular_price_geo'][$index], $product_geo_data['sale_price_geo'][$index]);
                    }
                }
                ?>
            </ul>

            <div id="woocs_multiple_simple_tpl_geo">
                <?php woocs_price_options_geo('__POST_ID__', '__INDEX__', array()) ?>
            </div>


        </div>
    <?php endif; ?>


</div>