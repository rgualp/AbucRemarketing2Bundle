$(document).ready(start);

/**
 * Este lo modifique
 */
function start() {
    $('.top20_items_per_page').click(function()
    {
        $('ul.top_rated_items_per_page li.active').removeClass('active');
        $(this).addClass('active');
        var show_rows = $(this).attr('data-content-value');
        visualize_rows(show_rows);
    });

    $('.top20_category').click(function()
    {
        var category;

        /*if ($(this).hasClass('active'))
        {
            category = '';
            $(this).removeClass('active');
        }
        else
        {*/
            $('ul.top_rated_category li.active').removeClass('active');
            $(this).addClass('active');
            category = $(this).attr('data-content-value');
        //}

        change_category(category);
    });

    $('.top_rated_tools .paginator-cont a').click(do_paginate);
}

function change_category(category)
{
    var url = $("#top_rated_placeholder").attr("data-url-change-category");
    var result = $("#top_rated_placeholder");

    show_loading();
    $.post(url, {
        'category': category
    }, function(data) {
        result.html(data);
        $("[rel='tooltip']").tooltip();
        start();
        hide_loading();
    });
}

function visualize_rows(show_rows)
{
    var url = $("#top_rated_placeholder").attr("data-url");
    var result = $("#top_rated_placeholder");

    show_loading();
    $.post(url, {
        'show_rows': show_rows
    }, function(data) {
        result.html(data);
        $("[rel='tooltip']").tooltip();
        start();
        hide_loading();
    });
}

function do_paginate()
{
    var url = $("#top_rated_placeholder").attr("data-url");
    var result = $("#top_rated_placeholder");

    //show_loading();
    $.post(url, null, function(data) {
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
