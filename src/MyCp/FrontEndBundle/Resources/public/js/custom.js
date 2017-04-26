$(function () {
    $("[rel='tooltip']").tooltip();
    $("[data-rel='tooltip']").tooltip();

    startCustom();
    startTypeHead();
});

function startCustom() {
    $('.numeric').keydown(function (e) {
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

var myMap = null;
var activities = [];
function initActivitiesMap() {
    // Activities Map
    if ($("#destination-map").length > 0) {
        $.get(url_load_activities, [], function (result) {
            activities = result.activities;

            for (var activity in activities) {
                if (activities.hasOwnProperty(activity)) {
                    $link = $('<a href="' + activity + '" class="popover-icon col-sm-2 activity" rel="tooltip" title="' + activities[activity].description + '" style="background-image: url(' + activities[activity].image + ')">' + activities[activity].name + '</a>');
                    $link.click(function (s) {
                        if (myMap === null) {
                            myMap = jQuery("#destination-map").gmap3('get');
                        }
                        myMap.panTo(new google.maps.LatLng(22.01300, -79.26635));

                        $("#activity-menu a").each(function (d) {
                            $(this).removeClass("activate");
                        });
                        jQuery(this).addClass("activate");
                        s.preventDefault();
                        var activity = activities[$(this).attr("href")];
                        addMarkers(activity);
                    })
                }
                $("#activity-menu .row").append($link);
            }

            jQuery("#activity-menu a:first-child").addClass("activate");
            var firstactivity = activities[jQuery("#activity-menu a.activate").attr("href")];
            addMarkers(firstactivity);
        })
        if (jQuery().gmap3 && google != undefined) {

            //Activities Map
            var strictBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(20.591652, -81.474609),
                new google.maps.LatLng(22.87744, -76.904297)
            );

            var center = new google.maps.LatLng(22.01300, -79.26635);
            jQuery("#destination-map").gmap3({
                marker: {
                    values: [{
                        latLng: [22.01300, -79.26635],
                        options: {
                            icon: default_icon
                        }
                    }]
                },
                map: {
                    options: {
                        center: center,
                        zoom: 7,
                        mapTypeControl: false,
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        },
                        navigationControl: false,
                        scrollwheel: false,
                        streetViewControl: false,
                        zoomControl: false,
                        draggable: true,
                        styles: [{
                            "featureType": "administrative",
                            "elementType": "labels",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "administrative.country",
                            "elementType": "geometry.stroke",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "administrative.province",
                            "elementType": "geometry.stroke",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "landscape",
                            "elementType": "geometry",
                            "stylers": [{"visibility": "on"}, {"color": "#e3e3e3"}]
                        }, {
                            "featureType": "landscape.natural",
                            "elementType": "labels",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "poi",
                            "elementType": "all",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "road",
                            "elementType": "all",
                            "stylers": [{"color": "#cccccc"}]
                        }, {
                            "featureType": "road",
                            "elementType": "labels",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "transit",
                            "elementType": "labels.icon",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "transit.line",
                            "elementType": "geometry",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "transit.line",
                            "elementType": "labels.text",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "transit.station.airport",
                            "elementType": "geometry",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "transit.station.airport",
                            "elementType": "labels",
                            "stylers": [{"visibility": "off"}]
                        }, {
                            "featureType": "water",
                            "elementType": "geometry",
                            "stylers": [{"color": "#FFFFFF"}]
                        }, {"featureType": "water", "elementType": "labels", "stylers": [{"visibility": "off"}]}]
                    },
                    events: {
                        drag: function () {
                            if (myMap === null) {
                                myMap = jQuery("#destination-map").gmap3('get');
                            }
                            if (strictBounds.contains(myMap.getCenter()))
                                return;

                            var c = myMap.getCenter(),
                                x = c.lng(),
                                y = c.lat(),
                                maxX = strictBounds.getNorthEast().lng(),
                                maxY = strictBounds.getNorthEast().lat(),
                                minX = strictBounds.getSouthWest().lng(),
                                minY = strictBounds.getSouthWest().lat();

                            if (x < minX) x = minX;
                            if (x > maxX) x = maxX;
                            if (y < minY) y = minY;
                            if (y > maxY) y = maxY;

                            myMap.panTo(new google.maps.LatLng(y, x));
                        }
                    }
                }
            });
        }
    }
}
function paintOwnership(activity) {
    var i = 0;
    var total_item_show = 6;
    var prov_array = new Array();

    jQuery("#ownership-of-map").empty();
    jQuery("#ownership-of-map").append(jQuery("#loader-ownership-map").html());

    var url = url_load_ownership_map;
    for (var destination in activity.destinations) {
        url = activity.destinations[destination].url;
        if (i <= total_item_show)
            prov_array.push(activity.destinations[destination].prov_id);
        i++;
    }
    $("#ownership-of-map").load(url, {'prov_array': prov_array}, function (s) {
        if ($('.bttrlazyloading').length > 0){
            $('.bttrlazyloading').each(function () {
                $("#"+$(this).attr("id")).bttrlazyloading();
            });
        }
    });

}
function addMarkers(activity) {

    var clear = {name: "marker"};
    var act_icon = activity.icons;

    jQuery("#destination-map").gmap3(
        {
            clear: clear,
        }
    );

    /*jQuery("#destination-map").gmap3(
     {action: 'setCenter', args:[ new google.maps.LatLng(22.01300, -79.26635) ]}
     );*/
    //removeMarkers(dmarkers);

    for (var destination in activity.destinations) {

        jQuery("#destination-map").gmap3({
            marker: {
                latLng: activity.destinations[destination].location,
                data: activity.destinations[destination].html,
                options: {
                    title: activity.destinations[destination].name,
                    icon: act_icon
                },
                events: {
                    click: function (marker, event, context) {
                        var map = jQuery(this).gmap3("get"),
                            infowindow = jQuery(this).gmap3({get: {name: "infowindow"}});

                        if (infowindow) {
                            infowindow.open(map, marker);
                            infowindow.setContent('<div class="infoWindow">' + context.data + '</div>');
                        } else {
                            jQuery(this).gmap3({
                                infowindow: {
                                    anchor: marker,
                                    options: {content: context.data}
                                }
                            });
                        }
                    }
                }
            }
        });
    }

    paintOwnership(activity);

}

function startTypeHead() {

    if ($('[data-provide="typeahead"]').length > 0) {
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

var substringMatcher = function (strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;

        // an array that will be populated with substring matches
        matches = [];

        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');

        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function (i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });

        cb(matches);
    };
};

function reservations() {
    $('a.combo_genders_elements').click(function () {
        var item_value = $(this).attr('data-value');
        $('#user_tourist_gender').val(item_value);
    });

    $('ul.combo_nationality_items a.option').click(function () {
        var item_value = $(this).attr('data-value');
        $('#user_tourist_nationality').val(item_value);
    });

    $('ul.combo_hours_elements a.option').click(function () {
        var item_value = $(this).attr('data-value');
        $('#reservation_hour').val(item_value);
    });
}

function change_faq() {
    $('#faq_categories li').click(function () {
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
        }, function (data) {
            result.html(data);
            hide_loading();
        });
        return false;
    });
}

