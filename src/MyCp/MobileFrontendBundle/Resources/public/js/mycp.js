var slider = document.getElementById('sliderRegular');

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
	locale: window.navigator.language

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
    locale: window.navigator.language

});
var tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
$('#input_departure_date').data('DateTimePicker').date(tomorrow);
$("#input_arrival_date").on("dp.change", function(e) {
    $('#input_departure_date').data("DateTimePicker").minDate(e.date.add(1, 'days'));


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
function closeNav() {
    	
	document.getElementById("myNav").style.width = "0%";
	$('#search').empty();
	 $('#search').append('search');
	
}