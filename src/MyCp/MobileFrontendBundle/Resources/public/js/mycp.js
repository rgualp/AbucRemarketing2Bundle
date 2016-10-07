var Mycp = function(){

    var initializer = function () {
        startCustom();
    }


    var startCustom = function() {
        $('.numeric').keydown(function(e) {
            $('#log').text('keyCode: ' + e.keyCode);
            if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 8 && e.keyCode != 9)
                e.preventDefault();
        });

        currency_change();
        language_change();
        manage_favorities(".favorite_off_action");
        manage_favorities(".favorite_on_action");

        details_favorites("#delete_from_favorites");
        details_favorites("#add_to_favorites");

        delete_from_list_favorites();

        change_faq();
        reservations();

        //Para los acordiones
        $(".accordion a.accordion-toggle").addClass("collapsed");

    }

    var reservations = function(){
        $('a.combo_genders_elements').click(function(){
            var item_value=$(this).attr('data-value');
            $('#user_tourist_gender').val(item_value);
        });

        $('ul.combo_nationality_items a.option').click(function(){
            var item_value=$(this).attr('data-value');
            $('#user_tourist_nationality').val(item_value);
        });

        $('ul.combo_hours_elements a.option').click(function(){
            var item_value=$(this).attr('data-value');
            $('#reservation_hour').val(item_value);
        });
    }

    var change_faq = function()
    {
        $('#faq_categories li').click(function() {
            $('#faq_categories li').removeClass("active");
            $(this).addClass("active");
            var category_id = $(this).attr('data-value');
            //var municipality =$('#dll_municipalities').val();
            var url = $('#faq_categories').attr('data-url-filter');
            var result = $('#div_result');
            category_id = (category_id != -1) ? category_id : null;

            show_loading();
            $.post(url, {
                'category_id': category_id
            }, function(data) {
                result.html(data);
                hide_loading();
            });
            return false;
        });
    }

    var language_change = function()
    {
        $(".language_link").click(function() {
            var url = $(this).attr('data-url');
            var refresh_url = $(this).attr('data-refresh-url');
            var new_lang_code = $(this).attr('data-new-lang-code');
            var current_lang_code = $(this).attr('data-current-lang-code');

            $.post(url,
                {
                    'lang_code': $(this).attr('data-new-lang-code'),
                    'lang_name': $(this).attr('data-lang-name'),
                    'lang_id': $(this).attr('data-lang-id')
                }
                , function(data) {
                    window.location = refresh_url.replace("/" + current_lang_code + "/", "/" + new_lang_code + "/");
                });
        });
    }

    var currency_change = function()
    {
        $(".currency_link").click(function() {
            var url = $(this).attr('data-url');
            var refresh_url = $(this).attr('data-refresh-url');
            $.post(url,
                {
                    'curr_id': $(this).attr('data-currency-id'),
                    'refresh_url': $(this).attr('data-refresh-url')
                }
                , function(data) {
                    window.location = refresh_url;
                });
        });
    }

    var manage_favorities = function(favorite_class)
    {
        $(favorite_class).each(function () {
            $(this).click(function(e) {
                var temp=$('#count-fav').text();
                if($(this).hasClass('favorite_on_action'))
                    temp--;
                if($(this).hasClass('favorite_off_action'))
                    temp++;

                $('#count-fav').text(temp);
                $('#count-fav').html(temp);

                var url = $(this).attr('data-url');
                var favorite_type = $(this).attr('data-favorite-type');
                var element_id = $(this).attr('data-element-id');
                var list_preffix = $(this).attr('data-list-preffix');
                var result_id = "favorite_" + favorite_type + "_" + element_id;
                var include_text = $(this).attr('data-include_text');

                $.post(url,
                    {
                        'favorite_type': favorite_type,
                        'element_id': element_id,
                        'list_preffix': list_preffix,
                        'include_text': include_text
                    }
                    , function(data) {
                        $("." + result_id).html(data);

                        manage_favorities("#fav_" + element_id);
                        //manage_favorities(".favorite_on_action");
                    });
            });
        });
    }


    var details_favorites = function(favorite_button)
    {
        $(favorite_button).click(function() {

            var temp=$('#count-fav').text();
            if(favorite_button=='#delete_from_favorites')
                temp--;
            else
                temp++;
            $('#count-fav').text(temp);
            $('#count-fav').html(temp);
            var url = $(this).attr('data-url');
            var favorite_type = $(this).attr('data-type');
            var element_id = $(this).attr('data-id');

            $.post(url,
                {
                    'favorite_type': favorite_type,
                    'element_id': element_id
                }
                , function(data) {
                    $(".favorites_details").html(data);

                    details_favorites("#delete_from_favorites");
                    details_favorites("#add_to_favorites");
                });
        });
    }

    var delete_from_list_favorites = function()
    {
        $(".delete_from_favorite_list").click(function() {
            var temp=$('#count-fav').text();
            temp--;
            $('#count-fav').text(temp);
            $('#count-fav').html(temp);
            var url = $(this).attr('data-url');
            var favorite_type = $(this).attr('data-favorite-type');
            var element_id = $(this).attr('data-element-id');
            var url_statistics = $(this).attr('data-url-statistics');

            show_loading();
            $.post(url,
                {
                    'favorite_type': favorite_type,
                    'element_id': element_id
                }, function(data) {
                    update_statistics(favorite_type, url_statistics);
                    if (favorite_type == "ownership" || favorite_type == "ownershipfav")
                        $("#div_result").html(data);
                    else if (favorite_type == "destination")
                        $("#div_result_destinations").html(data);
                    delete_from_list_favorites();
                    hide_loading();
                    $('img.lazy').jail();
                });
        });
    }

    var update_statistics = function(favorite_type, url)
    {
        $.post(url,
            {
                'favorite_type': favorite_type
            }, function(data) {
                if (favorite_type == "ownership")
                    $("#total_fav_own").html(data);
                else if (favorite_type == "destination")
                    $("#total_fav_dest").html(data);
            });
    }

    var show_loading = function()
    {
        $('#loading').removeClass('hidden');
    }

    var hide_loading = function()
    {
        $('#loading').addClass('hidden');
    }

//net socials
    var send2Friend = function () {
        var url = $('#send_to_friend_popup').attr('data-url');
        var email_type = $('#send_to_friend_popup').attr('data-email-type');
        var dest_prop_id = $('#send_to_friend_popup').attr('data-dest-prop-id');
        var name_from = $('#name_from').val();
        var email_from = $('#email_from').val();
        var email_to = $('#email_to').val();

        if (!document.mail_popup_form.checkValidity()) {
            if (name_from === '') {
                $('#name_from').css("borderColor", "red");
            } else if (email_from === '') {
                $('#email_from').css("borderColor", "red");
            } else {
                $('#email_to').css("borderColor", "red");
            }
            return;
        }

        $('#sending_mail').removeClass('hidden');
        $.post(url, {
            'email_type': email_type,
            'dest_prop_id': dest_prop_id,
            'name_from': name_from,
            'email_from': email_from,
            'email_to': email_to
        }, function(data) {
            $('#name_from').val("");
            $('#email_from').val("");
            $('#name_from').val("");
            $('#email_to').val("");
            $('#send_to_friend_popup').modal('hide');
            $('#sending_mail').addClass('hidden');
        });
    }

    var move = function(id)
    {
        var to = $(id);
        var position_to = $(to).offset();
        var distance = position_to.top;
        $('body,html').animate({scrollTop: distance}, 500);
    }


    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializer();
        },
        manage_favorities: function (classs) {
            manage_favorities(classs);
        }
    }
}();
Mycp.init();
