$(document).ready(start)

/**
* Este lo modifique
*/
function start(){
    $('.top20_items_per_page').click(function()
    {
        
       $('.top_rated_tools li.active').removeClass('active');
       $(this).addClass('active'); 
       var show_rows = $(this).attr('data-content-value');
       $('#items_per_page').html(show_rows);
       
       visualize_rows(show_rows);
    });
    
    $('.top_rated_tools .paginator-cont a').click(do_paginate);
}

function visualize_rows(show_rows)
{
    var url = $("#top_rated_placeholder").attr("data-url");
    var result = $("#top_rated_placeholder");
    
    show_loading();
    $.post(url,{
            'show_rows':show_rows
        },function(data){
            result.html(data);            
            start();
            hide_loading();
        });
}

function do_paginate()
{
     var url = $("#top_rated_placeholder").attr("data-url");
    var result = $("#top_rated_placeholder");
    
    //show_loading();
    $.post(url,null,function(data){
            result.html(data);
            //hide_loading();
            start();
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
