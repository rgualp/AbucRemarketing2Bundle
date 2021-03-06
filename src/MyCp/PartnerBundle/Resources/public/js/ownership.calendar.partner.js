reservationsBody();
var totalPriceService=0;
var totalPriceDinner=0;
var totalPriceBreakfast=0;
function totalPrice(curr,percent, totalNights)
{
    $('#agency_commission').css({display: 'none'});
    $('#agency_commission_one').css({display: 'none'});
    var real_price=0;
    var total_price_var=0;
    var rooms_price='';
    $('.price-room').each(function() {
        total_price_var=total_price_var+parseFloat(this.innerHTML);
        real_price=real_price+parseFloat(this.innerHTML/curr);
    });

    var ids_rooms='';
    $('.id_room').each(function() {
        ids_rooms=ids_rooms+'&'+(this.innerHTML);});

    var count_guests='';
    $('.guest').each(function() {
        count_guests=count_guests+'&'+(this.innerHTML);});

    var count_kids='';
    $('.kids').each(function() {
        count_kids=count_kids+'&'+(this.innerHTML);});

    var from_date=$('#data_reservation').attr('from_date');
    var to_date=$('#data_reservation').attr('to_date');

    var string_url=from_date+'/'+to_date+'/'+ids_rooms+'/'+count_guests+'/'+count_kids;

    var avgPrice = normalize_prices(total_price_var / totalNights);
    var tourist_fee_percent = 0;
    var roomsTotal = $('.id_room').size();

    if(totalNights == 1)
    {
        if(roomsTotal == 1)
        {
            if(avgPrice < parseInt(20)*curr )
                tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-nr-until-20-percent"));
            else if(avgPrice >= parseInt(20)*curr && avgPrice < parseInt(25)*curr)
                tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-nr-from-20-to-25-percent"));
            else if(avgPrice >= parseInt(25)*curr)
                tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-nr-from-more-25-percent"));
        }
        else
            tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-night-several-rooms-percent"));
    }
    else if(totalNights == 2) {
        tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-2-nights-percent"));
    }
    else if(totalNights == 3)
        tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-3-nights-percent"));
    else if(totalNights == 4)
        tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-4-nights-percent"));
    else if(totalNights >= 5)
        tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-5-nights-percent"));


    $('#data_reservation').val(string_url);
    console.log(total_price_var);

    $('#subtotal_price').html(normalize_prices(total_price_var));
    var percent_value=total_price_var * percent / parseFloat(100);
    //var tourist_service = total_price_var*tourist_fee_percent;
    var tourist_service = total_price_var*0.1;
    var tour_serv=(total_price_var+totalPriceDinner+totalPriceBreakfast)*parseFloat(0.1);

    $("#tourist_service").html(normalize_prices(tour_serv));
    $('#initial_deposit').html(normalize_prices(percent_value));
    $('#accommodation_price').html(normalize_prices(total_price_var));
    $('#pay_at_service').html(normalize_prices(total_price_var - percent_value));

    var fixed_tax = /*parseFloat($("#tourist_service").data("fixed-tax"))*/0;
    fixed_tax = fixed_tax*curr;
    var commissionAgency = parseInt($("#commissionAgency").val());
    var completePayment = parseInt($("#completePayment").val());
    console.log(total_price_var,tourist_service,fixed_tax);
    var summatoryTax = parseFloat(total_price_var+tourist_service + fixed_tax);
    console.log("Sumatoria " + summatoryTax);
    //var agencyCommissionTax = parseFloat((total_price_var+tourist_service + fixed_tax) * commissionAgency/100);
    //var agencyCommissionTax = parseFloat((total_price_var+tourist_service + fixed_tax) * parseFloat(0.1));
    console.log("Comision Agencia Especial " );
    var transferTax = parseFloat((total_price_var+tourist_service + fixed_tax) * parseFloat(0.1));
    console.log("Tansferencia " + transferTax);
    var totalCost = parseFloat(total_price_var+tour_serv + transferTax);
    console.log("Costo total " + totalCost);

    if(completePayment != 0)
        var prepayment = parseFloat(totalCost);
    else
        var prepayment = parseFloat(summatoryTax) ;

    $('#total_prepayment').html(normalize_prices(prepayment));
    $('#total_price').html( normalize_prices(totalPriceDinner + totalPriceBreakfast + totalCost ) );

    $("#commissionPercent").html(percent);
    $("#totalNightsToShow").html(totalNights);
    $("#service_transfer_tax").html(normalize_prices(transferTax));
  //  $("#agency_commission").html(normalize_prices(agencyCommissionTax));
  //  $("#agency_commission_one").html(normalize_prices(agencyCommissionTax));
    $('.calendar-results').css({display: 'block'});

    /*if(checkTotalPrice) {
        if (total_price_var !== originalTotalPrice) {
            $("#btn_submit").attr("disabled", true);
            $(".btn_submit").attr("disabled", true);
            $("#error").css({display: 'block'});
            $(".all-prices-numbers").addClass("error");

        }
        else {
            $("#btn_submit").removeAttr("disabled");
            $(".btn_submit").removeAttr("disabled");
            $("#error").css({display: 'none'});
            $(".all-prices-numbers").removeClass("error");

        }
    }
    else{*/
        $("#btn_submit").removeAttr("disabled");
        $(".btn_submit").removeAttr("disabled");
        $("#error").css({display: 'none'});
        $(".all-prices-numbers").removeClass("error");
    //}
}
function updateService(){
   // $('.calendar-results').css({display: 'none'});
    var count_guests=0;
    $('.guest').each(function() {
        count_guests =eval(count_guests)+ eval(this.innerHTML);
    });
    var count_kids=0;
    $('.kids').each(function() {
        count_kids =eval(count_kids)+ eval(this.innerHTML);
    });
    $('#persons_breakfast').html(eval(count_guests)+eval(count_kids));
    $('#persons_dinner').html(eval(count_guests)+eval(count_kids));
    $("[id*=total_nights]").html($("#totalNights").val());

    var total= eval(count_guests)+eval(count_kids);



    if( $('#dinner').is(':checked') ) {
        totalPriceDinner = normalize_prices(parseFloat($('.col-dinnerPrice').data("dinnerprice"))* total*$("#totalNights").val());

    }
    else {

        totalPriceDinner = 0;

    }

    if( $('#breakfast').is(':checked') ) {
        totalPriceBreakfast = normalize_prices(parseFloat($('.col-breakfastprice').data("breakfastprice"))* total*$("#totalNights").val());

    }
    else{

        totalPriceBreakfast = 0;
    }



    $("#servicefast").val(totalPriceBreakfast);
    $("#servicedinner").val(totalPriceDinner);


    $('#calcdinner').html( normalize_prices(parseFloat($('.col-dinnerPrice').data("dinnerprice"))* total*$("#totalNights").val()));
    $('#calcbreakfast').html( normalize_prices(parseFloat($('.col-breakfastprice').data("breakfastprice"))* total*$("#totalNights").val()));

    var tour_serv=(eval($('#accommodation_price').html())+totalPriceDinner+totalPriceBreakfast)*parseFloat(0.1);
    $("#tourist_service").html(normalize_prices(tour_serv));

}
function reservationsBody()
{
    var total_persons=0;

    $('#rooms_selected > tbody tr').each(function(){
        $(this).remove();
    });

    total_price_var=0;

    $('#dinner').change(function(){
        if(this.checked) {
            $('#col-pricedinner').removeClass("hidden");
            updateService();
            $('#total_price').html(normalize_prices(eval($('#total_price').html())+totalPriceDinner));
        }
        else{
            $('#col-pricedinner').addClass("hidden");
            $('#total_price').html(normalize_prices(eval($('#total_price').html())-totalPriceDinner));
            updateService();
        }

        $('#total_prepayment').html(normalize_prices(eval($('#total_price').html())-eval($('#agency_commission').html())));

    });
    $('#breakfast').change(function(){
        if(this.checked) {
            $('#col-pricebreakfast').removeClass("hidden");
            updateService();
            $('#total_price').html(normalize_prices(eval($('#total_price').html())+totalPriceBreakfast));
        }
        else{
            $('#col-pricebreakfast').addClass("hidden");
            $('#total_price').html(normalize_prices(eval($('#total_price').html())-totalPriceBreakfast));
            updateService();
        }

        $('#total_prepayment').html(normalize_prices(eval($('#total_price').html())-eval($('#agency_commission').html())));

    });
    $('.guest_number').change(function(){

        if($('#tr_'+$(this).attr('data')).html()){
            if(eval($('#combo_guest_'+$(this).attr('data')).val())+eval($('#combo_kids_'+$(this).attr('data')).val())==0)
            {
                $('#tr_'+$(this).attr('data')).remove();
                if ($('#rooms_selected >tbody >tr').length == 0){
                    $('#rooms_selected').css({display: 'none'});
                    $('.calendar-results').css({display: 'none'});
                }
                else
                {
                    totalPrice($(this).attr('data_curr'),$(this).attr('percent_charge'), $("#totalNights").val());
                }
            }
            else
            {

                value=0;
                persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());

                if(($(this).attr('data_is_triple')==='1' || $(this).attr('data_is_triple')==='true') && persons>=3)
                {
                    value=$(this).attr('data_total')*$(this).attr('data_curr') + (($(this).attr('data_curr')*$(this).attr('data_triple_recharge')) * (cont_array_dates -1));
                    $('.normalPrice_' + $(this).attr('data')).css({display: 'none'});
                    $('.triplePrice_' + $(this).attr('data')).css({display: 'block'});
                    $('#tripleAlert_' + $(this).attr('data')).css({display: 'block'});
                }
                else
                {
                    value=$(this).attr('data_total')*$(this).attr('data_curr');
                    $('.normalPrice_' + $(this).attr('data')).css({display: 'block'});
                    $('.triplePrice_' + $(this).attr('data')).css({display: 'none'});
                    $('#tripleAlert_' + $(this).attr('data')).css({display: 'none'});
                }
                value= normalize_prices(value);
                $('#guest_'+$(this).attr('data')).html($('#combo_guest_'+$(this).attr('data')).val());
                $('#kids_'+$(this).attr('data')).html($('#combo_kids_'+$(this).attr('data')).val());
                $('#price_'+$(this).attr('data')).html(value);
                $('#rooms_selected').css({display: 'block'});
                totalPrice($(this).attr('data_curr'),$(this).attr('percent_charge'), $("#totalNights").val());

            }
        }
        else
        {
            value=0;
            real_value=0;
            persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());
            if(($(this).attr('data_is_triple')==='1' || $(this).attr('data_is_triple')==='true') && persons>=3)
            {
                value=$(this).attr('data_total')*$(this).attr('data_curr') +(($(this).attr('data_curr')*$(this).attr('data_triple_recharge')) * (cont_array_dates -1)) ;
                $('.normalPrice_' + $(this).attr('data')).css({display: 'none'});
                $('.triplePrice_' + $(this).attr('data')).css({display: 'block'});
                $('#tripleAlert_' + $(this).attr('data')).css({display: 'block'});
            }
            else
            {
                value=$(this).attr('data_total')*$(this).attr('data_curr');
                $('.normalPrice_' + $(this).attr('data')).css({display: 'block'});
                $('.triplePrice_' + $(this).attr('data')).css({display: 'none'});
                $('#tripleAlert_' + $(this).attr('data')).css({display: 'none'});
            }

            value= normalize_prices(value);

            $('#rooms_selected').css({display: 'table'});
            $('.calendar-results').css({display: 'block'});

            $('#rooms_selected > tbody:last').append('<tr id="tr_'+$(this).attr('data')+'">' +
                '<td class="id_room" style="display: none;">'+$(this).attr('data')+'</td>' +
                '<td>'+this.parentNode.parentNode.cells[0].innerHTML+'</td>' +
                '<td>'+this.parentNode.parentNode.cells[1].innerHTML+'</td>' +
                '<td>'+$("#totalNights").val()+'</td>' +
                '<td class="guest" id="guest_'+$(this).attr('data')+'">'+$('#combo_guest_'+$(this).attr('data')).val()+'</td>'+
                '<td class="kids" id="kids_'+$(this).attr('data')+'">'+$('#combo_kids_'+$(this).attr('data')).val()+'</td>'+
                '<td class="price-room" id="price_'+$(this).attr('data')+'">'+value+'</td>');

            totalPrice($(this).attr('data_curr'),$(this).attr('percent_charge'), $("#totalNights").val());
        }
        updateService();
    });
}

function normalize_prices(price)
{
    return Math.round(price * Math.pow(10,2))/Math.pow(10,2);
}