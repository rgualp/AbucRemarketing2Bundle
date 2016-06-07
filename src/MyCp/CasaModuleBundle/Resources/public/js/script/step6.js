/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step6 = function () {

    var collectionHolderPhotos;
    // setup an "add a course" link
    //var addCourseLink = $('<a id="add-course" href="#" class="btn-add-link">' +
    //    '<i class="fa fa-plus-circle"></i>' +
    //    '</a>');
    //
    //$('#btnAddDishes').append(addCourseLink);
    var addPhotoLink=$('#addPhotoLink');
    var newPhotoLi = $('<li></li>');

    $(document).ready(function () {
        // Get the ul that holds the collection of Photos
        collectionHolderPhotos = $('ul#ownPhotos');

        // add a delete course link to all of the existing tag form li elements
        collectionHolderPhotos.find('li').each(function (index) {
             console.log(index);
            addPhotoFormDeleteLink($(this),index);
        });

        // add the "add a Photo" anchor and li to the Photos ul
        collectionHolderPhotos.append(newPhotoLi);

        // count the current form inputs we have (e.g. 2), use that as the new
        // index when inserting a new item (e.g. 2)
        collectionHolderPhotos.data('index', collectionHolderPhotos.find('li').length - 1);

        addPhotoLink.on('click', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // add a new tag form (see next code block)
            if(collectionHolderPhotos.find('li').length>51){
                swal("Alcanzó el máximo de imagenes permitidas", "Límite alcanzado", "error");
            }
            else{
            addPhotoForm(collectionHolderPhotos, newPhotoLi);
            var temp = collectionHolderPhotos.find('li').length - 2;
            $("#mycp_mycpbundle_ownership_step_photos_photos_"+temp+"_file").click();
            }
            //$('#form_type_sabrus_event_new_event_menu_Photos_'+temp+'_title').focus();
            //$('#form_type_sabrus_event_new_event_menu_Photos_'+temp+'_title').rules( "add", {
            //    required: false
            //});
            //$('#form_type_sabrus_event_new_event_menu_Photos_'+temp+'_description').rules( "add", {
            //    required: false
            //});

        });
        //addPhotoForm(collectionHolderPhotos, newPhotoLi);
    });

    function addPhotoForm(collectionHolder, newLinkLi) {


        // Get the data-prototype explained earlier
        var prototype = collectionHolder.data('prototype');

        // get the new index
        var index = collectionHolder.find('li').length - 1;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var newFormLi = $('<li class="col-sm-4"></li>').append(newForm);
        newLinkLi.before(newFormLi);

        // add a delete link to the new form
        addPhotoFormDeleteLink(newFormLi, index);

    }
    $(document).on('mouseover', 'li.col-sm-4', function(){
        //alert('in');
        $( this ).find('.btn-minus-link').removeClass('hide');
        $( this ).find('.set-picture-link').removeClass('hide');
        $( this ).find('.picture-link-m').css('box-shadow','5px 5px 10px rgba(0, 0, 0, 0.4)');
        $( this ).find('.picture-link').css('box-shadow','5px 5px 10px rgba(0, 0, 0, 0.4)');
    });
    $(document).on('mouseleave', 'li.col-sm-4', function(){
        //alert('out');
        $( this ).find('.btn-minus-link').addClass('hide');
        $( this ).find('.set-picture-link').addClass('hide');
        $( this ).find('.picture-link-m').css('box-shadow','none');
        $( this ).find('.picture-link').css('box-shadow','none');
    });
    //$( "div.enterleave" )
    //    .mouseenter(function() {
    //        n += 1;
    //        $( this ).find( "span" ).text( "mouse enter x " + n );
    //    })
    //    .mouseleave(function() {
    //        $( this ).find( "span" ).text( "mouse leave" );
    //    });
    function addPhotoFormDeleteLink(mediaFormLi, index) {
        index += 1;
        var inde;
        if(index>=10)
            inde = $('<h4 style="padding: 40px 9px 15px;">' + index + '</h4>');
        else
            inde = $('<h4>' + index + '</h4>');
        var removeFormA = $('<a href="#" class="btn btn-danger btn-minus-link hide">' +
            'Eliminar' +
            '</a>');
        var modFormA = $('<a class="btn btn-primary set-picture-link hide">' +
            'Cambiar' +
            '</a>');
        mediaFormLi.append(modFormA);
        mediaFormLi.append(removeFormA);
        //mediaFormLi.append(inde);
        modFormA.on('click', function (e) {
            e.preventDefault();
            //alert('epa');
            var pos=parseInt(index)-1;
            var name='mycp_mycpbundle_ownership_step_photos[photos]['+pos+'][file]';
            var nameD='mycp_mycpbundle_ownership_step_photos[photos]['+pos+'][description]';
            $("input[name='"+name+"']").click();
            $("input[name='"+nameD+"']").removeClass('hide');
            $("input[name='"+nameD+"']").prev().removeClass('hide');
            $("input[name='"+nameD+"']").parent().find('span.step-span').addClass('hide');

        });
          removeFormA.on('click', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            if(mediaFormLi.hasClass('uploaded')){
                swal({
                    title: "¿Estás seguro?",
                    text: "Se borrará la foto del servidor!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#e94b3d",
                    cancelButtonColor: "#64a433",
                    confirmButtonText: "Sí",
                    cancelButtonText: "No",
                    closeOnConfirm: true
                }, function () {
                    HoldOn.open();
                    var url=mediaFormLi.data('url');
                    $.post(url,
                        function (success) {
                            if (success == 'Ok') {
                                swal("Eliminado!", "La foto fue eliminada", "success");
                                mediaFormLi.remove();
                            }
                            else {
                                swal("Ocurrió un error y no pudo borrarse la foto, inténtelo de nuevo", "", "error");
                            }
                            HoldOn.close();
                        }
                    ).fail(function () {
                        HoldOn.close();
                        swal("Ocurrió un error y no pudo borrarse la foto, inténtelo de nuevo", "", "error");
                    });

                });
            }
            else{
            mediaFormLi.remove();
            }
        });
    }
   //$(document).on('click', '.picture-link', function(){
   // var name=$(this).data('input');
   //    $("input[name='"+name+"']").click();
   //});
    $(document).on('change', '.photo-input', function(){
       var fileName = $(this).val();
        readURL(''+$(this).attr('id'));
   });
    function readURL(input) {
        if ($('#'+input)[0].files) {

            var reader = new FileReader();
            var res = input.replace("_file", "");
            reader.onload = function (e) {
                $('#thumb-'+res)
                    .attr('src', e.target.result);
                    //.width('100%')
                    //.height('100%');
                $('#thumb-'+res).next().addClass('hide');
                $('#thumb-'+res).removeClass('hide');
            };

            reader.readAsDataURL($('#'+input)[0].files[0]);
            //console.log(reader);
            //alert(reader.readAsDataURL(input.files[0]));
        }}

    var saveStep6=function(){
        var _url=$('#mycp_mycpbundle_ownership_step_photos').attr('action');
        //var values = $('#mycp_mycpbundle_ownership_step_photos').serialize();
        //var form=$('#mycp_mycpbundle_ownership_step_photos');
        var form = document.getElementById("mycp_mycpbundle_ownership_step_photos");
        var $envio = $.ajax({
            url: _url,
            //data: values,
            data: new FormData(form),
            processData: false,
            contentType: false,
            type: 'POST'
        });
        $envio.error(function(data){
            //
        });
        $envio.success(function(data){
            //
        });

    }

    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            var event=App.getEvent();
            event.clickBtnContinueAfter.add(saveStep6,this);



        }
    };
}();
//Start step6
Step6.init();





