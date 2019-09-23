jQuery(function ($) {
    var alert_w = $('#woocs_alert_notice');
    alert_w.on('click', '.notice-dismiss', function (e) {
        //e.preventDefault
        $.post(ajaxurl, {
            action: 'woocs_dismiss_alert',
            alert: 'woocommerce-currency-switcher',
            sec: jQuery("#woocs_alert_notice").data('nonce')
        });
    });
});


