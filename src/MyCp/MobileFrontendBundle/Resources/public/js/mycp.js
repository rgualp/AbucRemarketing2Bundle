
$(function () {


    $("#btn_search").click(search);
    $('#btn_insert_comment').click(insert_comment);
});
function insert_comment()
{
    HoldOn.open();
    hide_element($('#message_text'));

    // var result = $('#user_comments');
    var url = $('#btn_insert_comment').attr('data-url');

    /*var user_name = $('#input_name').val();
     var user_email = $('#input_email').val();*/
    var comment = $('#input_comment').val();
    var rating = $('#ratings-hidden').val();

    if (validate())
    {
        $.post(url, {
            'com_comments': comment,
            'com_rating': rating
        }, function(data) {
            // result.html(data);

            document.reload()




        });
    }
    else
    {
        HoldOn.close();

    }

    return false;
}
function validate()
{

    var comment = $('#input_comment').val();
    var valid = true;
    var error_text = '';



    if (comment === '')
    {
        valid = false;
        error_text += $('#input_comment').attr("requiered-message") + ' <br/>';

    }

    if (error_text !== '')
    {
        $('#message_text').html(error_text);
    }


    return valid;
}
function show_element(element)
{
    element.removeClass('hidden');
}

function hide_element(element)
{
    element.addClass('hidden');
}

var slider = document.getElementById('priceFilter');

noUiSlider.create(slider, {
	start: [ 0, 300 ],
	connect: true,
	tooltips:true,
	range: {
		min:  0,
		max:  300
	}
});
$('#input_departure_date').datetimepicker({
    icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-chevron-up",
        down: "fa fa-chevron-down",
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove'
    },
	toolbarPlacement:'top',
	defaultDate: new Date(),
	minDate:moment(new Date()),
	format: 'DD/MM/YYYY',


});
$('#input_arrival_date').datetimepicker({
    icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-chevron-up",
        down: "fa fa-chevron-down",
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-screenshot',
        clear: 'fa fa-trash',
        close: 'fa fa-remove'
    },
    toolbarPlacement:'top',
    defaultDate: new Date(),
    minDate:moment(new Date()),
    format: 'DD/MM/YYYY',


});
var tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 2);
$('#input_departure_date').data('DateTimePicker').date(tomorrow);
$("#input_arrival_date").on("dp.change", function(e) {
    $('#input_departure_date').data("DateTimePicker").minDate(e.date.add(2, 'days'));


});

/* Open when someone clicks on the span element */
function openNav() {
    
	if(document.getElementById("myNav").style.width=="100%"){
	 document.getElementById("myNav").style.width = "0%";
	 $('#search').empty();
	 $('#search').append('search');
	}
	else{
	document.getElementById("myNav").style.width = "100%";
	$('#search').empty();
	$('#search').append('close');
	}
}
function create_dateDMY(date_text) {
    var date = date_text.split('/');
    if (date.length == 3)
    {
        var date_result = parseInt(date[0], 10) + '-' + (parseInt(date[1], 10)) + '-' + parseInt(date[2], 10);
        return date_result;
    }
    return null;
}

function search() {
    document.getElementById("myNav").style.width = "0%";
    $('#search').empty();
    $('#search').append('search');
    var url = $('#btn_search').attr('data-url');

    var text = $('#input_text').val().toString();
    text = text.replace("-", "--");
    text = text.replace(/ /g, "-");
    text = text.replace("ñ", "_nn_");
    text = text.replace("Ñ", "_nn_");
    text = text.replace("á", "a");
    text = text.replace("é", "e");
    text = text.replace("í", "i");
    text = text.replace("ó", "o");
    text = text.replace("ú", "u");
    text = text.replace("ü", "u");
    text = text.toLowerCase();
    text = text.replace("_nn_", "ñ");
    if (text != $('#input_text').attr("placeholder") && text != "")
        url = url.toString().replace('_text', text);
    else
        url = url.toString().replace('_text', null);

    var arrival = $('#input_arrival_date').val();
    if (typeof arrival !== "undefined" && arrival != $('#input_arrival_date').attr("placeholder") && arrival != "")
        url = url.toString().replace('_arrival', create_dateDMY(arrival));
    else
        url = url.toString().replace('_arrival', null);

    var departure = $('#input_departure_date').val();
    if (typeof departure !== "undefined" && departure != $('#input_departure_date').attr("placeholder") && departure != "")
        url = url.toString().replace('_departure', create_dateDMY(departure));
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