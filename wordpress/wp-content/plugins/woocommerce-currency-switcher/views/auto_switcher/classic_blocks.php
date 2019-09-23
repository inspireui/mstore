<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
global $WOOCS;
$currencies = apply_filters('woocs_currency_manipulation_before_show', $WOOCS->get_currencies());
?>
<?php ob_start() ?> 
.woocs_auto_switcher {
top: <?php echo $top ?>;
} 
.woocs_auto_switcher li a {
background:<?php echo $color ?>;
}
.woocs_auto_switcher li a.woocs_curr_curr {
background:<?php echo $hover_color ?>;
}
.woocs_auto_switcher li  a:hover {
background:<?php echo $hover_color ?>;
}
.woocs_auto_switcher li  a span {
background:<?php echo $hover_color ?>;
}
.woocs_auto_switcher.left li span:after {
border-right: 10px solid <?php echo $hover_color ?>;
}
.woocs_auto_switcher.right li a span:after {
border-left: 10px solid <?php echo $hover_color ?>;
}
<?php
$data = ob_get_clean();
wp_add_inline_style('woocommerce-currency-switcher', $data);
?>           
<ul class='woocs_auto_switcher <?php echo $side ?>' data-view="classic_blocks">
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
            <a data-currency="<?php echo $key ?>" class="  <?php echo $current ?> woocs_auto_switcher_link" href="#"><?php echo $base_text ?> 
                <span><div ><?php echo $add_text ?></div></span>
            </a> 
        </li>
    <?php endforeach; ?>

</ul>
