
var MapV2 = function () {

    var map_big;
    var markers_big;
    var initialize_map = function() {
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
    var getCenterPosition = function(tempdata){
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
    var showMarkerInfo = function(div_element)
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

    var hideMarkerInfo = function(div_element)
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
    var showMarkerInfoGeneric = function(div_element,map,markers)
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

    var hideMarkerInfoGeneric = function(div_element,map,markers)
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
    var showOwnershipsByIds = function(ids_array)
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

    var generateTriggerCallback = function(object, eventType) {
        return function() {
            google.maps.event.trigger(object, eventType);
        };
    }

    return {
        init: function () {
            initialize_map();
        }
    }
}();
MapV2.init();

