/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step5 = function () {

    var saveStep5=function(){
        Step5.saveFacilities(false);
    }

    var onclickBtnSaveFacilities=function(){
        $('#saveStepFacilities').on('click',function(){
            Step5.saveFacilities(true);
        });
    }

    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            onclickBtnSaveFacilities();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep5,this);
        },
        saveFacilities:function(flag){
            var url= $("#facilities-div").data("url");
            var hasBreakfast = $("#breakfast").is(':checked');
            var breakfastPrice = $("#facilitiesBreakfast").val();
            var hasDinner = $("#dinner").is(':checked');
            var dinnerPriceFrom = $("#facilitiesDinnerFrom").val();
            var dinnerPriceTo = $("#facilitiesDinnerTo").val();
            var hasParking = $("#parking").is(':checked');
            var parkingPrice = $("#facilitiesParking").val();
            var hasJacuzzy = $("#jacuzzy").is(':checked');
            var hasSauna = $("#sauna").is(':checked');
            var hasPool = $("#pool").is(':checked');
            var hasParkingBike = $("#parking_bike").is(':checked');
            var hasPet = $("#pet").is(':checked');
            var hasLaundry = $("#laundry").is(':checked');
            var hasEmail = $("#email").is(':checked');

            var data={
                idAccommodation:App.getOwnId(),
                hasBreakfast: hasBreakfast,
                breakfastPrice: breakfastPrice,
                hasDinner: hasDinner,
                dinnerPriceFrom: dinnerPriceFrom,
                dinnerPriceTo: dinnerPriceTo,
                hasParking: hasParking,
                parkingPrice: parkingPrice,
                hasJacuzzy: hasJacuzzy,
                hasSauna: hasSauna,
                hasPool: hasPool,
                hasParkingBike: hasParkingBike,
                hasPet: hasPet,
                hasLaundry: hasLaundry,
                hasEmail: hasEmail,
                dashboard: flag
            };
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
//Start step5
Step5.init();





