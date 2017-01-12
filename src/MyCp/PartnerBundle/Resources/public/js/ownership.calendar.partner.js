reservationsBody();
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

    if(totalNights == 1)
    {
        if(roomsTotal == 1)
        {
            if(avgPrice < 20*curr )
                tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-nr-until-20-percent"));
            else if(avgPrice >= 20*curr && avgPrice < 25*curr)
                tourist_fee_percent = parseFloat($("#tourist_service").attr("data-one-nr-from-20-to-25-percent"));
            else if(avgPrice >= 25*curr)
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
    var percent_value=total_price_var * percent / 100;
    var tourist_service = total_price_var*tourist_fee_percent;

    $("#tourist_service").html(normalize_prices(tourist_service));
    $('#initial_deposit').html(normalize_prices(percent_value));
    $('#accommodation_price').html(normalize_prices(total_price_var));
    $('#pay_at_service').html(normalize_prices(total_price_var - percent_value));

    var fixed_tax = $("#tourist_service").data("fixed-tax");
    var commissionAgency = parseInt($("#commissionAgency").val());
    var completePayment = parseInt($("#completePayment").val());

    var summatoryTax = parseFloat(total_price_var)  + parseFloat(fixed_tax) + parseFloat(tourist_service);
    var agencyCommissionTax = parseFloat(summatoryTax * commissionAgency/100);
    var totalCost = parseFloat(summatoryTax * 1.1);

    if(completePayment != 0)
        var prepayment = parseFloat(totalCost - agencyCommissionTax);
    else
        var prepayment = parseFloat(percent_value)  + parseFloat(fixed_tax) + parseFloat(tourist_service);

    $('#total_prepayment').html(normalize_prices(prepayment));
    $('#total_price').html( normalize_prices(totalCost) );

    $("#commissionPercent").html(percent);
    $("#totalNightsToShow").html(totalNights);
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
function reservationsBody()
{

    $('#rooms_selected > tbody tr').each(function(){
        $(this).remove();
    });

    total_price_var=0;
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

                if($(this).attr('data_is_triple')==='true' && persons>=3)
                {
                    value=$(this).attr('data_total')*$(this).attr('data_curr') + (($(this).attr('data_curr')*$(this).attr('data_triple_recharge')) * (cont_array_dates -1));
                }
                else
                {
                    value=$(this).attr('data_total')*$(this).attr('data_curr');
                }
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
            if($(this).attr('data_is_triple')==='true' && persons>=3)
            {
                value=$(this).attr('data_total')*$(this).attr('data_curr') +(($(this).attr('data_curr')*$(this).attr('data_triple_recharge')) * (cont_array_dates -1)) ;
            }
            else
            {
                value=$(this).attr('data_total')*$(this).attr('data_curr');
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
    });
}

function normalize_prices(price)
{
    return Math.round(price * Math.pow(10,2))/Math.pow(10,2);
}