/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2016.
 *========================================================================*/
var Step6 = function () {

    var collectionHolderPhotos;
    // setup an "add a course" link
    //var addCourseLink = $('<a id="add-course" href="#" class="icon-add-link">' +
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
        collectionHolderPhotos.find('li').each(function () {
            addPhotoFormDeleteLink($(this));
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
            addPhotoForm(collectionHolderPhotos, newPhotoLi);
            var temp = collectionHolderPhotos.find('li').length - 2;
            $("#mycp_mycpbundle_ownership_step_photos_photos_"+temp+"_file").click();
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

    function addPhotoFormDeleteLink(mediaFormLi, index) {
        index += 1;
        var inde;
        if(index>=10)
            inde = $('<h4 style="padding: 40px 9px 15px;">' + index + '</h4>');
        else
            inde = $('<h4>' + index + '</h4>');
        var removeFormA = $('<a href="#" class="icon-minus-link">' +
            '<i class="fa fa-times-circle"></i>' +
            '</a>');
        mediaFormLi.append(removeFormA);
        //mediaFormLi.append(inde);

        removeFormA.on('click', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            mediaFormLi.remove();
        });
    }
   $(document).on('click', '.picture-link', function(){
    var name=$(this).data('input');
       $("input[name='"+name+"']").click();
   });
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

        alert('Save form 6');
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





