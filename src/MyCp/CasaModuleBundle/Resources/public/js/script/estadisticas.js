/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Estadistica = function () {

    var plotObj;

    var inits = function () {

        initCollapse();
        initControlCalendar();

        // data-nonavaliable-percent="{{ nonavaliable_percent }}" data-avaliable-percent="{{ avaliable_percent }}"



        createFlot();
        createYearPlot();
    }

    var initControlCalendar = function(){

        var lastdates = last_date_calculate.split("/");
        var laststartDate,currentDate = new Date(parseInt(lastdates[2]),parseInt(lastdates[1]) - 1, parseInt(lastdates[0]) );


        $(".btn-control-calendar").each(function (e) {
            $(this).click(function (s) {
                s.preventDefault();
                $("#mounthreview").removeClass("off");
                $("#mounthreview .btn-show-block").removeClass("closed");
                if (!$(this).hasClass("inactive")){
                    var increment = parseInt($(this).attr("data-index"));
                    var current_index = $("#calendar-date-content").find("span.span-dates.dateactive").index();
                    $("#calendar-date-content").find("span.span-dates.dateactive").removeClass("dateactive");
                    $("#calendar-date-content").find("span.span-dates").eq(current_index + increment).addClass("dateactive");
                    $(".btn-control-calendar").eq(0).removeClass("inactive");
                    $(".btn-control-calendar").eq(1).removeClass("inactive");
                    if ((current_index + increment) == 0){
                        $(".btn-control-calendar").eq(0).addClass("inactive");
                    }
                    if ((current_index + increment) == ($("#calendar-date-content").find("span.span-dates").length - 1)){
                        $(".btn-control-calendar").eq(1).addClass("inactive");
                    }

                    loadStatisticByDate($("#calendar-date-content").find("span.span-dates.dateactive").attr("data-dates"));
                }
            })
        });

        $(".btn-control-year").each(function (e) {
            $(this).click(function (s) {
                s.preventDefault();
                $("#yearreview").removeClass("off");
                $("#yearreview .btn-show-block").removeClass("closed");
                if (!$(this).hasClass("inactive")){
                    var increment = parseInt($(this).attr("data-index"));
                    var current_index = $("#calendar-year-content").find("span.span-dates.dateactive").index();
                    $("#calendar-year-content").find("span.span-dates.dateactive").removeClass("dateactive");
                    $("#calendar-year-content").find("span.span-dates").eq(current_index + increment).addClass("dateactive");
                    $(".btn-control-year").eq(0).removeClass("inactive");
                    $(".btn-control-year").eq(1).removeClass("inactive");
                    if ((current_index + increment) == 0){
                        $(".btn-control-year").eq(0).addClass("inactive");
                    }
                    if ((current_index + increment) == ($("#calendar-year-content").find("span.span-dates").length - 1)){
                        $(".btn-control-year").eq(1).addClass("inactive");
                    }

                    loadStatisticByYear($("#calendar-year-content").find("span.span-dates.dateactive").attr("data-dates"));
                }
            })
        });

    }

    var loadStatisticByDate = function (statistic_date) {
        var data = {
            fecha: statistic_date,
            ownershipID: statistic_ownershipID
        }

        $("#spiner").show();
        $("#resumen-mensual").empty();

        $("#resumen-mensual").load(url_load_date, data, function (result) {
            createFlot();
            $("#spiner").hide();
        });
    }

    var loadStatisticByYear = function (year) {
        var data = {
            year: year,
            ownershipID: statistic_ownershipID
        }

        $("#spineryear").show();
        $("#resumen-anual").empty();

        $("#resumen-anual").load(url_load_year, data, function (result) {
            createYearPlot();
            $("#spineryear").hide();
        });
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

    var createFlot = function () {

        var avaliable = parseInt($("#flot-pie-chart").attr("data-avaliable-percent"));
        var nonavaliable = parseInt($("#flot-pie-chart").attr("data-nonavaliable-percent"));

        var data = [{
            label: "<i class='mycp-icon-disponible'></i>",
            data: avaliable,
            color: "#64a433",
        }, {
            label: "<i class='mycp-icon-nodisponible'></i>",
            data: nonavaliable,
            color: "#094e75",
        }];

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

    var createYearPlot = function () {
        var avaliable = parseInt($("#year-flot-pie-chart").attr("data-avaliable-percent"));
        var nonavaliable = parseInt($("#year-flot-pie-chart").attr("data-nonavaliable-percent"));

        var data = [{
            label: "<i class='mycp-icon-disponible'></i>",
            data: avaliable,
            color: "#64a433",
        }, {
            label: "<i class='mycp-icon-nodisponible'></i>",
            data: nonavaliable,
            color: "#094e75",
        }];

        var yearplotObj = $.plot($("#year-flot-pie-chart"), data, {
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

    return {
        //main function to initiate template pages
        init: function () {
            inits();
        },
        refreshPLot: function (data) {

        }
    };
}();
//Start Property
Estadistica.init();





