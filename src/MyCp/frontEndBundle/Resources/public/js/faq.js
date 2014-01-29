$(document).ready(start);

function start() {
    $('#faq_categories li').click(function() {
        $('#faq_categories li').removeClass("active");
        $(this).addClass("active");
        var category_id = $(this).attr('data-value');
        //var municipality =$('#dll_municipalities').val();
        var url = $('#faq_categories').attr('data-url-filter');
        var result = $('#div_result');
        category_id = (category_id != -1) ? category_id : null;

        show_loading();
        $.post(url, {
            'category_id': category_id
        }, function(data) {
            result.html(data);
            hide_loading();
        });
        return false;
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
