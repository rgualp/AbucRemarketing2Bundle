reservations_in_details();
function total_price(curr,percent)
{
    real_price=0;
    total_price_var=0;
    rooms_price='';
    $('.price-room').each(function() {
        total_price_var=total_price_var+parseFloat(this.innerHTML);
        real_price=real_price+parseFloat(this.innerHTML/curr);
    });

    ids_rooms='';
    $('.id_room').each(function() {
        ids_rooms=ids_rooms+'&'+(this.innerHTML);});

    count_guests='';
    $('.guest').each(function() {
        count_guests=count_guests+'&'+(this.innerHTML);});

    count_kids='';
    $('.kids').each(function() {
        count_kids=count_kids+'&'+(this.innerHTML);});

    count_kids_age1='';
    $('.kidsAge1').each(function() {
        count_kids_age1=count_kids_age1+'&'+(this.innerHTML);});

    count_kids_age2='';
    $('.kidsAge2').each(function() {
        count_kids_age2=count_kids_age2+'&'+(this.innerHTML);});

    count_kids_age3='';
    $('.kidsAge3').each(function() {
        count_kids_age3=count_kids_age3+'&'+(this.innerHTML);});

    from_date=$('#data_reservation').attr('from_date');
    to_date=$('#data_reservation').attr('to_date');

    string_url=from_date+'/'+to_date+'/'+ids_rooms+'/'+count_guests+'/'+count_kids +'/'+count_kids_age1 +'/'+count_kids_age2 +'/'+count_kids_age3;
    $('#data_reservation').val(string_url);
    $('#total_price').html( normalize_prices(total_price_var) );
    $('#subtotal_price').html(normalize_prices(total_price_var));
    percent_value=total_price_var * percent / 100;
    $('#initial_deposit').html(normalize_prices(percent_value));
    $('#pay_at_service').html(normalize_prices(total_price_var - percent_value));
    $('#total_prepayment').html(normalize_prices(percent_value + 10*curr));
    $('.calendar-results').css({display: 'block'});
}
function reservations_in_details()
{

    $('#rooms_selected > tbody tr').each(function(){
        $(this).remove();
    });

    total_price_var=0;
    $('.guest_number').change(function(){
        if($('#tr_'+$(this).attr('data')).html()){

            //Mostrar varios combos para seleccionar la edad
            var children =  parseInt($('#combo_kids_'+$(this).attr('data')).val());

            /*$("#childrenImg1_"+$(this).attr('data')).css({display: 'none'});
            $("#childrenAge1_"+$(this).attr('data')).css({display: 'none'});

            $("#childrenImg2_"+$(this).attr('data')).css({display: 'none'});
            $("#childrenAge2_"+$(this).attr('data')).css({display: 'none'});

            $("#childrenImg3_"+$(this).attr('data')).css({display: 'none'});
            $("#childrenAge3_"+$(this).attr('data')).css({display: 'none'});

            if(children != 0)
                $("#childreAgeTh").css({display: 'block'});

            switch (children){
                case 1:{
                    $("#childrenImg1_"+$(this).attr('data')).css({display: 'block'});
                    $("#childrenAge1_"+$(this).attr('data')).css({display: 'block'});
                    break;
                }
                case 2:{
                    $("#childrenImg1_"+$(this).attr('data')).css({display: 'block'});
                    $("#childrenAge1_"+$(this).attr('data')).css({display: 'block'});

                    $("#childrenImg2_"+$(this).attr('data')).css({display: 'block'});
                    $("#childrenAge2_"+$(this).attr('data')).css({display: 'block'});
                    break;
                }
                case 3:{
                    $("#childrenImg1_"+$(this).attr('data')).css({display: 'block'});
                    $("#childrenAge1_"+$(this).attr('data')).css({display: 'block'});

                    $("#childrenImg2_"+$(this).attr('data')).css({display: 'block'});
                    $("#childrenAge2_"+$(this).attr('data')).css({display: 'block'});

                    $("#childrenImg3_"+$(this).attr('data')).css({display: 'block'});
                    $("#childrenAge3_"+$(this).attr('data')).css({display: 'block'});
                    break;
                }
                default:{
                    $("#childrenImg1_"+$(this).attr('data')).css({display: 'none'});
                    $("#childrenAge1_"+$(this).attr('data')).css({display: 'none'});

                    $("#childrenImg2_"+$(this).attr('data')).css({display: 'none'});
                    $("#childrenAge2_"+$(this).attr('data')).css({display: 'none'});

                    $("#childrenImg3_"+$(this).attr('data')).css({display: 'none'});
                    $("#childrenAge3_"+$(this).attr('data')).css({display: 'none'});
                    break;
                }
            }*/

            if(eval($('#combo_guest_'+$(this).attr('data')).val())+eval($('#combo_kids_'+$(this).attr('data')).val())==0)
            {
                $('#tr_'+$(this).attr('data')).remove();
                if ($('#rooms_selected >tbody >tr').length == 0){
                    $('#rooms_selected').css({display: 'none'});
                    $('.calendar-results').css({display: 'none'});
                }
                else
                {
                    total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));
                }
            }
            else
            {
                value=0;
                persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());

                if($(this).attr('data_type_room')==='Habitación Triple' && persons>=3)
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
                total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));

            }
        }
        else
        {
            value=0;
            real_value=0;
            persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());
            if($(this).attr('data_type_room')==='Habitación Triple' && persons>=3)
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
                '<td>1</td>' +
                '<td class="guest" id="guest_'+$(this).attr('data')+'">'+$('#combo_guest_'+$(this).attr('data')).val()+'</td>'+
                '<td class="kids" id="kids_'+$(this).attr('data')+'">'+$('#combo_kids_'+$(this).attr('data')).val()+'</td>'+
                '<td class="kidsAge1" id="kidsage1_'+$(this).attr('data')+'" style="display:none">'+$('#combo_kidsage_1_'+$(this).attr('data')).val()+'</td>'+
                '<td class="kidsAge2" id="kidsage2_'+$(this).attr('data')+'" style="display:none">'+$('#combo_kidsage_2_'+$(this).attr('data')).val()+'</td>'+
                '<td class="kidsAge3" id="kidsage3_'+$(this).attr('data')+'" style="display:none">'+$('#combo_kidsage_3_'+$(this).attr('data')).val()+'</td>'+
                '<td class="price-room" id="price_'+$(this).attr('data')+'">'+value+'</td>');

            total_price($(this).attr('data_curr'),$(this).attr('percent_charge'));
        }
    });
}

function normalize_prices(price)
{
    return Math.round(price * Math.pow(10,2))/Math.pow(10,2);
}
