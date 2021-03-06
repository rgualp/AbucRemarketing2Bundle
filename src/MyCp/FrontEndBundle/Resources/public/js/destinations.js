$(document).ready(startD);

function startD() {
    // $('#destinations-slider').royalSlider({
    //     arrowsNav: true,
    //     loop: true,
    //     keyboardNavEnabled: true,
    //     imageScaleMode: 'fill',
    //     arrowsNavAutoHide: false,
    //     autoScaleSlider: true,
    //     autoScaleSliderWidth: 960,
    //     autoScaleSliderHeight: 350,
    //     controlNavigation: 'bullets',
    //     numImagesToPreload: 0,
    //     startSlideId: 0,
    //     autoPlay: true,
    //     transitionType: 'move',
    //     deeplinking: {
    //         enabled: true,
    //         change: false
    //     }
    // });
    
    // initialize_map();
    // $('#change_view_to_list').click(change_view_to_list);
    // $('#change_view_to_photo').click(change_view_to_photo);
    // $('#change_view_to_map').click(change_view_to_map);
    //
    // $('.details_items_per_page').click(function()
    // {
    //    $('.blue_nav_tools li.active').removeClass('active');
    //    $(this).addClass('active');
    //    var show_rows = $(this).attr('data-content-value');
    //    $('#items_per_page').html(show_rows);
    //
    //    visualize_rows(show_rows);
    // });
    
    //$('.top_rated_tools .paginator-cont a').click(do_paginate);
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
    var result = $('#near_houses_container');

    $.post(url, {
        'view': viewType
    }, function(data) {
        result.html(data);
        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");

        if (viewType === 'MAP')
            initialize_map();
        hide_loading();
    });
    return false;
}


var map_big;
var markers_big;
function initialize_map()
{
    var json_url = $("#json_source").attr('data-url');
    var icon_small = $("#json_source").attr('data-icon-small');
    var icon = $("#json_source").attr('data-icon');
        
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
            closeBoxURL: "https://www.google.com/intl/en_us/mapfiles/close.gif",
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

function visualize_rows(show_rows)
{
    var url = $("#nav_blue_placeholder").attr("data-url");
    var result = $("#nav_blue_placeholder");
    
    //show_loading();
    $.post(url,{
            'show_rows':show_rows
        },function(data){
            result.html(data);
            //hide_loading();
            start();
        });
}

function do_paginate()
{
     var url = $("#nav_blue_placeholder").attr("data-url");
    var result = $("#nav_blue_placeholder");
    
    //show_loading();
    $.post(url,null,function(data){
            result.html(data);
            //hide_loading();
            start();
        });
}

function show_loading()
{
    $('#loading').removeClass('hidden');
}

function hide_loading()
{
    $('#loading').addClass('hidden');

}
