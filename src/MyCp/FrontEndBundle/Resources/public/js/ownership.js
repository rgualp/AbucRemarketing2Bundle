$(document).ready(start_s);

function start_s() {
    $('#select_change_order').change(change_order);
    $('#change_view_to_list').click(change_view_to_list);
    $('#change_view_to_photo').click(change_view_to_photo);
    $('#change_view_to_map').click(change_view_to_map);

    // load_upper_filters();

    //Buscador que esta encima de los filtros
    $('#button_research').click(function(){
        research(1);
    });

    $(window).keydown(function(event){
        if (event.keyCode == 13){
            research(1);
        }
    });



    //Filtros
    $('.action_remove_filter_up').change(function() {
        remove_filter_up($(this));
    });

    $('#filters-submit').click(function () {
        research(1);
    });
    datePickersStarUp();
    $('#btn_insert_comment').click(insert_comment);
    //reservations_in_details();
    datePickersStarUp_searcher();
    connectSearchOnEnter();
    $("#btn_search").click(function () {
        research(1);
    });

    research(-1);

    top_rated();
}

function top_rated()
{
    $('.top20_items_per_page').click(function()
    {
        $('ul.top_rated_items_per_page li.active').removeClass('active');
        $(this).addClass('active');
        var show_rows = $(this).attr('data-content-value');
        visualize_rows(show_rows);
    });

    $('.top20_category').click(function()
    {
        var category;
        $('ul.top_rated_category li.active').removeClass('active');
        $(this).addClass('active');
        category = $(this).attr('data-content-value');

        change_category(category);
    });

    $('.top_rated_tools .paginator-cont a').click(do_paginate);
}

function change_category(category)
{
    var url = $("#top_rated_placeholder").attr("data-url-change-category");
    var result = $("#top_rated_placeholder");

    show_loading();
    $.post(url, {
        'category': category
    }, function(data) {
        result.html(data);
        $("[rel='tooltip']").tooltip();
        $("[data-rel='tooltip']").tooltip();
        start();
        hide_loading();
    });
}

function visualize_rows(show_rows)
{
    var url = $("#top_rated_placeholder").attr("data-url");
    var result = $("#top_rated_placeholder");

    show_loading();
    $.post(url, {
        'show_rows': show_rows
    }, function(data) {
        result.html(data);
        $("[rel='tooltip']").tooltip();
        $("[data-rel='tooltip']").tooltip();
        start();
        hide_loading();
    });
}

function do_paginate()
{
    var url = $("#top_rated_placeholder").attr("data-url");
    var result = $("#top_rated_placeholder");

    //show_loading();
    $.post(url, null, function(data) {
        result.html(data);
        //hide_loading();
        start();
    });
}

// function datePickersStarUp_searcher() {
//     $('.show_calendar').click(function(){
//     $("#"+$(this).prev().attr('id')).datepicker('show');
//     });
//     $('#input_arrival_date').datepicker({
//         format: 'dd/mm/yyyy',
//         todayBtn: true,
//         autoclose: true,
//         startDate: today_date,
//         date: start_date,
//         language: $('#input_arrival_date').attr('data-localization')
//     }).on('changeDate', function(ev) {
//             var startDate = new Date(ev.date);
//             startDate.setDate(startDate.getDate() + 1);
//             departure_datepicker.setStartDate(startDate);
//             var valueDate = new Date(ev.date);
//             valueDate.setDate(valueDate.getDate() + 2);
//             departure_datepicker.setDate(valueDate);
//         });
//
//     var departure_datepicker = $('#input_departure_date').datepicker({
//         format: 'dd/mm/yyyy',
//         todayBtn: false,
//         autoclose: true,
//         startDate: '+1d',
//         date: end_date,
//         language: $('#input_departure_date').attr('data-localization')
//     }).data('datepicker');
// }

function connectSearchOnEnter() {
    $('#orange_search_bar').keydown(function(e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if ((keycode === 13)) {
            research();
        }
    });
}

