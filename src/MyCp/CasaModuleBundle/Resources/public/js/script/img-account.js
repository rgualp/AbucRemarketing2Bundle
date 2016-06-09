/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var ImgAccount = function () {
    /**
     * Funcion que contiene todo lo referente a la toma de la imagen
     */
    var showModalImg=function(){
        var  localStream=null;
        // Grab elements, create settings, etc.
        var canvas = document.getElementById("canvas"),
            context = canvas.getContext("2d"),
            video_camera = document.getElementById("video"),
            videoObj = {"video": true},
            errBack = function (error) {
                console.log("Video capture error: ", error.code);
            };
        canvas.width = canvas.width;
        $('#myModal').on('show.bs.modal', function (event) {
            // Put video listeners into place
            if (navigator.getUserMedia) { // Standard
                navigator.getUserMedia(videoObj, function (stream) {
                    video_camera.src = stream;
                    video_camera.play();
                    localStream=stream;
                }, errBack);
            } else if (navigator.webkitGetUserMedia) { // WebKit-prefixed
                navigator.webkitGetUserMedia(videoObj, function (stream) {
                    video_camera.src = window.webkitURL.createObjectURL(stream);
                    video_camera.play();
                    localStream=stream;
                }, errBack);
            } else if (navigator.mozGetUserMedia) { // WebKit-prefixed
                navigator.mozGetUserMedia(videoObj, function (stream) {
                    video_camera.src = window.URL.createObjectURL(stream);
                    video_camera.play();
                    localStream=stream;
                }, errBack);
            }
        });
        $('#take-picture').on('click', function () {
            $('#video').addClass('hide');
            $('#canvas').removeClass('hide');
            context.drawImage(video_camera, 0, 0, 340, 240);
            $('#repeat-take-picture').removeClass('hide');
            $('#use-take-picture').removeClass('hide');
            $('#take-picture').addClass('hide');
        });
        $('#use-take-picture').on('click', function () {
            var base64String = canvas.toDataURL('img/png');

            // Create a Blob from a base64 string
            var blob = ImgAccount.dataURItoBlob(base64String);
            var fd = new FormData();
            // Get instance object XMLHttpRequest
            var xmlhttp='';
            if (window.XMLHttpRequest) {
                xmlhttp = new XMLHttpRequest();
            }
            else if (window.ActiveXObject) {
                xmlhttp = new XMLHttpRequest("Microsoft.XMLHTTP");
            }
            var form = $('#image-upload-form');
            fd.append('acl', $("input[name=acl]").val());
            fd.append('success_action_status', $("input[name=success_action_status]").val());
            fd.append('x-amz-date', $("input[name=x-amz-date]").val());
            fd.append("file", blob);
            xmlhttp.open('POST',  form.attr('action'), true);
            xmlhttp.send(fd);
            //Prepare response
            xmlhttp.onreadystatechange = function(){
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    ImgAccount.showResponse(JSON.parse(xmlhttp.responseText));
                }
            };
            $('#myModal').modal('hide');
        });
        $('#myModal').on('hide.bs.modal', function (event) {
            $('#use-take-picture').addClass('hide');
            $('#take-picture').removeClass('hide');
            $('#video').removeClass('hide');
            $('#canvas').addClass('hide');
            if(localStream){
                localStream.stop();
                $('#video')[0].src=null;
            }
        });
    }

    /**
     * Funcion para cuando se da click en el boton subir imagen
     */
    var onclickBtnUploadImg=function(){
        $('#img-file').on('click',function(){
            $('#imputfile').click();
            var form = $('#image-upload-form');
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
                        return false;
                    else{
                        ImgAccount.showResponse(data.result);
                    }

                }
            });
        })
    }

    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            onclickBtnUploadImg();
            showModalImg();
        },
        /**
         * Funcion que devuelve un blob imagen
         * @param dataURI
         * @returns {Blob}
         */
        dataURItoBlob:function(dataURI) {
            var byteString = atob(dataURI.split(',')[1]);

            var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }

            var bb = new Blob([ab], {"type": mimeString});
            return bb;
        },
        /**
         * Funcion para cambiar la foto
         * @param obj
         */
        showResponse:function(obj){
            var imageURL='/uploads/userImages/' + obj.dir + '?timestamp=' + new Date().getTime();
            $(".dashboard-avatar").css("background", 'url('+imageURL+') transparent no-repeat scroll center center / cover');
        }
    };
}();
//Start ImgAccount
ImgAccount.init();





