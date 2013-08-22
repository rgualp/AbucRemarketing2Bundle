$(document).ready(start)

function start(){

    $('#btn_insert_comment').click(insert_comment);
    initialize_map();
    $('#filter_date_from').datepicker({
        format:'dd/mm/yyyy',
        todayBtn:'linked',
        startDate: start_date
    }).on('changeDate', function(ev){
            $('.datepicker').hide();
    });


    $('#filter_date_to').datepicker({
        format:'dd/mm/yyyy',
        todayBtn:'linked',
        startDate: end_date
    }).on('changeDate', function(ev){
            $('.datepicker').hide();
        });

    // ernesto code
    total_price=0;
    $('.guest_number').change(function(){
        if($('#'+$(this).attr('name')).html()){
            if(this.value==0)
            {
                $('#tr_'+$(this).attr('name')).remove();
                if ($('#rooms_selected >tbody >tr').length == 0){
                    $('#rooms_selected').css({display: 'none'})
                    $('#button_availability').css({display: 'none'})
                }
            }
            else
            {
                value=0;
                if(this.parentNode.parentNode.cells[1].innerHTML=='Habitación Triple')
                {
                    value=this.value*this.parentNode.parentNode.cells[2].innerHTML*cont_array_dates+10;
                }
                else
                {
                    value=this.value*this.parentNode.parentNode.cells[2].innerHTML*cont_array_dates;
                }
                $('#'+$(this).attr('name')).html(this.value);
                $('#price_'+$(this).attr('name')).html(value);

                total_price=0;
                $('.price').each(function() {
                    total_price=total_price+parseInt(this.innerHTML)});

                ids_rooms='';
                $('.id_room').each(function() {
                    ids_rooms=ids_rooms+','+(this.innerHTML)});
                $('#ids_rooms').val(ids_rooms);

                count_guests='';
                $('.guest').each(function() {
                    count_guests=count_guests+','+(this.innerHTML)});
                $('#guests').val(count_guests);

                $('#total_price').html(total_price);
                $('#total_price_submit').val(total_price);

            }
        }
        else
        {
            value=0;
            if(this.parentNode.parentNode.cells[1].innerHTML=='Habitación Triple')
            {
                value=this.value*this.parentNode.parentNode.cells[2].innerHTML*cont_array_dates+10;
            }
            else
            {
                value=this.value*this.parentNode.parentNode.cells[2].innerHTML*cont_array_dates;
            }


            $('#rooms_selected').css({display: 'table'})
            $('#button_availability').css({display: 'block'})
            $('#rooms_selected > tbody:last').append('<tr id="tr_'+$(this).attr('name')+'">' +
                '<td class="id_room" style="display: none;">'+$(this).attr('data')+'</td>' +
                '<td>'+this.parentNode.parentNode.cells[0].innerHTML+'</td>' +
                '<td>'+this.parentNode.parentNode.cells[1].innerHTML+'</td>' +
                '<td>1</td>' +
                '<td class="guest" id="'+$(this).attr('name')+'">'+this.value+'</td>'+
                '<td class="price" id="price_'+$(this).attr('name')+'">'+value+'</td>');

            total_price=0;
            $('.price').each(function() {
                total_price=total_price+parseInt(this.innerHTML)});

            ids_rooms='';
            $('.id_room').each(function() {
                ids_rooms=ids_rooms+','+(this.innerHTML)});
            $('#ids_rooms').val(ids_rooms);

            count_guests='';
            $('.guest').each(function() {
                count_guests=count_guests+','+(this.innerHTML)});
            $('#guests').val(count_guests);

            $('#total_price').html(total_price);
            $('#total_price_submit').val(total_price);
        }
    });
    // fin ernesto code

}

