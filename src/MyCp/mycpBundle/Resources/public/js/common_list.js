$('.delete').click(function(){
    url=$(this).attr('data');
    $('#confirmation_action').attr('href',url);
    $('#confirmation_modal').modal('show');
})