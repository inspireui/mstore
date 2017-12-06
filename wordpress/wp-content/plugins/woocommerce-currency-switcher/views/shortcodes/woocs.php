<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
//*** hide if there is checkout page
global $post;
if (!class_exists('WooCommerce')) {
    echo "<div class='notice'>". _e('Warning: Woocommerce is not activated', 'woocommerce-currency-switcher')."</div>";
    return;
}
if (get_option('woocs_restrike_on_checkout_page', 0))
{
    if (is_object($post))
    {
        if ($this->get_checkout_page_id() == $post->ID)
        {
            return "";
        }
    }
}

//***

$drop_down_view = $this->get_drop_down_view();

//***
if ($drop_down_view == 'flags')
{
    foreach ($this->get_currencies() as $key => $currency)
    {
        if (!empty($currency['flag']))
        {
            ?>
            <a href="#" class="woocs_flag_view_item <?php if ($this->current_currency == $key): ?>woocs_flag_view_item_current<?php endif; ?>" data-currency="<?php echo $currency['name'] ?>" title="<?php echo $currency['name'] . ', ' . $currency['symbol'] . ' ' . $currency['description'] ?>"><img src="<?php echo $currency['flag'] ?>" alt="<?php echo $currency['name'] . ', ' . $currency['symbol'] ?>" /></a>
            <?php
        }
    }
} else
{
    $empty_flag = WOOCS_LINK . 'img/no_flag.png';
    $show_money_signs = get_option('woocs_show_money_signs', 1);
//***
    if (!isset($show_flags))
    {
        $show_flags = get_option('woocs_show_flags', 1);
    }



    if (!isset($width))
    {
        $width = '100%';
    }

    if (!isset($flag_position))
    {
        $flag_position = 'right';
    }
    ?>


    <?php if ($drop_down_view == 'wselect'): ?>
        <style type="text/css">
            .woocommerce-currency-switcher-form .wSelect, .woocommerce-currency-switcher-form .wSelect-options-holder {
                width: <?php echo $width ?> !important;
            }
            <?php if (!$show_flags): ?>
                .woocommerce-currency-switcher-form .wSelect-option-icon{
                    padding-left: 5px !important;
                }
            <?php endif; ?>
        </style>
    <?php endif; ?>


        <form method="<?php echo apply_filters('woocs_form_method','post')?>" action="" class="woocommerce-currency-switcher-form <?php if ($show_flags): ?>woocs_show_flags<?php endif; ?>" data-ver="<?php echo WOOCS_VERSION ?>">
        <input type="hidden" name="woocommerce-currency-switcher" value="<?php echo $this->current_currency ?>" />
        <select name="woocommerce-currency-switcher" style="width: <?php echo $width ?>;" data-width="<?php echo $width ?>" data-flag-position="<?php echo $flag_position ?>" class="woocommerce-currency-switcher" onchange="woocs_redirect(this.value);
                    void(0);">
                    <?php foreach ($this->get_currencies() as $key => $currency) : ?>

                <?php
                $option_txt = apply_filters('woocs_currname_in_option', $currency['name']);

                if ($show_money_signs)
                {
                    if (!empty($option_txt))
                    {
                        $option_txt.=', ' . $currency['symbol'];
                    } else
                    {
                        $option_txt = $currency['symbol'];
                    }
                }
                //***
                if (isset($txt_type))
                {
                    if ($txt_type == 'desc')
                    {
                        if (!empty($currency['description']))
                        {
                            $option_txt = $currency['description'];
                        }
                    }
                }
                ?>

                <option <?php if ($show_flags) : ?>style="background: url('<?php echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>') no-repeat 99% 0; background-size: 30px 20px;"<?php endif; ?> value="<?php echo $key ?>" <?php selected($this->current_currency, $key) ?> data-imagesrc="<?php if ($show_flags) echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>" data-icon="<?php if ($show_flags) echo(!empty($currency['flag']) ? $currency['flag'] : $empty_flag); ?>" data-description="<?php echo $currency['description'] ?>"><?php echo $option_txt ?></option>
            <?php endforeach; ?>
        </select>
        <div style="display: none;">WOOCS <?php echo WOOCS_VERSION ?></div>
    </form>
    <?php
}

