$(document).ready(start);


function start() {
    $('#select_change_order').change(change_order);
    $('#change_view_to_list').click(change_view_to_list);
    $('#change_view_to_photo').click(change_view_to_photo);
    $('#change_view_to_map').click(change_view_to_map);

    load_upper_filters();

    //Buscador que esta encima de los filtros
    $('#button_research').click(research);

    //Filtros
    $('.action_remove_filter_up').change(function() {
        remove_filter_up($(this));
    });
    $('#own_reservation_type').change(function() {
        filter_by_others(false);
    });
    $('input[name=own_category]').change(function() {
        filter_by_others(false);
    });
    $('input[name=own_type]').change(function() {
        filter_by_others(false);
    });
    $('input[name=own_price]').change(function() {
        filter_by_others(false);
    });
    $('input[name=room_total]').change(function() {
        filter_by_others(false);
    });
    $('input[name=room_type]').change(function() {
        filter_by_others(false);
    });
    $('input[name=room_bathroom]').change(function() {
        filter_by_others(false);
    });
    $('#room_airconditioner').change(function() {
        filter_by_others(false);
    });
    $('#room_audiovisuals').change(function() {
        filter_by_others(false);
    });
    $('#room_kids').change(function() {
        filter_by_others(false);
    });
    $('#room_smoker').change(function() {
        filter_by_others(false);
    });
    $('#room_safe').change(function() {
        filter_by_others(false);
    });
    $('#room_balcony').change(function() {
        filter_by_others(false);
    });
    $('#room_terraza').change(function() {
        filter_by_others(false);
    });
    $('#room_courtyard').change(function() {
        filter_by_others(false);
    });

    $('input[name=room_beds_total]').change(function() {
        filter_by_others(false);
    });
    $('input[name=room_windows_total]').change(function() {
        filter_by_others(false);
    });
    $('input[name=others_languages]').change(function() {
        filter_by_others(false);
    });
    $('input[name=others_included]').change(function() {
        filter_by_others(false);
    });
    $('input[name=others_not_included]').change(function() {
        filter_by_others(false);
    });
    $('#room_others_pets').change(function() {
        filter_by_others(false);
    });
    $('#room_others_internet').change(function() {
        filter_by_others(false);
    });

    initialize_map();
}

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

function research()
{
    show_loading();
    var url = $('#button_research').attr('data-url');

    var arrival = $('#input_arrival_date').val();
    var departure = $('#input_departure_date').val();
    var guests = $('#input_guests').val();
    var rooms = $('#input_room').val();
    var text = $('#input_text').val();

    var result = $('#div_result');

    arrival = (arrival != $('#input_arrival_date').attr('placeholder')) ? create_date(arrival) : null;
    departure = (departure != $('#input_departure_date').attr('placeholder')) ? create_date(departure) : null;
    text = (text != $('#input_text').attr('placeholder')) ? text : null;

    $.post(url, {
        'arrival': arrival,
        'departure': departure,
        'guests': guests,
        'rooms': rooms,
        'text': text
    }, function(data) {
        result.html(data);
        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");

        $(".filter_upper_item").each(function() {
            var control_id = $(this).attr("data-control-id");
            var control_name = $(this).attr("data-control-name");
            var item_value = $(this).attr("data-value");

            if (control_id !== "")
                $(this).getElementById(control_id).checked = false;

            if (control_name != "" && item_value != "")
            {
                $('input[name=' + control_name + ']').each(function() {
                    if ($(this).val() == item_value)
                        $(this).removeAttr("checked");
                });
            }
            $(this).remove();
            hide_loading();
        });
        filter_by_others(true);
        initialize_map();
    });

    return false;
}

