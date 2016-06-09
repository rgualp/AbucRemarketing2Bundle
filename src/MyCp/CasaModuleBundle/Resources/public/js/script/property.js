/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Property = function () {

    var deleteProperty=function(){
        $('#delete-property').on('click',function(){

        })
    }
    var activeProperty=function(){
        $('#deactive-property').on('click',function(){
            var url=$(this).data('href');
            HoldOn.open();
            $.ajax({
                type: 'post',
                url: url,
                data:  {forced:false},
                success: function (data) {
                    HoldOn.close();
                    if(data.success){
                      /*  if(!val){
                            $('#deactiveRoom_'+idroom).addClass('hide');
                            $('#activeRoom_'+idroom).removeClass('hide');
                        }
                        else{
                            $('#deactiveRoom_'+idroom).removeClass('hide');
                            $('#activeRoom_'+idroom).addClass('hide');
                        }*/
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
                                data:  {forced:true},
                                success: function (data) {
                                    HoldOn.close();
                                    if(data.success){
                                        /*if(!val){
                                            $('#deactiveRoom_'+idroom).addClass('hide');
                                            $('#activeRoom_'+idroom).removeClass('hide');
                                        }
                                        else{
                                            $('#deactiveRoom_'+idroom).removeClass('hide');
                                            $('#activeRoom_'+idroom).addClass('hide');
                                        }*/
                                    }
                                }
                            });
                        });
                    }
                }
            });
        })
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            deleteProperty();
            activeProperty();
        }
    };
}();
//Start Property
Property.init();





