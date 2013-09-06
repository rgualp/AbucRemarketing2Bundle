$(document).ready(start)

/**
* Este lo modifique
*/
function start(){
    $('#select_provinces').change(load_municipalities);
    
    var auto_disabled = $('#select_municipalities').attr('data-disabled');
    
    if(auto_disabled != null && auto_disabled == "true")
        $('#select_municipalities').attr("disabled","disabled");
    else
        $('#select_municipalities').change(filter_by_location);
}

/**
* Este lo agregue
*/
function load_municipalities()
{
    var province = $('#select_provinces').val();
    if(province != -1)
    {
        show_loading();
        var url=$('#select_provinces').attr('data-url');
        var result=$('#div_municipalities');

        $.post(url,{
            'province':province
        },function(data){
            result.html(data);
            if(document.getElementById('select_municipalities') != null)
                document.getElementById('select_municipalities').disabled =  province == -1;
            $('#select_municipalities').change(filter_by_location);
            filter_by_location();
            hide_loading();
        });
    }
    else
        {
            $('#select_municipalities').val(-1);
            $('#select_municipalities').attr("disabled","disabled");
            filter_by_location();
        }
        

    return false;
}

/**
* Este lo modifique
*/
function filter_by_location()
{
    var province = $('#select_provinces').val();
    var municipality =$('#select_municipalities').val();
    var url=$('#select_provinces').attr('data-url-filter');
    var result=$('#div_result');
    province = (province != -1) ? province: null;
    
    municipality = (municipality != -1) ? municipality: null;

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
