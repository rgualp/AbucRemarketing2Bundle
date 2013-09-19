$('a.option').click(function(){
    val=$(this).html();
    parent=$(this).attr('data');
    $('#'+parent).html(val);
   
    var data_value =  $(this).attr("data-value");
    $('#'+parent).attr("data-value",data_value);
});