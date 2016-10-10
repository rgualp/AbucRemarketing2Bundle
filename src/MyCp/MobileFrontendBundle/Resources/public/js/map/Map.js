/**
 Map script to handle the entire layout and base functions
 **/
var Map = function () {

    var map_big;
    var markers_big;
    var ib;
    var icon_small = $("#big_map").attr('data-icon-small');
    var icon = $("#big_map").attr('data-icon');
    var markerClusterBig;
    /**
     * initialize Map
     */
    var initializeMap=function(zoom, latitud, longitud, data){
        if (Map.validMap()){
            var geocoder = new google.maps.Geocoder();
            var address = $('#address').val();
            if (document.getElementById("big_map") != null){
                var center_big = new google.maps.LatLng(latitud, longitud);//La Habana 23.09725, -82.37548
                var options_big = {
                    'zoom': zoom,
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
                    closeBoxURL: "https://www.google.com/intl/en_us/mapfiles/close.gif",
                    infoBoxClearance: new google.maps.Size(1, 1),
                    isHidden: false,
                    pane: "floatPane",
                    enableEventPropagation: false
                };
                ib = new InfoBox(myOptions);
                markers_big = [];
                var mcOptions = {
                    gridSize: 50,
                    maxZoom: 15
                };
                markerClusterBig = new MarkerClusterer(map_big, markers_big, mcOptions);

                if (data != null){
                    Map.createMarkerAndListenerEvent(data);
                }

            }
        }

    }

    return {
        //main function to initiate template pages
        init: function (zoom, latitud, longitud,data) {
            //IMPORTANT!!!: Do not modify the call order.
            initializeMap(zoom, latitud, longitud,data);
        },
        validMap:function(){
            if (!(typeof window.google === 'object' && window.google.maps)) {
                var console = window.console;
                if (console && console.log) {
                    console.log('Google Maps API is required connection.');
                }
                return false;
            }
            else
                return true;
        },
        createMarkerAndListenerEvent:function(data){
            if(Map.validMap()){
                for (var i = 0; i < data.length; i++) {
                    var latlng = new google.maps.LatLng(data[i].latitude, data[i].longitude);
                    var marker_bullet = new google.maps.Marker({
                        id: data[i].id,
                        map: map_big,
                        position: latlng,
                        title: data[i].title,
                        icon: icon_small,
                        content: "<tr><td class='map_image' style='background-image:url(" + data[i].image + ")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>" + data[i].title + "<br/><b>" + data[i].content + "</b></td></tr>",

                    });
                    Map.mouseoverAddEvent(data,marker_bullet,i);
                    Map.mouseoutAddEvent(data,marker_bullet,i);
                    markers_big.push(marker_bullet);
                }
            }
        },
        mouseoverAddEvent:function(data,marker_bullet,i){
            if(Map.validMap()){
                google.maps.event.addListener(marker_bullet, 'mouseover', (function(marker_bullet, i)
                {
                    return function()
                    {
                        var boxText = document.createElement("div");
                        boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #373737;color:#fff; padding: 5px; font-size:11px";
                        boxText.innerHTML = "<div class='row'>" +
                            "<div class='map_image col-sm-4' style='background-image:url(" + data[i].image + ");height: 60px !important; margin: 0 10px;width: 65px !important;'></div>" +
                            "<div class='col-sm-8' style='line-height:12px;text-align: left'>" + data[i].title + "<br/>" +
                            "<b>" + data[i].content + "</b>" +
                            "</div>" +
                            "</div>";

                        ib.setContent(boxText);
                        ib.open(map_big, marker_bullet);

                        $('.elementList[data-id="' + data[i].id + '"]').addClass("markerActive");
                    };
                })(marker_bullet, i));
            }
        },
        mouseoutAddEvent:function(data,marker_bullet,i){
            if(Map.validMap()){
                google.maps.event.addListener(marker_bullet, 'mouseout', (function(marker_bullet, i)
                {
                    return function()
                    {
                        ib.close();
                        $('.elementList[data-id="' + data[i].id + '"]').removeClass("markerActive");
                    };
                })(marker_bullet, i));
            }

        },
        generateTriggerCallback:function(object, eventType){
            if(Map.validMap()){
                return function() {
                    google.maps.event.trigger(object, eventType);
                };
            }
        },
        removeMarkers:function(){
            if(Map.validMap()){
                for(var i=0; i<markers_big.length; i++)
                    markers_big[i].setMap(null);
            }

        },
        setCenter:function (lat,long) {
            if(Map.validMap()){
                map_big.setZoom(15);
                map_big.setCenter(new google.maps.LatLng(lat, long));
            }
        }

    };
}();
Map.init(7,22.01300, -79.26635,null);