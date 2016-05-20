/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var Step6 = function () {

    var saveStep6=function(){
        alert('Save form 6');
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep6,this);
        }
    };
}();
//Start step6
Step6.init();





