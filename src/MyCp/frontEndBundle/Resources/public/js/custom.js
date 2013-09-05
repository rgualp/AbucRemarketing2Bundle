$(document).ready(start)

function start(){
    $('.numeric').keydown(function(e) {
        $('#log').text('keyCode: ' + e.keyCode);
        if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 8 && e.keyCode != 9)
            e.preventDefault();
    });
    
    currency_change(); 
    manage_favorities(".favorite_off"); 
    manage_favorities(".favorite_on");
}

function currency_change()
{
    $(".currency_link").click(function(){
        var url = $(this).attr('data-url');
        var refresh_url = $(this).attr('data-refresh-url');
        $.post(url,
        {
            'curr_id':$(this).attr('data-currency-id'),
            'refresh_url' : $(this).attr('data-refresh-url')
        }
        ,function(data){
            window.location = refresh_url;
        });
    });  
}

function manage_favorities(favorite_class)
{
    $(favorite_class).click(function(){
        var url = $(this).attr('data-url');
        var favorite_type = $(this).attr('data-favorite-type');
        var element_id = $(this).attr('data-element-id');
        var list_preffix = $(this).attr('data-list-preffix');
        var result_id = "favorite_" + favorite_type + "_" + element_id;
        
        $.post(url,
        {
            'favorite_type':favorite_type,
            'element_id' : element_id,
            'list_preffix' : list_preffix
        }
        ,function(data){
            $("."+result_id).html(data);
            manage_favorities(".favorite_off"); 
            manage_favorities(".favorite_on");
        });
    });  
}

