reservationsBody();
function totalPrice(curr,percent, totalNights)
{
    console.log('backendjs');
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
                tourist_fee_percent = $("#tourist_service").data("one-nr-until-20-percent");
            else if(avgPrice >= 20*curr && avgPrice < 25*curr)
                tourist_fee_percent = $("#tourist_service").data("one-nr-from-20-to-25-percent");
            else if(avgPrice >= 25*curr)
                tourist_fee_percent = $("#tourist_service").data("one-nr-from-more-25-percent");
        }
        else
            tourist_fee_percent = $("#tourist_service").data("one-night-several-rooms-percent");
    }
    else if(totalNights == 2)
        tourist_fee_percent = $("#tourist_service").data("one-2-nights-percent");
    else if(totalNights == 3)
        tourist_fee_percent = $("#tourist_service").data("one-3-nights-percent");
    else if(totalNights == 4)
        tourist_fee_percent = $("#tourist_service").data("one-4-nights-percent");
    else if(totalNights >= 5)
        tourist_fee_percent = $("#tourist_service").data("one-5-nights-percent");


    $('#data_reservation').val(string_url);

    $('#subtotal_price').html(normalize_prices(total_price_var));
    var percent_value=total_price_var * percent / 100;
    var tourist_service = total_price_var*tourist_fee_percent;
    $("#tourist_service").html(normalize_prices(tourist_service));
    $('#initial_deposit').html(normalize_prices(percent_value));
    $('#pay_at_service').html(normalize_prices(total_price_var - percent_value));

    var fixed_tax = $("#tourist_service").data("fixed-tax");
    var prepayment = parseFloat(percent_value)  + parseFloat(fixed_tax) + parseFloat(tourist_service);
    console.log(prepayment);
    if(nights >= 4){
        var discount=total_price*0.1;
        prepayment=prepayment-discount;
        total_price=total_price-discount;
        $('#discount-amount').html(normalize_prices(discount));

        $('#discount').removeClass('d-none').addClass('d-flex');

    }
    $('#total_price').html(normalize_prices(total_price));
    $('#total_prepayment').html(normalize_prices(prepayment));

    $("#commissionPercent").html(percent);
    $("#totalNightsToShow").html(totalNights);
    $('.calendar-results').css({display: 'block'});

    if(checkTotalPrice) {
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
    else{
        $("#btn_submit").removeAttr("disabled");
        $(".btn_submit").removeAttr("disabled");
        $("#error").css({display: 'none'});
        $(".all-prices-numbers").removeClass("error");
    }
}

function isUp2(name, id)
{
    var name = "#" + name;
    var doubleUp2 = $(name).attr("data-up-2");

    if(doubleUp2 == "1"){
        var guests = $(name).val();
        var otherName = (name == "#combo_guest_" + id) ? "#combo_kids_" + id : "#combo_guest_" + id;

        if(guests == 1)
        {
            $(otherName + " option[value='2']").remove();
        }
        else if(guests == 2){
            $(otherName + " option[value='1']").remove();
            $(otherName + " option[value='2']").remove();
        }
        else
        {
            $(otherName).empty();
            $(otherName).append('<option value="0">0</option>');
            $(otherName).append('<option value="1">1</option>');
            $(otherName).append('<option value="2">2</option>');

            $(name).empty();
            $(name).append('<option value="0">0</option>');
            $(name).append('<option value="1">1</option>');
            $(name).append('<option value="2">2</option>');
        }
    }
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
                    console.log("Antes de ver el up 2");
                    isUp2($(this).attr("name"), $(this).attr('data'));
                    totalPrice($(this).attr('data_curr'),$(this).attr('percent_charge'), $("#totalNights").val());
                }
            }
            else
            {

                value=0;
                persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());

                console.log("Antes de ver el up 2");
                isUp2($(this).attr("name"), $(this).attr('data'));

                if(($(this).attr('data_is_triple')==='true' || $(this).attr('data_is_triple')==='1') && persons>=3)
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
            if(($(this).attr('data_is_triple')==='true' || $(this).attr('data_is_triple')==='1') && persons>=3)
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
        console.log($("#totalNights").val());
        if($("#totalNights").val()>=4){
            $('#discount-cotent').removeClass('d-none').addClass('d-flex');
        }
    });

}

function normalize_prices(price)
{
    return Math.round(price * Math.pow(10,2))/Math.pow(10,2);
}