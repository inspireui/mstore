//https://codepen.io/General-Dev/pen/JRjwPa
jQuery(function ($) {
    /*Dropdown Menu*/
    $('.woocs-style-1-dropdown').click(function () {
        $(this).attr('tabindex', 1).focus();
        $(this).toggleClass('woocs-style-1-active');
        $(this).find('.woocs-style-1-dropdown-menu').slideToggle(300);
    });
    $('.woocs-style-1-dropdown').focusout(function () {
        $(this).removeClass('woocs-style-1-active');
        $(this).find('.woocs-style-1-dropdown-menu').slideUp(300);
    });
    $('.woocs-style-1-dropdown .woocs-style-1-dropdown-menu li').click(function () {
        $(this).parents('.woocs-style-1-dropdown').find('span').text($(this).text());
        $(this).parents('.woocs-style-1-dropdown').find('input').attr('value', $(this).attr('id'));
    });
    /*End Dropdown Menu*/

    jQuery('.woocs-style-1-dropdown-menu li').on('click', function () {

        var l = woocs_remove_link_param('currency', window.location.href);
        l = l.replace("#", "");

        if (woocs_special_ajax_mode) {
            var data = {
                action: "woocs_set_currency_ajax",
                currency: jQuery(this).data('currency')
            };

            jQuery.post(woocs_ajaxurl, data, function (value) {
                //location.reload();
                window.location = l;
            });
        } else {
            if (Object.keys(woocs_array_of_get).length === 0) {
                window.location = l + '?currency=' + jQuery(this).data('currency');
            } else {
                woocs_redirect(jQuery(this).data('currency'));
            }
        }
    });

    //***

    document.addEventListener('after_woocs_get_products_price_html', function (e) {
        var current_currency = e.detail.current_currency;
        //current_currency='USD';
        jQuery.each(jQuery('.woocs-style-1-dropdown'), function (i, d) {
            jQuery(d).find('.woocs-style-1-select > span').html(jQuery(d).find(`li[data-currency=${current_currency}]`).html());
        });
    });
});



