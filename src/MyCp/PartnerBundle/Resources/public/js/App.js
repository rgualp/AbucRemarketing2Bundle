/**
 App script to handle the entire layout and base functions
 **/
var App = function () {
    // Get the ul that holds the collection of urls
    var $collectionHolder = $('.contact');
    // setup an "add a tag" link
    var $addTagLink = $('<a href="#" class="add_tag_link hide">Add a tag</a>');
    var $newLinkLi = $('<li class="hide"></li>').append($addTagLink);

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    /**
    * Add form contact in to Agency
    */
    var addFormContactInAgency=function(){
        App.addTagForm($collectionHolder, $newLinkLi);
    }
    /**
     * Register Agency
     */
    var initForm=function(){
        var form = $("#form-agency");
        form.on("submit", function (e) {
            App.validateFormAgency();
            if(form.valid()){
                var formData = new FormData(form[0]);
                formData.append("password", $('#password').val());
                form.submit();
               /* HoldOn.open();
                $.ajax({
                    url: form.attr('action'),
                    type: "post",
                    dataType: "html",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR){
                        HoldOn.close();
                        var xdata = jQuery.parseJSON(data);
                        if(xdata.success){
                            $('#myModalRegisterAgency').modal('hide');
                            swal(xdata.msg, "", "success");
                        }
                        else
                            swal(xdata.msg, "", "error");
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        HoldOn.close();
                        swal("Error", "", "error");
                    }
                });*/
            }
            else{
                toastr.error("{{ 'label.an_error_occurred'|trans }}!", 'error');
            }
        });
    }
    return {
        //main function to initiate template pages
        init: function () {
          //IMPORTANT!!!: Do not modify the call order.
            addFormContactInAgency();
            initForm();
        },
        addTagForm:function($collectionHolder, $newLinkLi) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder.data('prototype');

        // get the new index
        var index = $collectionHolder.data('index');

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<div style="margin-top: -47px"></div>').append(newForm);
        $newLinkLi.before($newFormLi);
    },
        validateFormAgency:function(){
            var form = $("#form-agency");
            form.validate({
                errorElement: '', //default input error message container
                errorClass: 'help-inline', // default input error message class
                ignore: "",
                rules:  {
                    'partner_agency[email]':{
                        email: true
                    },
                    'partner_agency[contacts][0][email]':{
                        email: true
                    },
                    'partner_agency[phone]':{
                        number: true
                    },
                    'partner_agency[contacts][0][phone]':{
                        number: true
                    },
                    'partner_agency[contacts][0][mobile]':{
                        number: true
                    },
                    confirm: {
                        equalTo: "#password"
                    }
                }
            });
        }
    };
}();
App.init();

