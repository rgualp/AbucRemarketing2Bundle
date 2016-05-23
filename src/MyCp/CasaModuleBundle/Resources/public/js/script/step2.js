/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step2 = function () {
    $('#mycp_mycpbundle_ownership_step1_own_langs1').select2(
        {
            placeholder: "Seleccione",
            allowClear: true
        }
    );
    /**
     * Para inicializar el Mapa
     */
    var initializeMap=function(){

        //var strictBounds = new google.maps.LatLngBounds(
        //    new google.maps.LatLng(28.70, -127.50),
        //    new google.maps.LatLng(48.85, -55.90)
        //);
        var options = {
            map: "#map_canvas",
            markerOptions: {
                draggable: true,
                icon: iconUrl
            },
            location: province+', Cuba'
            //,
            //bounds: strictBounds
        };

        var geomap= $("#mycp_mycpbundle_ownership_step1_geolocate").geocomplete(options);
    }

    //$('#mycp_mycpbundle_ownership_step1_own_langs1').on('change', function(){
    //    var txt = $("#mycp_mycpbundle_ownership_step1_own_langs1 option:selected").text();
    //    var val = $("#mycp_mycpbundle_ownership_step1_own_langs1 option:selected").val();
    //  alert(txt);
    //  alert(val);
    //});

    var saveStep2=function(){
        alert('Save form 2');
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializeMap();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep2,this);
        }
    };
}();
//Start step2
Step2.init();





