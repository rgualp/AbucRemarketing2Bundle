/**
 Dashboard script to handle the entire layout and base functions
 **/
var Dashboard = function () {
    var start=0;
    var limit=4;
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
                $('.container-map').css('margin-top','351px');
            }
        });
        $('#icon-top').on('click',function(){
            $('#more-filter').addClass('hide');
            $('#icon-top').addClass('hide');
            $('#icon-back').removeClass('hide');
            $('#text-more').removeClass('hide');
            $('.container-map').css('margin-top','100px');
        });
    }
    var onclickBtnSearch=function(){
        $('#btn_search').on('click',function(){
            var data_params={};
            var form = $("#form-filter-ownership");
            var result = $('#list-ownership');
            $('.slimScrollBar').css('top','0px');
            result.innerHTML="";
            var _url = $(this).data('url');

            form.serializeArray().map(function(x){data_params[x.name] = x.value;});
            HoldOn.open();
            data_params['start']=0;
            data_params['limit']=4;
            start=0;
            limit=4;
            $.post(_url, data_params, function(response) {
                HoldOn.close();
                result.html(response.response_twig);
                //se manda a eliminar los market
                Map.removeMarkers();
                //Se manda a pintar al map
                Map.createMarkerAndListenerEvent(response.response_json);
                start=limit+1;
                limit=limit+5;
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
    var infiniteScroll=function(){
        $('#search-result').scroll(function () {
            if ($('#search-result').scrollTop() >=$('#list-ownership').height()-$('#search-result').height()){
               var data_params={};
                var form = $("#form-filter-ownership");
                var result = $('#list-ownership');
                var _url = $('#btn_search').data('url');
                data_params['start']=start;
                data_params['limit']=limit;
                form.serializeArray().map(function(x){data_params[x.name] = x.value;});
                HoldOn.open();
                $.post(_url, data_params, function(response) {
                    //Se manda a pintar al map
                    Map.createMarkerAndListenerEvent(response.response_json);

                    var top=$('.slimScrollBar').css('top').split('px');
                    var newTop=top[0]-50;
                    $('.slimScrollBar').css('top',newTop+'px');
                    HoldOn.close();
                    result.append(response.response_twig);
                    start=limit+1;
                    limit=limit+5;
                });
            }
        })
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
            infiniteScroll();
        },
        setStart:function(a){
            start=a
        },
        getStart:function(a){
            return start;
        },
        setLimit:function(a){
            limit=a
        },
        getLimit:function(a){
            return limit;
        }
    };
}();
Dashboard.init();