function load_upper_filters()
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
    var others_not_included_items = [];

    var innerHtml = $("#filter_upper").html();

    $('input[name=own_type]:checked').each(function() {
        own_type_items.push($(this).val());
        if (document.getElementById("fu_own_type_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }

    });

    $('input[name=own_category]:checked').each(function() {
        own_category_items.push($(this).val());
        if (document.getElementById("fu_own_category_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=own_price]:checked').each(function() {
        own_price_items.push($(this).val());
        own_price_from_items.push($(this).val());
        own_price_to_items.push(parseInt($(this).val()) + 25);

        if (document.getElementById("fu_own_price_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_price'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=room_total]:checked').each(function() {
        room_total_items.push($(this).val());

        if (document.getElementById("fu_room_total_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_total_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_total'><i class='icon-remove-sign'></i>" + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=room_type]:checked').each(function() {
        room_type_items.push($(this).val());
        if (document.getElementById("fu_room_type_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=room_bathroom]:checked').each(function() {
        room_bathroom_items.push($(this).val());
        if (document.getElementById("fu_room_bathroom_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_bathroom_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_bathroom'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }
    });


    var room_climatization = '';
    room_climatization = room_climatization + ((document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked) ? "'" + $('#room_airconditioner').attr('data-value') + "'" : "");

    if (document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked && document.getElementById("fu_room_airconditioner") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_airconditioner' data-control-id='room_airconditioner' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_airconditioner").parent().text() + "</a> ");
    }

    var room_audiovisuals = document.getElementById('room_audiovisuals') != null && document.getElementById('room_audiovisuals').checked;
    if (room_audiovisuals && document.getElementById("fu_room_audiovisuals") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_audiovisuals' data-control-id='room_audiovisuals' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_audiovisuals").parent().text() + "</a> ");
    }

    var room_kids = document.getElementById('room_kids') != null && document.getElementById('room_kids').checked;
    if (room_kids && document.getElementById("fu_room_kids") == null)
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_kids' data-control-id='room_kids' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_kids").parent().text() + "</a> ");

    var room_smoker = document.getElementById('room_smoker') != null && document.getElementById('room_smoker').checked;
    if (room_smoker && document.getElementById("fu_room_smoker") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_smoker' data-control-id='room_smoker' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_smoker").parent().text() + "</a> ");
    }

    var room_safe = document.getElementById('room_safe') != null && document.getElementById('room_safe').checked;
    if (room_safe && document.getElementById("fu_room_safe") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_safe' data-control-id='room_safe' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_safe").parent().text() + "</a> ");
    }

    var room_balcony = document.getElementById('room_balcony') != null && document.getElementById('room_balcony').checked;
    if (room_balcony && document.getElementById("fu_room_balcony") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_balcony' data-control-id='room_balcony' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_balcony").parent().text() + "</a> ");
    }

    var room_terraza = document.getElementById('room_terraza') != null && document.getElementById('room_terraza').checked;
    if (room_terraza && document.getElementById("fu_room_terraza") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_terraza' data-control-id='room_terraza' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_terraza").parent().text() + "</a> ");
    }

    var room_courtyard = document.getElementById('room_courtyard') != null && document.getElementById('room_courtyard').checked;
    if (room_courtyard && document.getElementById("fu_room_courtyard") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_courtyard' data-control-id='room_courtyard' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_courtyard").parent().text() + "</a> ");
    }


    $('input[name=room_beds_total]:checked').each(function() {
        room_beds_total_items.push($(this).val());
        if (document.getElementById("fu_room_beds_total_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_beds_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_beds_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=room_windows_total]:checked').each(function() {
        room_windows_total_items.push($(this).val());
        if (document.getElementById("fu_room_windows_total_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_windows_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_windows_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=others_languages]:checked').each(function() {
        others_languages_items.push($(this).val());
        if (document.getElementById("fu_others_languages_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_languages_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_languages'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=others_included]:checked').each(function() {
        others_included_items.push($(this).val());
        if (document.getElementById("fu_others_included_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }
    });

    $('input[name=others_not_included]:checked').each(function() {
        others_not_included_items.push($(this).val());
        if (document.getElementById("fu_others_not_included_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_not_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_not_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }
    });

    var room_others_pets = document.getElementById('room_others_pets') != null && document.getElementById('room_others_pets').checked;
    if (room_others_pets && document.getElementById("fu_room_others_pets") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_pets' data-control-id='room_others_pets' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_pets").parent().text() + "</a> ");
    }

    var room_others_internet = document.getElementById('room_others_internet') != null && document.getElementById('room_others_internet').checked;
    if (room_others_internet && document.getElementById("fu_room_others_internet") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_internet' data-control-id='room_others_internet' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_internet").parent().text() + "</a> ");
    }

    $(".filter_upper_item").click(function() {
        filter_upper($(this));
    });

    if ($("#filter_upper").html() != "")
        $("#filter_upper").css("display", "block");

    var checked_filters = {
        //"own_reservation_type": (own_reservation_type != null && own_reservation_type != "" && own_reservation_type != "-1" && own_reservation_type != -1) ? own_reservation_type : null,
        "own_category": (own_category_items.length > 0) ? own_category_items : null,
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
        "own_others_internet": room_others_internet
    };
    return checked_filters;
}

function filter_by_others(refreshStatistics)
{
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
    var control_name = element.attr("data-control-name");
    var item_value = element.attr("data-value");

    if (control_id !== "")
        document.getElementById(control_id).checked = false;

    if (control_name != "" && item_value != "")
    {
        $('input[name=' + control_name + ']').each(function() {
            if ($(this).val() == item_value)
                $(this).removeAttr("checked");
        });
    }

    //alert($("#filter_upper").html());
    /*if ($("#filter_upper").html())
     $("#filter_upper").css("display", "none");*/

    filter_by_others(false);
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
function initialize_map() {
    var json_url = $("#json_source").attr('data-url');
    var icon_small = $("#json_source").attr('data-icon-small');
    //var icon = $("#json_source").attr('data-icon');         

    if (document.getElementById("map") != null)
    {
        var center = new google.maps.LatLng(22.01300, -79.26635);//La Habana 23.09725, -82.37548
        var options = {
            'zoom': 4,
            'center': center,
            'mapTypeId': google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("map"), options);

        var markers = [];
        $.getJSON(json_url, function(data) {
            if (data) {
                for (i = 0; i < data.length; i++) {
                    var latlng = new google.maps.LatLng(data[i].latitude, data[i].longitude);
                    var marker_bullet = new google.maps.Marker({
                        id: data[i].id,
                        map: map,
                        position: latlng,
                        title: data[i].title,
                        icon: icon_small

                    });
                    markers.push(marker_bullet);

                }

                var mcOptions = {
                    gridSize: 50,
                    maxZoom: 15
                };
                var markerCluster = new MarkerClusterer(map, markers, mcOptions);
            }
        });
    }

    if (document.getElementById("big_map") != null)
    {
        var center_big = new google.maps.LatLng(22.01300, -79.26635);//La Habana 23.09725, -82.37548
        var options_big = {
            'zoom': 6,
            'center': center_big,
            'mapTypeId': google.maps.MapTypeId.ROADMAP
        };

        map_big = new google.maps.Map(document.getElementById("big_map"), options_big);
        var myOptions = {
            //content: boxText
            //,
            disableAutoPan: false,
            maxWidth: 0,
            pixelOffset: new google.maps.Size(-140, 0),
            zIndex: null,
            boxStyle: {
                //background: "url('tipbox.gif') no-repeat",
                opacity: 0.85,
                width: "280px"
            },
            closeBoxMargin: "10px 2px 2px 2px",
            closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
            infoBoxClearance: new google.maps.Size(1, 1),
            isHidden: false,
            pane: "floatPane",
            enableEventPropagation: false
        };
        var ib = new InfoBox(myOptions);


        markers_big = [];
        $.getJSON(json_url, function(data) {
            if (data) {
                for (i = 0; i < data.length; i++) {
                    var latlng = new google.maps.LatLng(data[i].latitude, data[i].longitude);
                    var marker_bullet = new google.maps.Marker({
                        id: data[i].id,
                        map: map_big,
                        position: latlng,
                        //title:data[i].title,
                        content: "<tr><td class='map_image' style='background-image:url(" + data[i].image + ")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>" + data[i].title + "<br/><b>" + data[i].content + "</b></td></tr>",
                        icon: icon

                    });

                    google.maps.event.addListener(marker_bullet, 'mouseover', (function(marker_bullet, i)
                    {
                        return function()
                        {

                            var boxText = document.createElement("div");
                            boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px";
                            boxText.innerHTML = "<table><tr><td class='map_image' style='background-image:url(" + data[i].image + ")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>" + data[i].title + "<br/><b>" + data[i].content + "</b></td></tr></table>";

                            ib.setContent(boxText);
                            ib.open(map_big, marker_bullet);

                            $('.elementList[data-id="' + data[i].id + '"]').addClass("markerActive");
                        };
                    })(marker_bullet, i));

                    google.maps.event.addListener(marker_bullet, 'mouseout', (function(marker_bullet, i)
                    {
                        return function()
                        {
                            ib.close();
                            $('.elementList[data-id="' + data[i].id + '"]').removeClass("markerActive");
                        };
                    })(marker_bullet, i));

                    $("#own_" + data[i].id).onclick = generateTriggerCallback(marker_bullet, "mouseover");
                    markers_big.push(marker_bullet);
                }

                var mcOptions = {
                    gridSize: 50,
                    maxZoom: 15
                };
                var markerClusterBig = new MarkerClusterer(map_big, markers_big, mcOptions);

                //Evento click en un cluster - Hace q se actualice el listado
                google.maps.event.addListener(markerClusterBig, 'click',
                        function(cluster) {
                            ib.close();
                            var markers = cluster.getMarkers();

                            var markers_in_bound = [];
                            for (var i = 0; i < markers.length; i++) {
                                if (map_big.getBounds().contains(markers[i].getPosition())) {
                                    markers_in_bound.push(markers[i].get("id"));
                                }
                            }
                            showOwnershipsByIds(markers_in_bound);
                        });

                //Evento mouseover en el cluster
                google.maps.event.addListener(markerClusterBig, 'mouseover',
                        function(cluster) {
                            var markers = cluster.getMarkers();

                            var boxText = document.createElement("div");
                            boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px;";
                            var boxContent = "<table>";

                            for (var i = 0; i < markers.length; i++) {
                                if (map_big.getBounds().contains(markers[i].getPosition())) {
                                    $('.elementList[data-id="' + markers[i].get("id") + '"]').addClass("markerActive");
                                    boxContent += markers[i].get("content");
                                    boxContent += "<tr><td colspan='2'><hr/></td></tr>";
                                }
                            }
                            var total_translate = $("#big_map").attr("data-total-translate");
                            boxContent += "<tr><td colspan='2' style='text-align:right'>" + total_translate + ": <b>" + cluster.getSize() + "<b/></td></tr>";
                            boxContent += "</table>";
                            boxText.innerHTML = boxContent;
                            ib.setContent(boxText);
                            ib.setPosition(cluster.getCenter());
                            ib.open(map_big);
                        });

                //Evento mouseout en el cluster
                google.maps.event.addListener(markerClusterBig, 'mouseout',
                        function(cluster) {
                            var markers = cluster.getMarkers();
                            for (var i = 0; i < markers.length; i++) {
                                if (map_big.getBounds().contains(markers[i].getPosition())) {
                                    $('.elementList[data-id="' + markers[i].get("id") + '"]').removeClass("markerActive");
                                    ib.close();
                                }
                            }
                        });
            }
        });

        //Evento zoom_changed en el mapa general - Hace q se actualice el listado
        google.maps.event.addListener(map_big, 'zoom_changed', function() {
            var markers_in_bound = [];
            for (var i = 0; i < markers_big.length; i++) {
                if (map_big.getBounds().contains(markers_big[i].getPosition())) {
                    markers_in_bound.push(markers_big[i].get("id"));
                }
            }
            showOwnershipsByIds(markers_in_bound);

        }); //close listener

        //vento dragend en el mapa general - Hace q se actualice el listado
        google.maps.event.addListener(map_big, 'dragend', function() {
            var markers_in_bound = [];
            for (var i = 0; i < markers_big.length; i++) {
                if (map_big.getBounds().contains(markers_big[i].getPosition())) {
                    markers_in_bound.push(markers_big[i].get("id"));
                }
            }
            showOwnershipsByIds(markers_in_bound);
        }); //close listener

        $('.elementList').mouseover(function() {
            showMarkerInfo($(this));
        });

        $('.elementList').mouseout(function() {
            hideMarkerInfo($(this));
        });

    }
}

function showMarkerInfo(div_element)
{
    var id_marker = div_element.attr('data-id');
    $('.elementList[data-id="' + id_marker + '"]').addClass("markerActive");
    for (var i = 0; i < markers_big.length; i++) {
        if (map_big.getBounds().contains(markers_big[i].getPosition()) && markers_big[i].get("id") == id_marker) {
            google.maps.event.trigger(markers_big[i], "mouseover");
            break;
        }
    }

}

function hideMarkerInfo(div_element)
{
    var id_marker = div_element.attr('data-id');
    $('.elementList[data-id="' + id_marker + '"]').removeClass("markerActive");

    for (var i = 0; i < markers_big.length; i++) {
        if (map_big.getBounds().contains(markers_big[i].getPosition()) && markers_big[i].get("id") == id_marker) {
            google.maps.event.trigger(markers_big[i], "mouseout");
            break;
        }
    }

}

function showOwnershipsByIds(ids_array)
{
    var url = $('#big_map').attr('data-resized-url');
    var result = $('#map_resized_results');
    show_loading();
    $.post(url,
            {
                'own_ids': ids_array
            }
    , function(data) {
        result.html(data);
        $('.elementList').mouseover(function() {
            showMarkerInfo($(this));
        });

        $('.elementList').mouseout(function() {
            hideMarkerInfo($(this));
        });
        hide_loading();
    });
}

function generateTriggerCallback(object, eventType) {
    return function() {
        google.maps.event.trigger(object, eventType);
    };
}

function checkCheckBoxes(array_of_values, name_checkboxes)
{
    if (array_of_values != null && array_of_values.length > 0)
    {
        for (var i = 0; i < array_of_values.length; i++)
        {
            for (var j = 0; j < document.getElementsByName(name_checkboxes).length; j++)
                document.getElementsByName(name_checkboxes)[j].checked = (document.getElementsByName(name_checkboxes)[j].value == array_of_values[i]);
        }
    }
}

function show_loading()
{
    $('.loading').removeClass('hidden');
}

function hide_loading()
{
    $('.loading').addClass('hidden');

}

function create_date(date_text) {
    var date = date_text.split('/');
    if (date.length == 3)
    {
        var date_result = parseInt(date[2], 10) + '-' + (parseInt(date[1], 10)) + '-' + parseInt(date[0], 10);
        return date_result;
    }
    return null;

}
