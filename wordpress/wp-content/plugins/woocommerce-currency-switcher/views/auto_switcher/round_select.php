<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
global $WOOCS;
$currencies = apply_filters('woocs_currency_manipulation_before_show', $WOOCS->get_currencies());
?>
<img src=""/>

<?php ob_start() ?> 
.woocs_auto_switcher.cd-stretchy-nav.nav-is-visible ul a.woocs_curr_curr {
color: <?php echo $hover_color ?>;
}
.woocs_auto_switcher.cd-stretchy-nav.nav-is-visible ul a:hover{
color: <?php echo $hover_color ?>;
}
.woocs_auto_switcher.cd-stretchy-nav {
top: <?php echo $top ?>;
}
.woocs_auto_switcher.cd-stretchy-nav .stretchy-nav-bg {
background: <?php echo $color ?>;
}
<?php if (stripos($basic_field, "__FLAG__") !== false): ?>
    .woocs_auto_switcher .woocs_base_text{
    top: 30%;   
    }
<?php endif ?>   
<?php
$data = ob_get_clean();
wp_add_inline_style('woocommerce-currency-switcher', $data);
?>  

<nav class="woocs_auto_switcher  cd-stretchy-nav <?php echo $side ?>" data-view="round_select">
    <a class="cd-nav-trigger" href="#">
        <span class="woocs_current_text"><?php echo $this->prepare_field_text($currencies[$WOOCS->current_currency], $basic_field); ?></span> 
    </a>

    <ul>
        <?php
        foreach ($currencies as $key => $item):
            if (isset($item['hide_on_front']) AND $item['hide_on_front']) {
                continue;
            }

            $current = "";
            if ($key == $WOOCS->current_currency) {
                $current = "woocs_curr_curr";
            }
            $base_text = $this->prepare_field_text($item, $basic_field);
            $add_text = $this->prepare_field_text($item, $add_field);
            ?>  
            <li>
                <a data-currency="<?php echo $key ?>" href="#" class="<?php echo $current ?> woocs_auto_switcher_link">
    <?php if ($side == 'left'): ?>
                        <span class="woocs_base_text"><?php echo $base_text ?></span>                  
                        <span class="woocs_add_field"> <?php echo $add_text ?> </span>              
    <?php else: ?>
                        <span class="woocs_add_field"> <?php echo $add_text ?> </span>
                        <span class="woocs_base_text"><?php echo $base_text ?></span>
    <?php endif; ?>
                </a>
            </li>
<?php endforeach; ?>
    </ul>

    <span aria-hidden="true" class="stretchy-nav-bg"></span>
</nav>