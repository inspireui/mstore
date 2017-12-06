<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>


<?php



if (isset($args['before_widget']))
{
    echo $args['before_widget'];
}
?>

<div class="widget widget-woocommerce-currency-switcher">
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


    <?php
    $show_flags = $instance['show_flags'];
    if ($show_flags === 'true')
    {
        $show_flags = 1;
    } else
    {
        $show_flags = 0;
    }
    //+++
    $txt_type = 'code';
    if (isset($instance['txt_type']))
    {
        $txt_type = $instance['txt_type'];
    }
    echo do_shortcode("[woocs txt_type='{$txt_type}' show_flags={$show_flags} width='{$instance['width']}' flag_position='{$instance['flag_position']}']");
    ?>
</div>

<?php
if (isset($args['after_widget']))
{
    echo $args['after_widget'];
}