function create_dateDMY(date_text) {
    var date = date_text.split('/');
    if (date.length == 3)
    {
        var date_result = parseInt(date[0], 10) + '-' + (parseInt(date[1], 10)) + '-' + parseInt(date[2], 10);
        return date_result;
    }
    return null;
}

// function datePickersStarUp(){
//
//     $('#top_reservation_filter_date_from').datepicker({
//         format:'dd/mm/yyyy',
//         todayBtn:'linked',
//         autoclose: true,
//         startDate: today_date,
//         date: start_date,
//         language: $('#top_reservation_filter_date_from').attr('data-localization')
//     }).on('changeDate', function(ev){
//             var startDate = new Date(ev.date);
//             startDate.setDate(startDate.getDate() + 1);
//             reservation_filter_date_to.setStartDate(startDate);
//             var date = new Date(ev.date);
//             date.setDate(date.getDate() + 2);
//             reservation_filter_date_to.setDate(date);
//
//             var startDate = new Date(ev.date);
//             startDate.setDate(startDate.getDate() + 1);
//             $('#filter_date_from').datepicker("setDate", startDate);
//             $('#filter_date_to').datepicker("setDate", date);
//
//
//             $('.datepicker').hide();
//             $('#top_reservation_submit_button').attr('type','submit');
//             $('#top_reservation_submit_button').attr('onclick','');
//             $('#top_reservation_submit_button').html(reservation_see_prices_text);
//         });
//
//     var reservation_filter_date_to = $('#top_reservation_filter_date_to').datepicker({
//         format:'dd/mm/yyyy',
//         todayBtn:false,
//         autoclose: true,
//         startDate: '+1d',
//         date: end_date,
//         language: $('#top_reservation_filter_date_to').attr('data-localization')
//     }).data('datepicker');
//
//     $('#filter_date_from').datepicker({
//         format:'dd/mm/yyyy',
//         todayBtn:'linked',
//         autoclose: true,
//         startDate: today_date,
//         date: start_date,
//         language: $('#filter_date_from').attr('data-localization')
//     }).on('changeDate', function(ev){
//             var startDate = new Date(ev.date);
//             startDate.setDate(startDate.getDate() + 1);
//             $('#filter_date_to').datepicker("setStartDate", startDate);
//             var date = new Date(ev.date);
//             date.setDate(date.getDate() + 2);
//             $('#filter_date_to').datepicker("setDate", date);
//             $('.datepicker').hide();
//
//             refresh_calendar(startDate.getDate() + '/' + (startDate.getMonth() + 1) + '/' + startDate.getFullYear(),date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear());
//
//             /*var start_date = new Date(ev.date);
//              start_date.setDate(start_date.getDate() + 1);
//              $('#top_reservation_filter_date_from').datepicker("setDate", start_date);
//              $('#top_reservation_filter_date_to').datepicker("setDate", date);*/
//         });
//
//     var filter_date_to =$('#filter_date_to').datepicker({
//         format:'dd/mm/yyyy',
//         todayBtn: false,
//         autoclose: true,
//         startDate: '+1d',
//         date: end_date,
//         language: $('#filter_date_to').attr('data-localization')
//     }).data('datepicker');
// }

function change_order()
{
    //Obtener parametros
    var order = $('#select_change_order').val();
    show_loading();
    var result = $('#div_result');
    var url = $('#select_change_order').attr('data-url');

    $.post(url, {
        'order': order
    }, function(data) {
        result.html(data);
        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");
        hide_loading();
    });

    return false;
}

function change_view_to_list(event)
{
    _changeViewTo($('#change_view_to_list').attr('data-url'), 'LIST', event);
}

function change_view_to_photo(event)
{
    _changeViewTo($('#change_view_to_photo').attr('data-url'), 'PHOTOS', event);
}

function change_view_to_map(event)
{
    _changeViewTo($('#change_view_to_map').attr('data-url'), 'MAP', event);
}

