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
        for(var i=0;i<$('#nav-tabs-backend li').size();i++){
            var data={};
            var form = $("#form-number-"+i);
            $("#form-number-"+(i+1)).serializeArray().map(function(x){data[x.name] = x.value;});
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
                    showRealPriceRoom();
                    App.initializePlugins('.js-switch-'+($('#nav-tabs-backend li').size()-1));
                    HoldOn.close();
                }
            });
    }

    /**
     * Para salvar el paso
     */
    var saveStep4=function(index){
        if(index==4)
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
     * Para cuando se modifica el precio de una habitación
     */
    var showRealPriceRoom=function(){
            $(".price_low_season").on('change', function (){
                var roomId = $(this).data("roomid");
               Step4.calculateRealRoomPrice("input_price_low_season_"+roomId, "span.input_price_low_season_"+roomId);
            });

            $(".price_high_season").on('change', function (){
                var roomId = $(this).data("roomid");
                Step4.calculateRealRoomPrice("input_price_high_season_"+roomId, "span.input_price_high_season_"+roomId);
            });

            $(".price_special_season").on('change', function (){
                var roomId = $(this).data("roomid");
                Step4.calculateRealRoomPrice("input_price_special_season_"+roomId, "span.input_price_special_season_"+roomId);
            });
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

    var isValidPrices = function(){
        var v = true;
        var s = '';
        for(var i=0;i<$('#nav-tabs-backend li').size();i++){
            var data={};
            $("#form-number-"+(i + 1)).serializeArray().map(function(x){data[x.name] = x.value;});

            /*
             price_high_season
             price_low_season
             price_special_season
             */
            if((data.hasOwnProperty('price_high_season') && (data['price_high_season'] == '' || data['price_high_season'] == 'NaN') ) || (data.hasOwnProperty('price_low_season') && (data['price_low_season'] == '' || data['price_low_season'] == 'NaN'))/* || (data.hasOwnProperty('price_special_season') && data['price_special_season'] == '')*/){
                if(s == ''){
                    s = 'Hab ' + (i + 1);
                }
                else {
                    s += ', ' + 'Hab ' + (i + 1);
                }
                v = false;
            }
        }

        if (!v){
            $('#li2 a').click();
            swal("El precio de las habitaciones " + s + " es obligatorio.", "", "error");
        }
        return v;
    };

    var roundNumber = function(num, scale) {
        var number = Math.round(num * Math.pow(10, scale)) / Math.pow(10, scale);
        if(num - number > 0) {
            return (number + Math.floor(2 * Math.round((num - number) * Math.pow(10, (scale + 1))) / 10) / Math.pow(10, scale));
        } else {
            return number;
        }
    };

    var normalizePrices = function(price, decimal)
    {
        //return (Math.round(price * Math.pow(10,2))/Math.pow(10,2));
        return parseFloat(parseFloat(Math.round(price*100)/100).toFixed(decimal));
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
            showRealPriceRoom();
            //Se captura el evento de guardar el paso
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep4,this);
            initialicePlugins();

        },
        isValidPrices:function(){
          return isValidPrices();
        },
        saveRoom:function(flag, callback){
            if(!isValidPrices()){
                //alert('Precios invalidos');
                return;
            }
            HoldOn.open();
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

                $.ajax({
                    type: 'post',
                    url: url,
                    data:  {rooms: rooms,idown:App.getOwnId()},
                    success: function (data) {
                        if(callback && typeof callback === "function"){
                            callback();
                        }
                        else {
                            var response=data;
                            if(data.success){
                                var j=1;
                                for(var i=1;i<=$('#nav-tabs-backend li').size()-1;i++){
                                    var idRoom=response.ids[parseInt(j)-parseInt(1)];
                                    if($("#id-room-"+i).val()==""){
                                        document.getElementById('id-room-'+i).value=idRoom;
                                    }
                                }

                                HoldOn.close();
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
        calculateRealRoomPrice: function(inputElement, spanElement){
            var rInputElement = $('#r_' + inputElement);
            var inputElement = $('#' + inputElement);
            var commission = $("#inputCommission").val();

            if(rInputElement.val() != '' && rInputElement.val() != 0 && rInputElement.val() != '0'){
                var toTeceive = parseFloat(rInputElement.val());
                var inSite = toTeceive / (1 - commission / 100);
                inSite = normalizePrices(inSite, 2);
                /*var a = roundNumber(4.448, 2);
                 var b = normalizePrices(4.448, 2);*/

                inputElement.val(inSite);
                if(inSite > 0) {
                    $(spanElement).html("En el sitio " + inSite + " CUC.");
                    $(spanElement).removeClass("hide");
                }
            }
            else {
                $(spanElement).html("El precio es obligatorio.");
                $(spanElement).removeClass("hide");
                inputElement.val('');
            }

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





