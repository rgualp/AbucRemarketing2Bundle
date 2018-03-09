/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step2 = function () {
    if ($('#mycp_mycpbundle_ownership_step1_own_langs').val() == '') {
        $('#mycp_mycpbundle_ownership_step1_own_langs').val(0);
    }
    ;
    $('#mycp_mycpbundle_ownership_step1_own_langs1').select2(
        {
            placeholder: "Idiomas hablados en su casa",
            width: 'element',
            allowClear: true
        }
    );
    if (typeof langs != 'undefined')
        $('#mycp_mycpbundle_ownership_step1_own_langs1').select2('val', langs);
    $('#mycp_mycpbundle_ownership_step1_own_langs1').on("select2:select", function (e) {
        //console.log("select2:select", e.params.data.id);
        var value = $('#mycp_mycpbundle_ownership_step1_own_langs').val();
        value = parseInt(value) + parseInt(e.params.data.id);
        $('#mycp_mycpbundle_ownership_step1_own_langs').val(value);
    });
    $('#mycp_mycpbundle_ownership_step1_own_langs1').on("select2:unselect", function (e) {
        //console.log("select2:select", e.params.data.id);
        var value = $('#mycp_mycpbundle_ownership_step1_own_langs').val();
        value = parseInt(value) - parseInt(e.params.data.id);
        $('#mycp_mycpbundle_ownership_step1_own_langs').val(value);
    });
    /**
     * Para inicializar el Mapa
     */
    var initializeMap = function () {
        var options = {
            map: "#map_canvas",
            markerOptions: {
                draggable: true,
                icon: (typeof iconUrl !== 'undefined') ? iconUrl : ''
            },
            location: (typeof lat == 'undefined') ? (typeof province !== 'undefined') ? province : '' + ', Cuba' : new google.maps.LatLng(lat, lng)
        };
        if (typeof lat !== 'undefined') {
            options['mapOptions'] = {
                center: new google.maps.LatLng(lat, lng)
            };

        }
        var geomap = $("#mycp_mycpbundle_ownership_step1_geolocate").geocomplete(options).bind("geocode:dragged", function (event, latLng) {
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_x').val(latLng.lat());
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_y').val(latLng.lng());

        });

        geomap.bind("geocode:result", function (event, result) {
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_x').val(result.geometry.location.lat());
            $('#mycp_mycpbundle_ownership_step1_own_geolocate_y').val(result.geometry.location.lng());

        });
    }

    var saveStep2 = function (index) {
        var $OWNERSHIP_STEP1 = $("#mycp_mycpbundle_ownership_step1");
        if (index == 2) {
            $OWNERSHIP_STEP1.validate();
            var _url = $OWNERSHIP_STEP1.attr('action');
            var values = $OWNERSHIP_STEP1.serialize();
            HoldOn.open();
            var $envio = $.ajax({
                url: _url,
                data: values,
                type: 'POST'
            });
            $envio.error(function (data) {
                toastr.error('Ha ocurrido un error');
            });
            $envio.success(function (data) {
                if (data.success) {
                    toastr.info("Datos guardados satisfactoriamente.");
                } else {
                    toastr.error('Ha ocurrido un error');
                }
            });
            HoldOn.close();
        }
    };

    var onclickBtnSaveCasa = function () {
        $('#saveStepCasa').on('click', function () {
            Step2.saveStep2(2);
        });
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            try {
                initializeMap();
            } catch (ex) {
                console.log("Error initialized map " + ex.message);
            }

            var event = App.getEvent();
            event.clickBtnContinueAfter.add(saveStep2, this);
            onclickBtnSaveCasa();
        }
        ,
        saveStep2: saveStep2
    };
}();
//Start step2
Step2.init();





