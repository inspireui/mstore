<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class WOOCS_RATES extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(__CLASS__, __('WooCommerce Currency Rates', 'woocommerce-currency-switcher'), array(
            'classname' => __CLASS__,
            'description' => __('WooCommerce Currency Rates by realmag777', 'woocommerce-currency-switcher')
                )
        );
    }

    public function widget($args, $instance)
    {
        $data = array();
        $data['args'] = $args;
        $data['instance'] = $instance;
        wp_enqueue_script('jquery');
        global $WOOCS;
        echo $WOOCS->render_html(WOOCS_PATH . 'views/widgets/rates.php', $data);
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['exclude'] = $new_instance['exclude'];
        $instance['precision'] = $new_instance['precision'];
        return $instance;
    }

    public function form($instance)
    {
        $defaults = array(
            'title' => __('WooCommerce Currency Rates', 'woocommerce-currency-switcher'),
            'exclude' => '',
            'precision' => 4
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $data = array();
        $data['instance'] = $instance;
        $data['widget'] = $this;
        global $WOOCS;
        echo $WOOCS->render_html(WOOCS_PATH . 'views/widgets/rates_form.php', $data);
    }

}
