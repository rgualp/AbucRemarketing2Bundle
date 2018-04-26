/*
 *
 *   INSPINIA - Responsive Admin Theme
 *   version 2.2
 *
 */

var MycpMobile = function(){

    var inicializer = function () {
        $("#btn-footer-coll").click(function (e) {
            if ($(this).hasClass("isopen")){
                $(this).removeClass("isopen");
            }else{
                $(this).addClass("isopen");
            }
        });

        var url_top_rate = $("ul.navigation").attr("data-url");
        var page = 1;
        var cant_items = $("ul.navigation").attr("data-cant-items");

        $("#toprate-navigation-prev").click(function (e) {
            e.preventDefault();
            if (page > 1) {
                $("#loading").show();
                page--;
                var param = {'page': page};
                $("#top-list-container").load(url_top_rate, param, function (data) {
                    $("#loading").hide();
                    Mycp.init();
                    updateArrowTopRate(page, cant_items);
                });


            }
        });

        $("#toprate-navigation-next").click(function (e) {
            e.preventDefault();
            if (page < cant_items){
                $("#loading").show();
                page++;
                var param = {'page': page};
                $("#top-list-container").load(url_top_rate, param, function (data) {
                    $("#loading").hide();
                    Mycp.init();
                    updateArrowTopRate(page, cant_items);
                })

            }
        });

        // $("#top_rated_placeholder").on("swipeleft",function(e){
        //     if (page < cant_items){
        //         $("#loading").show();
        //         page++;
        //         var param = {'page': page};
        //         $("#top-list-container").load(url_top_rate, param, function (data) {
        //             $("#loading").hide();
        //             Mycp.init();
        //             updateArrowTopRate(page, cant_items);
        //         });
        //
        //     }
        // }).on("swiperight",function(e){
        //     if (page > 1) {
        //         $("#loading").show();
        //         page--;
        //         var param = {'page': page};
        //         $("#top-list-container").load(url_top_rate, param, function (data) {
        //             $("#loading").hide();
        //             Mycp.init();
        //             updateArrowTopRate(page, cant_items);
        //         });
        //
        //     }
        // });
        updateArrowTopRate(page, cant_items);
    }

    var updateArrowTopRate = function (page, totals_item) {
        $("#toprate-navigation-prev").show();
        $("#toprate-navigation-next").show();
        if (page == 1){
            $("#toprate-navigation-prev").hide();
        }else if (page == totals_item){
            $("#toprate-navigation-next").hide();
        }
    }

    return {
        init: function(){
            inicializer();
        }
    }
}();
MycpMobile.init();

