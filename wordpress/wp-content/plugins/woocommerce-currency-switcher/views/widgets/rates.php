<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
if (isset($args['before_widget']))
{
    echo $args['before_widget'];
}
?>

<div class="widget widget-woocommerce-currency-rates">
    <?php
    if (!empty($instance['title']))
    {
        if (isset($args['before_title']))
        {
            echo $args['before_title'];
            echo $instance['title'];
            echo $args['after_title'];
        } else
        {
            ?>
            <h3 class="widget-title"><?php echo $instance['title'] ?></h3>
            <?php
        }
    }
    ?>
            
    <?php echo do_shortcode('[woocs_rates exclude="' . $instance['exclude'] . '" precision="' . $instance['precision'] . '"]'); ?>
</div>

<?php
if (isset($args['after_widget']))
{
    echo $args['after_widget'];
}

