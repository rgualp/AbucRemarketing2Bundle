$('a.option').click(function(){
    val=$(this).html();
    parent=$(this).attr('data');
    $('.'+parent).html(val);
})