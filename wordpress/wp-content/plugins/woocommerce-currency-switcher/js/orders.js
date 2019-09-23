"use strict";

function woocs_recalculate_all_orders_data() {
    if (confirm('Sure? This operation could not be rollback!!')) {
        jQuery('.woocs_recalculate_all_orders_curr_button').prop('href', 'javascript:void(0);');

        var data_count = {
            action: "woocs_all_order_ids"
        };
        jQuery('.woocs_ajax_preload').show();
        jQuery.post(ajaxurl, data_count, function (ids) {

            var orders = jQuery.parseJSON(ids);
            if (orders.length) {
                woocs_recalculate_all_orders_data_do(0, orders);
            }
        });

    }
}
function woocs_recalculate_all_orders_data_do(start, orders) {
    var count_orders = orders.length;
    var step = 10;
    var orders_ids = orders.slice(start, start + step);
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'woocs_recalculate_orders_data',
            order_ids: orders_ids
        },
        success: function (dat) {
            if ((start + step) > count_orders) {
                jQuery('.woocs_ajax_preload').hide();
                alert("Done!");
                window.location.reload();
            } else {
                woocs_recalculate_all_orders_data_do(start + step, orders)
            }
        },
        error: function () {
            alert("Something wrong!");
        }
    });

}


