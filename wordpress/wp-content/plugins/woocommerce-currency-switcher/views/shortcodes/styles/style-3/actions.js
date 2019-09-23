//https://www.cssscript.com/demo/material-design-dialog-dudialog/
jQuery(function ($) {
    $('.woocs-style-3-du-dialog').removeAttr('style');

    $('.woocs-style-3-du-dialog-starter').on('click', function () {
        $(this).prev('.woocs-style-3-du-dialog').addClass('woocs-style-3-dlg--open');
    });

    $('.woocs-style-3-close').on('click', function () {
        $('.woocs-style-3-du-dialog').removeClass('woocs-style-3-dlg--open');
    });

    $(document).keyup(function (e) {
        if (e.keyCode == 27) {
            $('.woocs-style-3-close').trigger('click');
        }
    });

    //***

    $('.woocs-style-3-du-dialog input[type=radio]').on('click', function () {

        //$(_this).parents('.woocs-style-3-du-dialog').addClass('woocs-style-3-dlg--closing');
        var _this = this;
        setTimeout(function () {
            $(_this).parents('.woocs-style-3-du-dialog').addClass('woocs-style-3-dlg--closing');
            $(_this).parents('.woocs-style-3-du-dialog').removeClass('woocs-style-3-dlg--open');
        }, 333);

        //***

        var l = woocs_remove_link_param('currency', window.location.href);
        l = l.replace("#", "");

        if (woocs_special_ajax_mode) {
            var data = {
                action: "woocs_set_currency_ajax",
                currency: $(this).val()
            };

            $.post(woocs_ajaxurl, data, function (value) {
                window.location = l;
            });
        } else {
            if (Object.keys(woocs_array_of_get).length === 0) {
                window.location = l + '?currency=' + $(this).val();
            } else {
                woocs_redirect($(this).val());
            }
        }

        return true;
    });

    //***

    document.addEventListener('after_woocs_get_products_price_html', function (e) {
        var current_currency = e.detail.current_currency;
        //current_currency='USD';
        jQuery(`.woocs-style-3-dlg-select-radio`).prop('checked', false);
        jQuery(`.woocs-style-3-dlg-select-radio[value=${current_currency}]`).prop('checked', true);
        jQuery('.woocs-style-3-du-dialog-starter').html(jQuery(`.woocs-style-3-dlg-select-radio[value=${current_currency}]`).next('label').html());
        jQuery('.woocs-style-3-du-dialog-starter').css('background-image', 'url(' + jQuery(`.woocs-style-3-dlg-select-radio[value=${current_currency}]`).parent().data('flag') + ')');
    });
});

