/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step5 = function () {

    var saveStep5=function(index){
        if(index==5)
        Step5.saveFacilities(false);
    }

    var onclickBtnSaveFacilities=function(){
        $('#saveStepFacilities').on('click',function(){
            Step5.saveFacilities(true);
        });
    }
    $('span#breakfast').on('click', function(){
       if($("#breakfast").is(':checked')){
            data= $("input#breakfast").data('cmpenabled');
            if(typeof(data) !== "undefined")
            $(''+data).removeClass('hide');
        }
    });
    $('span#dinner').on('click', function(){
       if($("#dinner").is(':checked')){
            data= $("input#dinner").data('cmpenabled');
            if(typeof(data) !== "undefined")
            $(''+data).removeClass('hide');
        }
    });
    $('span#parking').on('click', function(){
       if($("#parking").is(':checked')){
            data= $("input#parking").data('cmpenabled');
            if(typeof(data) !== "undefined")
            $(''+data).removeClass('hide');
        }
    });
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            onclickBtnSaveFacilities();
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep5,this);
        },
        saveFacilities:function(flag){
            //if(flag)
                HoldOn.open();
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
                url: url,
                data:  data,
                success: function (data) {
                    // if(flag)
                        HoldOn.close();
                }
            });
        }

    };
}();
//Start step5
Step5.init();





