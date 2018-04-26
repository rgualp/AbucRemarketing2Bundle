reservations_in_details();
function total_price(curr,percent)
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

    var count_kids_age1='';
    $('.guest_age_1').each(function(){
        count_kids_age1=count_kids_age1+'&'+$(this).val();
        //console.log("Age1:" + $(this).val());
    });

    var count_kids_age2='';
    $('.guest_age_2').each(function(){
        count_kids_age2=count_kids_age2+'&'+$(this).val();
        //console.log("Age2:" + $(this).val());
    });

    var count_kids_age3='';
    $('.guest_age_3').each(function(){
        count_kids_age3=count_kids_age3+'&'+$(this).val();
        //console.log("Age3:" + $(this).val());
    });

    var from_date=$('#data_reservation').attr('from_date');
    var to_date=$('#data_reservation').attr('to_date');

    var string_url=from_date+'/'+to_date+'/'+ids_rooms+'/'+count_guests+'/'+count_kids +'/'+count_kids_age1 +'/'+count_kids_age2 +'/'+count_kids_age3;

    var nights = $("#totalNights").val();
    var avgPrice = normalize_prices(total_price_var / nights);
    var tourist_fee_percent = 0;
    var roomsTotal = $('.id_room').size();

    //nights = nights * roomsTotal;

    if(nights == 1)
    {
        if(roomsTotal == 1)
        {
            if(avgPrice < 20*curr )
                tourist_fee_percent = $("#tourist_service").attr("data-one-nr-until-20-percent");
            else if(avgPrice >= 20*curr && avgPrice < 25*curr)
                tourist_fee_percent = $("#tourist_service").attr("data-one-nr-from-20-to-25-percent");
            else if(avgPrice >= 25*curr)
                tourist_fee_percent = $("#tourist_service").attr("data-one-nr-from-more-25-percent");
        }
        else
            tourist_fee_percent = $("#tourist_service").attr("data-one-night-several-rooms-percent");
    }
    else if(nights == 2)
        tourist_fee_percent = $("#tourist_service").attr("data-one-2-nights-percent");
    else if(nights == 3)
        tourist_fee_percent = $("#tourist_service").attr("data-one-3-nights-percent");
    else if(nights == 4)
        tourist_fee_percent = $("#tourist_service").attr("data-one-4-nights-percent");
    else if(nights >= 5)
        tourist_fee_percent = $("#tourist_service").attr("data-one-5-nights-percent");


    $('#data_reservation').val(string_url);
    $('#accommodation_price').html( normalize_prices(total_price_var) );
    $('#subtotal_price').html(normalize_prices(total_price_var));
    var percent_value=total_price_var * percent / 100;
    var tourist_service = total_price_var*parseFloat(tourist_fee_percent);
    var fixed_tax = parseFloat($("#tourist_service").data("fixed-tax")) *curr;
    $("#tourist_service").html(normalize_prices(tourist_service));
    var total_price = total_price_var + tourist_service + fixed_tax;
    $('#total_price').html(normalize_prices(total_price));
    var pay_at_service = total_price_var - percent_value;
    $('#pay_at_service').html(normalize_prices(pay_at_service));
    $("#pay_at_service_cuc").html("CUC " + normalize_prices(pay_at_service/curr));


    var prepayment = percent_value + fixed_tax + tourist_service;
    /*console.log("Porciento" + percent_value);
    console.log("Turista" + tourist_service);
    console.log("Tarifa fija" + fixed_tax);
    console.log(prepayment);*/
    $('#total_prepayment').html(normalize_prices(prepayment));
    $('.calendar-results').css({display: 'block'});

}

