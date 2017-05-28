/*
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

$(document).ready(function () {

    // MetsiMenu
    $('#sideBarLeft').metisMenu();

    $('.navbar-minimalize').click(function () {
        SmoothlyMenu();
    });

    $(".price-mask").formatNumber();

    if($("div").is(".datepicker")) {
        $('.datepicker').datetimepicker({
            locale: "ru",
            format: "YYYY-MM-DD HH:mm:ss",
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-angle-up",
                down: "fa fa-angle-down"
            }
        });
    }

    $('[data-toggle="tooltip"]').tooltip();

    $('.item-detail-show').on('click', function() {
        $(this).children('.fa').toggleClass('fa-rotate-90');
        $(this).parents('.item').find('.item-detail').slideToggle('slow');
    });

    $(".inputfile").change(function(){
        var filename = $(this).val().replace(/.*\\/, "");
        $(this).siblings('label').children('span').text(filename);
    });

});

function SmoothlyMenu() {
    var body = $("body");
    var width = $(window).width();

    if (width >= 992) {
        $('.metismenu li').hide();
        // For smoothly turn on menu
        setTimeout(
            function () {
                $('.metismenu li').fadeIn(400);
            }, 100);
        body.toggleClass("mini-navbar").removeClass("navbar-hidden");
    } else if (width < 769) {
        body.toggleClass("mini-navbar").removeClass("navbar-hidden");
    } else {
        body.toggleClass("navbar-hidden")
    }
}