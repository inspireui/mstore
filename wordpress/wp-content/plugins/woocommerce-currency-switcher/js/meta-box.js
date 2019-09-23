 "use strict";
var woocs_old_currency = null;
function woocs_change_order_data() {
    woocs_old_currency = jQuery('#woocs_order_metabox .woocs_order_currency i').html();
    jQuery('#woocs_order_metabox .woocs_order_currency select').show();
    jQuery('#woocs_order_metabox .woocs_order_currency select').attr('name', 'woocs_order_currency');
    jQuery('#woocs_order_metabox .woocs_order_currency select').val(woocs_old_currency);
    jQuery('.woocs_change_order_curr_button').hide();
    jQuery('.woocs_cancel_order_curr_button').show();
}

function woocs_cancel_order_data() {
    jQuery('#woocs_order_metabox .woocs_order_currency select').hide();
    jQuery('#woocs_order_metabox .woocs_order_currency select').attr('name', 'woocs_order_currency2');
    jQuery('.woocs_change_order_curr_button').show();
    jQuery('.woocs_cancel_order_curr_button').hide();
}

function woocs_recalculate_order_data() {
    if (confirm('Sure? This operation could not be rollback!!')) {
        var id= jQuery(".woocs_recalculate_order_curr_button").data('order_id');
        jQuery('.woocs_recalculate_order_curr_button').prop('href', 'javascript:void(0);');
        var data = {
            action: "woocs_recalculate_order_data",
            order_id: id
        };

        jQuery.post(ajaxurl, data, function (data) {
            window.location.reload();
        });
    }
}


