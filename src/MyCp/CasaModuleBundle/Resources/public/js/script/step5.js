/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step5 = function () {

    var hasBreakfastVisibility = function(){
        var hasBreakfast = $("#hasBreakfast").val();
        if(hasBreakfast)
        {
            $(".facilitiesBreakfastWithPrice").css("display","block");
            $(".facilitiesBreakfastPrice").css("display", "none");
        }
        else
        {
            $(".facilitiesBreakfastWithPrice").css("display","none");
            $(".facilitiesBreakfastPrice").css("display", "block");
        }
    }

    var hasDinnerVisibility = function(){
        var hasDinner = $("#hasDinner").val();
        if(hasDinner)
        {
            $(".facilitiesDinnerWithPrice").css("display","block");
            $(".facilitiesDinnerPrice").css("display", "none");
        }
        else
        {
            $(".facilitiesDinnerWithPrice").css("display","none");
            $(".facilitiesDinnerPrice").css("display", "block");
        }
    }

    var hasParkingVisibility = function(){
        var hasParking = $("#hasParking").val();
        if(hasParking)
        {
            $(".facilitiesParkingWithPrice").css("display","block");
            $(".facilitiesParkingPrice").css("display", "none");
        }
        else
        {
            $(".facilitiesParkingWithPrice").css("display","none");
            $(".facilitiesParkingPrice").css("display", "block");
        }
    }

    var showDivPrices=function(){
        //Showing facilities prices divs
        hasBreakfastVisibility();
        hasDinnerVisibility();
        hasParkingVisibility();
    }

    var changeBreakfastPrice = function(){
        $(".facilitiesBreakfastWithPrice").css("display","none");
        $(".facilitiesBreakfastPrice").css("display", "block");
    }

    var changeDinnerPrice = function(){
        $(".facilitiesDinnerWithPrice").css("display","none");
        $(".facilitiesDinnerPrice").css("display", "block");
    }

    var changeParkingPrice = function(){
        $(".facilitiesParkingWithPrice").css("display","none");
        $(".facilitiesParkingPrice").css("display", "block");
    }

    var switchBreakfast = function(){
        var isChecked = $("#breakfast").is(':checked');

        if(!isChecked){
            $(".facilitiesBreakfastWithPrice").css("display","none");
            $(".facilitiesBreakfastPrice").css("display", "none");
        }
        else{
            hasBreakfastVisibility();
        }
    }

    var switchDinner = function(){
        var isChecked = $("#dinner").is(':checked');

        if(!isChecked){
            $(".facilitiesDinnerWithPrice").css("display","none");
            $(".facilitiesDinnerPrice").css("display", "none");
        }
        else{
            hasDinnerVisibility();
        }
    }

    var switchParking = function(){
        var isChecked = $("#parking").is(':checked');

        if(!isChecked){
            $(".facilitiesParkingWithPrice").css("display","none");
            $(".facilitiesParkingPrice").css("display", "none");
        }
        else{
            hasParkingVisibility();
        }
    }

    var saveStep5=function(){
        /**
         * Para salvar los facilities
         */
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

        $.ajax({
            type: 'post',
            url: url,
            data:  {
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
                hasEmail: hasEmail
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
            event.clickBtnContinueAfter.add(saveStep5,this);

            showDivPrices();

            $("#btn-change-breakfast").click(changeBreakfastPrice);
            $("#btn-change-dinner").click(changeDinnerPrice);
            $("#btn-change-parking").click(changeParkingPrice);

            $("#breakfast").change(switchBreakfast);
            $("#dinner").change(switchDinner);
            $("#parking").change(switchParking);

        }

    };
}();
//Start step5
Step5.init();





