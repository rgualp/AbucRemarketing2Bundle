reservations_in_details();
function reservations_in_details()
{
    total_price_var=0;
    $('.guest_number').change(function(){
        if($('#tr_'+$(this).attr('data')).html()){
            if(eval($('#combo_guest_'+$(this).attr('data')).val())+eval($('#combo_kids_'+$(this).attr('data')).val())==0)
            {

                $('#tr_'+$(this).attr('data')).remove();
                if ($('#rooms_selected >tbody >tr').length === 0){
                    $('#rooms_selected').css({display: 'none'});
                    $('.calendar-results').css({display: 'none'});
                }
                else
                {
                    total_price($(this).attr('data_curr'));
                }
            }
            else
            {
                value=0;
                persons=parseInt($('#combo_kids_'+$(this).attr('data')).val()) + parseInt($('#combo_guest_'+$(this).attr('data')).val());

                if($(this).attr('data_type_room')==='Habitación Triple' && persons>=3)
                {
                    value=this.parentNode.parentNode.cells[2].innerHTML*(cont_array_dates-1) + (($(this).attr('data_curr')*10) * (cont_array_dates -1));

                }
                else
                {
                    value=this.parentNode.parentNode.cells[2].innerHTML*(cont_array_dates-1);
                }
                $('#guest_'+$(this).attr('data')).html($('#combo_guest_'+$(this).attr('data')).val());
                $('#kids_'+$(this).attr('data')).html($('#combo_kids_'+$(this).attr('data')).val());
                $('#price_'+$(this).attr('data')).html(value);

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
                value=$(this).attr('data_total')*$(this).attr('data_curr') +(($(this).attr('data_curr')*10) * (cont_array_dates -1)) ;
            }
            else
            {
                value=$(this).attr('data_total')*$(this).attr('data_curr');
            }

            value=Math.round(value * Math.pow(10,2))/Math.pow(10,2);

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

            from_date=$('#data_reservation').attr('from_date');
            to_date=$('#data_reservation').attr('to_date');

            string_url=from_date+'/'+to_date+'/'+ids_rooms+'/'+count_guests+'/'+count_kids;
            $('#data_reservation').val(string_url);
            $('#total_price').html(total_price_var );
            $('#subtotal_price').html(total_price_var);
            percent_value=total_price_var * percent / 100;
            $('#initial_deposit').html(percent_value);
            $('#total_prepayment').html(percent_value + 10*curr);
            $('.calendar-results').css({display: 'block'});
        }
    });
}