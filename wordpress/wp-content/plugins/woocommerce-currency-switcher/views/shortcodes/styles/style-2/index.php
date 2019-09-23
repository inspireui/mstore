<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
//style-2
$all_currencies = apply_filters('woocs_currency_manipulation_before_show', $this->get_currencies());

//+++

$empty_flag = WOOCS_LINK . 'img/no_flag.png';
$show_money_signs = get_option('woocs_show_money_signs', 1);

//***

if (!isset($show_flags)) {
    $show_flags = get_option('woocs_show_flags', 1);
}

if (!isset($width)) {
    $width = '100%';
}

if (!isset($flag_position)) {
    $flag_position = 'right';
}

//***

$flags_data = [];
if ($show_flags) {
    foreach ($all_currencies as $key => $currency) {

        if (isset($currency['hide_on_front']) AND $currency['hide_on_front']) {
            continue;
        }

        $flag = (!empty($currency['flag']) ? $currency['flag'] : $empty_flag);

        if ($this->current_currency !== $currency['name']) {
            $flags_data[$currency['name']] = "background-image: url(" . $flag . "); background-size: 40px 25px; background-repeat: no-repeat; background-position: 99% 10px;";
        } else {
            $flags_data[$currency['name']] = "background-image: url(" . $flag . "); background-repeat: no-repeat; background-position: 0 0;";
        }
    }
}

//+++

$options = [];
foreach ($all_currencies as $key => $currency) {

    if (isset($currency['hide_on_front']) AND $currency['hide_on_front']) {
        continue;
    }

    $option_txt = apply_filters('woocs_currname_in_option', $currency['name']);

    if ($show_money_signs) {
        if (!empty($option_txt)) {
            $option_txt .= ', ' . $currency['symbol'];
        } else {
            $option_txt = $currency['symbol'];
        }
    }
    //***
    if (isset($txt_type)) {
        if ($txt_type == 'desc') {
            if (!empty($currency['description'])) {
                $option_txt = $currency['description'];
            }
        }
    }

    $options[$currency['name']] = $option_txt;
}

//***

$height = (count($options) - 2) * 65 + 200;
?>


<div class="woocs-style-2-drop-down" style="width: <?= $width ?>;" data-expanded-height="<?= $height ?>">
    <div class="woocs-style-2-from">
        <div class="woocs-style-2-from-contents">
            <?php if ($show_flags): ?>
                <div class="woocs-style-2-avatar woocs-style-2-me" style="<?php echo (isset($flags_data[$this->current_currency]) ? $flags_data[$this->current_currency] : '') ?>"></div>
            <?php endif; ?>
            <div class="woocs-style-2-name"><?= $options[$this->current_currency] ?></div>
        </div>
    </div>
    <div class="woocs-style-2-to">
        <div class="woocs-style-2-to-contents">


            <div class="woocs-style-2-top" <?php if (isset($head_bg)): ?>style="background: <?= $head_bg ?>;"<?php endif; ?>>

                <?php if ($show_flags): ?>
                    <div class="woocs-style-2-avatar-large woocs-style-2-me" style="<?php echo (isset($flags_data[$this->current_currency]) ? $flags_data[$this->current_currency] : '') ?>"></div>
                <?php endif; ?>

                <div class="woocs-style-2-name-large" <?php if (isset($head_txt_color)): ?>style="color: <?= $head_txt_color ?>;"<?php endif; ?>><?= $options[$this->current_currency] ?></div>
                <div class="woocs-style-2-x-touch">
                    <div class="woocs-style-2-x" <?php if (isset($head_close_bg)): ?>style="background: <?= $head_close_bg ?>;"<?php endif; ?>>
                        <div class="woocs-style-2-line1" <?php if (isset($head_close_color)): ?>style="background: <?= $head_close_color ?>;"<?php endif; ?>></div>
                        <div class="woocs-style-2-line2" <?php if (isset($head_close_color)): ?>style="background: <?= $head_close_color ?>;"<?php endif; ?>></div>
                    </div>
                </div>
            </div>


            <div class="woocs-style-2-bottom">


                <?php foreach ($options as $key => $value) : ?>
                    <?php if ($key === $this->current_currency AND ! $this->shop_is_cached) continue; ?>
                    <div class="woocs-style-2-row">
                        <div class="woocs-style-2-link" data-currency="<?php echo $key ?>" data-flag="<?php echo (isset($all_currencies[$key]['flag']) ? $all_currencies[$key]['flag'] : '') ?>" style="<?php
                        if (isset($flags_data[$key])) {
                            echo $flags_data[$key];
                        }
                        ?>; <?php if ($key === $this->current_currency): ?>background-size: 40px 25px; background-repeat: no-repeat; background-position: 99% 10px;<?php endif; ?>"><?= $value ?></div>
                    </div>
                <?php endforeach; ?>

            </div>



        </div>
    </div>
</div>

<div class="woocs_display_none">WOOCS v.<?php echo WOOCS_VERSION ?></div>


