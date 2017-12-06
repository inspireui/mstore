<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
global $WOOCS;

if (!class_exists('WooCommerce')) {
    echo "<div class='notice'>". _e('Warning: Woocommerce is not activated', 'woocommerce-currency-switcher')."</div>";
    return;
}

$currencies = $WOOCS->get_currencies();
if (isset($exclude))
{
    $exclude = explode(',', $exclude);
} else
{
    $exclude = array();
}

if (!isset($precision))
{
    $precision = 2;
}

$current_currency = $WOOCS->current_currency;
?>

<div class="woocs_converter_shortcode">
    <input type="text"  placeholder="<?php _e('enter amount', 'woocommerce-currency-switcher') ?>" class="woocs_converter_shortcode_amount" value="1" /><br />
    <input type="hidden" value="<?php echo $precision ?>" class="woocs_converter_shortcode_precision" />
    <select class="woocs_converter_shortcode_from">
        <?php
        if (!empty($currencies))
        {
            foreach ($currencies as $key => $c)
            {
                if (in_array($key, $exclude))
                {
                    continue;
                }
                ?>
                <option <?php selected($current_currency, $key) ?> value="<?php echo $key ?>"><?php echo $c['name'] ?></option>
                <?php
            }
        }
        ?>
    </select>&nbsp;<?php _e('to', 'woocommerce-currency-switcher') ?>&nbsp;<select class="woocs_converter_shortcode_to">
        <?php
        if (!empty($currencies))
        {
            foreach ($currencies as $key => $c)
            {
                if (in_array($key, $exclude))
                {
                    continue;
                }
                ?>
                <option value="<?php echo $key ?>"><?php echo $c['name'] ?></option>
                <?php
            }
        }
        ?>
    </select><br />
    <input type="text" readonly="" placeholder="<?php _e('results', 'woocommerce-currency-switcher') ?>" class="woocs_converter_shortcode_results" value="" /><br />

    <button class="button woocs_converter_shortcode_button" type="button"><?php _e('Convert', 'woocommerce-currency-switcher') ?></button>


</div>


