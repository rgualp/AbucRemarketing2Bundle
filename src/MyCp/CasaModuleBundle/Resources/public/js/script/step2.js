/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var Step2 = function () {

    /**
     * Para inicializar el Mapa
     */
    var initializeMap=function(){
        var options = {
            map: "#map_canvas",
            markerOptions: {
                draggable: true
            },
            location: '{{ ownership.ownAddressProvince }}, Cuba'
        };
        var geomap= $("#mycp_mycpbundle_ownership_step1_geolocate").geocomplete(options);
    }
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





