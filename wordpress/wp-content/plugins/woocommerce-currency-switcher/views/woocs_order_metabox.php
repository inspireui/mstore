<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
$currencies = $this->get_currencies();
$rate = get_post_meta($post->ID, '_woocs_order_rate', TRUE);
$currency = get_post_meta($post->ID, '_order_currency', TRUE);
$base_currency = get_post_meta($post->ID, '_woocs_order_base_currency', TRUE);
$changed_mannualy = get_post_meta($post->ID, '_woocs_order_currency_changed_mannualy', TRUE);
if (empty($base_currency))
{
    $base_currency = $this->default_currency;
}
?>

<div id="woocs_order_metabox" >
    <strong><?php esc_html_e('Order currency', 'woocommerce-currency-switcher') ?></strong>: 
    <span class="woocs_order_currency">
        <i><?php echo $currency ?></i>
        <select name="woocs_order_currency2" class="woocs_settings_hide" >
            <?php foreach ($currencies as $key => $curr) : ?>
                <option value="<?php echo $key ?>"><?php echo $curr['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </span>&nbsp;<span class="tips" data-tip="<?php esc_html_e('Currency in which the customer paid.', 'woocommerce-currency-switcher') ?><?php if ($changed_mannualy > 0): ?> <?php printf(esc_html__('THIS order currency is changed manually %s!', 'woocommerce-currency-switcher'), date('d-m-Y', $changed_mannualy)) ?><?php endif; ?>">[?]</span><br />
    <strong><?php esc_html_e('Base currency', 'woocommerce-currency-switcher') ?></strong>: <?php echo $base_currency ?><br />
    <strong><?php esc_html_e('Order currency rate', 'woocommerce-currency-switcher') ?></strong>: <?php echo $rate ?>&nbsp;<span class="tips" data-tip="<?php esc_html_e('Currency rate when the customer paid ', 'woocommerce-currency-switcher') ?>">[?]</span><br />
    <strong><?php esc_html_e('Total amount', 'woocommerce-currency-switcher') ?></strong>: 
    <?php
    $_REQUEST['no_woocs_order_amount_total'] = 1;
    echo trim(number_format($order->get_total(), $this->price_num_decimals) . ' ' . $currency);
    ?><br />
    <hr />
    <a href="javascript:woocs_change_order_data();void(0);" class="button woocs_change_order_curr_button"><?php esc_html_e('Change order currency', 'woocommerce-currency-switcher') ?>&nbsp;<img class="help_tip" data-tip="<?php esc_html_e('For new manual order ONLY!!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></a>
    <a href="javascript:woocs_cancel_order_data();void(0);" class="woocs_settings_hide" class="button woocs_cancel_order_curr_button"><?php esc_html_e('cancel', 'woocommerce-currency-switcher') ?></a><br />


    <?php if ($currency !== $this->default_currency): ?>
        <hr />
        <a data-order_id="<?php echo $post->ID ?>" href="javascript:woocs_recalculate_order_data();void(0);" class="button woocs_recalculate_order_curr_button"><?php esc_html_e("Recalculate order", 'woocommerce-currency-switcher') ?>&nbsp;<img class="help_tip" data-tip="<?php esc_html_e('Recalculate current order with basic currency. Recommended test this option on the clone of your site! Read the documentation of the plugin about it!', 'woocommerce-currency-switcher') ?>" src="<?php echo WOOCS_LINK ?>/img/help.png" height="16" width="16" /></a><br />

        <?php endif; ?>

</div>


