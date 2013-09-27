$('a.combo_genders_elements').click(function(){
    var item_value=$(this).attr('data-value');
    $('#user_tourist_gender').val(item_value);
});

$('ul.combo_nationality_items a.option').click(function(){
    var item_value=$(this).attr('data-value');
    $('#user_tourist_nationality').val(item_value);
});

$('ul.combo_hours_elements a.option').click(function(){
    var item_value=$(this).attr('data-value');
    $('#reservation_hour').val(item_value);
});