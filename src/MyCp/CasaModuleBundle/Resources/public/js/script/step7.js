/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var Step7 = function () {

    var saveStep7=function(){
        alert('Save form 7');
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep7,this);
        }
    };
}();
//Start step7
Step7.init();





