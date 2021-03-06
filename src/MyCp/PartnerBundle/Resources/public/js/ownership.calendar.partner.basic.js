reservationsBody();
var totalPriceService=0;
var totalPriceDinner=0;
var totalPriceBreakfast=0;
function totalPrice(curr,percent, totalNights)
{

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




    $('#data_reservation').val(string_url);


    $('#subtotal_price').html(normalize_prices(total_price_var));
    var prepayment=total_price_var * percent / parseFloat(100);


    var tour_serv=0;

    $("#tourist_service").html(normalize_prices(tour_serv));


    $('#accommodation_price').html(normalize_prices(total_price_var));
    $('#pay_at_service').html(normalize_prices(total_price_var - prepayment));
    $("#pay_at_service_cuc").html("CUC " + normalize_prices((total_price_var - prepayment)/curr));




    var transferTax = 0;

    var totalCost = parseFloat(total_price_var+tour_serv + transferTax);




    $('#total_prepayment').html(normalize_prices(prepayment));
    $('#total_price').html( normalize_prices(totalPriceDinner + totalPriceBreakfast + totalCost ) );

    $("#commissionPercent").html(percent);
    $("#totalNightsToShow").html(totalNights);
    $("#service_transfer_tax").html(normalize_prices(transferTax));
    $("#service_transfer_tax").css({display: 'none'});

    $('.calendar-results').css({display: 'block'});

        $("#btn_submit").removeAttr("disabled");
        $(".btn_submit").removeAttr("disabled");
        $("#error").css({display: 'none'});
        $(".all-prices-numbers").removeClass("error");

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

    var tour_serv=0;
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