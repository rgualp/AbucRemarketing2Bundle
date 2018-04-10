$(document).ready(start_s);
function start_s() {
    var page = $('#div_result').attr("data-current-page");
    //Buscador que esta encima de los filtros
    $('#btn_search').click(function(){
        $( "#div_result" ).empty();
         research(1);
    });
    $('#loadmore').click(function(){
        research(parseInt(page)+1);
    });
    research(1);



}

function research(_page)
{
   HoldOn.open();

    var url = $('#div_result').attr('data-url');
    var result = $('#div_result');

    if (_page == -1){
        var page = result.attr("data-current-page");
    }else{
        var page = _page;
    }

    var checked_filters = load_upper_filters(page);

    $.post(url, checked_filters, function(data) {

        result.attr("data-cant-page", data.cant_pages);

        if (page == 1 && page == parseInt(data.cant_pages)){
            otherpage = 1;
            result.empty();
            result.append(data.html);
        }else if (page == 1){
            otherpage = 1;
            result.empty();
            result.append(data.html);
        } else if ( page <= data.cant_pages ){
            result.append(data.html);
        }



        HoldOn.close();




    });

    return false;
}
function create_date(date_text) {
    var date = date_text.split('/');
    if (date.length == 3)
    {
        var date_result = parseInt(date[2], 10) + '-' + (parseInt(date[1], 10)) + '-' + parseInt(date[0], 10);
        return date_result;
    }
    return null;

}
function load_upper_filters(page)
{
    //var own_reservation_type= $("#own_reservation_type").val();
    var own_type_items = [];
    var own_category_items = [];
    var own_price_items = [];
    var own_price_from_items = [];
    var own_price_to_items = [];
    var room_total_items = [];
    var room_type_items = [];
    var room_bathroom_items = [];
    var room_beds_total_items = [];
    var room_windows_total_items = [];
    var others_languages_items = [];
    var others_included_items = [];
    var own_update_avaliable = $(':input[type="checkbox"][name="own_update_avaliable"]').is(':checked');
    var own_awards = [];
    var own_inmediate_booking = $(':input[type="checkbox"][name="own_inmediate_booking"]').is(':checked');
    var own_inmediate_booking2 = $(':input[type="checkbox"][name="own_inmediate_booking2"]').is(':checked');
    var others_not_included_items = [];
    var order_price=$(':input[type="radio"][name="priceOrder"]:checked').val();
    var order_comments='';
    if($('#commentsOrder').hasClass('active'))
        order_comments='ASC';
    var order_books='';
    if($('#booksOrder').hasClass('active'))
        order_books='ASC';

    $('input[name=own_category]:checked').each(function() {
        own_category_items.push($(this).val());
        if (document.getElementById("fu_own_category_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_own_category_" + $(this).val()).remove();
        }
    });
    $('input[name=own_awards]:checked').each(function() {
        own_awards.push($(this).val());
        if (document.getElementById("fu_own_awards_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_awards_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_awards'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_own_awards_" + $(this).val()).remove();
        }
    });


    var rangePrice=$('#priceFilter');
    var prices= slider.noUiSlider.get()
    if(prices!=''){

        own_price_items.push(parseInt(prices[0]));
        own_price_from_items.push(parseInt(prices[0]));
        own_price_to_items.push(parseInt(prices[1]));

    }
    $('input[name=own_price]:checked').each(function() {
        own_price_items.push($(this).val());
        own_price_from_items.push($(this).val());
        own_price_to_items.push(parseInt($(this).val()) + 25);

        if (document.getElementById("fu_own_price_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_price'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_own_price_" + $(this).val()).remove();
        }
    });

    $('input[name=room_total]:checked').each(function() {
        room_total_items.push($(this).val());

        if (document.getElementById("fu_room_total_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_total_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_total'><i class='icon-remove-sign'></i>" + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_room_total_" + $(this).val()).remove();
        }
    });
    $('input[name=own_type]:checked').each(function() {
        own_type_items.push($(this).val());
        if (document.getElementById("fu_own_type_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_own_type_" + $(this).val()).remove();
        }
    });

    $('input[name=room_type]:checked').each(function() {
        room_type_items.push($(this).val());
        if (document.getElementById("fu_room_type_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_room_type_" + $(this).val()).remove();
        }
    });

    $('input[name=room_bathroom]:checked').each(function() {
        room_bathroom_items.push($(this).val());
        if (document.getElementById("fu_room_bathroom_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_bathroom_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_bathroom'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_room_bathroom_" + $(this).val()).remove();
        }
    });


    var room_climatization = '';
    room_climatization = room_climatization + ((document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked) ? $('#room_airconditioner').attr('data-value') : "");

    if (document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked && document.getElementById("fu_room_airconditioner") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_airconditioner' data-control-id='room_airconditioner' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_airconditioner").parent().text() + "</a> ");
    }else{
        $("#fu_room_airconditioner").remove();
    }

    var room_audiovisuals = document.getElementById('room_audiovisuals') != null && document.getElementById('room_audiovisuals').checked;
    if (room_audiovisuals && document.getElementById("fu_room_audiovisuals") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_audiovisuals' data-control-id='room_audiovisuals' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_audiovisuals").parent().text() + "</a> ");
    }else{
        $("#fu_room_audiovisuals").remove();
    }

    var room_kids = document.getElementById('room_kids') != null && document.getElementById('room_kids').checked;
    if (room_kids && document.getElementById("fu_room_kids") == null){
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_kids' data-control-id='room_kids' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_kids").parent().text() + "</a> ");
    }else{
        $("#fu_room_kids").remove();
    }

    var room_smoker = document.getElementById('room_smoker') != null && document.getElementById('room_smoker').checked;
    if (room_smoker && document.getElementById("fu_room_smoker") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_smoker' data-control-id='room_smoker' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_smoker").parent().text() + "</a> ");
    }else{
        $("#fu_room_smoker").remove();
    }

    var room_safe = document.getElementById('room_safe') != null && document.getElementById('room_safe').checked;
    if (room_safe && document.getElementById("fu_room_safe") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_safe' data-control-id='room_safe' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_safe").parent().text() + "</a> ");
    }else{
        $("#fu_room_safe").remove();
    }

    var room_balcony = document.getElementById('room_balcony') != null && document.getElementById('room_balcony').checked;
    if (room_balcony && document.getElementById("fu_room_balcony") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_balcony' data-control-id='room_balcony' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_balcony").parent().text() + "</a> ");
    }else{
        $("#fu_room_balcony").remove();
    }

    var room_terraza = document.getElementById('room_terraza') != null && document.getElementById('room_terraza').checked;
    if (room_terraza && document.getElementById("fu_room_terraza") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_terraza' data-control-id='room_terraza' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_terraza").parent().text() + "</a> ");
    }else{
        $("#fu_room_terraza").remove();
    }

    var room_courtyard = document.getElementById('room_courtyard') != null && document.getElementById('room_courtyard').checked;
    if (room_courtyard && document.getElementById("fu_room_courtyard") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_courtyard' data-control-id='room_courtyard' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_courtyard").parent().text() + "</a> ");
    }else{
        $("#fu_room_courtyard").remove();
    }


    $('input[name=room_beds_total]:checked').each(function() {
        room_beds_total_items.push($(this).val());
        if (document.getElementById("fu_room_beds_total_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_beds_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_beds_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_room_beds_total_" + $(this).val()).remove();
        }
    });

    $('input[name=room_windows_total]:checked').each(function() {
        room_windows_total_items.push($(this).val());
        if (document.getElementById("fu_room_windows_total_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_windows_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_windows_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_room_windows_total_" + $(this).val()).remove();
        }
    });

    $('input[name=others_languages]:checked').each(function() {
        others_languages_items.push($(this).val());
        if (document.getElementById("fu_others_languages_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_languages_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_languages'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_others_languages_" + $(this).val()).remove();
        }
    });

    $('input[name=others_included]:checked').each(function() {
        others_included_items.push($(this).val());
        if (document.getElementById("fu_others_included_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_others_included_" + $(this).val()).remove();
        }
    });

    $('input[name=others_not_included]:checked').each(function() {
        others_not_included_items.push($(this).val());
        if (document.getElementById("fu_others_not_included_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_not_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_not_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        }else{
            $("#fu_others_not_included_" + $(this).val()).remove();
        }
    });

    var room_others_pets = document.getElementById('room_others_pets') != null && document.getElementById('room_others_pets').checked;
    if (room_others_pets && document.getElementById("fu_room_others_pets") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_pets' data-control-id='room_others_pets' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_pets").parent().text() + "</a> ");
    }else{
        $("#fu_room_others_pets").remove();
    }

    var room_others_internet = document.getElementById('room_others_internet') != null && document.getElementById('room_others_internet').checked;
    if (room_others_internet && document.getElementById("fu_room_others_internet") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_internet' data-control-id='room_others_internet' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_internet").parent().text() + "</a> ");
    }else{
        $("#fu_room_others_internet").remove();
    }

    if (own_inmediate_booking && document.getElementById("fu_own_inmediate_booking") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_inmediate_booking' data-control-id='own_inmediate_booking' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#own_inmediate_booking").parent().text() + "</a> ");
    }else{
        $("#fu_own_inmediate_booking").remove();
    }

    if (own_update_avaliable && document.getElementById("fu_own_update_avaliable") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_update_avaliable' data-control-id='own_update_avaliable' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#own_update_avaliable").parent().text() + "</a> ");
    }else{
        $("#fu_own_update_avaliable").remove();
    }

    if ( own_inmediate_booking2 && document.getElementById("fu_own_inmediate_booking2") == null)
    {
        innerHtml = $("#filter_upper").html();
        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_inmediate_booking2' data-control-id='own_inmediate_booking2' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#own_inmediate_booking2").parent().text() + "</a> ");
    }else{
        $("#fu_own_inmediate_booking2").remove();
    }


    $(".filter_upper_item").unbind();

    $(".filter_upper_item").click(function() {
        filter_upper($(this));
    });

    if ($("#filter_upper").html() != "")
        $("#filter_upper").css("display", "block");

    var arrival = $('#input_arrival_date').val();
    var departure = $('#input_departure_date').val();
    var guests = $('#input_guests').val();
    var rooms = $('#input_room').val();
    var text = $('#input_text').val();
    var order_price=$(':input[type="radio"][name="priceOrder"]:checked').val();
    var order_comments='';
    if($('#commentsOrder').hasClass('active'))
        order_comments='ASC';
    var order_books='';
    if($('#booksOrder').hasClass('active'))
        order_books='ASC';
    var result = $('#div_result');
    var own_category_items=[];
    $('input[name=own_category]:checked').each(function() {
        own_category_items.push($(this).val());
        if (document.getElementById("fu_own_category_" + $(this).val()) == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
        }
    });

    arrival = (arrival != $('#input_arrival_date').attr('placeholder')) ? create_date(arrival) : null;
    departure = (departure != $('#input_departure_date').attr('placeholder')) ? create_date(departure) : null;
    text = (text != $('#input_text').attr('placeholder')) ? text : null;


    var checked_filters = {
        //"own_reservation_type": (own_reservation_type != null && own_reservation_type != "" && own_reservation_type != "-1" && own_reservation_type != -1) ? own_reservation_type : null,
        'arrival': arrival,
        'departure': departure,
        'guests': guests,
        'rooms': rooms,
        'text': text,
        'own_update_avaliable': (own_update_avaliable) ? 1 : null,
        'own_inmediate_booking2': (own_inmediate_booking2) ? 1 : null,
        "own_category": (own_category_items.length > 0) ? own_category_items : null,
        "own_award": (own_awards.length > 0) ? own_awards: null,
        "own_inmediate_booking": (own_inmediate_booking) ? own_inmediate_booking: null,
        "own_type": (own_type_items.length > 0) ? own_type_items : null,
        "own_price": (own_price_items.length > 0) ? own_price_items : null,
        "own_price_from": (own_price_from_items.length > 0) ? own_price_from_items : null,
        "own_price_to": (own_price_to_items.length > 0) ? own_price_to_items : null,
        "own_rooms_number": (room_total_items.length > 0) ? room_total_items : null,
        "room_type": (room_type_items.length > 0) ? room_type_items : null,
        "own_beds_total": (room_beds_total_items.length > 0) ? room_beds_total_items : null,
        "room_bathroom": (room_bathroom_items.length > 0) ? room_bathroom_items : null,
        "room_windows_total": (room_windows_total_items.length > 0) ? room_windows_total_items : null,
        "room_climatization": room_climatization,
        "room_audiovisuals": room_audiovisuals,
        "room_kids": room_kids,
        "room_smoker": room_smoker,
        "room_safe": room_safe,
        "room_balcony": room_balcony,
        "room_terraza": room_terraza,
        "room_courtyard": room_courtyard,
        "own_others_languages": (others_languages_items.length > 0) ? others_languages_items : null,
        "own_others_included": (others_included_items.length > 0) ? others_included_items : null,
        "own_others_not_included": (others_not_included_items.length > 0) ? others_not_included_items : null,
        "own_others_pets": room_others_pets,
        "own_others_internet": room_others_internet,
        "order_price": order_price,
        "order_comments": order_comments,
        "order_books": order_books,
        "page": page

    };
    return checked_filters;

}