function _changeViewTo(url, viewType, event) {
    show_loading();
    var result = $('#div_result');

    $.post(url, {
        'view': viewType
    }, function(data) {
        result.html(data);

        if (viewType === "MAP")
        {
            $('#map').css('visibility', 'hidden');
            $('#map').css('display', 'none');
            initialize_map();
        }
        else
        {
            $('#map').css('visibility', 'visible');
            $('#map').css('display', 'block');
        }

        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");

        hide_loading();
    });

    /*$('a.link').each(function(i, item) {
     $(item).css('background-position', 'top');
     });
     $(event.target).css('background-position', 'bottom');
     */
    return false;
}

function research(_page)
{
    show_loading();

    var url = $('#button_research').attr('data-url');
    var result = $('#div_result');

    if (_page == -1){
        var page = result.attr("data-current-page");
    }else{
        var page = _page;
    }

    var checked_filters = load_upper_filters(page);

    $.post(url, checked_filters, function(data) {

        result.attr("data-cant-page", data.cant_pages);

        if (page == 1 && page == parseInt(data.cant_pages)){
            otherpage = 1;
            result.empty();
            result.append(data.html);
        }else if (page == 1){
            otherpage = 1;
            result.empty();
            result.append(data.html);
        } else if ( page <= data.cant_pages ){
            result.append(data.html);
        }




        hide_loading();
        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");
        initialize_search_map();
    });

    return false;
}

