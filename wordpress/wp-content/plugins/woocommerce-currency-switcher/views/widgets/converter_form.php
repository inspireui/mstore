<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>


<p>
    <label for="<?php echo $widget->get_field_id('title'); ?>"><?php _e('Title', 'woocommerce-currency-switcher') ?>:</label>
    <input class="widefat" type="text" id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
</p>


<p>
    <label for="<?php echo $widget->get_field_id('exclude'); ?>"><?php _e('Currencies excluding from view', 'woocommerce-currency-switcher') ?>:</label>
    <input class="widefat" type="text" id="<?php echo $widget->get_field_id('exclude'); ?>" name="<?php echo $widget->get_field_name('exclude'); ?>" value="<?php echo $instance['exclude']; ?>" />
    <br /><i><?php _e('Examples: EUR,GBP,UAH', 'woocommerce-currency-switcher') ?></i>
</p>


<p>
    <label for="<?php echo $widget->get_field_id('precision'); ?>"><?php _e('Precision', 'woocommerce-currency-switcher') ?>:</label>
    <input class="widefat" type="text" id="<?php echo $widget->get_field_id('precision'); ?>" name="<?php echo $widget->get_field_name('precision'); ?>" value="<?php echo $instance['precision']; ?>" />
    <br /><i><?php _e('Count of digits after point', 'woocommerce-currency-switcher') ?></i>
</p>


