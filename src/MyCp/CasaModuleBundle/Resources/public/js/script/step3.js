/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var Step3 = function () {

    var saveStep3=function(){
        alert('Save form 3');
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





