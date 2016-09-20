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

       $('#requests_ownership_filter_arrival').datepicker({
           format: 'dd/mm/yyyy',
           todayBtn: true,
           autoclose: true,
           startDate: start_date,
           date: start_date,
           language: $('#requests_ownership_filter_arrival').attr('data-localization')
       }).on('changeDate', function(ev) {
               var startDate = new Date(ev.date);
               startDate.setDate(startDate.getDate() + 1);
               departure_datepicker.setStartDate(startDate);
               var valueDate = new Date(ev.date);
               valueDate.setDate(valueDate.getDate() + 1);
               departure_datepicker.setDate(valueDate);
           });
        var departure_datepicker = $('#requests_ownership_filter_exit').datepicker({
            format: 'dd/mm/yyyy',
            todayBtn: false,
            autoclose: true,
            startDate: '+3d',
            date: end_date,
            language: $('#requests_ownership_filter_exit').attr('data-localization')
        }).data('datepicker');
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
                if(!$('#search-result').hasClass('hide'))
                    $('#big_map').removeClass('hide');
                $('.container-map').css('margin-top','351px');
            }
        });
        $('#icon-top').on('click',function(){
            $('#more-filter').addClass('hide');
            $('#icon-top').addClass('hide');
            $('#icon-back').removeClass('hide');
            $('#text-more').removeClass('hide');
            if($('#search-result').hasClass('hide'))
                $('#big_map').addClass('hide');
            $('.container-map').css('margin-top','100px');
        });
    }
    var form = $('#form-filter-ownership');
    var validatorForm = form.validate({
        errorElement: 'span',
        errorClass: 'has-error',
        ignore: "",
        rules: {
            'requests_ownership_filter[arrival]': {
                required: true
            }
        },
        invalidHandler: function (event, validator) {},
        highlight: function (element, clsError) { // hightlight error inputs
            element = $(element);
            element.parent().addClass(clsError);
        },
        unhighlight: function (element, clsError) { // revert the change done by hightlight
            element = $(element);
            element.parent().removeClass(clsError);
        }
    });
    var onclickBtnSearch=function(){
        $('#btn_search').on('click',function(){
            if(validatorForm.form()){
                var data_params={};
                var form = $("#form-filter-ownership");
                var result = $('#list-ownership');
                $('.slimScrollBar').css('top','0px');
                result.innerHTML="";
                var _url = $(this).data('url');
                $('#big_map').removeClass('hide');
                if (typeof Map != "undefined")
                    Map.removeMarkers();
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
            }

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
                $.post(_url, data_params, function(response) {
                    //Se manda a pintar al map
                    Map.createMarkerAndListenerEvent(response.response_json);

                    var top=$('.slimScrollBar').css('top').split('px');
                    var newTop=top[0]-50;
                    $('.slimScrollBar').css('top',newTop+'px');
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

            result.slimScroll({
                height: '300px',
                railOpacity: 0.9,
                color: '#ffffff',
                opacity : 1,
                alwaysVisible : false
            });

            if(!hasContent) {
                result.html(loadingText);
                //Mostrar listado de reservaciones abiertas
                $.post(_url, data, function (data) {
                    result.html(data);
                    result.data("content", true);
                    onEndReservationButton();
                    onAddToOpeneservationButton();
                });
            }
        });
    }

    var details_favorites = function (favorite_button)
    {
        var url;
        $(favorite_button).unbind();
        $(favorite_button).click(function() {

            var temp=$('#count-fav').text();
            if(favorite_button=='#delete_from_favorites'){
                temp--;
                $('i',favorite_button).removeClass('ion-ios-star').removeClass('ion-ios-star-outline').addClass('ion-ios-star-outline');
                $(favorite_button).attr('id', "add_to_favorites");
                url = $(this).attr('data-remove-url');
            }else{
                temp++;
                $('i',favorite_button).removeClass('ion-ios-star').removeClass('ion-ios-star-outline').addClass('ion-ios-star');
                $(favorite_button).attr('id', "delete_from_favorites");
                url = $(this).attr('data-add-url');
            }

            $('#count-fav').text(temp);
            $('#count-fav').html(temp);

            var favorite_type = $(this).attr('data-type');
            var element_id = $(this).attr('data-id');

            $.post(url,
                {
                    'favorite_type': favorite_type,
                    'element_id': element_id
                }
                , function(data) {
                    $(".favorites_details").html(data);

                    details_favorites("#delete_from_favorites");
                    details_favorites("#add_to_favorites");
                });
        });
    }

    var onAddNewOpenReservationButton = function(){
        $("#btnAddNewOpenReservation").on('click',function() {
            var result = $('#openReservationsList');
            var dateFrom = $("#requests_ownership_filter_arrival").val();
            var dateTo = $("#requests_ownership_filter_exit").val();
            var clientName = $("#partner_reservation_name").val();
            var adults = $("#partner_reservation_adults").val();
            var children = $("#partner_reservation_children").val();
            var _url = $("#btnAddNewOpenReservation").data("url");
            $(".alertLabel").addClass("hidden");

            var isValid = (dateFrom != "" && dateTo != "" && clientName != "" && adults != "" && children != "");

            if(isValid) {
                var loadingText = result.data("loadingtext");
                result.html(loadingText);

                $.post(_url, {
                    'dateFrom': dateFrom,
                    'dateTo': dateTo,
                    'clientName': clientName,
                    'adults': adults,
                    'children': children,
                    'accommodationId': selectedAccommodationForReserve
                }, function (response) {

                    if (response.success) {
                        result.html(response.html);
                        result.data("content", true);
                        onEndReservationButton();
                        onAddToOpeneservationButton();

                        //Clear data from inputs
                        $("#partner_reservation_name").val("");
                        $("#partner_reservation_adults").val("");
                        $("#partner_reservation_children").val("");
                    }


                });
            }
            else{
                $(".alertLabel").removeClass("hidden");
            }

        });
    }

    var onEndReservationButton = function(){
        $(".closeReservation").on('click',function() {
            var id = $(this).data("id");
            var result = $('#openReservationsList');
            var _url = $(this).data("url");

            var loadingText = result.data("loadingtext");
            result.html(loadingText);

            $.post(_url, {
                'id': id
            }, function (response) {

                if(response.success) {
                    result.html(response.html);
                    result.data("content", true);
                    onEndReservationButton();
                    onAddToOpeneservationButton();
                }
            });
        });
    }

    var onAddToOpeneservationButton = function(){
        $(".addToOpenReservation").on('click',function() {
            var id = $(this).data("id");
            var result = $('#openReservationsList');
            var _url = $(this).data("url");

            var dateFrom = $("#requests_ownership_filter_arrival").val();
            var dateTo = $("#requests_ownership_filter_exit").val();
            $(".alertLabel").addClass("hidden");

            if(dateFrom != "" && dateTo != "" && selectedAccommodationForReserve != "") {
                var loadingText = result.data("loadingtext");
                result.html(loadingText);

                $.post(_url, {
                    'id': id,
                    'dateFrom': dateFrom,
                    'dateTo': dateTo,
                    'accommodationId': selectedAccommodationForReserve
                }, function (response) {

                    if (response.success) {
                        result.html(response.html);
                        result.data("content", true);
                        onEndReservationButton();
                        onAddToOpeneservationButton();
                    }
                });
            }
            else
            {
                $(".alertLabel").removeClass("hidden");
            }
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
            onEndReservationButton();
            onAddToOpeneservationButton();
            infiniteScroll();
            details_favorites("#delete_from_favorites");
            details_favorites("#add_to_favorites");
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

