/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step4 = function () {
    var id_active="11";
    var html_nav_addTab="";
    var url_add_tab="";
    var dataStep4=new Array();

    /**
     * Para llenar un arreglo con los datos del paso 4
     */
    var fillDataStep4=function(){
        for(var i=1;i<=$('#nav-tabs-backend li').size()-1;i++){
            var data={};
            var form = $("#form-number-"+i);
            $("#form-number-"+i).serializeArray().map(function(x){data[x.name] = x.value;});
            dataStep4.push(data);
        }
    }

    /**
     * Para cuando se cambia de tab
     */
    var changeTab=function(){
        $(document).on( 'shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        });
    }

    /**
     * Para adicionar tab dinámicos
     */
    var addContentTab=function(el){
        HoldOn.open();
        var data={'num':$('#nav-tabs-backend li').size()};
        url_add_tab=el.data("href");
        //Función q retorna el html de la respuesta
        $.post( el.data("href"),data,
            function (data, status, response) {
                if (status && status == 'success') {
                    //Le quito la clase al que esta activo en caso de que tenga algun tab
                    $('#nav1'+id_active+'').removeClass('active');
                    $('#tab1'+id_active+'').removeClass('active');
                    Step4.deleteEndTab();
                    //Lo adiciono y lo activo
                    var id=$('#nav-tabs-backend li').size()+1;
                    var text='Hab '+($('#nav-tabs-backend li').size()+1);
                    $('#nav-tabs-backend').append('<li id="nav1'+id+'" class="active"><a id="'+id+'" data-toggle="tab" href="#tab1'+id+'" data-tab="'+id+'">'+text+'<span class="closeTab" onclick="Step4.closeTab($(this))">×</span></a></li>');
                    //Adiciono el contenido del tab
                    $('#tab-content-backend').append('<div id="tab1'+id+'" class="tab-pane active">'
                        +data.html
                        +'</div>');
                    id_active=id;
                    $('#nav1'+id_active+'').addClass('active');
                    $('#tab1'+id_active+'').addClass('active');
                    Step4.addEndTab();
                    App.initializePlugins('.js-switch-'+($('#nav-tabs-backend li').size()-1));
                    HoldOn.close();
                }
            });
    }

    /**
     * Para salvar el paso
     */
    var saveStep4=function(){
        Step4.saveRoom(false);
    }

    /**
     * Para cuando se click en el boton salvar
     */
    var onclickBtnSaveRoom=function(){
        $('#saveStepRoom').on('click',function(){
            Step4.saveRoom(true);
        })
    }

    /**
     * Para activar o desactivar la habitacion
     */
    var activeRoom=function(){
        $('.change-activate').on('click',function(){
            Step4.changeActiveRoom(($(this).hasClass('deactivate'))?false:true,$(this).data('idroom'),$(this).data('href'));
        });
    }

    /**
     * Para eliminar una habitacion
     */
    var onclickBtnDeleteRoom=function(){
        $('.delete-room').on('click',function(){
            var idroom=$(this).data('idroom');
            var url_delete_room=$(this).data('href');
            var noroom=$(this).data('noroom');
            swal({
                title: "",
                text: "¿Está seguro que desea eliminar la habitación seleccionada?",
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
                    url: url_delete_room,
                    data:  {idroom:idroom},
                    success: function (data) {
                        HoldOn.close();
                        if(data.success){
                            $('#nav1'+noroom).remove(); //remove li of tab
                            $('#tab1'+noroom).remove(); //remove respective tab content
                        }
                    }
                });
            });
        });
    }

    /**
     * Funcion para inicializar los plugins
     */
    var initialicePlugins=function(){
        //Si hay room inicializar
        if($('#nav-tabs-backend').data('numroom')>0){
            for(var i=0;i<=$('#nav-tabs-backend').data('numroom');i++)
                App.initializePlugins('.js-switch-'+i);
        }
        else
            App.initializePlugins('.js-switch-'+($('#nav-tabs-backend li').size()-1));
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            changeTab();
            activeRoom();
            onclickBtnDeleteRoom();
            onclickBtnSaveRoom();
            fillDataStep4();
            //Se captura el evento de guardar el paso
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep4,this);
            initialicePlugins();

        },
        saveRoom:function(flag){
            var rooms = new Array();
            var url='';
            for(var i=0;i<$('#nav-tabs-backend li').size();i++){
                var data={};
                if(url==''){
                    var form = $("#form-number-"+(i + 1));
                    url= form.attr('action');
                }
                $("#form-number-"+(i + 1)).serializeArray().map(function(x){data[x.name] = x.value;});
                rooms.push(data);
            }
            //if(!App.equals(rooms,dataStep4)){

                dataStep4=rooms;
                /**
                 * Para salvar las rooms
                 */
                console.log(rooms);
                $.ajax({
                    type: 'post',
                    url: url,
                    data:  {rooms: rooms,idown:App.getOwnId()},
                    success: function (data) {
                        var response=data;
                        if(data.success){
                            var j=1;
                            for(var i=1;i<=$('#nav-tabs-backend li').size()-1;i++){
                                var idRoom=response.ids[parseInt(j)-parseInt(1)];
                                if($("#id-room-"+i).val()==""){
                                    document.getElementById('id-room-'+i).value=idRoom;
                                }
                            }
                        }
                    }
                });
           // }
        },
        getActiveTab:function(){
            return id_active;
        },
        addTabTabpanel:function(el){
            addContentTab(el);
        },
        closeTab:function(el) {
            //there are multiple elements which has .closeTab icon so close the tab whose close icon is clicked
            var tabContentId = el.parent().data('tab');
            el.parent().parent().remove(); //remove li of tab
            $('#nav-tabs-backend a:last').tab('show'); // Select first tab
            $('#tab1'+tabContentId).remove(); //remove respective tab content
        },
        addEndTab:function(){
            $('#nav-tabs-backend').append('<li id="addTab" data-href="'+url_add_tab+'" onclick="Step4.addTabTabpanel($(this))" >'+html_nav_addTab+'</li>');
            //Adiciono el contenido del tab
            $('#tab-content-backend').append('<div class="tab-pane" id="tab25"></div>');
        },
        deleteEndTab:function(){
            //Remuevo el ultimo tab y lo adiciono
            html_nav_addTab=$('#addTab').html();
            $('#addTab').remove(); //remove li of tab
            $('#tab25').remove(); //remove respective tab content
        },
        changeActiveRoom:function(val,idroom,url){
            HoldOn.open();
            $.ajax({
                type: 'post',
                url: url,
                data:  {idroom:idroom,val:val,forced:false},
                success: function (data) {
                    HoldOn.close();
                    if(data.success){
                        if(!val){
                            $('#deactiveRoom_'+idroom).addClass('hide');
                            $('#activeRoom_'+idroom).removeClass('hide');
                        }
                        else{
                            $('#deactiveRoom_'+idroom).removeClass('hide');
                            $('#activeRoom_'+idroom).addClass('hide');
                        }
                    }
                    else{
                        swal({
                            title: "¿Estás seguro?",
                            text: "La habitación que usted desea desactivar tiene reservas hechas, quiere desactivar la misma!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#e94b3d",
                            cancelButtonColor: "#64a433",
                            confirmButtonText: "Sí",
                            cancelButtonText: "No",
                            closeOnConfirm: true
                        }, function () {
                           HoldOn.open();
                            HoldOn.open();
                            $.ajax({
                                type: 'post',
                                url: url,
                                data:  {idroom:idroom,val:val,forced:true},
                                success: function (data) {
                                    HoldOn.close();
                                    if(data.success){
                                        if(!val){
                                            $('#deactiveRoom_'+idroom).addClass('hide');
                                            $('#activeRoom_'+idroom).removeClass('hide');
                                        }
                                        else{
                                            $('#deactiveRoom_'+idroom).removeClass('hide');
                                            $('#activeRoom_'+idroom).addClass('hide');
                                        }
                                    }
                                }
                            });
                        });
                    }
                }
            });
        }
    };
}();
//Start step4
Step4.init();





