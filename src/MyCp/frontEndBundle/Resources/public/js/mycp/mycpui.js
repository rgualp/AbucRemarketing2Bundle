$('a.option').click(function(){
    val=$(this).html();
    parent=$(this).attr('data');
    $('#'+parent).html(val);
   
    var data_value =  $(this).attr("data-value");
    $('#'+parent).attr("data-value",data_value);
    
    if($(this).hasClass("combo_nationality_items"))
         $('#user_tourist_nationality').val(data_value);
     
     if($(this).hasClass("combo_genders_elements"))
         $('#user_tourist_gender').val(data_value);
     
     if($(this).hasClass("combo_hours_elements"))
         $('#reservation_hour').val(data_value);
});