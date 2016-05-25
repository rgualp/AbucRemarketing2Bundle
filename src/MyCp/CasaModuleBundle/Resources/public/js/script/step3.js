/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step3 = function () {

    var saveStep3=function(){
        var data={};
        $("#form-description-room").serializeArray().map(function(x){data[x.name] = x.value;});
        data['idown']=App.getOwnId();
        $.ajax({
            type: 'post',
            url: $("#form-description-room").attr('action'),
            data:  data,
            success: function (data) {
            }
        });
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep3,this);
        }
    };
}();
//Start step3
Step3.init();





