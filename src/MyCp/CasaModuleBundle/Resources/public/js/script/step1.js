/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
/**

 **/
var Step1 = function () {

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
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializeMap();
        }
    };
}();
Step1.init();





