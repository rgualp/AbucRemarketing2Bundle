/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step3 = function () {
    var dataStep3={};
    var saveStep3=function(){
        Step3.saveDescription(false);
    }
    var fillDataStep3=function(){
        $("#form-description-room").serializeArray().map(function(x){dataStep3[x.name] = x.value;});
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
            fillDataStep3();
            onclickBtnSaveDescription();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep3,this);
        },
        saveDescription:function(flag){
            //if(flag)
                HoldOn.open();
            var data={};
            $("#form-description-room").serializeArray().map(function(x){data[x.name] = x.value;});
            if(!App.equals(data,dataStep3)){ //Sino son iguales los dos objetos no los salvo
                $("#form-description-room").serializeArray().map(function(x){dataStep3[x.name] = x.value;});
                data['idown']=App.getOwnId();
                data['dashboard']=flag;
                $.ajax({
                    type: 'post',
                    url: $("#form-description-room").attr('action'),
                    data:  data,
                    success: function (data) {
                        if(flag)
                            HoldOn.close();
                    }
                });
            }

        }
    };
}();
//Start step3
Step3.init();





