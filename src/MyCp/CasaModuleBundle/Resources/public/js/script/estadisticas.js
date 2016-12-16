/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Estadistica = function () {

    var plotObj;

    var inits = function () {

        initCollapse();
        initControlCalendar();

        var data = [{
            label: "<i class='mycp-icon-disponible'></i>",
            data: 25,
            color: "#64a433",
        }, {
            label: "<i class='mycp-icon-nodisponible'></i>",
            data: 75,
            color: "#094e75",
        }];

        createFlot(data);
    }

    var initControlCalendar = function(){
        var now = new Date();
        var mes = now.getMonth();
        console.log(mes);
        $(".btn-control-calendar").each(function (e) {
            $(this).click(function (s) {
                s.preventDefault();

            })
        })
    }

    var initCollapse = function () {
        $(".btn-show-block").each(function (s) {
            $(this).click(function (e) {
                e.preventDefault();
                var block = $(this).attr("href");
                $(block).toggleClass("off");
                $(this).toggleClass("closed");
            })
        })
    }

    var createFlot = function (data) {


        plotObj = $.plot($("#flot-pie-chart"), data, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                    label: {
                        show: true,
                        radius: 3/4,
                        threshold: 0
                    }
                }
            },
            legend: {
                show: false
            }
        });
    }

    var refreshFlot = function (data) {
        plotObj.setData(data);
    }

    return {
        //main function to initiate template pages
        init: function () {
            inits();
        },
        refreshPLot: function (data) {
            refreshFlot(data);
        }
    };
}();
//Start Property
Estadistica.init();





