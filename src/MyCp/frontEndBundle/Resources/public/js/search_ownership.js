$(document).ready(start);

$('a.option').click(function() {
    var parent = $(this).attr('data');
    if (parent === "select_change_order")
        change_order($(this).attr("data-value"));
});

function start() {
    $('#change_view_to_list').click(change_view_to_list);
    $('#change_view_to_photo').click(change_view_to_photo);
    $('#change_view_to_map').click(change_view_to_map);


    //Buscador que esta encima de los filtros
    $('#button_research').click(research);

    //Filtros
    $('#own_reservation_type').change(filter_by_others);
    $('input[name=own_category]').change(filter_by_others);
    $('input[name=own_type]').change(filter_by_others);
    $('input[name=own_price]').change(filter_by_others);
    $('input[name=room_total]').change(filter_by_others);
    $('input[name=room_type]').change(filter_by_others);
    $('input[name=room_bathroom]').change(filter_by_others);
    $('#room_airconditioner').change(filter_by_others);
    $('#room_audiovisuals').change(filter_by_others);
    $('#room_kids').change(filter_by_others);
    $('#room_smoker').change(filter_by_others);
    $('#room_safe').change(filter_by_others);
    $('#room_balcony').change(filter_by_others);
    $('#room_terraza').change(filter_by_others);
    $('#room_courtyard').change(filter_by_others);

    $('input[name=room_beds_total]').change(filter_by_others);
    $('input[name=room_windows_total]').change(filter_by_others);
    $('input[name=others_languages]').change(filter_by_others);
    $('input[name=others_included]').change(filter_by_others);
    $('input[name=others_not_included]').change(filter_by_others);
    $('#room_others_pets').change(filter_by_others);
    $('#room_others_internet').change(filter_by_others);

    initialize_map();
}

function change_order(order)
{
    //Obtener parametros
    // var order=$('#select_change_order').val();
    show_loading();
    var result = $('#div_result');
    var url = $('#select_change_order').attr('data-url');

    $.post(url, {
        'order': order
    }, function(data) {
        result.html(data);
        manage_favorities(".favorite_off");
        manage_favorities(".favorite_on");
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
        $('#map').css('visibility', 'hidden');
        $('#map').css('display', 'none');

        manage_favorities(".favorite_off");
        manage_favorities(".favorite_on");

        if (viewType === 'MAP')
            initialize_map();
        hide_loading();
    });

    $('a.link').each(function(i, item) {
        $(item).css('background-position', 'top');
    });
    $(event.target).css('background-position', 'bottom');

    return false;
}

function research()
{
    show_loading();
    var url = $('#button_research').attr('data-url');

    var arrival = $('#input_arrival_date').val();
    var departure = $('#input_departure_date').val();
    var guests = $('#input_guests').attr("data-value");
    var rooms = $('#input_rooms').attr("data-value");
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
        manage_favorities(".favorite_off");
        manage_favorities(".favorite_on");
        filter_by_others();
        initialize_map();
        hide_loading();

    });

    return false;
}

