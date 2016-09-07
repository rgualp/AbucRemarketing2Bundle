/**
 Dashboard script to handle the entire layout and base functions
 **/
var Dashboard = function () {
   /**
    * Dashboard form init plugins
    */
       var selectedAccommodationForReserve = 0;

    var initPlugins=function(){
       var config = {
           '.chosen-select'           : {},
           '.chosen-select-deselect'  : {allow_single_deselect:true},
           '.chosen-select-no-single' : {disable_search_threshold:10},
           '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
           '.chosen-select-width'     : {width:"95%"}
       }
       for (var selector in config) {
           $(selector).chosen(config[selector]);
       }
       $('.input-date').datepicker({
           todayBtn: "linked",
           keyboardNavigation: false,
           forceParse: false,
           calendarWeeks: true,
           autoclose: true,
           format:'dd/mm/yyyy'
       });
       $("#priceFilter").slider({});
    }
    var onclickBtnMoreFilter=function(){
        $('#more-filter').addClass('hide');
        $('#icon-back').on('click',function(){
            if($('#more-filter').hasClass('hide')){
                $('#more-filter').removeClass('hide');
                $('#icon-top').removeClass('hide');
                $('#icon-back').addClass('hide');
                $('#text-more').addClass('hide');
            }
        });
        $('#icon-top').on('click',function(){
            $('#more-filter').addClass('hide');
            $('#icon-top').addClass('hide');
            $('#icon-back').removeClass('hide');
            $('#text-more').removeClass('hide');
        });
    }
    var onclickBtnSearch=function(){
        $('#btn_search').on('click',function(){
            var data={};
            var form = $("#form-filter-ownership");
            var result = $('#search-result');
            var _url = form.attr('action');

            form.serializeArray().map(function(x){data[x.name] = x.value;});
            HoldOn.open();
            $.post(_url, data, function(data) {
                HoldOn.close();
                result.html(data);
                onShowReservationModal();
                $('#search-result').removeClass('hide');
                $('#search-result').slimScroll({
                    height: '580px',
                    railOpacity: 0.9,
                    color: '#0d3044',
                    opacity : 1,
                    alwaysVisible : true
                });

            });
        });

    }
    var onShowReservationModal=function(){
        $(".createReservation").on('click',function() {
            selectedAccommodationForReserve = $(this).data("ownid");
            $('#myModalReservation').modal("show");
            var result = $('#openReservationsList');
            var _url = result.data("url");
            var hasContent = result.data("content");
            var loadingText = result.data("loadingtext");
            var data={};

            if(!hasContent) {
                result.html(loadingText);
                //Mostrar listado de reservaciones abiertas
                $.post(_url, data, function (data) {
                    result.html(data);
                    result.data("content", true);
                });
            }
        });
    }

    var onAddNewOpenReservationButton = function(){
        $("#btnAddNewOpenReservation").on('click',function() {
            console.log("Adding new open reservation");
        });
    }

    return {
        //main function to initiate template pages
        init: function () {
            initPlugins();
            onclickBtnMoreFilter();
            onclickBtnSearch();
            onShowReservationModal();
            onAddNewOpenReservationButton();
        }
    };
}();
Dashboard.init();

