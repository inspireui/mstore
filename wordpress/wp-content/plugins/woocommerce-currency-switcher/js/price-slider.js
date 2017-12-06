jQuery(function ($) {
    // woocommerce_price_slider_params is required to continue, ensure the object exists
    if (typeof woocommerce_price_slider_params === 'undefined') {
	return false;
    }

    // Get markup ready for slider
    $('input#min_price, input#max_price').hide();
    $('.price_slider, .price_label').show();

    // Price slider uses jquery ui
    var min_price = $('.price_slider_amount #min_price').data('min');
    var max_price = $('.price_slider_amount #max_price').data('max');

    current_min_price = parseInt(min_price, 10);
    current_max_price = parseInt(max_price, 10);

    if (woocommerce_price_slider_params.min_price)
	current_min_price = parseInt(woocommerce_price_slider_params.min_price, 10);
    if (woocommerce_price_slider_params.max_price)
	current_max_price = parseInt(woocommerce_price_slider_params.max_price, 10);

    $('body').bind('price_slider_create price_slider_slide', function (event, min, max) {

	var label_min = min;
	var label_max = max;

	if (woocs_current_currency.rate !== 1) {
	    label_min = Math.ceil(label_min * parseFloat(woocs_current_currency.rate));
	    label_max = Math.ceil(label_max * parseFloat(woocs_current_currency.rate));
	}

	//+++
	label_min = number_format(label_min, 2, '.', ',');
	label_max = number_format(label_max, 2, '.', ',');

	if ($.inArray(woocs_current_currency.name, woocs_array_no_cents) || woocs_current_currency.hide_cents == 1) {
	    label_min = label_min.replace('.00', '');
	    label_max = label_max.replace('.00', '');
	}

	//+++

	var currency_symbol = woocommerce_price_slider_params.currency_symbol;
	if (typeof currency_symbol == 'undefined') {
	    currency_symbol = woocommerce_price_slider_params.currency_format_symbol;
	}

	//+++

	if (woocs_current_currency.position === 'left') {

	    $('.price_slider_amount span.from').html(currency_symbol + label_min);
	    $('.price_slider_amount span.to').html(currency_symbol + label_max);

	} else if (woocs_current_currency.position === 'left_space') {

	    $('.price_slider_amount span.from').html(currency_symbol + " " + label_min);
	    $('.price_slider_amount span.to').html(currency_symbol + " " + label_max);

	} else if (woocs_current_currency.position === 'right') {

	    $('.price_slider_amount span.from').html(label_min + currency_symbol);
	    $('.price_slider_amount span.to').html(label_max + currency_symbol);

	} else if (woocs_current_currency.position === 'right_space') {

	    $('.price_slider_amount span.from').html(label_min + " " + currency_symbol);
	    $('.price_slider_amount span.to').html(label_max + " " + currency_symbol);

	}

	$('body').trigger('price_slider_updated', min, max);
    });

    $('.price_slider').slider({
	range: true,
	animate: true,
	min: min_price,
	max: max_price,
	values: [current_min_price, current_max_price],
	create: function (event, ui) {

	    $('.price_slider_amount #min_price').val(current_min_price);
	    $('.price_slider_amount #max_price').val(current_max_price);

	    $('body').trigger('price_slider_create', [current_min_price, current_max_price]);
	},
	slide: function (event, ui) {

	    $('input#min_price').val(ui.values[0]);
	    $('input#max_price').val(ui.values[1]);

	    $('body').trigger('price_slider_slide', [ui.values[0], ui.values[1]]);
	},
	change: function (event, ui) {

	    $('body').trigger('price_slider_change', [ui.values[0], ui.values[1]]);

	}
    });

});


//https://github.com/kvz/phpjs/blob/master/functions/strings/number_format.js
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '')
	    .replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
	    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	    s = '',
	    toFixedFix = function (n, prec) {
		var k = Math.pow(10, prec);
		return '' + (Math.round(n * k) / k)
			.toFixed(prec);
	    };
// Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
	    .split('.');
    if (s[0].length > 3) {
	s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
	    .length < prec) {
	s[1] = s[1] || '';
	s[1] += new Array(prec - s[1].length + 1)
		.join('0');
    }
    return s.join(dec);
}