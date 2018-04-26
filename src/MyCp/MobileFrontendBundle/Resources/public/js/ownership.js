$(document).ready(start);

function start() {
    $('#select_change_order').change(change_order);
    $('#change_view_to_list').click(change_view_to_list);
    $('#change_view_to_photo').click(change_view_to_photo);
    $('#change_view_to_map').click(change_view_to_map);

    $('#btn_insert_comment').click(insert_comment);

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

function search() {
    var url = $('#btn_search').attr('data-url');

    var text = $('#input_text').val().toString();
    text = text.replace("-", "--");
    text = text.replace(/ /g, "-");
    text = text.replace("ñ", "_nn_");
    text = text.replace("Ñ", "_nn_");
    text = text.replace("á", "a");
    text = text.replace("é", "e");
    text = text.replace("í", "i");
    text = text.replace("ó", "o");
    text = text.replace("ú", "u");
    text = text.replace("ü", "u");
    text = text.toLowerCase();
    text = text.replace("_nn_", "ñ");
    if (text != $('#input_text').attr("placeholder") && text != "")
        url = url.toString().replace('_text', text);
    else
        url = url.toString().replace('_text', null);

    var arrival = $('#input_arrival_date').val();
    if (arrival != $('#input_arrival_date').attr("placeholder") && arrival != "")
        url = url.toString().replace('_arrival', create_dateDMY(arrival));
    else
        url = url.toString().replace('_arrival', null);

    var departure = $('#input_departure_date').val();
    if (departure != $('#input_departure_date').attr("placeholder") && departure != "")
        url = url.toString().replace('_departure', create_dateDMY(departure));
    else
        url = url.toString().replace('_departure', null);

    var guests = $('#input_guests').val();
    if (guests != $('#input_guests').attr("placeholder") && guests != "")
        url = url.toString().replace('_guests', guests);
    else
        url = url.toString().replace('_guests', '1');

    var rooms = $('#input_room').val();
    if (rooms != $('#input_room').attr("placeholder") && rooms != "")
        url = url.toString().replace('_rooms', rooms);
    else
        url = url.toString().replace('_rooms', '1');
    //'order_price':'_order_price', 'order_comments':'_order_comments', 'order_books':'_order_books'
    var order_price=$(input[type='radio', name='priceOrder']).val();
    if (order_price != "")
        url = url.toString().replace('_order_price', order_price);
    else
        url = url.toString().replace('_order_price', '');
    window.location = url;
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
        Mycp.manage_favorities(".favorite_off_action");
        Mycp.manage_favorities(".favorite_on_action");
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

        Mycp.manage_favorities(".favorite_off_action");
        Mycp.manage_favorities(".favorite_on_action");

        hide_loading();
    });

    /*$('a.link').each(function(i, item) {
     $(item).css('background-position', 'top');
     });
     $(event.target).css('background-position', 'bottom');
     */
    return false;
}

