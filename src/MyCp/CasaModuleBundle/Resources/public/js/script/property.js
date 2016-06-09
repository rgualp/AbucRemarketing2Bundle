/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Property = function () {

    /**
     * Funcion para cuando se da click en el boton eliminar propiedad
     */
    var onclickBtnDeleteProperty=function(){
        $('#delete-property').on('click',function(){
            swal({
                title: "¿Estás seguro?",
                text: "¿Está seguro que desea eliminar la propiedad?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e94b3d",
                cancelButtonColor: "#64a433",
                confirmButtonText: "Sí",
                cancelButtonText: "No",
                closeOnConfirm: true
            }, function () {
                $('#myModalLogin').modal('show');
            });
        });
    }

    /**
     * Funcion para cuando se da click en el boton activar una propiedead
     */
    var onclickBtnActivateProperty=function(){
        $('#activate-property').on('click',function(){
            var url=$(this).data('href');
            HoldOn.open();
            $.ajax({
                type: 'post',
                url: url,
                data:  {active:true},
                success: function (data) {
                    HoldOn.close();
                    if(data.success){
                         $('#activate-property').addClass('hide');
                         $('#deactive-property').removeClass('hide');
                    }
                }
            });
        })
    }

    /**
     * Funcion para cuando se da click en el boton desactivar una propiedead
     */
    var onclickBtnDeactiveProperty=function(){
        $('#deactive-property').on('click',function(){
            var url=$(this).data('href');
            HoldOn.open();
            $.ajax({
                type: 'post',
                url: url,
                data:  {active:false,forced:false},
                success: function (data) {
                    HoldOn.close();
                    if(data.success){
                        $('#activate-property').removeClass('hide');
                        $('#deactive-property').addClass('hide');
                    }
                    else{
                        swal({
                            title: "¿Estás seguro?",
                            text: "La casa que usted desea desactivar tiene reservas hechas, quiere desactivar la misma!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#e94b3d",
                            cancelButtonColor: "#64a433",
                            confirmButtonText: "Sí",
                            cancelButtonText: "No",
                            closeOnConfirm: true
                        }, function () {
                            HoldOn.open();
                            $.ajax({
                                type: 'post',
                                url: url,
                                data:  {active:false,forced:true},
                                success: function (data) {
                                    HoldOn.close();
                                    if(data.success){
                                        $('#activate-property').removeClass('hide');
                                        $('#deactive-property').addClass('hide');
                                    }
                                }
                            });
                        });
                    }
                }
            });
        })
    }

    /**
     * Funcion para cuando se da click en el boton autenticar
     */
    var onclickBtnLogin=function(){
        $('#btn-login').on('click',function(){
            var url=$(this).data('href');
            var url_property=$(this).data('delproperty');
            var url_logout=$(this).data('logout');
            var data={};
            $("#login-form").serializeArray().map(function(x){data[x.name] = x.value;});
            HoldOn.open();
            $.ajax({
                type: 'post',
                data:data,
                url: url,
                success: function (data) {
                    HoldOn.close();
                    if(data.success){
                        $('#myModalLogin').modal('hide');
                        swal({
                            title: "¿Estás seguro?",
                            text: "Su propiedad tiene reservas activas, se notificará, al equipo de reservas de MyCasaParticular de la eliminación de su propiedad.Al eliminar su propiedad automáticamente saldrá del sistema.",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#e94b3d",
                            cancelButtonColor: "#64a433",
                            confirmButtonText: "Sí",
                            cancelButtonText: "No",
                            closeOnConfirm: true
                        }, function () {
                            HoldOn.open();
                            $.ajax({
                                type: 'post',
                                data:data,
                                url: url_property,
                                success: function (data) {
                                    HoldOn.close();
                                    if(data.success){
                                        window.location.href = url_logout;
                                    }
                                }
                            });
                        });

                    }
                    else
                        $('#msg-error').removeClass('hide');
                }
            });
        });
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            onclickBtnDeleteProperty();
            onclickBtnDeactiveProperty();
            onclickBtnActivateProperty();
            onclickBtnLogin();
        }
    };
}();
//Start Property
Property.init();





