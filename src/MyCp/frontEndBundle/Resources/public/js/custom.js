$(document).ready(start)

function start(){
    $('.numeric').keydown(function(e) {
        $('#log').text('keyCode: ' + e.keyCode);
        if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 8 && e.keyCode != 9)
            e.preventDefault();
    });
    
    currency_change(); 
    language_change();
    manage_favorities(".favorite_off_action"); 
    manage_favorities(".favorite_on_action");
    
    details_favorites("#delete_from_favorites");
    details_favorites("#add_to_favorites");
    
    delete_from_list_favorites();
}

function language_change()
{
    $(".language_link").click(function(){
        var url = $(this).attr('data-url');
        var refresh_url = $(this).attr('data-refresh-url');
        var new_lang_code = $(this).attr('data-new-lang-code');
        var current_lang_code = $(this).attr('data-current-lang-code');
        $.post(url,
        {
            'lang_code':$(this).attr('data-new-lang-code'),
            'lang_name' : $(this).attr('data-lang-name')
        }
        ,function(data){
            window.location = refresh_url.replace("/"+current_lang_code+"/","/"+new_lang_code+"/") ;
        });
    });  
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
            
            manage_favorities(".favorite_off_action"); 
            manage_favorities(".favorite_on_action");
        });
    });  
}


function details_favorites(favorite_button)
{
    $(favorite_button).click(function(){
        var url = $(this).attr('data-url');
        var favorite_type = $(this).attr('data-type');
        var element_id = $(this).attr('data-id');
        
        $.post(url,
        {
            'favorite_type':favorite_type,
            'element_id' : element_id
        }
        ,function(data){
            $(".favorites_details").html(data);
            
            details_favorites("#delete_from_favorites");
            details_favorites("#add_to_favorites");
        });
    });  
}

function delete_from_list_favorites()
{
    $(".delete_from_favorite_list").click(function(){
        var url = $(this).attr('data-url');
        var favorite_type = $(this).attr('data-favorite-type');
        var element_id = $(this).attr('data-element-id');
        
        show_loading();
        $.post(url,
        {
            'favorite_type':favorite_type,
            'element_id' : element_id
        }
        ,function(data){
            if(favorite_type == "ownership")
                $("#div_result").html(data);
            else if(favorite_type == "destination")
                $("#div_result_destinations").html(data);
            delete_from_list_favorites();
            hide_loading();
        });
    }); 
}

function show_loading()
{
    $('#loading').removeClass('hidden');
}

function hide_loading()
{
    $('#loading').addClass('hidden');

}

