/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step7 = function () {

    var saveStep7=function(){
        Step7.saveProfile(false);
    }

    var onclickBtnSaveProfile=function(){
        $('#saveProfile').on('click',function(){
            Step7.saveProfile(true);
        });
    }

    var validatePassword = function() {
        var password = $("#password").val();
        var repeated = $("#repeated").val();

        if(password != "" || repeated != "")
        {
            if((password != "" && repeated == "") || (password == "" && repeated != "") || (password != "" && repeated != "" && password != repeated))
            {
                $("#errorLabel").html("Las contraseñas no coinciden.");
                $("#errorLabel").removeClass("hide");
                return false;
            }
            else if(password.length < 6){
                $("#errorLabel").html("La longitud de la nueva contraseña tiene que ser superior a 6 caracteres");
                $("#errorLabel").removeClass("hide");
                return false;
            }
        }
        $("#errorLabel").addClass("hide");
        return true;
    }



    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            onclickBtnSaveProfile();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep7,this);

            $("#btnPublish").click(App.fireEventSaveTab());
        },
        saveProfile:function(flag) {
            var validate = true;
            var changePassword = false;

            if (flag) {
                validate = validatePassword();
                changePassword = validate;
            }

            if (validate) {
                $("#loading").removeClass("hide");
                var url = $("#submit-url").val();
                var homeownerName = $("#homeownerName").val();
                var mobile = $("#own_mobile_number").val();
                var phone = $("#own_phone_number").val();
                var mainStreet = $("#owner_street").val();
                var streetNumber = $("#owner_street_number").val();
                var between1 = $("#owner_between_street_1").val();
                var between2 = $("#owner_between_street_2").val();
                var municipalityId = $("#owner_municipality").val();
                var provinceId = $("#owner_province").val();
                var email = $("#owner_email").val();
                var email2 = $("#owner_email_2").val();
                var secondOwner = $("#owner_second_owner").val();
                var password = $("#password").val();

                $.ajax({
                    type: 'post',
                    url: url,
                    data: {
                        idAccommodation: App.getOwnId(),
                        mobile: mobile,
                        phone: phone,
                        mainStreet: mainStreet,
                        streetNumber: streetNumber,
                        between1: between1,
                        between2: between2,
                        municipalityId: municipalityId,
                        provinceId: provinceId,
                        email: email,
                        email2: email2,
                        homeownerName: homeownerName,
                        secondOwner: secondOwner,
                        password: password,
                        changePassword: changePassword,
                        dashboard: flag
                    },
                    success: function (data) {
                        $("#loading").addClass("hide");
                    }
                });
            }
        }
    };
}();
//Start step7
Step7.init();





