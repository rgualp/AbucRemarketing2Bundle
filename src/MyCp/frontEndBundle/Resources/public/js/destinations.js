$(document).ready(start)

function start(){
    $('#dll_provinces').change(filter_by_location);
}

function filter_by_location()
{
    var province = $('#dll_provinces').val();
    //var municipality =$('#dll_municipalities').val();
    var url=$('#dll_provinces').attr('data-url-filter');
    var result=$('#div_result');
    province = (province != -1) ? province: null;
    
    var municipality = null

    show_loading();
    $.post(url,{
        'province':province,
        'municipality': municipality
    },function(data){
        result.html(data);
        hide_loading();
    });
    return false;
}

function show_loading()
{
    $('#loading').removeClass('hidden');
}

function hide_loading()
{
    $('#loading').addClass('hidden');

}
