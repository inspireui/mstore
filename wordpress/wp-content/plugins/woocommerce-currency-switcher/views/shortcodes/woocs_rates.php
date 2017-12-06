<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

if (!class_exists('WooCommerce')) {
    echo "<div class='notice'>". _e('Warning: Woocommerce is not activated', 'woocommerce-currency-switcher')."</div>";
    return;
}
global $WOOCS;
$currencies = $WOOCS->get_currencies();
//***
if (isset($exclude)) {
    $exclude_string = $exclude;
    $exclude = explode(',', $exclude);
} else {
    $exclude_string = "";
    $exclude = array();
}
//***
if (!isset($current_currency)) {
    $current_currency = $WOOCS->current_currency;
}

//***

if (!isset($precision)) {
    $precision = 2;
}
?>

<div class="woocs_rates_shortcode">

    <?php if (!empty($currencies)): ?>
        <select class="woocs_rates_current_currency" data-precision="<?php echo $precision ?>" data-exclude="<?php echo $exclude_string ?>">
	    <?php
	    if (!empty($currencies)) {
		foreach ($currencies as $key => $c) {
		    if (in_array($key, $exclude)) {
			continue;
		    }
		    ?>
	    	<option <?php selected($current_currency, $key) ?> value="<?php echo $key ?>"><?php printf(__('1 %s is', 'woocommerce-currency-switcher'), $c['name']) ?></option>
		    <?php
		}
	    }
	    ?>
        </select><br />
        <ul class="woocs_currency_rates">            
	    <?php foreach ($currencies as $key => $c) : ?>
		<?php
		if ($key == $current_currency OR in_array($key, $exclude)) {
		    continue;
		}
		?>
		<li>
		    <?php if (!empty($c['flag'])): ?>
		    <img class="woocs_currency_rate_flag" src="<?php echo $c['flag'] ?>" alt="<?php echo $c['name'] ?>" />&nbsp;
		    <?php endif; ?>
		    <strong><?php echo $key ?></strong>:&nbsp;<?php
		    $v = $c['rate'] / $currencies[$current_currency]['rate'];
		    echo number_format($v, intval($precision), $this->decimal_sep, '');
		    ?><br />
		</li>
	    <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</div>

