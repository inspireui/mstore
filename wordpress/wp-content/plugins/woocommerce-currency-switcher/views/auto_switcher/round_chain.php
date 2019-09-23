<?php if (!defined('ABSPATH')) die('No direct access allowed'); 
global $WOOCS;
$currencies=apply_filters('woocs_currency_manipulation_before_show', $WOOCS->get_currencies());
?>
   <?php ob_start()?> 
        .woocs_auto_switcher {
            top: <?php echo  $top?>;
        } 
        .woocs_auto_switcher a .woocs_auto_switcher_text:hover {
            background: <?php echo  $hover_color?>;
        }
        .woocs_auto_switcher a .woocs_auto_switcher_text, .woocs_auto_switcher{
            background: <?php echo  $color?>;
        }
        .woocs_auto_switcher a.curr_curr .woocs_auto_switcher_text{
            background: <?php echo  $hover_color?>;
        }
        <?php 
        $count=0;
        foreach ($currencies as $key=>$item):
         $count++;   
            ?>
        .woocs_auto_switcher:hover .woocs_auto_switcher_item_<?php echo  $key?> {
          left:calc(38px*<?php echo $count?>);
        }
        <?php endforeach;?>
    <?php 
    $data= ob_get_clean();
    wp_add_inline_style( 'woocommerce-currency-switcher', $data );
?>  

<div class="woocs_auto_switcher">
    <span class="woocs_current_text"><?php echo  $this->prepare_field_text($currencies[$WOOCS->current_currency],$basic_field); ?></span>
    <?php foreach ($currencies as $key=>$item):
        
    if (isset($item['hide_on_front']) AND $item['hide_on_front']) {
        continue;
    }    
        
    $current="";
    if($key==$WOOCS->current_currency){
        $current="curr_curr";
    }
    $base_text=$this->prepare_field_text($item,$basic_field);
    $add_text=$this->prepare_field_text($item,$add_field);
        ?>  
    <a class=" woocs_auto_switcher_item woocs_auto_switcher_item_<?php echo  $key?> <?php echo  $current?>" href="#"> 
        <div class="woocs_auto_switcher_text">
            <span><?php echo  $base_text ?> </span>
        </div>
    </a> 

    <?php endforeach;?>
          
</div>

