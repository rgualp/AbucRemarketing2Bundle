$(document).ready(start);

function start()
{
    $('#btn_search').click(search);
    
    $('#input_arrival_date').datepicker({
    format:'dd/mm/yyyy',
    todayBtn:'linked',
    language:$('#input_arrival_date').attr('data-localization')
}).on('changeDate', function(ev){
        $('.datepicker').hide();
    });

$('#input_departure_date').datepicker({
    format:'dd/mm/yyyy',
    todayBtn:'linked',
    language:$('#input_departure_date').attr('data-localization')
}).on('changeDate', function(ev){
        $('.datepicker').hide();
    });
}

function search()
{
    var url=$('#btn_search').attr('data-url');
    
    var text = $('#input_text').val();
    if(text != $('#input_text').attr("placeholder") && text != "")
        url = url.toString().replace('_text', text);
    else url = url.toString().replace('_text', null);
    
    var arrival = $('#input_arrival_date').val();    
    if(arrival != $('#input_arrival_date').attr("placeholder") && arrival != "")
        url = url.toString().replace('_arrival', create_date(arrival));
    else url = url.toString().replace('_arrival', null);
    
    var departure = $('#input_departure_date').val();    
    if(departure != $('#input_departure_date').attr("placeholder") && departure != "")
        url = url.toString().replace('_departure', create_date(departure));
    else url = url.toString().replace('_departure', null);
    
    var guests = $('#input_guests').val();    
    if(guests != $('#input_guests').attr("placeholder") && guests != "")
        url = url.toString().replace('_guests', guests);
    else url = url.toString().replace('_guests', '1');
    
    window.location = url;
}

function create_date(date_text){
    var date=date_text.split('/');
    if(date.length == 3)
    {
        var date_result=parseInt(date[2],10)+ '-' + (parseInt(date[1],10)) + '-' + parseInt(date[0],10);
        return date_result;
    }
    return null;
   
}

