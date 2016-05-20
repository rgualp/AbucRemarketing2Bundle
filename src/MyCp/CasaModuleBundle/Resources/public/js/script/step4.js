/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step4 = function () {

    var id_active="11";
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
    var addTab=function(){
        $('#addRoom').on('click',function(){
            HoldOn.open();
            var data={};
            //Función q retorna el html de la respuesta
            $.post( $(this).data("href"),data,
                function (data, status, response) {
                    if (status && status == 'success') {
                        //Le quito la clase al que esta activo en caso de que tenga algun tab
                        if(id_active!==''){
                            $('#nav'+id_active+'').removeClass('active');
                            $('#tab'+id_active+'').removeClass('active');
                        }
                        //Lo adiciono y lo activo
                        $('#nav-tabs-backend').append('<li id="nav_'+self.attr("id")+'" class="active"><a id="'+self.attr("id")+'" data-toggle="tab" href="#tab'+self.attr("id")+'" data-tab="'+self.attr("id")+'">'+self.context.innerHTML+'<span class="closeTab" onclick="closeTab($(this))">×</span></a></li>');
                        //Adiciono el contenido del tab
                        $('#tab-content-backend').append('<div id="tab_'+self.attr("id")+'" class="tab-pane active">'
                            +data.html
                            +'</div>');
                        id_active=self.attr("id");
                        HoldOn.close();
                    }
                });

         /*   alert(1);
            var id='tab13';
            var text='Text';
            var html='Hola';
            //Lo adiciono y lo activo
            $('#nav-tabs-backend').append('<li id="nav_'+id+'" class="active"><a id="'+id+'" data-toggle="tab" href="#tab'+id+'" data-tab="'+id+'">'+text+'<span class="closeTab" onclick="closeTab($(this))">×</span></a></li>');
            //Adiciono el contenido del tab
            $('#tab-content-backend').append('<div id="tab_'+id+'" class="tab-pane active">'
                +html
                +'</div>');*/
        })
    }
    var saveStep4=function(){
        alert('Save form 4');
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            addTab();
            changeTab();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep4,this);
        },
        getActiveTab:function(){
            return id_active;
        }
    };
}();
//Start step4
Step4.init();





