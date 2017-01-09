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

    var registerAgency=function(){
        $('#myModalRegisterAgency').on('show.bs.modal', function (e) {
            //var $invoker = $(e.relatedTarget);
            $("#packageSelect").val($(e.relatedTarget).data('package'));
            $('#packageSelect').trigger('chosen:updated');
        });
    }
    /**
     * Register Agency
     */
    var initForm=function(){
        var config = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : {allow_single_deselect:true},
            '.chosen-select-no-single' : {disable_search_threshold:10},
            '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
            '.chosen-select-width'     : {width:"95%"}
        }
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }
        var form = $("#form-agency");
        var formData = new FormData(form[0]);
        formData.append("password", $('#password').val());

        $('.btn-register-partner').on('click',function(){
            $("#form-agency").validate({
                submitHandler: function(form) {
                    form.submit();
                },
                errorElement: 'span',
                errorClass: 'has-error',
                ignore: "",
                invalidHandler: function(event, validator) { //display error alert on form submit

                },
                rules: {
                    'partner_agency[name]':{
                        maxlength: 30
                    },
                    'partner_agency[contacts][0][name]':{
                        maxlength: 50
                    },
                    'partner_agency[email]':{
                        email: true
                    },
                    'partner_agency[contacts][0][email]':{
                        email: true
                    },
                    'partner_agency[address]':{
                        maxlength: 100
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
                    'partner_agency[phoneAux]':{
                        number: true
                    },
                    'password':{
                        minlength: 8
                    },
                    'terms':{
                        required:true
                    },
                    confirm: {
                        equalTo: "#password",
                        minlength: 8
                    }
                },
                highlight: function (element, clsError) { // hightlight error inputs
                    element = $(element);
                    element.parent().addClass(clsError);
                },
                unhighlight: function (element, clsError) { // revert the change done by hightlight
                    element = $(element);
                    element.parent().removeClass(clsError);
                },
                success: function(label) {
                    label.closest('.input-group').removeClass('has-error');
                    label.remove();
                }
            });
        });

    }
    return {
        //main function to initiate template pages
        init: function () {
          //IMPORTANT!!!: Do not modify the call order.
            addFormContactInAgency();
            initForm();
            registerAgency();
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
                errorElement: 'label',
                errorClass: 'error',
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