var map_big;
var markers_big;
function initialize_map() {
    var json_url = $("#json_source").attr('data-url');
    var icon_small = $("#json_source").attr('data-icon-small');
    var icon = $("#json_source").attr('data-icon');

    if (document.getElementById("map") != null)
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

        var map = new google.maps.Map(document.getElementById("map"), options);
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
                            boxText.innerHTML = "<div class='row'>" +
                                "<div class='map_image col-sm-4' style='background-image:url(" + data[i].image + ")'></div>" +
                                "<div class='col-sm-8' style='line-height:12px;text-align: left'>" + data[i].title + "<br/>" +
                                "<b>" + data[i].content + "</b>" +
                                "</div>" +
                                "</div>";

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

    if(document.getElementById("big_map_details") !== null)
    {
        var x = $("#big_map_details").attr("data-x");
        var y = $("#big_map_details").attr("data-y");
        var name = $("#big_map_details").attr("data-name");
        var description = $("#big_map_details").attr("data-description");
        var image = $("#big_map_details").attr("data-image");
        var icon = $("#big_map_details").attr("data-icon");

        var center_details = new google.maps.LatLng(x, y);//La Habana 23.09725, -82.37548
        var options_details = {
            zoom: 17,
            center: center_details,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var big_map_details = new google.maps.Map(document.getElementById("big_map_details"), options_details);

        var marker = new google.maps.Marker({
            position: center_details,
            map: big_map_details,
            title: name,
            icon: icon
        });



        var boxText = document.createElement("div");
        /*boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px";
        boxText.innerHTML = "<table><tr><td class='map_image' style='background-image:url("+image+")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>"+name+"<br/><b>" + description + "</b></td></tr></table>";*/

        boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px";
        boxText.innerHTML = "<table><tr><td class='map_image' style='background-image:url("+image+")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>"+name+"<br/><b>" + description + "</b></td></tr></table>";
        /*boxText.innerHTML = "<div class='row'>" +
            "<div class='map_image col-sm-4' style='background-image:url(" + image + ")'></div>" +
            "<div class='col-sm-8' style='line-height:12px;text-align: left'>" + name + "<br/>" +
            "<b>" + description + "</b>" +
            "</div>" +
            "</div>";
        */
        var myOptions = {
            content: boxText
            ,disableAutoPan: false
            ,maxWidth: 0
            ,pixelOffset: new google.maps.Size(-140, 0)
            ,zIndex: null
            ,boxStyle: {
                //background: "url('tipbox.gif') no-repeat",
                opacity: 0.85,
                width: "280px"
            }
            ,closeBoxMargin: "10px 2px 2px 2px"
            ,infoBoxClearance: new google.maps.Size(1, 1)
            ,isHidden: false
            ,pane: "floatPane"
            ,enableEventPropagation: false
        };

        var ib = new InfoBox(myOptions);

        google.maps.event.addListener(marker, 'mouseover', function() {
            ib.open(big_map_details, marker);
        });

        google.maps.event.addListener(marker, 'mouseout', function() {
            ib.close();
        });

        $("#mapTab").on('shown.bs.tab', function() {

  	/* Trigger map resize event */
        var center = big_map_details.getCenter();
	google.maps.event.trigger(big_map_details, 'resize');
        big_map_details.setCenter(center);
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

function insert_comment()
{
    show_element($('#loading'));
    hide_element($('#message_text'));

    var result = $('#user_comments');
    var url = $('#btn_insert_comment').attr('data-url');

    /*var user_name = $('#input_name').val();
     var user_email = $('#input_email').val();*/
    var comment = $('#input_comment').val();
    var rating = $('input[name=radio_rating]:checked').val();

    if (validate())
    {
        $.post(url, {
            'com_comments': comment,
            'com_rating': rating
        }, function(data) {
            result.html(data);
            clear_controls();
            hide_element($('#loading'));
            $('#message_text').html($('#message_text').attr('ok-message'));
            show_element($('#message_text'));

            refresh_rating();

        });
    }
    else
    {
        hide_element($('#loading'));
        show_element($('#message_text'));
    }

    return false;
}

function refresh_rating()
{
    var result = $('#ratings');
    var url = $('#ratings').attr('data-url');

    $.post(url, {
        'nothig': true
    }, function(data) {
        result.html(data);
    });

    return false;
}

function show_element(element)
{
    element.removeClass('hidden');
}

function hide_element(element)
{
    element.addClass('hidden');
}

function clear_controls()
{
    $('#input_name').val('');
    $('#input_email').val('');
    $('#input_comment').val('');
    $('input[name=radio_rating]').filter('[value=5]').attr('checked', 'checked');
}

function validate()
{
    /*var user_name = $('#input_name').val();
     var user_email = $('#input_email').val();*/
    var comment = $('#input_comment').val();
    var valid = true;
    var error_text = '';

    /*if(user_name == '')
     {
     valid = false;
     error_text += $('#input_name').attr("requiered-message") + ' <br/>';
     }

     if(user_email == '')
     {
     valid = false;
     error_text += $('#input_email').attr("requiered-message") +  ' <br/>';

     }
     else if(!valid_email(user_email))
     {
     valid = false;
     error_text += $('#input_email').attr("invalid-message") +  ' <br/>';
     */

    if (comment === '')
    {
        valid = false;
        error_text += $('#input_comment').attr("requiered-message") + ' <br/>';

    }

    if (error_text !== '')
    {
        $('#message_text').html(error_text);
    }


    return valid;
}

function valid_email(email_text){
    if( !(/\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(email_text)) ) {
        return false;
    }
    return true;
}
