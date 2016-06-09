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
                text: "La casa que usted desea eliminar puede tener reservas hechas, ¿quiere eliminar la misma?",
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
            //$('#login-form').submit();
            var url=$(this).data('href');
            var data={};
            $("#login-form").serializeArray().map(function(x){data[x.name] = x.value;});
            $.ajax({
                type: 'post',
                data:data,
                url: url,
                success: function (data) {
                    HoldOn.close();
                    if(data)
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





