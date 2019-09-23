jQuery(function ($) {

    $('.woocs-style-for-menu-dialog').parent().on('click', function (e) {
        e.stopPropagation();
        if ($(this).hasClass('woocs-style-for-menu-active')) {
            $('.woocs-style-for-menu-dialog').fadeOut(200);
            $(this).removeClass('woocs-style-for-menu-active');
        } else {
            $('.woocs-style-for-menu-dialog').delay(300).fadeIn(200);
            $(this).addClass('woocs-style-for-menu-active');
        }

        return false;
    });

    $(document.body).on('click', function (e) {
        $('.woocs-style-for-menu-dialog').fadeOut(200, function () {
            $('.woocs-style-for-menu-add').removeClass('woocs-style-for-menu-active');
        });
    });

    $(".woocs-style-for-menu-dialog").on('click', function (e) {
        e.stopPropagation();
    });

});

