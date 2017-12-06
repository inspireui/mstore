(function($) {
  "use strict";

	jQuery(document).ready(function($){

		//Anomation at load -----------------
		Pace.on('done', function(event) {
				
		});//Pace
			
			//Scroll to top		 
			$(".ql_scroll_top").click(function() {
			  $("html, body").animate({ scrollTop: 0 }, "slow");
			  return false;
			});


			/*
			WooCommerce Cart Widget Button
			=========================================================
			*/
			$('body').on('click', '.ql_cart-btn', function(event) {
				event.preventDefault();
				/* Act on the event */
				window.location.href = $(this).attr('href');
			});


			/*Woocommerce Cart
			------------------------------------------------*/
			//Add to cart animation
			$("body").on('click', '.product .add_to_cart_button:not(.product_type_variable)', function(event) {
				event.preventDefault();

				//Doesn't animate on mobile
				if ($(window).width() > 480) {
					var ql_isbig = false;
					if ($(".ql_products_big").length > 0) {
						var $imageToMove = $(this).parents(".product").find(".product_thumbnail_wrap").children("img");
						ql_isbig = true;
					}else{
						var $imageToMove = $(this).parents(".product_thumbnail_wrap").children("img");
					}
					//if has a second iamge then select that one.
					if ($imageToMove.length > 1 && ql_isbig == false) {
						$imageToMove = $imageToMove.eq(1);
					}else{
						$imageToMove = $imageToMove.eq(0);
					}
					var $parentImage = $imageToMove.parent();
					var test = $imageToMove.offset();
					var test_width = $imageToMove.width();

					//Clon the image to apply changes to that.
					var $clonedImage = $imageToMove.clone();
					$clonedImage.appendTo("body");
					//Position the image in the right place
					$clonedImage.css({
						position: 'absolute',
						top: test.top,
						left: test.left
					});
					$clonedImage.width(test_width);
					//get the cart position
					var cart_pos = $(".ql_cart-btn i").offset();
					//Start animation
					$clonedImage.addClass('ql_item_moving');
					$("body").addClass('ql_adding_tocart');
					setTimeout(function(){ $("body").removeClass('ql_adding_tocart'); $clonedImage.remove(); }, 2500);
					$clonedImage.css({
						top: cart_pos.top - 40,
						left: cart_pos.left
					});
					$clonedImage.width(25);
				};

			});//on click add to cart

			/*
			// WooCommerce Carousel
			//===========================================================
			*/
			//Carousel for Single WooCommerce images
			var ql_owl_woo = $('.ql_main_images');
			ql_owl_woo.owlCarousel({
			    center: true,
			    items: 1,
			    loop: false,
			    margin:10
			});

			var $ql_woo_thumbnails = $(".ql_thumbnail_column a");
			//Change thumbnails state on slider change
			ql_owl_woo.on('changed.owl.carousel', function(event) {
				var item = event.item.index;
				$ql_woo_thumbnails.removeClass("current");
				$ql_woo_thumbnails.eq(item).addClass("current");
			})
			//WooCommerce thumbnails
			$ql_woo_thumbnails.on('click', function(event) {
				event.preventDefault();
			});
			$ql_woo_thumbnails.hover(function() {				
				$ql_woo_thumbnails.removeClass("current");
				$(this).addClass("current");
				ql_owl_woo.trigger('to.owl.carousel', $(this).index());
			});
			//Prev and Next buttons
			$(".ql_main_images_btn.ql_next").on('click', function(event) {
				event.preventDefault();
				ql_owl_woo.trigger('next.owl.carousel');
			});
			$(".ql_main_images_btn.ql_prev").on('click', function(event) {
				event.preventDefault();
				ql_owl_woo.trigger('prev.owl.carousel');
			});
			//PhotoSwipe for WooCommerce Images
			initPhotoSwipe('.ql_main_images', 'img', ql_owl_woo);
			/*			
			//===========================================================
			*/


			
			/*
			// WooCommerce Custom Variations
			//===========================================================
			*/
			$(".variations").after('<div class="ql_custom_variations"></div>');
			//Create custom variations
			$(".variations tr").each(function(index, el) {
				var $select = $(this).find("select");
				var ul = $("<ul></ul>");
				var div_variation = $('<div class="ql_custom_variation"></div>');
				var select_id = $select.attr('id');

				ul.attr('id', 'ql_' + select_id);
				ul.attr('class', 'ql_select_id');

				//If the variation is color
				if (select_id.indexOf("color") > -1) {
					ul.addClass("ql_color_variation");
				};

				$select.find('option').each(function(index_option, el_option) {
					var current_value = $(this).attr('value');
					if (current_value != '') {
						var li = $("<li></li>");
						var a = $("<a href='#'></a>");
						a.attr('data-value', current_value);
						a.text($(this).text());
						//If the variation is color
						if (select_id.indexOf("color") > -1) {
							a.prepend($('<i></i>').css('background-color', current_value).addClass("ql_" + current_value));
						};
						li.append(a);
						ul.append(li);
					};
				});
				div_variation.append($("<h5></h5>").text($(el).find(".label").text()));
				div_variation.append(ul);
				$(".ql_custom_variations").append(div_variation);
			});
			$('body').on('click', ".ql_custom_variation ul li a", function(event) {
				event.preventDefault();
				var option_val = $(this).attr('data-value');
				var slect_id = $(this).parents(".ql_select_id").attr('id');;
				slect_id = slect_id.replace("ql_", "");
				$("#"+slect_id + ' option').each(function(index, el) {
					$(el).removeAttr('selected');
				});
				//$("#"+slect_id + ' option[value="' + option_val + '"]').prop('selected', true).attr('selected', 'selected'); //Old search
				jQuery("#"+slect_id + ' option').filter(function(){return this.value==option_val}).prop('selected', true).attr('selected', 'selected'); //Better search when there are HTML chars
				//$("#"+slect_id).val(option_val);
				$("#"+slect_id).change();

				$(this).parents(".ql_select_id").find("a").removeClass("current");
				$(this).addClass("current");
			});
			//Send the slider to the first slide in case there is a change of the main image
			$( "body" ).on( "woocommerce_variation_select_change", function () {
		    	ql_owl_woo.trigger('to.owl.carousel', 0);
			} );
			/*			
			//===========================================================
			*/







				$(".collapse").collapse({
				  toggle: false,
				  parent: ".panel-group"
				})


				//Accordion Icons (+ & -) Bootstrap-------------->
				$("body").on('hidden.bs.collapse', '.accordion .collapse', function(event) {
					var $a_i = $(this).prev().find(".accordion-toggle").children("i");
						$a_i.removeClass("fa-minus").addClass("fa-plus");
				});
				$("body").on('show.bs.collapse', '.accordion .collapse', function(event) {
					var $a_i = $(this).prev().find(".accordion-toggle").children("i");
						$a_i.removeClass("fa-plus").addClass("fa-minus");
				});
				//End Accordion Icons (+ & -) Bootstrap--------------<
				

				$('.dropdown-toggle').dropdown();
				//Tabs for Bootstrap-------------->
				$("body").on('click', '.ql_tabs a', function(e) {
					e.preventDefault();
				  	$(this).tab('show');
				});

				

				//Nav Menu for Bootstrap-------------->
				// $(".jqueryslidemenu ul.nav > li .children, .jqueryslidemenu ul.nav > li .sub-menu").each(function(index) {
				//     $(this).parent("li").addClass("dropdown");
				//     $(this).prev("a").addClass("dropdown-toggle").attr('data-toggle', 'dropdown').append('<b class="caret"></b>');
				//     $(this).addClass("dropdown-menu");
				// });
				//Nav Menu for Bootstrap-------------->



				
				
			
				
				
				
				//Tooltips
				$("*[rel^='tooltip']").tooltip();
				$('*[data-toggle="tooltip"]').tooltip();
				 
			

				
				//Quick Contact Form styling
				$(".quick_contact input").click(function(){
						$(this).parent().removeClass('has-error');
				});
				
				
				
				  
				//Sidebar Menu Function
				$('#sidebar .widget ul:not(.product-categories) li ul').parent().addClass('hasChildren').append("<i class='fa fa-angle-down'></i>");
				var children;
				$("#sidebar .widget ul:not(.product-categories) li").hoverIntent(
					function () {
						children = $(this).children("ul");
						if($(children).length > 0){ $(children).stop(true, true).slideDown('fast'); }
					}, 
					function () {
					  $(this).children('ul').stop(true, true).slideUp(500);
					}
				);
				//Footer Menu Function
				$('footer .widget ul:not(.product-categories) li ul').parent().addClass('hasChildren').append("<i class='fa fa-angle-down'></i>");
				var children;
				$("footer .widget ul:not(.product-categories) li").hoverIntent(
					function () {
						children = $(this).children("ul");
						if($(children).length > 0){ $(children).stop(true, true).slideDown('fast'); }
					}, 
					function () {
					  $(this).children('ul').stop(true, true).slideUp(500);
					}
				);	
				






				$(".wpcf7 input").on('focus', function(event) {
					$(this).removeClass("wpcf7-not-valid");

				});



			//Nav Menu for Bootstrap-------------->
			$(".jqueryslidemenu > ul > li .children").each(function(index) {
			    $(this).parent("li").addClass("dropdown");
			    $(this).prev("a").addClass("dropdown-toggle").attr('data-toggle', 'dropdown').append('<b class="caret"></b>');
			    $(this).addClass("dropdown-menu");
			});
			//Nav Menu for Bootstrap-------------->



	});//Dom ready






})(jQuery);




function stringToBoolean(string){
        switch(string.toLowerCase()){
                case "true": case "yes": case "1": return true;
                case "false": case "no": case "0": case null: return false;
                default: return Boolean(string);
        }
}
function scrollToElement(selector, time, verticalOffset) {
    time = typeof(time) != 'undefined' ? time : 1000;
    verticalOffset = typeof(verticalOffset) != 'undefined' ? verticalOffset : 0;
    element = jQuery(selector);
    offset = element.offset();
    offsetTop = offset.top + verticalOffset;
    jQuery('html, body').animate({
        scrollTop: offsetTop
    }, time);
}

//Debounce Function
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};