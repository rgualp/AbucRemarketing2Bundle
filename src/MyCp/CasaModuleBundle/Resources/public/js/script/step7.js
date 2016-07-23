/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step7 = function () {
    var saveStep7=function(){
        Step7.saveProfile(false, false);
    }

    var onclickBtnSaveProfile=function(){
        $('#saveProfile').on('click',function(){
            Step7.saveProfile(true, false);
        });
    }
    var onclickBtnPublish=function(){
        $('#btnPublish').on('click',function(){
            ajaxControllersPublish();
            Step7.saveProfile(false, true);
        });
    }
    var countAjax=0;
    var hasError = false;
    var ajaxControllersPublish= function(){
        $(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
            if (ajaxOptions.dataType != 'script') {
                countAjax++;
            }
        });
        $(document).ajaxComplete(function () {
            countAjax--;
            if(countAjax==0 && !hasError) window.location=publishUrl;
        });
        $(document).ajaxError(function () {
            countAjax--;if(countAjax==0 && !hasError) window.location=publishUrl;
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
            onclickBtnPublish();

            var event = App.getEvent();
            event.clickBtnContinueAfter.add(saveStep7, this);
            //
            //$("#btnPublish").click(
            //    ajaxControllersPublish(),
            //    App.fireEventSaveTab());
        },
        saveProfile:function(flag, publishAccommodation) {
            var validate = true;
            var changePassword = false;

            if (flag) {
                validate = validatePassword();
                changePassword = validate;
            }
           var emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
            //Se muestra un texto a modo de ejemplo, luego va a ser un icono
            if ($('#owner_email_2').val()!=''&&!emailRegex.test($('#owner_email_2').val())) {
                validate=false;
                $("#email2Errors").html("Email inválido.");
                $("#email2Errors").removeClass("hide");
                $('#owner_email_2').on('keyup', function(){
                    if ($('#owner_email_2').val()!=''&&emailRegex.test($('#owner_email_2').val())) {
                        $("#email2Errors").addClass("hide");

                    }
                    else $("#email2Errors").removeClass("hide");
                });
            }
            else   $("#email2Errors").addClass("hide");
            if (validate) {
                //$("#loading").removeClass("hide");
                HoldOn.open();
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
                        dashboard: flag,
                        publishAccommodation: publishAccommodation
                    },
                    success: function (data) {
                        //$("#loading").addClass("hide");
                        HoldOn.close();
                        if(publishAccommodation) {
                            if (data.success === false) {
                                swal({
                                    title: "Ooops!",
                                    text: data.msg,
                                    type: "error"
                                });
                                hasError = true;
                                return false;
                            }
                            else
                                window.location = publishUrl;
                        }
                    },
                    error: function(data){
                        HoldOn.close();
                        if(publishAccommodation) {
                            if (data.success  === false) {
                                swal({
                                    title: "Ooops!",
                                    text: data.msg,
                                    type: "error"
                                });
                                hasError = true;
                                return false;
                            }
                            else
                                window.location = publishUrl;
                        }
                    }
                });
            }
        }
    };
}();
//Start step7
Step7.init();





