<div class="woocs_multiple_simple_panel options_group pricing woocommerce_variation" style="<?php if ($type == 'simple'): ?>display: none;<?php endif; ?>">

    <ul class="woocs_tab_navbar">
        <?php if ($is_fixed_enabled): ?>
            <li><a href="javascript:woocs_open_tab('woocs_tab_user_role','<?php echo woocs_short_id($post_id) ?>');void(0)" id="woocs_tab_user_role_btn_<?php echo woocs_short_id($post_id) ?>" class="woocs_tab_button button"><?php esc_html_e('The price based on user role', 'woocommerce-currency-switcher') ?></a></li>
        <?php endif; ?>
    </ul>

    <input type="hidden" name="woocs_regular_price_user_role[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_sale_price_user_role[<?php echo $post_id ?>]" value="" />
    <input type="hidden" name="woocs_price_user_role_name[<?php echo $post_id ?>]" value="" />
    <?php
        if (!function_exists('woocs_price_options_user_role')) {

        function woocs_price_options_user_role($post_id, $index, $hash, $role_selected, $value_regular = '', $value_sale = '') {
            ?>
            <li id="woocs_li_user_role_<?php echo $hash ?>_<?php echo $index ?>">
                <div class="woocs_price_col">
                    <p class="form-field form-row _regular_price_field">
                        <label for="woocs_regular_user_role_<?php echo $hash ?>_<?php echo $index ?>"><?php esc_html_e('Regular price', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo get_woocommerce_currency_symbol(); ?></b>):</label>
                        <input type="text" class="short wc_input_price" name="woocs_regular_price_user_role[<?php echo $post_id ?>][<?php echo $index ?>]" id="woocs_regular_user_role_<?php echo $hash ?>_<?php echo $index ?>" value="<?php echo($value_regular > 0 ? $value_regular : '') ?>" placeholder="<?php esc_html_e('auto', 'woocommerce-currency-switcher') ?>">
                    </p>
                </div>
                <div class="woocs_price_col">
                    <p class="form-field form-row _sale_price_field">
                        <label for="woocs_sale_user_role_<?php echo $hash ?>_<?php echo $index ?>"><?php esc_html_e('Sale price', 'woocommerce-currency-switcher') ?>&nbsp;(<b><?php echo get_woocommerce_currency_symbol(); ?></b>):</label>
                        <input type="text" class="short wc_input_price" name="woocs_sale_price_user_role[<?php echo $post_id ?>][<?php echo $index ?>]" id="woocs_sale_user_role_<?php echo $hash ?>_<?php echo $index ?>" value="<?php echo($value_sale > 0 ? $value_sale : '') ?>" placeholder="<?php esc_html_e('auto', 'woocommerce-currency-switcher') ?>">
                    </p>
                </div>            
                <div class="woocs_price_col">
                    <p class="form-row">
                        <a href="javascript:woocs_remove_li_user_role_price('<?php echo $hash ?>','<?php echo $index ?>');void(0);" class="button"><?php esc_html_e('Remove', 'woocommerce-currency-switcher') ?></a>
                    </p>
                </div>
                <div style="clear: both;">
                    <p class="form-row">
                        <?php 
                        global $wp_roles;
                        $roles=$wp_roles->get_names();
                        ?>
                        <select name="woocs_price_user_role_name[<?php echo $post_id ?>][<?php echo $index ?>][]" multiple="" size="1" style="width: 80%;" <?php if ($index !== '__INDEX__'): ?>class="chosen_select"<?php endif; ?> data-placeholder="<?php esc_html_e('select user role', 'woocommerce-currency-switcher') ?>">
                            <option value="0"></option>
                            <?php foreach ($roles as $key => $value): ?>
                                <option <?php echo(in_array($key, $role_selected) ? 'selected=""' : '') ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </div>
            </li>
            <?php
        }

    }
    ?>
        <?php if ($is_fixed_enabled): ?>
    <div id="woocs_tab_user_role_<?php echo woocs_short_id($post_id) ?>" class="woocs_tab">
            <h4><?php esc_html_e('WOOCS - the price based on user role', 'woocommerce-currency-switcher') ?><img class="help_tip" data-tip="<?php esc_html_e('Gives ability to set different prices for each user role. Native WooCommerce price filter is blind for this data!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></h4>

            <a href="javascript: woocs_add_group_user_role('<?php echo $post_id ?>', '<?php echo woocs_short_id($post_id) ?>');void(0);" class="button"><?php esc_html_e('Add group', 'woocommerce-currency-switcher') ?></a>

<!--            <pre>
                <?php //print_r($user_role_data); 
                ?>
            </pre>-->
            <?php 
            $curr=$currencies[$default_currency];
            ?>
            <ul id="woocs_mlist_user_role_<?php echo woocs_short_id($post_id) ?>">
                <?php

                if (!empty($user_role_data) AND ! empty($user_role_data['price_user_role_name'])) {
                    foreach ($user_role_data['price_user_role_name'] as $index => $selected_role) {
                        if ($index==0) {
                            continue;
                        }

                        woocs_price_options_user_role($post_id, $index, woocs_short_id($post_id), (array)$selected_role, $this->prepare_float_to_show($user_role_data['regular_price_user_role'][$index], $curr['decimals']), $this->prepare_float_to_show($user_role_data['sale_price_user_role'][$index], $curr['decimals']));
                    }
                }
                ?>
            </ul>

            <div id="woocs_multiple_simple_tpl_user_role" >
                <?php woocs_price_options_user_role('__POST_ID__', '__INDEX__', '__HASH__', array()) ?>
            </div>


        </div>
    <?php endif; ?>
    
</div>

