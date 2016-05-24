/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step2 = function () {
   if($('#mycp_mycpbundle_ownership_step1_own_langs').val()==''){
       $('#mycp_mycpbundle_ownership_step1_own_langs').val(0);
   };
    $('#mycp_mycpbundle_ownership_step1_own_langs1').select2(
        {
            placeholder: "Seleccione",
            width: 'element',
            allowClear: true
        }
    );
    $('#mycp_mycpbundle_ownership_step1_own_langs1').on("select2:select", function (e) {
        //console.log("select2:select", e.params.data.id);
       var value= $('#mycp_mycpbundle_ownership_step1_own_langs').val();
        value=parseInt(value)+parseInt(e.params.data.id);
        $('#mycp_mycpbundle_ownership_step1_own_langs').val(value);
    });
    $('#mycp_mycpbundle_ownership_step1_own_langs1').on("select2:unselect", function (e) {
        //console.log("select2:select", e.params.data.id);
        var value= $('#mycp_mycpbundle_ownership_step1_own_langs').val();
        value=parseInt(value)-parseInt(e.params.data.id);
        $('#mycp_mycpbundle_ownership_step1_own_langs').val(value);
    });
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

        var geomap= $("#mycp_mycpbundle_ownership_step1_geolocate").geocomplete(options).bind("geocode:dragged", function(event, latLng){
            //console.log('Dragged Lat: '+latLng.lat());
            //console.log('Dragged Lng: '+latLng.lng());
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_x').val(latLng.lat());
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_y').val(latLng.lng());

        });
        geomap.bind("geocode:result", function(event, result) {
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_x').val(result.geometry.location.lat());
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_y').val(result.geometry.location.lng());
           //console.log( result.geometry.location.lat());
           //console.log( result.geometry.location.lng());

        });
    }

    //$('#mycp_mycpbundle_ownership_step1_own_langs1').on('change', function(){
    //    var txt = $("#mycp_mycpbundle_ownership_step1_own_langs1 option:selected").text();
    //    var val = $("#mycp_mycpbundle_ownership_step1_own_langs1 option:selected").val();
    //  alert(txt);
    //  alert(val);
    //});

    var saveStep2=function(){
      var _url=$('#mycp_mycpbundle_ownership_step1').attr('action');
        var values = $('#mycp_mycpbundle_ownership_step1').serialize();

        var $envio = $.ajax({
            url: _url,
            data: values,
            type: 'POST'
        });
        $envio.error(function(data){
            //
        });
        $envio.success(function(data){
    //
        });
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





