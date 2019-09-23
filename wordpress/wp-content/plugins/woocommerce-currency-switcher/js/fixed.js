"use strict";
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
function woocs_open_tab(tabName, hash) {
    var i = 0;
    var x = document.getElementsByClassName("woocs_tab");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    document.getElementById(tabName + '_' + hash).style.display = "block";

    //***
    jQuery('.woocs_tab_button').removeClass('active');
    jQuery('#' + tabName + '_btn_' + hash).addClass('active');
}

//***

function woocs_add_product_price(post_id, hash) {
    var code = jQuery('#woocs_mselect_' + hash).val();
    if (code) {
        var html = jQuery('#woocs_multiple_simple_tpl').html();
        html = html.replace(/__POST_ID__/gi, post_id);
        html = html.replace(/__HASH__/gi, hash);
        html = html.replace(/__CURR_CODE__/gi, code);
        jQuery('#woocs_mlist_' + hash).append(html);
        //***
        jQuery("#woocs_mselect_" + hash + " option[value='" + code + "']").remove();
        jQuery("#woocs_mselect_" + hash + " option").eq(0).prop('selected', 'selected');
    }
    woocs_enable_save_variation_changes();
}

function woocs_add_all_product_price(post_id, hash) {
    jQuery.each(jQuery("#woocs_mselect_" + hash + " option"), function (i, code) {
        jQuery("#woocs_mselect_" + hash + " option[value='" + code + "']").prop('selected', 'selected');
        woocs_add_product_price(post_id, hash);
    });
}
function woocs_add_fixed_field(post_id,selector, hash) {
    var code = jQuery('#woocs_multiple_simple_select_'+selector +'_'+ hash).val();
    
    if (code) {
        var html = jQuery('#woocs_multiple_simple_tpl_'+selector).html();
        console.log(html)
        html = html.replace(/__POST_ID__/gi, post_id);
        html = html.replace(/__HASH__/gi, hash);
        html = html.replace(/__CURR_CODE__/gi, code);
        jQuery('#woocs_multiple_simple_list_'+selector +'_' + hash).append(html);
        //***
        jQuery("#woocs_multiple_simple_select_"+selector +'_' + hash + " option[value='" + code + "']").remove();
        jQuery("#woocs_multiple_simple_select_"+selector +'_' + hash + " option").eq(0).prop('selected', 'selected');
    }
    woocs_enable_save_variation_changes();
}

function woocs_add_all_fixed_field(post_id,selector, hash) {
    jQuery.each(jQuery("#woocs_multiple_simple_select_"+selector +"_" + hash + " option"), function (i, code) {
        jQuery("#woocs_multiple_simple_select_"+selector +"_" + hash + " option[value='" + code + "']").prop('selected', 'selected');
        woocs_add_fixed_field(post_id,selector, hash);
    });
}

function woocs_add_select_product_price(hash, code) {
    jQuery('#woocs_mselect_' + hash).append('<option value="' + code + '">' + code + '</option>');
}
function woocs_add_select_fixed_field(hash, code,selector) {
    jQuery('#woocs_mselect_'+selector +'_' + hash).append('<option value="' + code + '">' + code + '</option>');
}

function woocs_remove_li_product_price(hash, code, geo) {
    if (geo) {
        //code is time here
        jQuery('#woocs_li_geo_' + hash + '_' + code).remove();
    } else {
        jQuery('#woocs_li_' + hash + '_' + code).remove();
    }

    woocs_add_select_product_price(hash, code);
    woocs_enable_save_variation_changes();
}
function woocs_remove_li_fixed_field(hash, code, geo,selector) {

    if (geo) {
        //code is time here
        jQuery('#woocs_mlist_geo_'+selector +'_' + hash + ' #woocs_li_geo_' + hash + '_' + code).remove();
    } else {
        jQuery('#woocs_multiple_simple_list_'+selector +'_' + hash + ' #woocs_li_' + hash + '_' + code).remove();
    }

    woocs_add_select_fixed_field(hash, code,selector);
    woocs_enable_save_variation_changes();
}

function woocs_enable_save_variation_changes() {
    //jQuery('.save-variation-changes').removeAttr('disabled');
    jQuery('.form-row textarea').trigger('change');
}

/**************************************/

function woocs_add_group_geo(post_id, hash) {
    var html = jQuery('#woocs_multiple_simple_tpl_geo').html();
    html = html.replace(/__POST_ID__/gi, post_id);
    html = html.replace(/__HASH__/gi, hash);
    var d = new Date();
    var index = d.getTime();
    html = html.replace(/__INDEX__/gi, index);
    jQuery('#woocs_mlist_geo_' + hash).append(html);
    //jQuery('#woocs_li_geo_' + post_id + '_' + index + ' select').addClass('chosen_select');
    jQuery('#woocs_li_geo_' + hash + '_' + index + ' select').chosen();
    jQuery('#woocs_li_geo_' + hash + '_' + index + ' select').trigger("liszt:updated");
    //***
    woocs_enable_save_variation_changes();
}

function woocs_add_group_user_role(post_id, hash) {
    var html = jQuery('#woocs_multiple_simple_tpl_user_role').html();
    html = html.replace(/__POST_ID__/gi, post_id);
    html = html.replace(/__HASH__/gi, hash);
    var d = new Date();
    var index = d.getTime();
    html = html.replace(/__INDEX__/gi, index);
    jQuery('#woocs_mlist_user_role_' + hash).append(html);
    //jQuery('#woocs_li_geo_' + post_id + '_' + index + ' select').addClass('chosen_select');
    jQuery('#woocs_li_user_role_' + hash + '_' + index + ' select').chosen();
    jQuery('#woocs_li_user_role_' + hash + '_' + index + ' select').trigger("liszt:updated");
    //***
    woocs_enable_save_variation_changes();
}
function woocs_remove_li_user_role_price(hash, code) {
    
    jQuery('#woocs_li_user_role_' + hash + '_' + code).remove();

    woocs_add_select_product_price(hash, code);
    woocs_enable_save_variation_changes();
}