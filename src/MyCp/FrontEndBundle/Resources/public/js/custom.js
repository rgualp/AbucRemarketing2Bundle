var dmarkers = [];

$(function() {
    $("[rel='tooltip']").tooltip();
    $("[data-rel='tooltip']").tooltip();

    startCustom();
});

function startCustom() {
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

    startTypeHead();

    initActivitiesMap();
    //Para los acordiones
    $(".accordion a.accordion-toggle").addClass("collapsed");
}

function initActivitiesMap(){
    // Activities Map

    if ($("#destination-map").length > 0){
        if (google != undefined){

            var center = new google.maps.LatLng(22.01300, -79.26635);//La Habana 23.09725, -82.37548
            var options = {
                'zoom': 7,
                'center': center,
                'styles': [{"featureType":"administrative","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"administrative.country","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"administrative.province","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#e3e3e3"}]},{"featureType":"landscape.natural","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"color":"#cccccc"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#FFFFFF"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"off"}]}],
                'navigationControl': false,
                'scrollwheel': false,
                'streetViewControl': false,
                'zoomControl': false,
                'mapTypeControl': false
            };

            var map = new google.maps.Map(document.getElementById("destination-map"), options);

            var myOptions = {
                disableAutoPan: false,
                closeBoxMargin: "10px 2px 2px 2px",
                closeBoxURL: "https://www.google.com/intl/en_us/mapfiles/close.gif",
                infoBoxClearance: new google.maps.Size(1, 1),
                isHidden: false,
                boxClass: "mybox",
                pane: "floatPane",
                pixelOffset: new google.maps.Size(-200, 10),
                enableEventPropagation: false
            };
            var ib = new InfoBoxC(myOptions);

            $("#activity-menu a").each(function (e) {
                $(this).click(function (s) {
                    $("#activity-menu a").each(function (d) {
                        $(this).removeClass("activate");
                    });
                    $(this).addClass("activate");
                    s.preventDefault();
                    var activity = activities[$(this).attr("href")];
                    addMarkers(activity, map, ib);
                })
            })
        }
    }
}

function addMarkers(activity, map, infobox){

    var clear = {name:"marker"};
    var act_icon = activity.icons;

    // $("#destination-map").gmap3(
    //     {action: 'setCenter', args:[ new google.maps.LatLng(22.01300, -79.26635) ]}
    // );
    removeMarkers(dmarkers);

    for (var destination in activity.destinations) {

        console.log(activity.destinations[destination]);
        var latlng = new google.maps.LatLng(activity.destinations[destination].location[0], activity.destinations[destination].location[1]);



        var marker_bullet = new google.maps.Marker({
            map: map,
            position: latlng,
            title: activity.destinations[destination].name,
            content: activity.destinations[destination].html,
            icon: act_icon
        });



        google.maps.event.addListener(marker_bullet, 'click', (function(marker_bullet, i)
        {
            infobox.close();
            infobox.setContent('');

            return function()
            {
                infobox.setContent('<div class="infoWindow" style="border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 0 25px 0 5px; font-size:11px">'+marker_bullet.content+'</div>');
                // infobox.setPixelOffset( new google.maps.Size(200,0));
                infobox.open(map, marker_bullet);
            };
        })(marker_bullet, i));

        dmarkers.push(marker_bullet);
        // $("#destination-map").gmap3({
        //     marker:{
        //         latLng: activity.destinations[destination].location,
        //         data   : activity.destinations[destination].html,
        //         options:{
        //             title: activity.destinations[destination].name,
        //             icon: act_icon
        //         },
        //         events : {
        //             click : function(marker, event, context) {
        //                 var map = $(this).gmap3("get"),
        //                     infowindow = $(this).gmap3({get:{name:"infowindow"}});
        //
        //                 if (infowindow){
        //                     infowindow.open(map, marker);
        //                     infowindow.setContent('<div class="infoWindow">'+context.data+'</div>');
        //                     infowindow.setPixelOffset( new google.maps.Size(200,0));
        //                 } else {
        //                     $(this).gmap3({
        //                         infowindow:{
        //                             anchor:marker,
        //                             options:{content: context.data}
        //                         }
        //                     });
        //                 }
        //             }
        //         }
        //     }
        // });
    }

}

function removeMarkers(gmarkers){
    for(i=0; i<gmarkers.length; i++){
        gmarkers[i].setMap(null);
    }
}

function startTypeHead(){

    if ($('[data-provide="typeahead"]').length > 0){
        $.get(url_autocomplete, function (result) {
            $('#input_text').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 1
                },
                {
                    name: 'states',
                    source: substringMatcher(result['autocompletetext'])
                }
            );
        })
    }

}

var substringMatcher = function(strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;

        // an array that will be populated with substring matches
        matches = [];

        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');

        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function(i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });

        cb(matches);
    };
};

function reservations(){
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

function change_faq()
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

function language_change()
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

function currency_change()
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

function manage_favorities(favorite_class)
{

    $(favorite_class).click(function(e) {
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
}


function details_favorites(favorite_button)
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

function delete_from_list_favorites()
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

function update_statistics(favorite_type, url)
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

function show_loading()
{
    $('#loading').removeClass('hidden');
}

function hide_loading()
{
    $('#loading').addClass('hidden');
}

//net socials
function send2Friend() {
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

function move(id)
{
    var to = $(id);
    var position_to = $(to).offset();
    var distance = position_to.top;
    $('body,html').animate({scrollTop: distance}, 500);
}
