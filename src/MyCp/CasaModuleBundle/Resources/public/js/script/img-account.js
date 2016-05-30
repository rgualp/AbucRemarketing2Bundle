/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var ImgAccount = function () {

    var clickBtnUploadImg=function(){
        $('#img-file').on('click',function(){
            $('#imputfile').click();
            var form = $('#image-upload-form');
           /* $("#imputfile").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
                if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    return false;
                }
                else {*/
                    form.fileupload({
                        url: form.attr('action'),
                        type: 'POST',
                        datatype: 'xml',
                        add: function (event, data) {
                            data.submit();
                        },
                        progress: function (e, data) {
                        },
                        fail: function (e, data) {
                        },
                        done: function (event, data) {
                            if(!data.result.success)
                                swal("{{ 'label.only.format' | trans }}"+fileExtension.join(', '), "", "error");
                            else{
                                var aux = data.files[0].type;
                                var ext = aux.split('/');
                                $(".user-avatar").attr("src", '/uploads/archivos/' + '{{ app.user.id }}' + '.' + ext[1] + '?timestamp=' + new Date().getTime());
                                $(".user-image").css("background", 'url(/uploads/archivos/' + '{{ app.user.id }}' + '.' + ext[1] + '?timestamp=' + new Date().getTime() + ')' + ' transparent no-repeat scroll center center / cover');
                            }

                        }
                    });
               // }
            //});

        })
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            clickBtnUploadImg();
        }
    };
}();
//Start ImgAccount
ImgAccount.init();





