<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php

class WOOCS_SELECTOR extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(__CLASS__, __('WooCommerce Currency Switcher', 'woocommerce-currency-switcher'), array(
            'classname' => __CLASS__,
            'description' => __('WooCommerce Currency Switcher by realmag777', 'woocommerce-currency-switcher')
                )
        );
        //$this->WP_Widget(__CLASS__, __('WooCommerce Currency Switcher', 'woocommerce-currency-switcher'), $settings);
    }

    public function widget($args, $instance)
    {
        $data = array();
        $data['args'] = $args;
        $data['instance'] = $instance;
        wp_enqueue_script('jquery');
        global $WOOCS;
        echo $WOOCS->render_html(WOOCS_PATH . 'views/widgets/selector.php', $data);
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['show_flags'] = $new_instance['show_flags'];
        $instance['width'] = $new_instance['width'];
        $instance['flag_position'] = $new_instance['flag_position'];
        $instance['txt_type'] = $new_instance['txt_type'];

        return $instance;
    }

    public function form($instance)
    {
        $defaults = array(
            'title' => __('WooCommerce Currency Switcher', 'woocommerce-currency-switcher'),
            'show_flags' => 'true',
            'width' => '100%',
            'flag_position' => 'right',
            'txt_type' => 'code'
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $data = array();
        $data['instance'] = $instance;
        $data['widget'] = $this;
        global $WOOCS;
        echo $WOOCS->render_html(WOOCS_PATH . 'views/widgets/selector_form.php', $data);
    }

}
