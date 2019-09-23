<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
if (!function_exists('woocs_restriction_options')) {

    function woocs_restriction_options($post_id, $curr_code,$hash, $type, $amouth_min = '', $amouth_max = '') {
        ?>
        <li id="woocs_li_<?php echo $hash ?>_<?php echo $curr_code ?>">
            <div class="woocs_price_col">
                <p class="form-field form-row restriction_min__field">
                    <label for="woocs_restriction_min_<?php echo $hash ?>_<?php echo $curr_code ?>"><?php esc_html_e('Minimum spend', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo $curr_code ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_restriction_min[<?php echo $post_id ?>][<?php echo $curr_code ?>]" id="woocs_restriction_min_<?php echo woocs_short_id($post_id) ?>_<?php echo $curr_code ?>" value="<?php echo($amouth_min > 0 ? $amouth_min : '') ?>" placeholder="<?php esc_html_e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
                <p class="form-field form-row _restriction_max_field">
                    <label for="woocs_restriction_max_<?php echo $hash ?>_<?php echo $curr_code ?>"><?php esc_html_e('Maximum spend', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo $curr_code ?></b>):</label>
                    <input type="text" class="short wc_input_price" name="woocs_restriction_max[<?php echo $post_id ?>][<?php echo $curr_code ?>]" id="woocs_restriction_max_<?php echo woocs_short_id($post_id) ?>_<?php echo $curr_code ?>" value="<?php echo($amouth_max > 0 ? $amouth_max : '') ?>" placeholder="<?php esc_html_e('auto', 'woocommerce-currency-switcher') ?>">
                </p>
            </div>
            <div class="woocs_price_col">
                <p class="form-row">
                    <a href="javascript:woocs_remove_li_fixed_field('<?php echo $hash ?>','<?php echo $curr_code ?>',false,'<?php echo $type ?>');void(0);" class="button"><?php esc_html_e('Remove', 'woocommerce-currency-switcher') ?></a>
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
            <li><a href="javascript:woocs_open_tab('woocs_tab_fixed_restriction','<?php echo woocs_short_id($post_id) ?>');void(0)" id="woocs_tab_fixed_restriction_<?php echo woocs_short_id($post_id) ?>btn_<?php echo woocs_short_id($post_id) ?>" class="woocs_tab_button button"><?php esc_html_e('The coupon fixed Minimum and Maximum spend rules', 'woocommerce-currency-switcher') ?></a></li>
        <?php endif; ?>

    </ul>

    <input type="hidden" name="woocs_restriction_min[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_restriction_max[<?php echo $post_id ?>]" value="" />

    <!---------------------------------------------------------------->

    <?php if ($is_fixed_enabled): ?>
        <div id="woocs_tab_fixed_restriction_<?php echo woocs_short_id($post_id) ?>" class="woocs_tab">
            <h4><?php esc_html_e('WOOCS - the <b>fixed</b> Minimum and Maximum spend ', 'woocommerce-currency-switcher') ?><img class="help_tip" data-tip="<?php esc_html_e('Here you can set FIXED amount for the coupon for any currency you want. In the case of empty amount field recounting by rate will work!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></h4>
            <select class="select short" id="woocs_multiple_simple_select_<?php echo $type ?>_<?php echo woocs_short_id($post_id) ?>">
                <?php foreach ($currencies as $code => $curr): ?>
                    <?php
                    if ($code === $default_currency OR ( $this->is_exists($post_id, $code, 'min_spend') OR $this->is_exists($post_id, $code, 'max_spend'))) {
                        continue;
                    }
                    ?>
                    <option value="<?php echo $code ?>"><?php echo $code ?></option>
                <?php endforeach; ?>
            </select>
            &nbsp;<a href="javascript:woocs_add_fixed_field('<?php echo $post_id ?>','<?php echo $type ?>', '<?php echo woocs_short_id($post_id) ?>');void(0);" class="button"><?php esc_html_e('Add', 'woocommerce-currency-switcher') ?></a>
            &nbsp;<a href="javascript:woocs_add_all_fixed_field('<?php echo $post_id ?>','<?php echo $type ?>', '<?php echo woocs_short_id($post_id) ?>');void(0);" class="button"><?php esc_html_e('Add all', 'woocommerce-currency-switcher') ?></a>
            <br />
            <br />
            <hr style="clear: both; overflow: hidden;" />
            <ul id="woocs_multiple_simple_list_<?php echo $type ?>_<?php echo woocs_short_id($post_id) ?>">
                <?php
                foreach ($currencies as $code => $curr) {
                    if ($this->is_exists($post_id, $code, 'min_spend') OR $this->is_exists($post_id, $code, 'max_spend')) {
                        woocs_restriction_options($post_id, $code, woocs_short_id($post_id), $type, $this->prepare_float_to_show($this->get_value($post_id, $code, 'min_spend'), $curr['decimals']), $this->prepare_float_to_show($this->get_value($post_id, $code, 'max_spend'), $curr['decimals']));
                    }
                }
                ?>
            </ul>
            <div id="woocs_multiple_simple_tpl_<?php echo $type ?>" >
                <?php woocs_restriction_options('__POST_ID__', '__CURR_CODE__', '__HASH__', $type) ?>
            </div>
        </div>
    <?php endif; ?>
    <!---------------------------------------------------------------->
</div>