function showAgesCombos(rowId)
{
    //Mostrar varios combos para seleccionar la edad
    var children =  parseInt($('#combo_kids_'+rowId).val());

    $("#childrenImg1_"+rowId).css({display: 'none'});
    $("#childrenAge1_"+rowId).css({display: 'none'});

    $("#childrenImg2_"+rowId).css({display: 'none'});
    $("#childrenAge2_"+rowId).css({display: 'none'});

    $("#childrenImg3_"+rowId).css({display: 'none'});
    $("#childrenAge3_"+rowId).css({display: 'none'});

    if(isCompletePayment) {
        if (children != 0)
            $("#childreAgeTh").css({display: 'table-cell'});

        switch (children) {
            case 1:
            {
                $("#childrenImg1_" + rowId).css({display: 'table-cell'});
                $("#childrenAge1_" + rowId).css({display: 'table-cell'});
                break;
            }
            case 2:
            {
                $("#childrenImg1_" + rowId).css({display: 'table-cell'});
                $("#childrenAge1_" + rowId).css({display: 'table-cell'});

                $("#childrenImg2_" + rowId).css({display: 'table-cell'});
                $("#childrenAge2_" + rowId).css({display: 'table-cell'});
                break;
            }
            case 3:
            {
                $("#childrenImg1_" + rowId).css({display: 'table-cell'});
                $("#childrenAge1_" + rowId).css({display: 'table-cell'});

                $("#childrenImg2_" + rowId).css({display: 'table-cell'});
                $("#childrenAge2_" + rowId).css({display: 'table-cell'});

                $("#childrenImg3_" + rowId).css({display: 'table-cell'});
                $("#childrenAge3_" + rowId).css({display: 'table-cell'});
                break;
            }
            default:
            {
                $("#childrenImg1_" + rowId).css({display: 'none'});
                $("#childrenAge1_" + rowId).css({display: 'none'});

                $("#childrenImg2_" + rowId).css({display: 'none'});
                $("#childrenAge2_" + rowId).css({display: 'none'});

                $("#childrenImg3_" + rowId).css({display: 'none'});
                $("#childrenAge3_" + rowId).css({display: 'none'});
                break;
            }
        }
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

function reservations_in_details()
{
    $('#rooms_selected > tbody tr').each(function(){
        $(this).remove();
    });

    total_price_var=0;
    $('.guest_age').change(function(){
        total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));
    });
    $('.guest_number').change(function(){

        isUp2($(this).attr("name"), $(this).attr('data'));
        showAgesCombos($(this).attr('data'));

        if($('#tr_'+$(this).attr('data')).html()){

            if(eval($('#combo_guest_'+$(this).attr('data')).val())+eval($('#combo_kids_'+$(this).attr('data')).val())==0)
            {
                $('#tr_'+$(this).attr('data')).remove();
                $('#tripleAlert_' + $(this).attr('data')).css({display: 'none'});
                if ($('#rooms_selected >tbody >tr').length == 0){
                    $('#rooms_selected').css({display: 'none'});
                    $('.calendar-results').css({display: 'none'});
                }
                else
                {
                    console.log("Antes de ver el up 2");
                    isUp2($(this).attr("name"), $(this).attr('data'));
                    total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));
                }
            }
            else
            {
                value=0;
                persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());
                console.log("Antes de ver el up 2");
                isUp2($(this).attr("name"), $(this).attr('data'));
                if(($(this).attr('data_is_triple')==='1' || $(this).attr('data_is_triple')==='true') && persons>=3 && !isCompletePayment)
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
                total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));

            }
        }
        else
        {
            value=0;
            real_value=0;
            persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());
            if(($(this).attr('data_is_triple')==='1' || $(this).attr('data_is_triple')==='true') && persons>=3 && !isCompletePayment)
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
                '<td>1</td>' +
                '<td class="guest" id="guest_'+$(this).attr('data')+'">'+$('#combo_guest_'+$(this).attr('data')).val()+'</td>'+
                '<td class="kids" id="kids_'+$(this).attr('data')+'">'+$('#combo_kids_'+$(this).attr('data')).val()+'</td>'+
                '<td class="price-room" id="price_'+$(this).attr('data')+'">'+value+'</td>');

            total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));
        }
    });
}

function normalize_prices(price)
{
    //return (Math.round(price * Math.pow(10,2))/Math.pow(10,2));
    return parseFloat(Math.round(price*100)/100).toFixed(2);
}
