/**
 * Created by vhagar on 15/01/2018.
 */
function roundTo(n, digits) {
    if (digits === undefined) {
        digits = 0;
    }

    var multiplicator = Math.pow(10, digits);
    n = parseFloat((n * multiplicator).toFixed(11));
    return Math.round(n) / multiplicator;
}
function totalPriceCalculator(reservations,invoice)
{   var total=0;
    Object.keys(reservations).forEach(function(key){
        total += (reservations[key].gen_res_total_in_site+(reservations[key].gen_res_total_in_site*0.1)+(reservations[key].gen_res_total_in_site+(reservations[key].gen_res_total_in_site*0.1))*0.1)

    });

    $("#append-c").remove();

    $('.append-content').append('<div id="append-c"><div class="text-right pull-left col-md-3">'+
   '<span class="h3 text-muted"><strong> Factura ID:'+invoice+' </strong></span></span>'+
'</div>'+
 '<br/>'+
        '<div class="text-right pull-left col-md-3" >'+
        '<span class="h3 text-muted amount"><strong> Reservas:' +Object.keys(reservations).length+'</strong></span>'+
        '</div>'+
        '<br/>'+
        '<div class="text-right pull-left col-md-3">'+
        '<span class="h3 text-muted total"><strong> Monto Total:'+roundTo((total*0.9),2)+'EUR</strong></span>'+
        '</div>'+
        '</div>'
    );



};
