$(document).ready(function(){
    /*$('.accordion').on('shown', function () {
        $(".icon-chevron-right").removeClass("icon-chevron-right").addClass("icon-chevron-down");
    });

    $('.accordion').on('hidden', function () {
        $(".icon-chevron-down").removeClass("icon-chevron-down").addClass("icon-chevron-right");
    });*/

    /*$('.accordion').on('shown', function(){
        $(this).parent().find(".icon-chevron-righ").removeClass("icon-chevron-righ").addClass("icon-chevron-down");
    });
    $('.accordion').on('hidden', function (){
        $(this).parent().find(".icon-chevron-down").removeClass("icon-chevron-down").addClass("icon-chevron-righ");
    });*/
});

$(function () {
    $('.tooltip_msg').tooltip();
});

$(function() {
    $("[rel='tooltip']").tooltip();
    $("[data-rel='tooltip']").tooltip();
});
