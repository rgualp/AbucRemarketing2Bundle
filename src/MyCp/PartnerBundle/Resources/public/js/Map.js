/**
 Map script to handle the entire layout and base functions
 **/
var Map = function () {

    var map_big;
    var markers_big;
    var ib;
    var icon_small = $("#big_map").attr('data-icon-small');
    var icon = $("#big_map").attr('data-icon');
    /**
     * initialize Map
     */
    var initializeMap=function(){

        var geocoder = new google.maps.Geocoder();
        var address = $('#address').val();
        // Hacemos la petición indicando la dirección e invocamos la función
        // geocodeResult enviando todo el resultado obtenido
        // geocoder.geocode({ 'address': address}, geocodeResult);
        if (document.getElementById("big_map") != null){
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
                closeBoxURL: "https://www.google.com/intl/en_us/mapfiles/close.gif",
                infoBoxClearance: new google.maps.Size(1, 1),
                isHidden: false,
                pane: "floatPane",
                enableEventPropagation: false
            };
            ib = new InfoBox(myOptions);
            markers_big = [];

            /* var mcOptions = {
             gridSize: 50,
             maxZoom: 15
             };
             var markerClusterBig = new MarkerClusterer(map_big, markers_big, mcOptions);*/
        }
    }

    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializeMap();
        },
        createMarkerAndListenerEvent:function(data){
            for (i = 0; i < data.length; i++) {
                var latlng = new google.maps.LatLng(data[i].latitude, data[i].longitude);
                var marker_bullet = new google.maps.Marker({
                    id: data[i].own_id,
                    map: map_big,
                    position: latlng,
                    content: "<tr><td class='map_image' style='background-image:url(" + data[i].photo + ")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>" + data[i].own_name + "<br/><b>" + data[i].prov_name + "</b></td></tr>",
                    icon: icon_small
                });
                Map.mouseoverAddEvent(data,marker_bullet,i);
                Map.mouseoutAddEvent(data,marker_bullet,i);
                $("#own_" + data[i].id).onclick = Map.generateTriggerCallback(marker_bullet, "mouseover");
                markers_big.push(marker_bullet);
            }
        },
        mouseoverAddEvent:function(data,marker_bullet,i){
            google.maps.event.addListener(marker_bullet, 'mouseover', (function(marker_bullet, i)
            {
                return function()
                {

                    var boxText = document.createElement("div");
                    boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #373737;color:#fff; padding: 5px; font-size:11px";
                    boxText.innerHTML = "<div class='row'>" +
                        "<div class='map_image col-sm-4' style='background-image:url(" + data[i].photo + ")'></div>" +
                        "<div class='col-sm-8' style='line-height:12px;text-align: left'>" + data[i].own_name + "<br/>" +
                        "<b>" + data[i].type + "</b> <br/>" +
                        "<b>" + data[i].type + "</b>" +
                        "</div>" +
                        "</div>";

                    ib.setContent(boxText);
                    ib.open(map_big, marker_bullet);

                    $('.elementList[data-id="' + data[i].id + '"]').addClass("markerActive");
                };
            })(marker_bullet, i));
        },
        mouseoutAddEvent:function(data,marker_bullet,i){
            google.maps.event.addListener(marker_bullet, 'mouseout', (function(marker_bullet, i)
            {
                return function()
                {
                    ib.close();
                    $('.elementList[data-id="' + data[i].id + '"]').removeClass("markerActive");
                };
            })(marker_bullet, i));
        },
        generateTriggerCallback:function(object, eventType){
            return function() {
                google.maps.event.trigger(object, eventType);
            };
        },
        removeMarkers:function(){
            for(var i=0; i<markers_big.length; i++)
                markers_big[i].setMap(null);
        }
    };
}();
Map.init();