function initialize_map()
{
    if(document.getElementById("big_map_details") != null)
    {
        var x = $("#big_map_details").attr("data-x");
        var y = $("#big_map_details").attr("data-y");
        var name = $("#big_map_details").attr("data-name");
        var description = $("#big_map_details").attr("data-description");
        var image = $("#big_map_details").attr("data-image");
        var icon = $("#big_map_details").attr("data-icon");
        
        var center_details = new google.maps.LatLng(x, y);//La Habana 23.09725, -82.37548
        var options_details = {
            zoom: 15,
            center: center_details,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var big_map_details = new google.maps.Map(document.getElementById("big_map_details"), options_details);
        
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(x,y),
            map: big_map_details,
            title: name//,
        //icon: icon
        });
        
        big_map_details.setCenter(marker.getPosition());
    
        var boxText = document.createElement("div");
        boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px";
        boxText.innerHTML = "<table><tr><td class='map_image' style='background-image:url("+image+")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>"+name+"<br/><b>" + description + "</b></td></tr></table>";
        
        var myOptions = {
                 content: boxText
                ,disableAutoPan: false
                ,maxWidth: 0
                ,pixelOffset: new google.maps.Size(-140, 0)
                ,zIndex: null
                ,boxStyle: { 
                  background: "url('tipbox.gif') no-repeat"
                  ,opacity: 0.85
                  ,width: "280px"
                 }
                ,closeBoxMargin: "10px 2px 2px 2px"
                ,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
                ,infoBoxClearance: new google.maps.Size(1, 1)
                ,isHidden: false
                ,pane: "floatPane"
                ,enableEventPropagation: false
        };
        
        var ib = new InfoBox(myOptions);

        google.maps.event.addListener(marker, 'mouseover', function() {
            ib.open(big_map_details, marker);
        });
    }
}

function insert_comment ()
{    
    show_element($('#loading'));
    hide_element($('#message_text'));
    
    var result=$('#user_comments');
    var url=$('#btn_insert_comment').attr('data-url');
    
    var user_name = $('#input_name').val();
    var user_email = $('#input_email').val();
    var comment = $('#input_comment').val();    
    var rating = $('input[name=radio_rating]:checked').val();
        
    if(validate())
    {
        $.post(url,{
            'com_user_name':user_name,
            'com_email':user_email,
            'com_comments':comment,
            'com_rating':rating
        },function(data){
            result.html(data);      
            clear_controls();
            hide_element($('#loading'));
            $('#message_text').html($('#message_text').attr('ok-message')); 
            show_element($('#message_text'));
            
            refresh_rating();
        });
    }
    else
    {
        hide_element($('#loading'));
        show_element($('#message_text'));
    }
    
    return false;
}

function refresh_rating()
{
    var result=$('#ratings');
    var url=$('#ratings').attr('data-url');
    
    $.post(url,{
        'nothig':true
    },function(data){
        result.html(data); 
    });
        
    return false;
}

function show_element(element)
{
    element.removeClass('hidden');
}

function hide_element(element)
{
    element.addClass('hidden');
}

function clear_controls()
{
    $('#input_name').val('');
    $('#input_email').val('');
    $('#input_comment').val('');
    $('input[name=radio_rating]').filter('[value=5]').attr('checked', 'checked');
}

function validate()
{
    var user_name = $('#input_name').val();
    var user_email = $('#input_email').val();
    var comment = $('#input_comment').val();
    var valid = true;
    var error_text= '';
    
    if(user_name == '')
    {
        valid = false;
        error_text += $('#input_name').attr("requiered-message") + ' <br/>';            
    }
        
    if(user_email == '')
    {
        valid = false;
        error_text += $('#input_email').attr("requiered-message") +  ' <br/>';
            
    }    
    else if(!valid_email(user_email))
    {
        valid = false;
        error_text += $('#input_email').attr("invalid-message") +  ' <br/>';
    }
        
    if(comment == '')
    {
        valid = false;
        error_text += $('#input_comment').attr("requiered-message") +  ' <br/>';
            
    }
    
    if(error_text != '')
    {
        $('#message_text').html(error_text);  
    }
    
    
    return valid;
}

function valid_email(email_text){
    if( !(/\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(email_text)) ) {
        return false;
    }
    return true;
}

