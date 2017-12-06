<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<span class="woocs_current_currency woocs_current_currency_<?php echo $currency ?>">

    <?php if (!empty($text)): ?>
        <strong class="woocs_current_currency_text"><?php echo $text ?></strong>
    <?php endif; ?>

    <?php if ($code): ?>
        <strong class="woocs_current_currency_code"><?php echo $currencies[$currency]['name'] ?></strong>&nbsp;
    <?php endif; ?>

    <?php if ($flag): ?>
        <img class="woocs_current_currency_flag" src="<?php echo $currencies[$currency]['flag'] ?>" alt="" />
    <?php endif; ?>

</span>
