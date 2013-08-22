$(document).ready(start)

function start(){
    $('#select_faqCategory').change(filter_by_category);
}

function filter_by_category()
{
    var category_id = $('#select_faqCategory').val();
    //var municipality =$('#dll_municipalities').val();
    var url=$('#select_faqCategory').attr('data-url-filter');
    var result=$('#div_result');
    category_id = (category_id != -1) ? category_id: null;

    show_loading();
    $.post(url,{
        'category_id':category_id
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