function language_change() {
    $(".language_link").click(function () {
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
            , function (data) {
                window.location = refresh_url.replace("/" + current_lang_code + "/", "/" + new_lang_code + "/");
            });
    });
}

function currency_change() {
    $(".currency_link").click(function () {
        var url = $(this).attr('data-url');
        var refresh_url = $(this).attr('data-refresh-url');
        $.post(url,
            {
                'curr_id': $(this).attr('data-currency-id'),
                'refresh_url': $(this).attr('data-refresh-url')
            }
            , function (data) {
                window.location = refresh_url;
            });
    });
}

function manage_favorities(favorite_class) {

    $(favorite_class).click(function (e) {
        var temp = $('#count-fav').text();
        if ($(this).hasClass('favorite_on_action'))
            temp--;
        if ($(this).hasClass('favorite_off_action'))
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
            , function (data) {
                $("." + result_id).html(data);

                manage_favorities("#fav_" + element_id);
                //manage_favorities(".favorite_on_action");
            });
    });
}


function details_favorites(favorite_button) {
    $(favorite_button).click(function () {

        var temp = $('#count-fav').text();
        if (favorite_button == '#delete_from_favorites')
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
            , function (data) {
                $(".favorites_details").html(data);

                details_favorites("#delete_from_favorites");
                details_favorites("#add_to_favorites");
            });
    });
}

function delete_from_list_favorites() {
    $(".delete_from_favorite_list").click(function () {
        var temp = $('#count-fav').text();
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
            }, function (data) {
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

function update_statistics(favorite_type, url) {
    $.post(url,
        {
            'favorite_type': favorite_type
        }, function (data) {
            if (favorite_type == "ownership")
                $("#total_fav_own").html(data);
            else if (favorite_type == "destination")
                $("#total_fav_dest").html(data);
        });
}

function show_loading() {
    $('#loading').removeClass('hidden');
}

function hide_loading() {
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
    }, function (data) {
        $('#name_from').val("");
        $('#email_from').val("");
        $('#name_from').val("");
        $('#email_to').val("");
        $('#send_to_friend_popup').modal('hide');
        $('#sending_mail').addClass('hidden');
    });
}

function move(id) {
    var to = $(id);
    var position_to = $(to).offset();
    var distance = position_to.top;
    $('body,html').animate({scrollTop: distance}, 500);
}
