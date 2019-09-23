"use strict";
jQuery(function ($) {
    $('#woocs_auto_switcher_skin').on("change", function () {
        var woocs_side_switcher_skin = $(this).val();
        if (woocs_side_switcher_skin == 'roll_blocks') {
            $('.woocs_roll_blocks_width').show(200);
        } else {
            $('.woocs_roll_blocks_width').hide(200);
        }
    });


    $('#woocs_is_multiple_allowed').on("change", function () {
        var woocs_is_multiple_allowed = parseInt($(this).val(), 10);
        var woocs_is_fixed_enabled = parseInt($('#woocs_is_fixed_enabled').val(), 10);
        //***
        if (woocs_is_multiple_allowed) {
            $('input[name=woocs_is_fixed_enabled]').parents('tr').show(200);
            $('input[name=woocs_is_fixed_coupon]').parents('tr').show(200);
            $('input[name=woocs_is_fixed_shipping]').parents('tr').show(200);
            if (woocs_is_fixed_enabled) {
                $('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').show(200);
            }
        } else {
            $('input[name=woocs_is_fixed_enabled]').parents('tr').hide(200);
            $('input[name=woocs_is_fixed_coupon]').parents('tr').hide(200);
            $('input[name=woocs_is_fixed_shipping]').parents('tr').hide(200);
            $('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').hide(200);
        }
    });


    //***

    init_switcher23();

    document.addEventListener('woocs_blind_option', function (e) {
        //_this.change_field(e.detail.page_id, e.detail.name, 'checkbox', e.detail.value);
        if (parseInt(e.detail.value, 10)) {
            alert(woocs_lang.blind_option);
        }

        //***

        if (e.detail.name === 'woocs_is_fixed_enabled') {
            if (parseInt(e.detail.value, 10)) {
                jQuery('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').show(200);
            } else {
                jQuery('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').hide(200);
            }
        }
    });


    document.addEventListener('woocs_is_auto_switcher', function (e) {
        if (parseInt(e.detail.value, 10)) {
            $('#tabs-6 .form-table tbody > tr').not(':first').show(200);
            $('#tabs-6 .form-table').not(':first').show(200);
            $('#tabs-6 .demo-img-1').show(200);
        } else {
            $('#tabs-6 .form-table tbody > tr').not(':first').hide(200);
            $('#tabs-6 .form-table').not(':first').hide(200);
            $('#tabs-6 .demo-img-1').hide(200);
        }
    });

    //***

    $('.woocs-select-all-in-select').on('click', function () {
        $(this).parents('td').find('select option').attr('selected', true);
        $(this).parents('td').find('select').trigger('change');
        return false;
    });

    $('.woocs-clear-all-in-select').on('click', function () {
        $(this).parents('td').find('select option').attr('selected', false);
        $(this).parents('td').find('select').trigger('change');
        return false;
    });

});

//*********************

function woocs_update_all_rates() {
    jQuery('.woocs_is_etalon:checked').trigger('click');
}

function woocs_add_money_sign2() {
    jQuery('a[href=#tabs-2]').trigger('click');
    jQuery('#tabs-2').find('#woocs_customer_signs').focus();
    $('#woocs_customer_signs').scroll();
}

function init_switcher23(container = '') {
    Array.from(document.querySelectorAll(container + ' .switcher23')).forEach((button) => {
        button.addEventListener('click', function () {

            if (this.value > 0) {
                this.value = 0;
                this.previousSibling.value = 0;
                this.removeAttribute('checked');
            } else {
                this.value = 1;
                this.previousSibling.value = 1;
                this.setAttribute('checked', 'checked');
            }

            //Trigger the event
            if (this.getAttribute('data-event').length > 0) {
                //window.removeEventListener(this.getAttribute('data-event'));
                document.dispatchEvent(new CustomEvent(this.getAttribute('data-event'), {detail: {
                        name: this.previousSibling.getAttribute('name'),
                        value: parseInt(this.value, 10)
                    }}));

                //this.setAttribute('data-event-attached', 1);
            }


            return true;
        });
    });
}