function load_upper_filters(page)
{
     //var own_reservation_type= $("#own_reservation_type").val();
        var own_type_items = [];
        var own_category_items = [];
        var own_price_items = [];
        var own_price_from_items = [];
        var own_price_to_items = [];
        var room_total_items = [];
        var room_type_items = [];
        var room_bathroom_items = [];
        var room_beds_total_items = [];
        var room_windows_total_items = [];
        var others_languages_items = [];
        var others_included_items = [];
        var own_awards = [];
        var own_inmediate_booking = $(':input[type="checkbox"][name="own_inmediate_booking"]').is(':checked');
        var own_inmediate_booking2 = $(':input[type="checkbox"][name="own_inmediate_booking2"]').is(':checked');
        var others_not_included_items = [];
        var order_price=$(':input[type="radio"][name="priceOrder"]:checked').val();
        var order_comments='';
        if($('#commentsOrder').hasClass('active'))
            order_comments='ASC';
        var order_books='';
        if($('#booksOrder').hasClass('active'))
            order_books='ASC';

        $('input[name=own_category]:checked').each(function() {
            own_category_items.push($(this).val());
            if (document.getElementById("fu_own_category_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_own_category_" + $(this).val()).remove();
            }
        });
        $('input[name=own_awards]:checked').each(function() {
            own_awards.push($(this).val());
            if (document.getElementById("fu_own_awards_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_awards_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_awards'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_own_awards_" + $(this).val()).remove();
            }
        });

        //$('input[name=own_price]:checked').each(function() {
        //    own_price_items.push($(this).val());
        //    own_price_from_items.push($(this).val());
        //    own_price_to_items.push(parseInt($(this).val()) + 25);
        //
        //    if (document.getElementById("fu_own_price_" + $(this).val()) == null)
        //    {
        //        innerHtml = $("#filter_upper").html();
        //        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_price'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        //    }
        //});
        var rangePrice=$('#priceFilter').val();
        if(rangePrice!=''){
            var res = rangePrice.split(",");
            own_price_items.push(parseInt(res[0]));
            own_price_from_items.push(parseInt(res[0]));
            own_price_to_items.push(parseInt(res[1]));
            if (document.getElementById("fu_own_price_" + rangePrice) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + rangePrice + "' data-control-id='#priceFilter' data-value='" + rangePrice + "' data-control-name='own_price'><i class='icon-remove-sign'></i>$(" + rangePrice + ")</a> ");
            }else{
                $("#fu_own_price_" + rangePrice).remove();
            }
        }
        $('input[name=own_price]:checked').each(function() {
            own_price_items.push($(this).val());
            own_price_from_items.push($(this).val());
            own_price_to_items.push(parseInt($(this).val()) + 25);

            if (document.getElementById("fu_own_price_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_price'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_own_price_" + $(this).val()).remove();
            }
        });

        $('input[name=room_total]:checked').each(function() {
            room_total_items.push($(this).val());

            if (document.getElementById("fu_room_total_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_total_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_total'><i class='icon-remove-sign'></i>" + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_room_total_" + $(this).val()).remove();
            }
        });
        $('input[name=own_type]:checked').each(function() {
            own_type_items.push($(this).val());
            if (document.getElementById("fu_own_type_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_own_type_" + $(this).val()).remove();
            }
        });

        $('input[name=room_type]:checked').each(function() {
            room_type_items.push($(this).val());
            if (document.getElementById("fu_room_type_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_room_type_" + $(this).val()).remove();
            }
        });

        $('input[name=room_bathroom]:checked').each(function() {
            room_bathroom_items.push($(this).val());
            if (document.getElementById("fu_room_bathroom_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_bathroom_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_bathroom'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_room_bathroom_" + $(this).val()).remove();
            }
        });


        var room_climatization = '';
        room_climatization = room_climatization + ((document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked) ? $('#room_airconditioner').attr('data-value') : "");

        if (document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked && document.getElementById("fu_room_airconditioner") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_airconditioner' data-control-id='room_airconditioner' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_airconditioner").parent().text() + "</a> ");
        }else{
            $("#fu_room_airconditioner").remove();
        }

        var room_audiovisuals = document.getElementById('room_audiovisuals') != null && document.getElementById('room_audiovisuals').checked;
        if (room_audiovisuals && document.getElementById("fu_room_audiovisuals") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_audiovisuals' data-control-id='room_audiovisuals' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_audiovisuals").parent().text() + "</a> ");
        }else{
            $("#fu_room_audiovisuals").remove();
        }

        var room_kids = document.getElementById('room_kids') != null && document.getElementById('room_kids').checked;
        if (room_kids && document.getElementById("fu_room_kids") == null){
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_kids' data-control-id='room_kids' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_kids").parent().text() + "</a> ");
        }else{
            $("#fu_room_kids").remove();
        }

        var room_smoker = document.getElementById('room_smoker') != null && document.getElementById('room_smoker').checked;
        if (room_smoker && document.getElementById("fu_room_smoker") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_smoker' data-control-id='room_smoker' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_smoker").parent().text() + "</a> ");
        }else{
            $("#fu_room_smoker").remove();
        }

        var room_safe = document.getElementById('room_safe') != null && document.getElementById('room_safe').checked;
        if (room_safe && document.getElementById("fu_room_safe") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_safe' data-control-id='room_safe' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_safe").parent().text() + "</a> ");
        }else{
            $("#fu_room_safe").remove();
        }

        var room_balcony = document.getElementById('room_balcony') != null && document.getElementById('room_balcony').checked;
        if (room_balcony && document.getElementById("fu_room_balcony") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_balcony' data-control-id='room_balcony' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_balcony").parent().text() + "</a> ");
        }else{
            $("#fu_room_balcony").remove();
        }

        var room_terraza = document.getElementById('room_terraza') != null && document.getElementById('room_terraza').checked;
        if (room_terraza && document.getElementById("fu_room_terraza") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_terraza' data-control-id='room_terraza' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_terraza").parent().text() + "</a> ");
        }else{
            $("#fu_room_terraza").remove();
        }

        var room_courtyard = document.getElementById('room_courtyard') != null && document.getElementById('room_courtyard').checked;
        if (room_courtyard && document.getElementById("fu_room_courtyard") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_courtyard' data-control-id='room_courtyard' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_courtyard").parent().text() + "</a> ");
        }else{
            $("#fu_room_courtyard").remove();
        }


        $('input[name=room_beds_total]:checked').each(function() {
            room_beds_total_items.push($(this).val());
            if (document.getElementById("fu_room_beds_total_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_beds_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_beds_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_room_beds_total_" + $(this).val()).remove();
            }
        });

        $('input[name=room_windows_total]:checked').each(function() {
            room_windows_total_items.push($(this).val());
            if (document.getElementById("fu_room_windows_total_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_windows_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_windows_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_room_windows_total_" + $(this).val()).remove();
            }
        });

        $('input[name=others_languages]:checked').each(function() {
            others_languages_items.push($(this).val());
            if (document.getElementById("fu_others_languages_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_languages_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_languages'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_others_languages_" + $(this).val()).remove();
            }
        });

        $('input[name=others_included]:checked').each(function() {
            others_included_items.push($(this).val());
            if (document.getElementById("fu_others_included_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_others_included_" + $(this).val()).remove();
            }
        });

        $('input[name=others_not_included]:checked').each(function() {
            others_not_included_items.push($(this).val());
            if (document.getElementById("fu_others_not_included_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_not_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_not_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }else{
                $("#fu_others_not_included_" + $(this).val()).remove();
            }
        });

        var room_others_pets = document.getElementById('room_others_pets') != null && document.getElementById('room_others_pets').checked;
        if (room_others_pets && document.getElementById("fu_room_others_pets") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_pets' data-control-id='room_others_pets' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_pets").parent().text() + "</a> ");
        }else{
            $("#fu_room_others_pets").remove();
        }

        var room_others_internet = document.getElementById('room_others_internet') != null && document.getElementById('room_others_internet').checked;
        if (room_others_internet && document.getElementById("fu_room_others_internet") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_internet' data-control-id='room_others_internet' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_internet").parent().text() + "</a> ");
        }else{
            $("#fu_room_others_internet").remove();
        }

        if (own_inmediate_booking && document.getElementById("fu_own_inmediate_booking") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_inmediate_booking' data-control-id='own_inmediate_booking' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#own_inmediate_booking").parent().text() + "</a> ");
        }else{
            $("#fu_own_inmediate_booking").remove();
        }

        if ( own_inmediate_booking2 && document.getElementById("fu_own_inmediate_booking2") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_inmediate_booking2' data-control-id='own_inmediate_booking2' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#own_inmediate_booking2").parent().text() + "</a> ");
        }else{
            $("#fu_own_inmediate_booking2").remove();
        }


        $(".filter_upper_item").unbind();

        $(".filter_upper_item").click(function() {
            filter_upper($(this));
        });

        if ($("#filter_upper").html() != "")
            $("#filter_upper").css("display", "block");

        var arrival = $('#input_arrival_date').val();
        var departure = $('#input_departure_date').val();
        var guests = $('#input_guests').val();
        var rooms = $('#input_room').val();
        var text = $('#input_text').val();
        var order_price=$(':input[type="radio"][name="priceOrder"]:checked').val();
        var order_comments='';
        if($('#commentsOrder').hasClass('active'))
            order_comments='ASC';
        var order_books='';
        if($('#booksOrder').hasClass('active'))
            order_books='ASC';
        var result = $('#div_result');
        var own_category_items=[];
        $('input[name=own_category]:checked').each(function() {
            own_category_items.push($(this).val());
            if (document.getElementById("fu_own_category_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }
        });

        arrival = (arrival != $('#input_arrival_date').attr('placeholder')) ? create_date(arrival) : null;
        departure = (departure != $('#input_departure_date').attr('placeholder')) ? create_date(departure) : null;
        text = (text != $('#input_text').attr('placeholder')) ? text : null;


        var checked_filters = {
            //"own_reservation_type": (own_reservation_type != null && own_reservation_type != "" && own_reservation_type != "-1" && own_reservation_type != -1) ? own_reservation_type : null,
            'arrival': arrival,
            'departure': departure,
            'guests': guests,
            'rooms': rooms,
            'text': text,
            'own_inmediate_booking2': (own_inmediate_booking2) ? 1 : null,
            "own_category": (own_category_items.length > 0) ? own_category_items : null,
            "own_award": (own_awards.length > 0) ? own_awards: null,
            "own_inmediate_booking": (own_inmediate_booking) ? own_inmediate_booking: null,
            "own_type": (own_type_items.length > 0) ? own_type_items : null,
            "own_price": (own_price_items.length > 0) ? own_price_items : null,
            "own_price_from": (own_price_from_items.length > 0) ? own_price_from_items : null,
            "own_price_to": (own_price_to_items.length > 0) ? own_price_to_items : null,
            "own_rooms_number": (room_total_items.length > 0) ? room_total_items : null,
            "room_type": (room_type_items.length > 0) ? room_type_items : null,
            "own_beds_total": (room_beds_total_items.length > 0) ? room_beds_total_items : null,
            "room_bathroom": (room_bathroom_items.length > 0) ? room_bathroom_items : null,
            "room_windows_total": (room_windows_total_items.length > 0) ? room_windows_total_items : null,
            "room_climatization": room_climatization,
            "room_audiovisuals": room_audiovisuals,
            "room_kids": room_kids,
            "room_smoker": room_smoker,
            "room_safe": room_safe,
            "room_balcony": room_balcony,
            "room_terraza": room_terraza,
            "room_courtyard": room_courtyard,
            "own_others_languages": (others_languages_items.length > 0) ? others_languages_items : null,
            "own_others_included": (others_included_items.length > 0) ? others_included_items : null,
            "own_others_not_included": (others_not_included_items.length > 0) ? others_not_included_items : null,
            "own_others_pets": room_others_pets,
            "own_others_internet": room_others_internet,
            "order_price": order_price,
            "order_comments": order_comments,
            "order_books": order_books,
            "page": page

        };
        return checked_filters;

}

function filter_by_others(refreshStatistics)
{
    $('#button_research').removeClass('hide');
    $('#more_filters').addClass('collapsed');
    var checked_filters = load_upper_filters();
    show_loading();

    var url = $('#filters').attr('data-url-filter');
    var result = $('#div_result');

    $.post(url, checked_filters, function(data) {
        result.html(data);
        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");

        /* if (refreshStatistics)
         refresh_filters_statistics(checked_filters);*/

        $('.action_remove_filter_up').change(function() {
            remove_filter_up($(this));
        });

        hide_loading();
    });
    return false;
}

function filter_upper(element)
{
    var control_id = element.attr("data-control-id");
    if(control_id=='#priceFilter'){
       $('#priceFilter').val('');
    }
    else {
        var control_name = element.attr("data-control-name");
        var item_value = element.attr("data-value");

        if (control_id !== "")
            document.getElementById(control_id).checked = false;

        if (control_name != "" && item_value != "") {
            $('input[name=' + control_name + ']').each(function () {
                if ($(this).val() == item_value)
                    $(this).removeAttr("checked");
            });
        }
    }
    research(1);
    element.remove();
}

function remove_filter_up(element)
{
    if (!element.is(":checked"))
    {
        var control_name = element.attr("name");
        var control_id = element.attr("id");
        var control_value = element.val();

        if (control_name != null && document.getElementById("fu_" + control_name + "_" + control_value) != null)
            $("#fu_" + control_name + "_" + control_value).remove();

        if (control_id != null && document.getElementById("fu_" + control_id + "_" + control_value) != null)
            $("#fu_" + control_id + "_" + control_value).remove();

        //alert($("#filter_upper").html());
        /* if ($("#filter_upper").html())
         $("#filter_upper").css("display", "none");*/
    }
}

function refresh_filters_statistics(checked_filters)
{
    var url = $('#filters').attr('data-url-statistics');
    var result = $('#filters');
    show_loading();
    $.post(url, checked_filters, function(data) {
        result.html(data);
        //$('#own_reservation_type').val((checked_filters['own_reservation_type'] != null ? checked_filters['own_reservation_type'] : "-1"));
        checkCheckBoxes(checked_filters['own_category'], "own_category");
        checkCheckBoxes(checked_filters['own_type'], "own_type");
        checkCheckBoxes(checked_filters['own_price'], "own_price");
        checkCheckBoxes(checked_filters['own_rooms_number'], "room_total");
        checkCheckBoxes(checked_filters['room_type'], "room_type");
        checkCheckBoxes(checked_filters['own_beds_total'], "room_beds_total");
        checkCheckBoxes(checked_filters['room_bathroom'], "room_bathroom");
        checkCheckBoxes(checked_filters['room_windows_total'], "room_windows_total");

        if (document.getElementById('room_airconditioner') != null)
            document.getElementById('room_airconditioner').checked = checked_filters['room_airconditioner'];

        if (document.getElementById('own_inmediate_booking') != null)
            document.getElementById('own_inmediate_booking').checked = checked_filters['own_inmediate_booking'];

        if (document.getElementById('own_inmediate_booking2') != null)
            document.getElementById('own_inmediate_booking2').checked = checked_filters['own_inmediate_booking2'];

        if (document.getElementById('room_audiovisuals') != null)
            document.getElementById('room_audiovisuals').checked = checked_filters['room_audiovisuals'];

        if (document.getElementById('room_kids') != null)
            document.getElementById('room_kids').checked = checked_filters['room_kids'];

        if (document.getElementById('room_smoker') != null)
            document.getElementById('room_smoker').checked = checked_filters['room_smoker'];

        if (document.getElementById('room_safe') != null)
            document.getElementById('room_safe').checked = checked_filters['room_safe'];

        if (document.getElementById('room_balcony') != null)
            document.getElementById('room_balcony').checked = checked_filters['room_balcony'];

        if (document.getElementById('room_terraza') != null)
            document.getElementById('room_terraza').checked = checked_filters['room_terraza'];

        if (document.getElementById('room_courtyard') != null)
            document.getElementById('room_courtyard').checked = checked_filters['room_courtyard'];

        checkCheckBoxes(checked_filters['others_languages'], "others_languages");
        checkCheckBoxes(checked_filters['others_included'], "others_included");
        checkCheckBoxes(checked_filters['others_not_included'], "others_not_included");

        if (document.getElementById('room_others_pets') != null)
            document.getElementById('room_others_pets').checked = checked_filters['room_others_pets'];

        if (document.getElementById('room_others_internet') != null)
            document.getElementById('room_others_internet').checked = checked_filters['room_others_internet'];

        start();
    });
    return false;
}

var map_big;
var markers_big;
function initialize_search_map() {
    var json_url = $("#json_search_source").attr('data-url');
    var icon_small = $("#json_search_source").attr('data-icon-small');
    var icon = $("#json_search_source").attr('data-icon');

    if (document.getElementById("search_map") != null)
    {
        //create empty LatLngBounds object
        //var latlngbounds = new google.maps.LatLngBounds();
        var center = new google.maps.LatLng(22.01300, -79.26635);//La Habana 23.09725, -82.37548
        //latlngbounds.extend(center);
        var options = {
            'zoom': 8,
            'center': center,
            'mapTypeId': google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("search_map"), options);
        var markers = [];
        $.getJSON(json_url, function(data) {
            if (data) {
                var myOptions_own = {
                    disableAutoPan: false,
                    maxWidth: 0,
                    pixelOffset: new google.maps.Size(-140, 0),
                    zIndex: null,
                    boxStyle: {
                        opacity: 0.85,
                        width: "280px"
                    },
                    closeBoxMargin: "10px 2px 2px 2px",
                    infoBoxClearance: new google.maps.Size(1, 1),
                    isHidden: false,
                    pane: "floatPane",
                    enableEventPropagation: false
                };
                var ib_own = new InfoBox(myOptions_own);
                var latlng_pos="";
                for (i = 0; i < data.length; i++) {
                    var latlng = new google.maps.LatLng(data[i].latitude, data[i].longitude);
                    if(latlng_pos==''){
                        if(data[i].destination.geolocate_x != '' && data[i].destination.geolocate_y != '')
                            latlng_pos= new google.maps.LatLng(data[i].destination.geolocate_x,data[i].destination.geolocate_y);
                    }
                    var marker_bullet = new google.maps.Marker({
                        id: data[i].id,
                        map: map,
                        position: latlng,
                        title: data[i].title,
                        icon: icon_small,
                        content: "<tr><td class='map_image' style='background-image:url(" + data[i].image + ")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>" + data[i].title + "<br/><b>" + data[i].content + "</b></td></tr>",

                    });

                    google.maps.event.addListener(marker_bullet, 'mouseover', (function(marker_bullet, i)
                    {
                        return function()
                        {
                            var boxText = document.createElement("div");
                            boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px";
                            boxText.innerHTML = "<div class='row'>" +
                                "<div class='map_image col-sm-4' style='background-image:url(" + data[i].image + ");height: 60px !important; margin: 0 10px;width: 65px !important;'></div>" +
                                "<div class='col-sm-8' style='line-height:12px;text-align: left'>" + data[i].title + "<br/>" +
                                "<b>" + data[i].content + "</b>" +
                                "</div>" +
                                "</div>";

                            ib_own.setContent(boxText);
                            ib_own.open(map, marker_bullet);
                        };
                    })(marker_bullet, i));

                    google.maps.event.addListener(marker_bullet, 'mouseout', (function(marker_bullet, i)
                    {
                        return function()
                        {
                            ib_own.close();
                            $('.elementList[data-id="' + data[i].id + '"]').removeClass("markerActive");
                        };
                    })(marker_bullet, i));
                    google.maps.event.addListener(marker_bullet, 'click', (function(marker_bullet, i)
                    {
                        return function()
                        {
                            var url = data[i].url;
                            window.open(url, '_blank');
                            return false;
                        };
                    })(marker_bullet, i));
                    markers.push(marker_bullet);
                }
                var mcOptions = {
                    gridSize: 50,
                    maxZoom: 15,
                    averageCenter:true
                };
                var markerCluster = new MarkerClusterer(map, markers, mcOptions);
                map.setCenter( latlng_pos);
                $('.elementList').mouseover(function() {
                    showMarkerInfoGeneric($(this),map,markers);
                });

                $('.elementList').mouseout(function() {
                    hideMarkerInfoGeneric($(this),map,markers);
                });
            }
        });
    }
}

function getCenterPosition(tempdata){
    var latitudearray = [];
    var longitudearray = [];
    var i;
    for(i=0; i<tempdata.length;i++){
        latitudearray.push(tempdata[i].position.lat());
        longitudearray.push(tempdata[i].position.lng());
    }
    latitudearray.sort(function (a, b) { return a-b; });
    longitudearray.sort(function (a, b) { return a-b; });
    var latdifferenece = latitudearray[latitudearray.length-1] - latitudearray[0];
    var temp = (latdifferenece / 2).toFixed(4) ;
    var latitudeMid = parseFloat(latitudearray[0]) + parseFloat(temp);
    var longidifferenece = longitudearray[longitudearray.length-1] - longitudearray[0];
    temp = (longidifferenece / 2).toFixed(4) ;
    var longitudeMid = parseFloat(longitudearray[0]) + parseFloat(temp);
    return new Object({latitudeMid:latitudeMid,longitudeMid:longitudeMid});
}

function showMarkerInfoGeneric(div_element,map,markers)
{
    var id_marker = div_element.attr('data-id');
    $('.elementList[data-id="' + id_marker + '"]').addClass("markerActive");
    for (var i = 0; i < markers.length; i++) {
        if (map.getBounds().contains(markers[i].getPosition()) && markers[i].get("id") == id_marker) {
            google.maps.event.trigger(markers[i], "mouseover");
            map.setCenter(markers[i].getPosition());
            map.setZoom(15);
            break;
        }
    }
}

function hideMarkerInfoGeneric(div_element,map,markers)
{
    var id_marker = div_element.attr('data-id');
    $('.elementList[data-id="' + id_marker + '"]').removeClass("markerActive");

    for (var i = 0; i < markers.length; i++) {
        if (map.getBounds().contains(markers[i].getPosition()) && markers[i].get("id") == id_marker) {
            google.maps.event.trigger(markers[i], "mouseout");
            map.setZoom(10);
            break;
        }
    }
}
