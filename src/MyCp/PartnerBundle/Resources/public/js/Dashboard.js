/**
 Dashboard script to handle the entire layout and base functions
 **/
var Dashboard = function () {
    var start = 0;
    var limit = 4;
    /**
     * Dashboard form init plugins
     */
    var selectedAccommodationForReserve = 0;
    //var cartPrepayment = 0;

    var initPlugins = function () {
        var config = {
            '.chosen-select': {},
            '.chosen-select-deselect': {allow_single_deselect: true},
            '.chosen-select-no-single': {disable_search_threshold: 10},
            '.chosen-select-no-results': {no_results_text: 'Oops, nothing found!'},
            '.chosen-select-width': {width: "95%"}
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
        }).on('changeDate', function (ev) {
            if (!ev.date) {
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
            language: $('#modalDateFrom').attr('data-localization')
        }).on('changeDate', function (ev) {
            if (!ev.date) {
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
            language: $('#modalDateTo').attr('data-localization')
        }).data('datepicker');

        $("#priceFilter").slider({});
    }
    var onclickBtnMoreFilter = function () {
        $('#more-filter').addClass('hide');
        $('#icon-back').on('click', function () {
            if ($('#more-filter').hasClass('hide')) {
                $('#more-filter').removeClass('hide');
                $('#icon-top').removeClass('hide');
                $('#icon-back').addClass('hide');
                $('#text-more').addClass('hide');
                if (!$('#search-result').hasClass('hide'))
                    $('#big_map').removeClass('hide');
                $('.container-map').css('margin-top', '351px');
            }
        });
        $('#icon-top').on('click', function () {
            $('#more-filter').addClass('hide');
            $('#icon-top').addClass('hide');
            $('#icon-back').removeClass('hide');
            $('#text-more').removeClass('hide');
            if ($('#search-result').hasClass('hide'))
                $('#big_map').addClass('hide');
            $('.container-map').css('margin-top', '100px');
        });
    }
    var form = $('#form-filter-ownership');
    var validatorForm = form.validate({
        errorElement: 'span',
        errorClass: 'has-error',
        ignore: "",
        rules: {},
        invalidHandler: function (event, validator) {
        },
        highlight: function (element, clsError) { // hightlight error inputs
            element = $(element);
            element.parent().addClass(clsError);
        },
        unhighlight: function (element, clsError) { // revert the change done by hightlight
            element = $(element);
            element.parent().removeClass(clsError);
        }
    });
    var onclickBtnSearch = function () {
        $('#btn_search').on('click', function () {
            if (validatorForm.form()) {
                var data_params = {};
                var form = $("#form-filter-ownership");
                var result = $('#list-ownership');
                $('.slimScrollBar').css('top', '0px');
                result.innerHTML = "";
                var _url = $(this).data('url');
                $('#big_map').removeClass('hide');
                if (typeof Map != "undefined")
                    Map.removeMarkers();
                form.serializeArray().map(function (x) {
                    data_params[x.name] = x.value;
                });
                HoldOn.open();
                data_params['start'] = 0;
                data_params['limit'] = 4;
                start = 0;
                limit = 4;
                $.post(_url, data_params, function (response) {
                    window.partner_arrival_date=response.partner_arrival_date;
                    window.partner_exit_date=response.partner_exit_date;

                    HoldOn.close();
                    result.html(response.response_twig);
                    //se manda a eliminar los market
                    Map.removeMarkers();
                    //Se manda a pintar al map
                    Map.createMarkerAndListenerEvent(response.response_json);
                    start = limit + 1;
                    limit = limit + 5;
                    onShowReservationModal();
                    $('#search-result').removeClass('hide');
                    $('#search-result').slimScroll({
                        height: '580px',
                        railOpacity: 0.9,
                        color: '#0d3044',
                        opacity: 1,
                        alwaysVisible: true
                    });
                });
            }

        });

    }
    var infiniteScroll = function () {
        $('#search-result').scroll(function () {
            if ($('#search-result').scrollTop() >= $('#list-ownership').height() - $('#search-result').height()) {
                var data_params = {};
                var form = $("#form-filter-ownership");
                var result = $('#list-ownership');
                var _url = $('#btn_search').data('url');
                data_params['start'] = start;
                data_params['limit'] = limit;
                form.serializeArray().map(function (x) {
                    data_params[x.name] = x.value;
                });
                $.post(_url, data_params, function (response) {
                    //Se manda a pintar al map
                    Map.createMarkerAndListenerEvent(response.response_json);

                    var top = $('.slimScrollBar').css('top').split('px');
                    var newTop = top[0] - 50;
                    $('.slimScrollBar').css('top', newTop + 'px');
                    result.append(response.response_twig);
                    start = limit + 1;
                    limit = limit + 5;

                    onShowReservationModal();
                });
            }
        })
    }
    var onShowReservationModal = function () {

        $(".createReservation").on('click', function () {
            if (!$('#myModalReservation').is(':visible')) {
                //hide alert label
                $(".alertLabel").addClass("hidden");
                //Reset select
                $("#partner_reservation_client").val('').trigger("chosen:updated");
                selectedAccommodationForReserve = $(this).data("ownid");
                var url= $(this).data("url");
                $('#myModalReservation').modal("show");
                var result = $('#openReservationsList');
                var _url = result.data("url");
                var hasContent = result.data("content");
                var loadingText = result.data("loadingtext");
                var accommodationName = $(this).data("accommodation");
                var data = {};

                result.removeClass("hidden");
                $("#openReservationsListDetails").addClass("hidden");

               if(window.partner_arrival_date!=''){
                   $("#modalDateFrom").val(window.partner_arrival_date);
               }

                if(window.partner_exit_date!=''){
                    $("#modalDateTo").val(window.partner_exit_date);
                }


                $("#accommodationName").html(accommodationName);

               /* $("#divResult").slimScroll({
                    height: '550px',
                    railOpacity: 0.9,
                    color: '#ffffff',
                    opacity: 1,
                    alwaysVisible: false
                });*/

                //if (!hasContent) {
                    result.html(loadingText);
                    //Mostrar listado de reservaciones abiertas
                    $.post(_url, {'accommodationId': selectedAccommodationForReserve}, function (data) {
                        result.html(data);
                        result.data("content", true);
                        onEndReservationButton();
                        onAddToOpeneservationButton();
                        onShowOpenReservationsListDetailsButton();
                        var start_date = $('#filter_date_from').val();
                        var end_date = $('#filter_date_to').val();

                        $('#filter_date_from').datepicker({
                            format: 'dd/mm/yyyy',
                            todayBtn: false,
                            autoclose: true,
                            startDate: start_date,
                            date: start_date
                        }).on('changeDate', function (ev) {
                                if (!ev.date) {
                                    return;
                                }
                                var startDate = new Date(ev.date);
                                startDate.setDate(startDate.getDate() + 2);
                                departure_datepicker.setStartDate(startDate);
                                var valueDate = new Date(ev.date);
                                valueDate.setDate(valueDate.getDate() + 2);
                                departure_datepicker.setDate(valueDate);
                                from = $('#filter_date_from').val();
                                to = $('#filter_date_to').val();
                                refresh_calendar(from, to,selectedAccommodationForReserve,url);
                            });
                        var departure_datepicker = $('#filter_date_to').datepicker({
                            format: 'dd/mm/yyyy',
                            todayBtn: false,
                            autoclose: true,
                            startDate: '+1d',
                            date: end_date
                        }).on('changeDate', function (ev) {
                                from = $('#filter_date_from').val();
                                to = $('#filter_date_to').val();
                                refresh_calendar(from, to,selectedAccommodationForReserve,url);
                            }).data('datepicker');

                        from = $('#filter_date_from').val();
                        to = $('#filter_date_to').val();
                        refresh_calendar(from, to,selectedAccommodationForReserve,url);
                    });
                //}
            }
        });
    }
    var refresh_calendar=function(from, to,own_id,url){

        if (from != '' && to != '') {
            $('.calendar-results').css({display: 'none'});
            element = $("#body_calendar");
            element.attr('class', 'container_loading');
            element.html('<div>&nbsp;</div>');
            $('#rooms_selected').css({display: 'none'});
            $('#all_data_numbers').css({display: 'none'});

            fields_dates = $('.form-control')
            btn_refresh = $('#button_refresh_calendar')
            fields_dates.attr('disabled', 'true');
            if (own_id!='') {
                $.ajax({
                    url: url,
                    data: {from: from, to: to, own_id: own_id}

                }).done(function (resp) {
                        element.removeAttr('class');
                        element.html(resp);
                        from = from.replace('/', '&');
                        from = from.replace('/', '&');
                        to = to.replace('/', '&');
                        to = to.replace('/', '&');
                        $('#data_reservation').attr('from_date', from);
                        $('#data_reservation').attr('to_date', to);
                        fields_dates.removeAttr('disabled');

                    });
            }
        }
    }
    var details_favorites = function (favorite_button) {
        var url;
        $(favorite_button).unbind();
        $(favorite_button).click(function () {

            var temp = $('#count-fav').text();
            if (favorite_button == '#delete_from_favorites') {
                temp--;
                $('i', favorite_button).removeClass('ion-ios-star').removeClass('ion-ios-star-outline').addClass('ion-ios-star-outline');
                $(favorite_button).attr('id', "add_to_favorites");
                url = $(this).attr('data-remove-url');
            } else {
                temp++;
                $('i', favorite_button).removeClass('ion-ios-star').removeClass('ion-ios-star-outline').addClass('ion-ios-star');
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
                , function (data) {
                    $(".favorites_details").html(data);

                    details_favorites("#delete_from_favorites");
                    details_favorites("#add_to_favorites");
                });
        });
    }
    var onAddNewOpenReservationButton = function () {
        var validator = $("#form-agency").validate({
            submitHandler: function(form) {
                //form.submit();
            },
            errorElement: 'span',
            errorClass: 'has-error',
            ignore: "",
            invalidHandler: function(event, validator) { //display error alert on form submit
            },
            rules: {
                'partner_reservation[name]':{
                    required:true
                }
            },
            highlight: function (element, clsError) { // hightlight error inputs
                element = $(element);
                element.parent().addClass(clsError);
            },
            unhighlight: function (element, clsError) { // revert the change done by hightlight
                element = $(element);
                element.parent().removeClass(clsError);
            },
            success: function(label) {
                label.closest('.input-group').removeClass('has-error');
                label.remove();
            }
        });

            $("#btnAddNewOpenReservation").on('click', function () {
            if(!validator.form()){
                return;
            }

            var result = $('#openReservationsList');
            var dateFrom = $("#filter_date_from").val();
            var dateTo = $("#filter_date_to").val();
            var clientName = $("#partner_reservation_name").val();
            //var clientEmail = $("#partner_reservation_email").val();
            var clientId = $("#partner_reservation_client").val();
            /*var adults = $("#partner_reservation_adults").val();
            var children = $("#partner_reservation_children").val();*/
            var _url = $("#btnAddNewOpenReservation").data("url");

            var roomType = $("#requests_ownership_filter_room_type").val();
            var roomsTotal = $("#requests_ownership_filter_room").val();

            $(".alertLabel").addClass("hidden");
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");

            //var isValid = (dateFrom != "" && dateTo != "" && clientName != "") && (adults != "" || children != "");

            //if (isValid) {
                var loadingText = result.data("loadingtext");
                result.html(loadingText);

                $.post(_url, {
                    'dateFrom': dateFrom,
                    'dateTo': dateTo,
                    'clientName': clientName,
                    /*'adults': (adults == "") ? 0 : adults,
                    'children': (children == "") ? 0 : children,*/
                    'accommodationId': selectedAccommodationForReserve,
                    'clientId':clientId,
                    'roomType': roomType,
                    'roomsTotal': roomsTotal
                   // 'clientEmail':clientEmail
                }, function (response) {

                    if (response.success) {
                        result.html(response.html);
                        result.data("content", true);
                        onEndReservationButton();
                        onAddToOpeneservationButton();
                        onShowOpenReservationsListDetailsButton();
                        //Clear data from inputs
                        $("#form-agency")[0].reset();
                    }

                    if (response.message != "") {
                        //crear otro para mensajes
                        $(".alertLabel").html(response.message);
                        $(".alertLabel").removeClass("hidden");
                    }
                });
            /*}
            else {
                $(".alertLabel").removeClass("hidden");
            }*/
        });
    }
    var onEndReservationButton = function () {
        $(".closeReservation").on('click', function () {
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

                if (response.success) {
                    result.html(response.html);
                    result.data("content", true);
                    onEndReservationButton();
                    onAddToOpeneservationButton();
                    onShowOpenReservationsListDetailsButton();
                }
            });
        });
    }
    var onAddToOpeneservationButton = function () {
        $(".addToOpenReservation").on('click', function () {
            var id = $(this).data("id");
            var result = $('#openReservationsList');
            var _url = $(this).data("url");
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");

            var dateFrom = $("#modalDateFrom").val();
            var dateTo = $("#modalDateTo").val();
            $(".alertLabel").addClass("hidden");

            if (dateFrom != "" && dateTo != "" && selectedAccommodationForReserve != "") {
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

                    if (response.message != "") {
                        //crear otro para mensajes
                        $(".alertLabel").html(response.message);
                        $(".alertLabel").removeClass("hidden");
                    }

                });
            }
            else {
                $(".alertLabel").removeClass("hidden");
            }
        });
    }
    var onShowOpenReservationsListDetailsButton = function () {
        $(".showReservationsDetails").on('click', function () {
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

                if (response.message != "") {
                    //crear otro para mensajes
                    $(".alertLabel").html(response.message);
                    $(".alertLabel").removeClass("hidden");
                }

            });
        });
    }
    var onCloseOpenReservationDetailedListButton = function () {
        $("#closeDetailedOpenReservationList").on('click', function () {
            var result = $('#openReservationsList');
            result.removeClass("hidden");
            $("#openReservationsListDetails").addClass("hidden");
        });
    }
    var onDeleteOpenReservationDetailButton = function () {
        $(".deleteOpenReservationDetail").on('click', function () {
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
                    if (response.html != "") {
                        result.html(response.html);
                        onCloseOpenReservationDetailedListButton();
                        onDeleteOpenReservationDetailButton();
                    }
                    else {
                        result.addClass("hidden");
                        $("#openReservationsList").removeClass("hidden");
                    }

                    $("#openReservationsList").html(response.htmlReservations);
                    onEndReservationButton();
                    onAddToOpeneservationButton();
                    onShowOpenReservationsListDetailsButton();
                }

                if (response.message != "") {
                    //crear otro para mensajes
                    $(".alertLabel").html(response.message);
                    $(".alertLabel").removeClass("hidden");
                }

            });

        });
    }
    var onViewMoreButton = function () {
        $('.icon-down').on('click', function () {
            if ($('.icon-up').hasClass('hide')) {
                var id = $(this).data("id");
                var icon_up = "#icon_up_" + id;
                var icon_down = "#icon_down_" + id;
                var _url = $(this).data("url");

                //Cargar si no tiene datos
                var cartItemContentId = $(this).data("element");

                if (!$(cartItemContentId).data("hascontent")) {
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
                else {
                    $(icon_up).removeClass('hide');
                    $(icon_down).addClass('hide');
                }

                $(cartItemContentId).removeClass('hide');
            }
        });
        $('.icon-up').on('click', function () {
            var id = $(this).data("id");
            var icon_up = "#icon_up_" + id;
            var icon_down = "#icon_down_" + id;
            $(icon_up).addClass('hide');
            $(icon_down).removeClass('hide');
            var cartItemContentId = $(this).data("element");
            $(cartItemContentId).addClass('hide');
        });
    }
    var onDeleteFromCartButton = function () {
        $(".deleteFromCart").on('click', function () {
            var that = $(this);

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
                var id = that.data("id");
                var _url = that.data("url");

                $("#loading_" + id).removeClass('hide');

                $.post(_url, {
                    'reservationId': id
                }, function (response) {
                    if (response.success) {
                        if (response.html != "") {
                            /*result.html(response.html);
                             onDeleteFromCartButton();
                             onViewMoreButton();
                             onCheckDetailsInCartButton();
                             onEmptyCartButton();
                             onPayActionButton();*/
                            window.location.href = that.data("urlsuccess");
                        }
                    }
                });
            });
        });
    }
    var onCheckDetailsInCartButton = function () {
        $(".checkAccommodations").on('change', function () {
            var owresid = $(this).data("owresid");
            var idreservation = $(this).data("idreservation");
            var counter = parseInt($("#accommodationsToPay_" + idreservation).html());
            if ($(this).is(":checked")) {
                $("#accommodationsToPay_" + idreservation).html(counter + 1);
            }
            else
                $("#accommodationsToPay_" + idreservation).html(counter - 1);

            var checkValues = $('input[name=checkAccommodationsToPay]:checked').map(function () {
                return $(this).data('owresid');
            }).get();

            if (checkValues.length > 0) {
                $("#toPayButtonAction").addClass("hide");
                $("#trigger-overlay").removeClass("hide");
            }
            else
            {
                $("#toPayButtonAction").removeClass("hide");
                $("#trigger-overlay").addClass("hide");
            }
        });
    }

    var onPayActionButton = function () {
        $("#trigger-overlay").on('click', function (e) {
            $('#pppppxxx').slimScroll({
                height: '100%',
                width:'100%',
                railOpacity: 0.9,
                color: '#0d3044',
                opacity: 1,
                alwaysVisible: true
            });

            var checkValues = $('input[name=checkAccommodationsToPay]:checked').map(function () {
                return $(this).data('owresid');
            }).get();

            if (checkValues.length == 0) {
                $("#overlayLoading").addClass("hide");
                $("#payNow").attr("disabled", "true");
            }

            else {

                $("#overlayLoading").removeClass("hide");
                $("#paymentsRow").addClass("hide");
                $("#rowTotalPrepaymentGeneral").addClass("hide");
                var _url = $(this).data("url");
                var result = $("#selectedReservations");

                result.html("");

                //Ir a buscar los datos de los ownRes seleccionados para pagar y generar un booking (server)
                $.post(_url, {
                    'checkValues': checkValues
                }, function (response) {
                    if (response.success) {
                        if (response.html != "") {
                            result.html(response.html);
                            onShowMorePaymentButton();
                            $("#completePayment").val(response.completePayment);

                            if(!response.completePayment) {
                                $("#totalPrepayment").html(response.totalPrepaymentTxt);
                                $("#totalPrepaymentGeneral").html(response.totalPrepaymentTxt);
                                $("#totalPrepaymentGeneralInput").val(response.totalPrepayment);
                                $("#totalAccommodationsPayment").html(response.totalAccommodationPaymentTxt);
                                $("#totalServiceTaxesPayment").html(response.totalServiceTaxPaymentTxt);
                                $("#totalServiceTaxesPrepayment").html(response.totalServiceTaxPaymentTxt);
                                $("#fixedTax").html(response.fixedTaxTxt);
                                $("#fixedTaxPrepayment").html(response.fixedTaxTxt);
                                $("#totalPercentAccommodationsPrepayment").html(response.totalPercentAccommodationPrepaymentTxt);
                                $("#totalPayment").html(response.totalPaymentTxt);
                            }
                            else{
                                $("#totalPrepayment").html(response.totalPrepaymentTxt);
                                $("#totalPrepaymentGeneral").html(response.totalPrepaymentTxt);
                                $("#totalPayment").html(response.totalPaymentTxt);

                                $("#totalPrepaymentGeneralInput").val(response.totalPrepayment);
                                $("#totalAccommodationsPayment").html(response.totalAccommodationPaymentTxt);
                                $("#totalTransferFeePayment").html(response.totalTransferFeePaymentTxt);

                                $("#agencyCommissionHeader").html(response.totalAgencyCommissionTxt);
                                $("#agencyCommissionContent").html(response.totalAgencyCommissionTxt);

                                $("#totalServiceTaxesPayment").html(response.totalServiceTaxPaymentTxt);
                                $("#totalServiceTaxesPrepayment").html(response.totalServiceTaxPaymentTxt);
                                $("#fixedTax").html(response.fixedTaxTxt);
                                $("#fixedTaxPrepayment").html(response.fixedTaxTxt);
                                //$("#totalPercentAccommodationsPrepayment").html(response.totalPercentAccommodationPrepaymentTxt);
                                $("#totalPayment").html(response.totalPaymentTxt);

                                $("#atServicePayment").html(response.totalPayAtAccommodationPaymentTxt);
                                $("#atServicePercentPayment").html(response.totalPayAtAccommodationPaymentTxt);
                            }

                            //cartPrepayment = response.totalPrepayment;
                            $("#paymentsRow").removeClass("hide");
                            $("#overlayLoading").addClass("hide");
                            $("#payNow").removeAttr("disabled");
                            $("#rowTotalPrepaymentGeneral").removeClass("hide");
                        }
                    }
                });

                $('#selectedReservations').slimScroll({
                    height: '350px',
                    railOpacity: 0.9,
                    color: '#0d3044',
                    opacity: 1,
                    alwaysVisible: true
                });
            }
        });
    }

    var onEmptyCartButton = function () {
        $("#emptyCart").on('click', function () {
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

    var onShowMorePaymentButton = function () {
        $('.icon-down-payment').on('click', function () {
            var genresid = $(this).data("genresid");
            var icon_up = "#icon-up-payment_" + genresid;
            var icon_down = "#icon-down-payment_" + genresid;
            var toShow = ".sectionToHide_" + genresid;
            $(icon_up).removeClass('hide');
            $(icon_down).addClass('hide');

            $(toShow).removeClass('hide');
        });
        $('.icon-up-payment').on('click', function () {
            var genresid = $(this).data("genresid");
            var icon_up = "#icon-up-payment_" + genresid;
            var icon_down = "#icon-down-payment_" + genresid;
            var toShow = ".sectionToHide_" + genresid;
            $(icon_up).addClass('hide');
            $(icon_down).removeClass('hide');
            $(toShow).addClass('hide');
        });
    }
    var onPayNowButton = function () {
        $("#payNow").on('click', function () {
            $("#overlayLoading").removeClass("hide");
            $(".payButton").attr("disabled", "true");
            var cartPrepayment = $("#totalPrepaymentGeneralInput").val();
            //var _url = $(this).data("url");
            var roomsToPay = $('input[name=checkAccommodationsToPay]:checked').map(function () {
                return $(this).data('owresid');
            }).get();
            $("#roomsToPay").val(roomsToPay);

            var reservationsToPay = $('input[name=checkAccommodationsToPay]:checked').map(function () {
                return $(this).data('idreservation') + "-" + $(this).data('genresid');
            }).get();
            $("#paymentExtraData").val(reservationsToPay);

           /* var extraData = $('select.arrivalTime').map(function () {
                var genResId = $(this).data('genresid');
                return {
                    "genResId": genResId,
                    "arrivalTime": $(this).val(),
                    "clientName": $("#clientName_" + genResId).val(),
                    "idReservation": $(this).data("idreservation")
                };
            }).get();
            $("#paymentExtraData").val(extraData);*/

            var form = $("#paPaymentForm");
            form.submit();

            //Ir a buscar los datos de los ownRes seleccionados para pagar y generar un booking (server)
            /*$.post(_url, {
                'roomsToPay': roomsToPay,
                'extraData': extraData,
                'cartPrepayment': cartPrepayment
            }, function (response) {

                if (response.success) {
                    if (response.url != "") {
                        window.location = response.url;
                    }
                }
            });*/
        });
    }
    var onclickAddClient=function(){
        $('#add-client').on('click',function(){
            $('#row-name').removeClass('hide');
            $("#partner_reservation_client").val('').trigger("chosen:updated");
            $("#form-agency")[0].reset();
        })
    }
    var onChangeClient=function(){
        var clientSelect = $('#partner_reservation_client');
        clientSelect.change(function(){
             if($(this).val()!=''){
                 $('#row-name').removeClass('hide');
                 $.post($(this).data('url'), {
                     'idClient': $(this).val()
                 }, function (response) {
                     if (response.success) {
                         $("#partner_reservation_name").val(response.fullname);
                        // $("#partner_reservation_email").val(response.email);
                     }
                 });
             }
            else{
                 $("#form-agency")[0].reset();
             }
        });
    }
    var onclickBtnRefreshClient=function(){
        $('#refresh-client').on('click',function(){
            Dashboard.refreshClientList();
        });

    }
    var onclickBtnResetFormReservation=function(){
        $('#btnResetFormReservation').on('click',function(){
            $("#form-agency")[0].reset();
            $("#partner_reservation_client").val('').trigger("chosen:updated");
        })

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
            onclickAddClient();
            onChangeClient();
            onclickBtnRefreshClient();
            onclickBtnResetFormReservation();
        },
        refreshClientList: function () {
            var _url=$('#partner_reservation_client').data('urlrefreshlist');
            var clientSelect =$('#partner_reservation_client');
            $.ajax({
                type: 'post',
                url: _url,
                success: function (data) {
                    data = (data['aaData']) ? (data['aaData']) : ([]);
                    clientSelect.html('');
                    for (var i = 0, total = data.length; i < total; i++) {
                        if(i == 0){
                            clientSelect.append('<option selected="selected" value=""></option>');
                        }
                        clientSelect.append('<option value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>');
                    }
                    clientSelect.trigger('update');
                    clientSelect.chosen().trigger("chosen:updated");
                },
                error: function (jqXHR, textStatus, errorThrown) {
                }
            });
        },
        setStart: function (a) {
            start = a
        },
        getStart: function (a) {
            return start;
        },
        setLimit: function (a) {
            limit = a
        },
        getLimit: function (a) {
            return limit;
        },
        setTextCart: function (value) {
            $('cart-count').text(value);
        },
        reloadProccessAndPending: function(){
            if(typeof filterPendingAction == 'function'){
                filterPendingAction();
            }
            if(typeof filterProccessAction == 'function'){
                filterProccessAction();
            }
        }
    };
}();
Dashboard.init();

