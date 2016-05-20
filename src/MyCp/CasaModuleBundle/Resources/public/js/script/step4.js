/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step4 = function () {

    var id_active="";
    /**
     *Para adicionar tab dinamicos
     */
    var addTab=function(){
        $('#addRoom').on('click',function(){
            alert(1);
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





