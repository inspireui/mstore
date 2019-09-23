(function ($, window) {

    'use strict';
    jQuery(function () {
        "use strict";
        jQuery('.form-table .forminp #woocommerce_currency').parents('tr').remove();
        jQuery('.form-table .forminp #woocommerce_currency_pos').parents('tr').remove();

    });
    $.fn.wfcTabs = function (options) {

        if (!this.length)
            return;

        return this.each(function () {

            var $this = $(this);

            ({
                init: function () {
                    this.tabsNav = $this.children('nav');
                    this.items = $this.children('.content-wrap').children('section');
                    this._show();
                    this._initEvents();
                },
                _initEvents: function () {
                    var self = this;
                    this.tabsNav.on('click', 'a', function (e) {
                        e.preventDefault();
                        self._show($(this));
                    });
                },
                _show: function (element) {

                    if (element == undefined) {
                        this.firsTab = this.tabsNav.find('li').first();
                        this.firstSection = this.items.first();

                        if (!this.firsTab.hasClass('tab-current')) {
                            this.firsTab.addClass('tab-current');
                        }

                        if (!this.firstSection.hasClass('content-current')) {
                            this.firstSection.addClass('content-current');
                        }
                    }

                    var $this = $(element),
                            $to = $($this.attr('href'));

                    if ($to.length) {
                        $this.parent('li').siblings().removeClass().end().addClass('tab-current');
                        $to.siblings().removeClass().end().addClass('content-current');
                    }

                }

            }).init();

        });
    };


})(jQuery, window);

jQuery('.wfc-tabs').wfcTabs();
jQuery(function ($) {

    jQuery.fn.life = function (types, data, fn) {
        jQuery(this.context).on(types, this.selector, data, fn);
        return this;
    };

    // jQuery("#tabs").tabs();

    jQuery('body').append('<div id="woocs_buffer" style="display: none;"></div>');

    jQuery("#woocs_list").sortable({
        handle: '.woocs_settings_move img'
    });

    jQuery('#woocs_add_currency').life('click', function () {
        jQuery('#woocs_list').append(jQuery('#woocs_item_tpl').html());
        return false;
    });
    jQuery('.woocs_del_currency').life('click', function () {
        jQuery(this).parents('li').hide(220, function () {
            jQuery(this).remove();
        });
        return false;
    });

    jQuery('.woocs_is_etalon').life('click', function () {
        jQuery('.woocs_is_etalon').next('input[type=hidden]').val(0);
        jQuery('.woocs_is_etalon').prop('checked', 0);
        jQuery(this).next('input[type=hidden]').val(1);
        jQuery(this).prop('checked', 1);
        jQuery(this).parents('li').find("input[name='woocs_rate[]']").val(1);
        jQuery(this).parents('li').find("input[name='woocs_rate_plus[]']").val('');
        //instant save
        var currency_name = jQuery(this).parents('li').find('input[name="woocs_name[]"]').val();
        if (currency_name.length) {
            woocs_show_stat_info_popup('Loading ...');
            var data = {
                action: "woocs_save_etalon",
                currency_name: currency_name
            };
            jQuery.post(ajaxurl, data, function (request) {
                try {
                    request = jQuery.parseJSON(request);
                    jQuery.each(request, function (index, value) {
                        var elem = jQuery('input[name="woocs_name[]"]').filter(function () {
                            return this.value.toUpperCase() == index;
                        });

                        if (elem) {
                            jQuery(elem).parent().find('input[name="woocs_rate[]"]').val(value);
                            jQuery(elem).parent().find('input[name="woocs_rate[]"]').text(value);
                        }
                    });

                    woocs_hide_stat_info_popup();
                    woocs_show_info_popup('Save changes please!', 1999);
                } catch (e) {
                    woocs_hide_stat_info_popup();
                    alert('Request error! Try later or another agregator!');
                }
            });
        }

        return true;
    });


    jQuery('.woocs_flag_input').life('change', function ()
    {
        jQuery(this).next('a.woocs_flag').find('img').attr('src', jQuery(this).val());
    });

    jQuery('.woocs_flag').life('click', function ()
    {
        var input_object = jQuery(this).prev('input[type=hidden]');
        window.send_to_editor = function (html)
        {
            woocs_insert_html_in_buffer(html);
            var imgurl = jQuery('#woocs_buffer').find('a').eq(0).attr('href');
            woocs_insert_html_in_buffer("");
            jQuery(input_object).val(imgurl);
            jQuery(input_object).trigger('change');
            tb_remove();
        };
        tb_show('', 'media-upload.php?post_id=0&type=image&TB_iframe=true');

        return false;
    });

    jQuery('.woocs_get_fresh_rate').life('click', function () {
        var currency_name = jQuery(this).parent().find('input[name="woocs_name[]"]').val();
        //console.log(currency_name);
        var _this = this;
        jQuery(_this).parent().find('input[name="woocs_rate[]"]').val('loading ...');
        var data = {
            action: "woocs_get_rate",
            currency_name: currency_name
        };
        jQuery.post(ajaxurl, data, function (value) {
            jQuery(_this).parent().find('input[name="woocs_rate[]"]').val(value);
        });

        return false;
    });

    //***

    $('.label.container').life('click', function () {
        $(this).find('input[type=radio]').trigger('click');
        return true;
    });

    //loader
    jQuery(".woocs-admin-preloader").fadeOut("slow");

});


function woocs_insert_html_in_buffer(html) {
    jQuery('#woocs_buffer').html(html);
}
function woocs_get_html_from_buffer() {
    return jQuery('#woocs_buffer').html();
}

function woocs_show_info_popup(text, delay) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
    window.setTimeout(function () {
        jQuery(".info_popup").fadeOut(400);
    }, delay);
}

function woocs_show_stat_info_popup(text) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
}


function woocs_hide_stat_info_popup() {
    window.setTimeout(function () {
        jQuery(".info_popup").fadeOut(400);
    }, 500);
}
function woocs_auto_hide_color() {
    if (jQuery('#woocs_is_auto_switcher').val() == 0) {
        jQuery('#woocs_auto_switcher_color').parents('tr').hide();
        jQuery('#woocs_auto_switcher_hover_color').parents('tr').hide();
    }
}
woocs_auto_hide_color();

function woocs_check_api_key_field() {
    var aggregator = jQuery("#woocs_currencies_aggregator").val();
    var is_api = ['free_converter', 'fixer', 'currencylayer','openexchangerates'];
    if (jQuery.inArray(aggregator, is_api) != -1) {
        jQuery("#woocs_aggregator_key").parents("tr").show();
    } else {
        jQuery("#woocs_aggregator_key").parents("tr").hide();
    }
}

woocs_check_api_key_field();
jQuery("#woocs_currencies_aggregator").change(function () {
    woocs_check_api_key_field();
});



function woocs_set_cookie(name, value, days = 365) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function woocs_get_cookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

