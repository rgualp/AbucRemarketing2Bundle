/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step3 = function () {

    var saveStep3=function(){
        Step3.saveDescription(false);
    }
    var onclickBtnSaveDescription=function(){
          $('#saveStepDescription').on('click',function(){
             Step3.saveDescription(true);
          });
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            onclickBtnSaveDescription();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep3,this);
        },
        saveDescription:function(flag){
            var data={};
            $("#form-description-room").serializeArray().map(function(x){data[x.name] = x.value;});
            data['idown']=App.getOwnId();
            data['dashboard']=flag;
            $.ajax({
                type: 'post',
                url: $("#form-description-room").attr('action'),
                data:  data,
                success: function (data) {
                }
            });
        }
    };
}();
//Start step3
Step3.init();





