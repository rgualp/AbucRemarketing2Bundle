$(document).ready(function() {
    datePickersStarUp();
    connectSearchOnEnter();
    $("#btn_search").click(search);
});

function datePickersStarUp() {
    $('#input_arrival_date').datepicker({
        format: 'dd/mm/yyyy',
        todayBtn: true,
        autoclose: true,
        startDate: today_date,
        date: start_date,
        language: $('#input_arrival_date').attr('data-localization')
    }).on('changeDate', function(ev) {
        var changeDate = new Date(ev.date);
        changeDate.setDate(changeDate.getDate() + 2);
        departure_datepicker.setStartDate(changeDate);
        departure_datepicker.setDate(changeDate);
    });

    var departure_datepicker = $('#input_departure_date').datepicker({
        format: 'dd/mm/yyyy',
        todayBtn: true,
        autoclose: true,
        startDate: '+1d',
        date: end_date,
        language: $('#input_departure_date').attr('data-localization')
    }).data('datepicker');
}

function connectSearchOnEnter() {
    $('#orange_search_bar').keydown(function(e) {
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if ((keycode === 13)) {
            search();
        }
    });
}

function search() {
    var url = $('#btn_search').attr('data-url');

    var text = $('#input_text').val().toString().replace(/ /g, "_").toLowerCase();
    if (text != $('#input_text').attr("placeholder") && text != "")
        url = url.toString().replace('_text', text);
    else
        url = url.toString().replace('_text', null);

    var arrival = $('#input_arrival_date').val();
    if (arrival != $('#input_arrival_date').attr("placeholder") && arrival != "")
        url = url.toString().replace('_arrival', create_date(arrival));
    else
        url = url.toString().replace('_arrival', null);

    var departure = $('#input_departure_date').val();
    if (departure != $('#input_departure_date').attr("placeholder") && departure != "")
        url = url.toString().replace('_departure', create_date(departure));
    else
        url = url.toString().replace('_departure', null);

    var guests = $('#input_guests').val();
    if (guests != $('#input_guests').attr("placeholder") && guests != "")
        url = url.toString().replace('_guests', guests);
    else
        url = url.toString().replace('_guests', '1');

    var rooms = $('#input_room').val();
    if (rooms != $('#input_room').attr("placeholder") && rooms != "")
        url = url.toString().replace('_rooms', rooms);
    else
        url = url.toString().replace('_rooms', '1');

    window.location = url;
}

function create_date(date_text) {
    var date = date_text.split('/');
    if (date.length == 3)
    {
        var date_result = parseInt(date[0], 10) + '-' + (parseInt(date[1], 10)) + '-' + parseInt(date[2], 10);
        return date_result;
    }
    return null;
}

function format_date(date_text) {
    var date = date_text.split('-');
    if (date.length == 3)
    {
        //var date_result = parseInt(date[0], 10) + '/' + (parseInt(date[1], 10)) + '/' + parseInt(date[2], 10);
        var d = new Date();
        d.setDate(parseInt(date[2], 10));
        d.setMonth(parseInt(date[1] - 1, 10));
        d.setYear(parseInt(date[0], 10));
        return d;
    }
    return null;

}