function filter_by_others()
{
    //var own_reservation_type= $("#own_reservation_type").val();
    var own_type = [];
    var own_category = [];
    var own_price = [];
    var own_price_from = [];
    var own_price_to = [];
    var room_total = [];
    var room_type = [];
    var room_bathroom = [];
    var room_beds_total = [];
    var room_windows_total = [];
    var others_languages = [];
    var others_included = [];
    var others_not_included = [];

    $('input[name=own_type]:checked').each(function() {
        own_type.push($(this).val());
    });

    $('input[name=own_category]:checked').each(function() {
        own_category.push($(this).val());
    });

    $('input[name=own_price]:checked').each(function() {
        own_price.push($(this).val());
        own_price_from.push($(this).val());
        own_price_to.push(parseInt($(this).val()) + 25);
    });

    $('input[name=room_total]:checked').each(function() {
        room_total.push($(this).val());
    });

    $('input[name=room_type]:checked').each(function() {
        room_type.push($(this).val());
    });

    $('input[name=room_bathroom]:checked').each(function() {
        room_bathroom.push($(this).val());
    });


    var room_climatization = '';
    room_climatization = room_climatization + ((document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked) ? "'" + $('#room_airconditioner').attr('data-value') + "'" : "");

    var room_audiovisuals = document.getElementById('room_audiovisuals') != null && document.getElementById('room_audiovisuals').checked;
    var room_kids = document.getElementById('room_kids') != null && document.getElementById('room_kids').checked;
    var room_smoker = document.getElementById('room_smoker') != null && document.getElementById('room_smoker').checked;
    var room_safe = document.getElementById('room_safe') != null && document.getElementById('room_safe').checked;
    var room_balcony = document.getElementById('room_balcony') != null && document.getElementById('room_balcony').checked;
    var room_terraza = document.getElementById('room_terraza') != null && document.getElementById('room_terraza').checked;
    var room_courtyard = document.getElementById('room_courtyard') != null && document.getElementById('room_courtyard').checked;

    $('input[name=room_beds_total]:checked').each(function() {
        room_beds_total.push($(this).val());
    });

    $('input[name=room_windows_total]:checked').each(function() {
        room_windows_total.push($(this).val());
    });

    $('input[name=others_languages]:checked').each(function() {
        others_languages.push($(this).val());
    });

    $('input[name=others_included]:checked').each(function() {
        others_included.push($(this).val());
    });

    $('input[name=others_not_included]:checked').each(function() {
        others_not_included.push($(this).val());
    });

    var room_others_pets = document.getElementById('room_others_pets') != null && document.getElementById('room_others_pets').checked;
    var room_others_internet = document.getElementById('room_others_internet') != null && document.getElementById('room_others_internet').checked;

    show_loading();
    var url = $('#filters').attr('data-url-filter');
    var result = $('#div_result');
    var checked_filters = {
        //"own_reservation_type": (own_reservation_type != null && own_reservation_type != "" && own_reservation_type != "-1" && own_reservation_type != -1) ? own_reservation_type : null,
        "own_category": (own_category.length > 0) ? own_category : null,
        "own_type": (own_type.length > 0) ? own_type : null,
        "own_price": (own_price.length > 0) ? own_price : null,
        "own_price_from": (own_price_from.length > 0) ? own_price_from : null,
        "own_price_to": (own_price_to.length > 0) ? own_price_to : null,
        "own_rooms_number": (room_total.length > 0) ? room_total : null,
        "room_type": (room_type.length > 0) ? room_type : null,
        "own_beds_total": (room_beds_total.length > 0) ? room_beds_total : null,
        "room_bathroom": (room_bathroom.length > 0) ? room_bathroom : null,
        "room_windows_total": (room_windows_total.length > 0) ? room_windows_total : null,
        "room_climatization": room_climatization,
        "room_audiovisuals": room_audiovisuals,
        "room_kids": room_kids,
        "room_smoker": room_smoker,
        "room_safe": room_safe,
        "room_balcony": room_balcony,
        "room_terraza": room_terraza,
        "room_courtyard": room_courtyard,
        "own_others_languages": (others_languages.length > 0) ? others_languages : null,
        "own_others_included": (others_included.length > 0) ? others_included : null,
        "own_others_not_included": (others_not_included.length > 0) ? others_not_included : null,
        "own_others_pets": room_others_pets,
        "own_others_internet": room_others_internet
    };

    $.post(url, checked_filters, function(data) {
        result.html(data);
        manage_favorities(".favorite_off");
        manage_favorities(".favorite_on");
       // refresh_filters_statistics(checked_filters);
        hide_loading();
    });
    return false;
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
    $('#loading').removeClass('hidden');
}

function hide_loading()
{
    $('#loading').addClass('hidden');

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
