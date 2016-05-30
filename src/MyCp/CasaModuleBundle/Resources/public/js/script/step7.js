/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step7 = function () {

    var saveStep7=function(){
        /**
         * Para salvar datos del propietario
         */
        var url= $("#submit-url-div").val();
        var mobile = $("#own_mobile_number").val();
        var phone = $("#own_phone_number").val();
        var mainStreet = $("#owner_street").val();
        var streetNumber = $("#owner_street_number").val();
        var between1 = $("#owner_between_street_1").val();
        var between2 = $("#owner_between_street_2").val();
        var municipalityId = $("#owner_municipality").val();
        var provinceId = $("#owner_province").val();
        var email2 = $("#owner_email_2").val();
        var secondOwner = $("#owner_second_owner").val();

        $.ajax({
            type: 'post',
            url: url,
            data:  {
                idAccommodation:App.getOwnId(),
                mobile: mobile,
                phone: phone,
                mainStreet: mainStreet,
                streetNumber: streetNumber,
                between1: between1,
                between2: between2,
                municipalityId: municipalityId,
                provinceId: provinceId,
                email2: email2,
                secondOwner: secondOwner
            },
            success: function (data) {
            }
    });
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





