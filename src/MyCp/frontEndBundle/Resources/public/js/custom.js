$(document).ready(start)

function start(){
    $('.numeric').keydown(function(e) {
        $('#log').text('keyCode: ' + e.keyCode);
        if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 8 && e.keyCode != 9)
            e.preventDefault();
    });
    
    currency_change();    
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
