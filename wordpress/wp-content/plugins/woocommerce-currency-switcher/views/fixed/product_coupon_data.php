<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
if (!function_exists('woocs_coupon_options')) {

    function woocs_coupon_options($post_id, $curr_code, $hash, $amount = '') {
        ?>
        <li id="woocs_li_<?php echo $hash ?>_<?php echo $curr_code ?>">
            <div class="woocs_price_col">
                <p class="form-field form-row _regular_price_field">
                    <label for="woocs_amount_<?php echo $hash ?>_<?php echo $curr_code ?>"><?php esc_html_e('Coupon amount', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo $curr_code ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_fixed_coupon[<?php echo $post_id ?>][<?php echo $curr_code ?>]" id="woocs_amount_<?php echo $post_id ?>_<?php echo $curr_code ?>" value="<?php echo($amount > 0 ? $amount : '') ?>" placeholder="<?php esc_html_e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
            </div>
            <div class="woocs_price_col">
                <p class="form-row">
                    <a href="javascript:woocs_remove_li_product_price('<?php echo $hash ?>','<?php echo $curr_code ?>',false);void(0);" class="button"><?php esc_html_e('Remove', 'woocommerce-currency-switcher') ?></a>
                </p>
            </div>
        </li>
        <?php
    }

}
?>

<div class="woocs_multiple_simple_panel options_group pricing woocommerce_variation" >

    <ul class="woocs_tab_navbar">
        <?php if ($is_fixed_enabled): ?>
            <li><a href="javascript:woocs_open_tab('woocs_tab_fixed','<?php echo woocs_short_id($post_id) ?>');void(0)" id="woocs_tab_fixed_btn_<?php echo woocs_short_id($post_id) ?>" class="woocs_tab_button button"><?php esc_html_e('The coupon fixed amount rules', 'woocommerce-currency-switcher') ?></a></li>
        <?php endif; ?>

    </ul>

    <input type="hidden" name="woocs_fixed_coupon[<?php echo $post_id ?>]" value="" />

    <!---------------------------------------------------------------->

    <?php if ($is_fixed_enabled): ?>
        <div id="woocs_tab_fixed_<?php echo woocs_short_id($post_id) ?>" class="woocs_tab">
            <h4><?php esc_html_e('WOOCS - the coupon fixed amount', 'woocommerce-currency-switcher') ?><img class="help_tip" data-tip="<?php esc_html_e('Here you can set FIXED amount for the coupon for any currency you want. In the case of empty amount field recounting by rate will work!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></h4>
            <select class="select short" id="woocs_mselect_<?php echo woocs_short_id($post_id) ?>">
                <?php foreach ($currencies as $code => $curr): ?>
                    <?php
                    if ($code === $default_currency OR $this->is_exists($post_id, $code, 'amount')) {
                        continue;
                    }
                    ?>
                    <option value="<?php echo $code ?>"><?php echo $code ?></option>
                <?php endforeach; ?>
            </select>
            &nbsp;<a href="javascript:woocs_add_product_price('<?php echo $post_id ?>','<?php echo woocs_short_id($post_id) ?>');void(0);" class="button"><?php esc_html_e('Add', 'woocommerce-currency-switcher') ?></a>
            &nbsp;<a href="javascript:woocs_add_all_product_price('<?php echo $post_id ?>','<?php echo woocs_short_id($post_id) ?>');void(0);" class="button"><?php esc_html_e('Add all', 'woocommerce-currency-switcher') ?></a>
            <br />
            <br />
            <hr style="clear: both; overflow: hidden;" />
            <ul id="woocs_mlist_<?php echo woocs_short_id($post_id) ?>">
                <?php
                foreach ($currencies as $code => $curr) {
                    if ($this->is_exists($post_id, $code, 'amount')) {
                        woocs_coupon_options($post_id, $code, woocs_short_id($post_id), $this->prepare_float_to_show($this->get_value($post_id, $code, 'amount'), $curr['decimals']));
                    }
                }
                ?>
            </ul>
            <div id="woocs_multiple_simple_tpl">
                <?php woocs_coupon_options('__POST_ID__', '__CURR_CODE__', '__HASH__') ?>
            </div>
        </div>
    <?php endif; ?>
    <!---------------------------------------------------------------->
</div>