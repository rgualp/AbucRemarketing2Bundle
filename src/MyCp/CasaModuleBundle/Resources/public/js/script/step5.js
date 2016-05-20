/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var Step5 = function () {

    var saveStep5=function(){
        alert('Save form 5');
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep5,this);
        }

    };
}();
//Start step5
Step5.init();





