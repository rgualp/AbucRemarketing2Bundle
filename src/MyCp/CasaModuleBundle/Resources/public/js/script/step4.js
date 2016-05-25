/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step4 = function () {
    var id_active="11";
    var html_nav_addTab="";
    var url_add_tab="";
    /**
     * Para cuando se cambia de tab
     */
    var changeTab=function(){
        $(document).on( 'shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        });
    }
    /**
     *Para adicionar tab dinámicos
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
                    $('#nav'+id_active+'').removeClass('active');
                    $('#tab'+id_active+'').removeClass('active');
                    Step4.deleteEndTab();
                    //Lo adiciono y lo activo
                    var id=(parseInt(id_active)+parseInt(2));
                    var text='Habitación '+($('#nav-tabs-backend li').size()+1);
                    $('#nav-tabs-backend').append('<li id="nav'+id+'" class="active"><a id="'+id+'" data-toggle="tab" href="#tab'+id+'" data-tab="'+id+'">'+text+'<span class="closeTab" onclick="Step4.closeTab($(this))">×</span></a></li>');
                    //Adiciono el contenido del tab
                    $('#tab-content-backend').append('<div id="tab'+id+'" class="tab-pane active">'
                        +data.html
                        +'</div>');
                    id_active=id;
                    $('#nav'+id_active+'').addClass('active');
                    $('#tab'+id_active+'').addClass('active');
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
        var rooms = new Array();
        var url='';
        for(var i=1;i<=$('#nav-tabs-backend li').size()-1;i++){
            var data={};
            if(url==''){
                var form = $("#form-number-"+i);
                url= form.attr('action');
            }
            $("#form-number-"+i).serializeArray().map(function(x){data[x.name] = x.value;});
            rooms.push(data);
        }
        /**
         * Para salvar las rooms
         */
        $.ajax({
            type: 'post',
            url: url,
            data:  {rooms: rooms,idown:App.getOwnId()},
            success: function (data) {
            }
        });
    }
    /**
     * Para desactivar la habitacion
     */
    var deactivateRoom=function(){
        $('#deactivateRoom').on('click',function(){

        });
    }
    var deleteRoom=function(){
        $('#deleteRoom').on('click',function(){

        });
    }
    /**
     * Para cuando se da click en el boton de cambiar
     */
    var changeDataStep4=function(){
        $('.changeDataStep4').on('click',function(){
            $($(this).data("cmpdisabled")).addClass('hide');
            $($(this).data("cmpenabled")).removeClass('hide');
            $(this).addClass('hide');
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
            deactivateRoom();
            deleteRoom();
            changeDataStep4();
            //Se captura el evento de guardar el paso
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep4,this);
            initialicePlugins();

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
            $('#tab'+tabContentId).remove(); //remove respective tab content
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
        }
    };
}();
//Start step4
Step4.init();





