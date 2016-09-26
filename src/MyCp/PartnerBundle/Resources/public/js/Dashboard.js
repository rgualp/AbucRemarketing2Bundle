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
    var cartPrepayment = 0;

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
           if(!ev.date){
               return;
           }
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

        //Datepickers en modal
        $('#modalDateFrom').datepicker({
            format: 'dd/mm/yyyy',
            todayBtn: true,
            autoclose: true,
            startDate: start_date,
            date: start_date,
            language: $('#modalDateFrom').attr('data-localization')
        }).on('changeDate', function(ev) {
            if(!ev.date){
                return;
            }

            var startDate = new Date(ev.date);
            startDate.setDate(startDate.getDate() + 1);
            Modal_departure_datepicker.setStartDate(startDate);
            var valueDate = new Date(ev.date);
            valueDate.setDate(valueDate.getDate() + 1);
            Modal_departure_datepicker.setDate(valueDate);
        });
        var Modal_departure_datepicker = $('#modalDateTo').datepicker({
            format: 'dd/mm/yyyy',
            todayBtn: false,
            autoclose: true,
            startDate: '+3d',
            date: end_date,
            language: $('#modalDateTo').attr('data-localization')
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
        rules: {},
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

                    onShowReservationModal();
                });
            }
        })
    }
    var onShowReservationModal=function(){
        $(".createReservation").on('click',function() {
            if(!$('#myModalReservation').is(':visible'))
            {
            selectedAccommodationForReserve = $(this).data("ownid");
            $('#myModalReservation').modal("show");
            var result = $('#openReservationsList');
            var _url = result.data("url");
            var hasContent = result.data("content");
            var loadingText = result.data("loadingtext");
            var accommodationName = $(this).data("accommodation");
            var data={};

            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");


            var searcherDateFrom = $("#requests_ownership_filter_arrival");
            if (typeof searcherDateFrom != "undefined")
            {
                $("#modalDateFrom").val(searcherDateFrom.val());
            }

            var searcherDateTo = $("#requests_ownership_filter_exit");
            if (typeof searcherDateTo != "undefined")
            {
                $("#modalDateTo").val(searcherDateTo.val());
            }

            $("#accommodationName").html(accommodationName);

            $("#divResult").slimScroll({
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
                    onShowOpenReservationsListDetailsButton();
                });
            }
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
            var dateFrom = $("#modalDateFrom").val();
            var dateTo = $("#modalDateTo").val();
            var clientName = $("#partner_reservation_name").val();
            var adults = $("#partner_reservation_adults").val();
            var children = $("#partner_reservation_children").val();
            var _url = $("#btnAddNewOpenReservation").data("url");
            $(".alertLabel").addClass("hidden");
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");

            var isValid = (dateFrom != "" && dateTo != "" && clientName != "") && (adults != "" || children != "");

            if(isValid) {
                var loadingText = result.data("loadingtext");
                result.html(loadingText);

                $.post(_url, {
                    'dateFrom': dateFrom,
                    'dateTo': dateTo,
                    'clientName': clientName,
                    'adults': (adults == "") ? 0 : adults,
                    'children': (children == "") ? 0 : children,
                    'accommodationId': selectedAccommodationForReserve
                }, function (response) {

                    if (response.success) {
                        result.html(response.html);
                        result.data("content", true);
                        onEndReservationButton();
                        onAddToOpeneservationButton();
                        onShowOpenReservationsListDetailsButton();

                        //Clear data from inputs
                        $("#partner_reservation_name").val("");
                        $("#partner_reservation_adults").val("");
                        $("#partner_reservation_children").val("");
                    }

                    if(response.message != "")
                    {
                        //crear otro para mensajes
                        $(".alertLabel").html(response.message);
                        $(".alertLabel").removeClass("hidden");
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
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");

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
                    onShowOpenReservationsListDetailsButton();
                }
            });
        });
    }
    var onAddToOpeneservationButton = function(){
        $(".addToOpenReservation").on('click',function() {
            var id = $(this).data("id");
            var result = $('#openReservationsList');
            var _url = $(this).data("url");
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");

            var dateFrom = $("#modalDateFrom").val();
            var dateTo = $("#modalDateTo").val();
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
                        onShowOpenReservationsListDetailsButton();
                    }

                    if(response.message != "")
                    {
                        //crear otro para mensajes
                        $(".alertLabel").html(response.message);
                        $(".alertLabel").removeClass("hidden");
                    }

                });
            }
            else
            {
                $(".alertLabel").removeClass("hidden");
            }
        });
    }
    var onShowOpenReservationsListDetailsButton = function(){
        $(".showReservationsDetails").on('click',function() {
            var reservationId = $(this).data("id");
            var reservationNumber = $(this).data("number");
            var clientName = $(this).data("client");
            var creationDate = $(this).data("date");

            var result = $('#openReservationsListDetails');
            var _url = $(this).data("url");

            result.removeClass("hidden");
            $("#openReservationsList").addClass("hidden");

            var loadingText = result.data("loadingtext");
            result.html(loadingText);

            $.post(_url, {
                'reservationId': reservationId,
                'reservationNumber': reservationNumber,
                'clientName': clientName,
                'creationDate': creationDate
            }, function (response) {

                if (response.success) {
                    result.html(response.html);
                    onCloseOpenReservationDetailedListButton();
                    onDeleteOpenReservationDetailButton();
                }

                if(response.message != "")
                {
                    //crear otro para mensajes
                    $(".alertLabel").html(response.message);
                    $(".alertLabel").removeClass("hidden");
                }

            });
        });
    }
    var onCloseOpenReservationDetailedListButton = function(){
        $("#closeDetailedOpenReservationList").on('click',function() {
            var result = $('#openReservationsList');
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");
        });
    }
    var onDeleteOpenReservationDetailButton = function(){
        $(".deleteOpenReservationDetail").on('click',function() {
            var result = $('#openReservationsListDetails');
            result.removeClass("hidden");
            $("#openReservationsList").addClass("hidden");

            var idOpenReservationDetail = $(this).data("detailsid");
            var idPaGeneralReservation = $(this).data("pagresid");
            var reservationNumber = $(this).data("number");
            var clientName = $(this).data("client");
            var creationDate = $(this).data("date");
            var _url = $(this).data("url");

            var loadingText = result.data("loadingtext");
            result.html(loadingText);

            $.post(_url, {
                'idOpenReservationDetail': idOpenReservationDetail,
                'idPaGeneralReservation': idPaGeneralReservation,
                'reservationNumber': reservationNumber,
                'clientName': clientName,
                'creationDate': creationDate
            }, function (response) {

                if (response.success) {
                    if(response.html != ""){
                        result.html(response.html);
                        onCloseOpenReservationDetailedListButton();
                        onDeleteOpenReservationDetailButton();
                    }
                    else{
                        result.addClass("hidden");
                        $("#openReservationsList").removeClass("hidden");
                    }

                    $("#openReservationsList").html(response.htmlReservations);
                    onEndReservationButton();
                    onAddToOpeneservationButton();
                    onShowOpenReservationsListDetailsButton();
                }

                if(response.message != "")
                {
                    //crear otro para mensajes
                    $(".alertLabel").html(response.message);
                    $(".alertLabel").removeClass("hidden");
                }

            });

        });
    }
    var onViewMoreButton=function(){
        $('.icon-down').on('click',function(){
            if($('.icon-up').hasClass('hide')){
                var id = $(this).data("id");
                var icon_up = "#icon_up_" + id;
                var icon_down = "#icon_down_" + id;
                var _url = $(this).data("url");

                //Cargar si no tiene datos
                var cartItemContentId = $(this).data("element");

                if(!$(cartItemContentId).data("hascontent"))
                {
                    $("#loading_" + id).removeClass('hide');
                    $.post(_url, {
                        'idReservation': id
                    }, function (response) {
                        if (response.success) {
                            $(cartItemContentId).html(response.html);
                            $(cartItemContentId).data("hascontent", true);
                            $("#loading_" + id).addClass('hide');
                            $(icon_up).removeClass('hide');
                            $(icon_down).addClass('hide');
                            onCheckDetailsInCartButton();
                        }
                    });
                }
                else
                {
                    $(icon_up).removeClass('hide');
                    $(icon_down).addClass('hide');
                }

                $(cartItemContentId).removeClass('hide');
            }
        });
        $('.icon-up').on('click',function(){
            var id = $(this).data("id");
            var icon_up = "#icon_up_" + id;
            var icon_down = "#icon_down_" + id;
            $(icon_up).addClass('hide');
            $(icon_down).removeClass('hide');
            var cartItemContentId = $(this).data("element");
            $(cartItemContentId).addClass('hide');
        });
    }
    var onDeleteFromCartButton = function(){
        $(".deleteFromCart").on('click',function() {
            swal({
                title: $("#alertMessages").data("alerttitle"),
                text: $("#alertMessages").data("alertcontent"),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $("#alertMessages").data("confirmbutton"),
                cancelButtonText: $("#alertMessages").data("cancelbutton"),
                closeOnConfirm: false
            }, function () {
            var result = $('#cartBody');
            var id = $(this).data("id");
            var _url = $(this).data("url");

            $("#loading_" + id).removeClass('hide');

            $.post(_url, {
                'reservationId': id
            }, function (response) {

                if (response.success) {
                    if(response.html != ""){
                        result.html(response.html);
                        onDeleteFromCartButton();
                        onViewMoreButton();
                        onCheckDetailsInCartButton();
                        onEmptyCartButton();
                        onPayActionButton();

                    }
                }
            });

            });
        });
    }
    var onCheckDetailsInCartButton = function(){
        $(".checkAccommodations").on('change',function() {
            var owresid = $(this).data("owresid");
            var idreservation = $(this).data("idreservation");
            var counter = parseInt($("#accommodationsToPay_" + idreservation).html());
            if ($(this).is(":checked")) {
                $("#accommodationsToPay_" + idreservation).html(counter+1);
            }
            else
                $("#accommodationsToPay_" + idreservation).html(counter-1);
        });
    }

    var onPayActionButton = function ()
    {
        $("#trigger-overlay").on('click',function() {
            $("#overlayLoading").removeClass("hide");
            $("#paymentsRow").addClass("hide");
            var _url = $(this).data("url");
            var result = $("#selectedReservations");

            var checkValues = $('input[name=checkAccommodationsToPay]:checked').map(function() {
                return $(this).data('owresid');
            }).get();


            if(checkValues.length == 0)
            {
                $("#overlayLoading").addClass("hide");
                $("#payNow").attr("disabled", "true");
                return;
            }

            //Ir a buscar los datos de los ownRes seleccionados para pagar y generar un booking (server)
            $.post(_url, {
                'checkValues': checkValues
            }, function (response) {

                if (response.success) {
                    if(response.html != ""){
                        result.html(response.html);
                        onShowMorePaymentButton();
                        $("#totalPrepayment").html(response.totalPrepaymentTxt);
                        $("#totalAccommodationsPayment").html(response.totalAccommodationPaymentTxt);
                        $("#totalServiceTaxesPayment").html(response.totalServiceTaxPaymentTxt);
                        $("#totalServiceTaxesPrepayment").html(response.totalServiceTaxPaymentTxt);
                        $("#fixedTax").html(response.fixedTaxTxt);
                        $("#fixedTaxPrepayment").html(response.fixedTaxTxt);
                        $("#totalPercentAccommodationsPrepayment").html(response.totalPercentAccommodationPrepaymentTxt);
                        $("#totalPayment").html(response.totalPaymentTxt);

                        $("#atServicePayment").html(response.totalPayAtAccommodationPaymentTxt);
                        $("#atServicePercentPayment").html(response.totalPayAtAccommodationPaymentTxt);

                        cartPrepayment = response.totalPrepayment;
                        $("#paymentsRow").removeClass("hide");
                        $("#overlayLoading").addClass("hide");
                        $("#payNow").removeAttr("disabled");
                    }
                }
            });

            $('#selectedReservations').slimScroll({
                height: '250px',
                railOpacity: 0.9,
                color: '#0d3044',
                opacity : 1,
                alwaysVisible : true
            });
        });
    }

    var onEmptyCartButton = function()
    {
       $("#emptyCart").on('click',function() {
           var _url = $(this).data("url");
               swal({
                   title: $("#alertMessages").data("alerttitle"),
                   text: $("#alertMessages").data("alertcontent"),
                   type: "warning",
                   showCancelButton: true,
                   confirmButtonColor: "#DD6B55",
                   confirmButtonText: $("#alertMessages").data("confirmbutton"),
                   cancelButtonText: $("#alertMessages").data("cancelbutton"),
                   closeOnConfirm: false
               }, function () {
                   window.location = _url;
               });
        });
    }

    var onShowMorePaymentButton=function(){
        $('.icon-down-payment').on('click',function(){
            var genresid = $(this).data("genresid");
            var icon_up = "#icon-up-payment_" + genresid;
            var icon_down = "#icon-down-payment_" + genresid;
            var toShow = ".sectionToHide_" + genresid;
            $(icon_up).removeClass('hide');
            $(icon_down).addClass('hide');

            $(toShow).removeClass('hide');
        });
        $('.icon-up-payment').on('click',function(){
            var genresid = $(this).data("genresid");
            var icon_up = "#icon-up-payment_" + genresid;
            var icon_down = "#icon-down-payment_" + genresid;
            var toShow = ".sectionToHide_" + genresid;
            $(icon_up).addClass('hide');
            $(icon_down).removeClass('hide');
            $(toShow).addClass('hide');
        });
    }
    var onPayNowButton = function (){
        $("#payNow").on('click',function() {
            $("#overlayLoading").removeClass("hide");
            $(".payButton").attr("disabled", "true");
            var _url = $(this).data("url");
            var roomsToPay = $('input[name=checkAccommodationsToPay]:checked').map(function() {
                return $(this).data('owresid');
            }).get();

            var extraData = $('select.arrivalTime').map(function() {
                var genResId = $(this).data('genresid');
                return {
                    "genResId": genResId,
                    "arrivalTime": $(this).val(),
                    "clientName": $("#clientName_"+genResId).val(),
                    "idReservation": $(this).data("idreservation")
                };
            }).get();

            //Ir a buscar los datos de los ownRes seleccionados para pagar y generar un booking (server)
            $.post(_url, {
                'roomsToPay': roomsToPay,
                'extraData': extraData,
                'cartPrepayment': cartPrepayment
            }, function (response) {

                if (response.success) {
                    if(response.url != ""){
                        window.location = response.url;
                    }
                }
            });
        });
    }


    return {
        init: function () {
            initPlugins();
            onclickBtnMoreFilter();
            onclickBtnSearch();
			onShowReservationModal();
            onAddNewOpenReservationButton();
            onEndReservationButton();
            onAddToOpeneservationButton();
            onShowOpenReservationsListDetailsButton();
            onCloseOpenReservationDetailedListButton();
            onDeleteOpenReservationDetailButton();
            onViewMoreButton();
            onDeleteFromCartButton();
            onCheckDetailsInCartButton();
            onPayActionButton();
            onEmptyCartButton();
            onShowMorePaymentButton();
            onPayNowButton();

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
        },
        setTextCart:function(value){
            $('cart-count').text(value);
        }
    };
}();
Dashboard.init();

