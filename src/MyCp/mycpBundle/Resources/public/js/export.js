$(document).ready(init);

function init()
{
    $('#select_all_cont').click(selectAll);
    $('#unselect_all_cont').click(unselectAll);
    $('#invert_selection_cont').click(invertSelection);
    $('#del_cont').click(deleteFromList);
    $('.data_ex').click(selectOption);

    search();

}

function selectAll()
{
    $('#selected_cont input').attr('checked', true);
    return false;
}

function unselectAll()
{
    $('#selected_cont input').attr('checked', false);
    return false;
}

function invertSelection()
{
    $('#selected_cont input').each(function()
    {
        var option = $(this);
        if (option.attr('checked') === true)
        {
            option.attr('checked', false);
        }
        else
        {
            option.attr('checked', true);
        }
    });

    return false;
}

function deleteFromList()
{
    var selectedOptions = $('#selected_cont li').filter(IsSelectedOptionLi);

    selectedOptions.remove();

    var options = $('#selected_cont li');
    options.removeClass();
    $('#selected_cont li:even').addClass('even');
    $('#selected_cont li:odd').addClass('odd');

    return false;
}

function IsSelectedOption()
{
    return $(this).attr('checked') === true;
}

function UnSelectedOption()
{
    return $(this).attr('checked') === false;
}

function IsSelectedOptionLi()
{
    return $(this).children('div').children('.users_table').children('tbody').children('tr').children('td.checked_column').children('input.check_ex').attr('checked');
}

function IsSelected()
{
    return $(this).attr('selected') === true;
}

function selectOption()
{
    var check = $(this).parent('td').prev('td').children('input');

    if (check.attr('checked') == true)
    {
        check.attr('checked', false);
    }
    else
    {
        check.attr('checked', true);
    }
}

function search()
{
    var url = $("#selected_cont").attr('data-url');

    disabledTable();

    $.post(url,null,
    function(data)
    {
        processGetResponse(data);
    });
    return false;
}

function processGetResponse(data)
{
    var list = $(data);
    list.children('div').children('.users_table').children('tbody').children('tr').children('td.data_column').children('div.data_ex').click(selectOption);
    $('#selected_cont .cont_list').append(list);

    $('#selected_cont .cont_list li').removeClass();
    $('#selected_cont .cont_list li:even').addClass('even');
    $('#selected_cont .cont_list li:odd').addClass('odd');

    enabledTable();
}

function disabledTable()
{
    $('#tabs-contacts .search_btn').attr('disabled', true);
    $('#selected_cont .disable_table_cont').css('visibility', 'visible');
    $('#selected_cont .load_table_cont').css('visibility', 'visible');
}

function enabledTable()
{
    $('#tabs-contacts .search_btn').attr('disabled', false);
    $('#selected_cont .disable_table_cont').css('visibility', 'hidden');
    $('#selected_cont .load_table_cont').css('visibility', 'hidden');
}

