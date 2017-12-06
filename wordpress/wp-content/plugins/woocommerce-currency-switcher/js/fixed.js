jQuery(function ($) {
    $('.woocs_multiple_simple_panel').show(200);
    //woocs_open_tab("woocs_tab_fixed");

    jQuery.fn.life = function (types, data, fn) {
        jQuery(this.context).on(types, this.selector, data, fn);
        return this;
    };

    $('.woocs_price_col .wc_input_price, .chosen_select').life('change', function () {
        woocs_enable_save_variation_changes();
    });

});

//http://www.w3schools.com/w3css/w3css_tabulators.asp
function woocs_open_tab(tabName, product_id) {
    var i = 0;
    var x = document.getElementsByClassName("woocs_tab");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    document.getElementById(tabName + '_' + product_id).style.display = "block";

    //***
    jQuery('.woocs_tab_button').removeClass('active');
    jQuery('#' + tabName + '_btn_' + product_id).addClass('active');
}

//***

function woocs_add_product_price(post_id) {
    var code = jQuery('#woocs_multiple_simple_select_' + post_id).val();
    if (code) {
        var html = jQuery('#woocs_multiple_simple_tpl').html();
        html = html.replace(/__POST_ID__/gi, post_id);
        html = html.replace(/__CURR_CODE__/gi, code);
        jQuery('#woocs_multiple_simple_list_' + post_id).append(html);
        //***
        jQuery("#woocs_multiple_simple_select_" + post_id + " option[value='" + code + "']").remove();
        jQuery("#woocs_multiple_simple_select_" + post_id + " option").eq(0).prop('selected', 'selected');
    }
    woocs_enable_save_variation_changes();
}

function woocs_add_all_product_price(post_id) {
    jQuery.each(jQuery("#woocs_multiple_simple_select_" + post_id + " option"), function (i, code) {
        jQuery("#woocs_multiple_simple_select_" + post_id + " option[value='" + code + "']").prop('selected', 'selected');
        woocs_add_product_price(post_id);
    });
}

function woocs_add_select_product_price(post_id, code) {
    jQuery('#woocs_multiple_simple_select_' + post_id).append('<option value="' + code + '">' + code + '</option>');
}

function woocs_remove_li_product_price(post_id, code, geo) {

    if (geo) {
        //code is time here
        jQuery('#woocs_multiple_simple_list_geo_' + post_id + ' #woocs_li_geo_' + post_id + '_' + code).remove();
    } else {
        jQuery('#woocs_multiple_simple_list_' + post_id + ' #woocs_li_' + post_id + '_' + code).remove();
    }

    woocs_add_select_product_price(post_id, code);
    woocs_enable_save_variation_changes();
}

function woocs_enable_save_variation_changes() {
    //jQuery('.save-variation-changes').removeAttr('disabled');
    //console.log('Hello World 2016!!');
    jQuery('.form-row textarea').trigger('change');
}

/**************************************/

function woocs_add_group_geo(post_id) {
    var html = jQuery('#woocs_multiple_simple_tpl_geo').html();
    html = html.replace(/__POST_ID__/gi, post_id);
    var d = new Date();
    var index = d.getTime();
    html = html.replace(/__INDEX__/gi, index);
    jQuery('#woocs_multiple_simple_list_geo_' + post_id).append(html);
    //jQuery('#woocs_li_geo_' + post_id + '_' + index + ' select').addClass('chosen_select');
    jQuery('#woocs_li_geo_' + post_id + '_' + index + ' select').chosen();
    jQuery('#woocs_li_geo_' + post_id + '_' + index + ' select').trigger("liszt:updated");
    //***
    woocs_enable_save_variation_changes();
}

