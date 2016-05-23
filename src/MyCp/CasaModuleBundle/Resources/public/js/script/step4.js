/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step4 = function () {
    var id_active="11";
    var numRoom=1;
    var html_nav_addTab="";
    var url_add_tab="";
    /**
     *
     */
    var changeTab=function(){
        $(document).on( 'shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
           // alert(1);
        });
    }
    /**
     *Para adicionar tab dinámicos
     */
    var addContentTab=function(el){
        HoldOn.open();
        var data={'num':(parseInt(numRoom)+parseInt(1))};
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
                    var text='Habitación '+(parseInt(numRoom)+parseInt(1));
                    numRoom=parseInt(numRoom)+parseInt(1);
                    $('#nav-tabs-backend').append('<li id="nav'+id+'" class="active"><a id="'+id+'" data-toggle="tab" href="#tab'+id+'" data-tab="'+id+'">'+text+'<span class="closeTab" onclick="Step4.closeTab($(this))">×</span></a></li>');
                    //Adiciono el contenido del tab
                    $('#tab-content-backend').append('<div id="tab'+id+'" class="tab-pane active">'
                        +data.html
                        +'</div>');
                    id_active=id;
                    $('#nav'+id_active+'').addClass('active');
                    $('#tab'+id_active+'').addClass('active');
                    Step4.addEndTab();
                    App.initializePlugins('.js-switch-'+numRoom);
                    HoldOn.close();
                }
            });
    }
    var saveStep4=function(){
        var obj = new Object();
        var url='';
        for(var i=1;i<=numRoom;i++){
            var data={};
            if(url==''){
                var form = $("#form-number-"+i);
                url= form.attr('action');
            }
            $("#form-number-"+i).serializeArray().map(function(x){data[x.name] = x.value;});
            obj['form-number-'+i]=data;
        }
        $.ajax({
            type: 'post',
            url: url,
            data: obj,
            success: function (data) {
                console.log(1);
            }
        });
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            changeTab();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep4,this);
            App.initializePlugins('.js-switch-'+numRoom);
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
            numRoom=parseInt(numRoom)-parseInt(1);
        },
        addEndTab:function(){
            $('#nav-tabs-backend').append('<li id="addTab" data-href="'+url_add_tab+'" onclick="Step4.addTabTabpanel($(this))" >'+html_nav_addTab+'</li>');
            //Adiciono el contenido del tab
            $('#tab-content-backend').append('<div class="tab-pane" id="tab12"></div>');
        },
        deleteEndTab:function(){
            //Remuevo el ultimo tab y lo adiciono
            html_nav_addTab=$('#addTab').html();
            $('#addTab').remove(); //remove li of tab
            $('#tab12').remove(); //remove respective tab content
        }
    };
}();
//Start step4
Step4.init();





