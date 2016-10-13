var Search = function(){

    var _page = 1;

    var inits = function () {
        $('#button_research').click(function () {
            research(false)
        });

        //Filtros
        $('.action_remove_filter_up').change(function() {
            remove_filter_up($(this));
        });

        $('#filters-submit').click(function () {
            $('#button_research').removeClass('hide');
            research(false);
        });

        $('#more_filters').on('click', function () {
            if ($('#more_filters').hasClass('collapsed')) {
                $('#button_research').addClass('hide');
                $('#more_filters').removeClass('collapsed');


                var full_height = $(window).height() - ($("#search-container").offset().top + $(".filters-container").height()) - 5;
                var scroll_h = full_height - 95;

                $('.mobile-more-filter').css("height", full_height + "px");

                $('#content-filter').slimScroll({
                    height: scroll_h + 'px',
                    start: 'top'
                });
            }
            else {
                $('#button_research').removeClass('hide');
                $('#more_filters').addClass('collapsed');
            }
        });

        $("#priceFilter").slider({});

        $('#cancel-filters').on('click',function(){
            $('#button_research').removeClass('hide');
            $('#more_filters').addClass('collapsed');
        });

        $(window).scroll(function() {
            if($(window).scrollTop() + window.innerHeight == $(document).height()) {
                research(true);
            }
        });

        datePickersStarUp();
        datePickersStarUp_searcher();

        research(false);

    }

    var initMaps = function (classs) {

        $(".show-map-btn").each(function () {
            $(this).unbind();
            $(this).click(function (e) {

                $(classs).each(function (e) {
                    var parent = $(this).parent();
                    if (!parent.hasClass("toClose")){
                        parent.addClass("toClose");
                    }
                });

                e.preventDefault();
                var ele = $($(this).attr("href"));
                if (ele.hasClass("toClose")){
                    ele.removeClass("toClose");
                    ele.removeClass('animated bounceIn').addClass('animated bounceIn').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                        $(this).removeClass('animated bounceIn');
                    });
                }
            });
        });

        $(classs).each(function (e) {
            var lat = $(this).attr("data-x");
            var lon = $(this).attr("data-y");
            var id = $(this).attr("id");
            if (!$(this).hasClass("has-map")){
                $(this).addClass("has-map")
                Map.init(id, 15, lat, lon);
                var parent = $(this).parent();
                parent.find(".close-btn").click(function (e) {
                    e.preventDefault();
                    if (!parent.hasClass("toClose")){
                        parent.removeClass('animated bounceOut').addClass('animated bounceOut').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                            $(this).removeClass('animated bounceOut');
                            $(this).addClass("toClose");
                        });
                    }
                });
            }
        });
    };

    var datePickersStarUp_searcher = function () {
        $('.show_calendar').click(function(){
            $("#"+$(this).prev().attr('id')).datepicker('show');
        });
        $('#input_arrival_date').datepicker({
            format: 'dd/mm/yyyy',
            todayBtn: true,
            autoclose: true,
            startDate: today_date,
            date: start_date,
            language: $('#input_arrival_date').attr('data-localization')
        }).on('changeDate', function(ev) {
            var startDate = new Date(ev.date);
            startDate.setDate(startDate.getDate() + 1);
            departure_datepicker.setStartDate(startDate);
            var valueDate = new Date(ev.date);
            valueDate.setDate(valueDate.getDate() + 2);
            departure_datepicker.setDate(valueDate);
        });

        var departure_datepicker = $('#input_departure_date').datepicker({
            format: 'dd/mm/yyyy',
            todayBtn: false,
            autoclose: true,
            startDate: '+1d',
            date: end_date,
            language: $('#input_departure_date').attr('data-localization')
        }).data('datepicker');
    }


    var datePickersStarUp = function(){

        $('#top_reservation_filter_date_from').datepicker({
            format:'dd/mm/yyyy',
            todayBtn:'linked',
            autoclose: true,
            startDate: today_date,
            date: start_date,
            language: $('#top_reservation_filter_date_from').attr('data-localization')
        }).on('changeDate', function(ev){
            var startDate = new Date(ev.date);
            startDate.setDate(startDate.getDate() + 1);
            reservation_filter_date_to.setStartDate(startDate);
            var date = new Date(ev.date);
            date.setDate(date.getDate() + 2);
            reservation_filter_date_to.setDate(date);

            var startDate = new Date(ev.date);
            startDate.setDate(startDate.getDate() + 1);
            $('#filter_date_from').datepicker("setDate", startDate);
            $('#filter_date_to').datepicker("setDate", date);


            $('.datepicker').hide();
            $('#top_reservation_submit_button').attr('type','submit');
            $('#top_reservation_submit_button').attr('onclick','');
            $('#top_reservation_submit_button').html(reservation_see_prices_text);
        });

        var reservation_filter_date_to = $('#top_reservation_filter_date_to').datepicker({
            format:'dd/mm/yyyy',
            todayBtn:false,
            autoclose: true,
            startDate: '+1d',
            date: end_date,
            language: $('#top_reservation_filter_date_to').attr('data-localization')
        }).data('datepicker');

        $('#filter_date_from').datepicker({
            format:'dd/mm/yyyy',
            todayBtn:'linked',
            autoclose: true,
            startDate: today_date,
            date: start_date,
            language: $('#filter_date_from').attr('data-localization')
        }).on('changeDate', function(ev){
            var startDate = new Date(ev.date);
            startDate.setDate(startDate.getDate() + 1);
            $('#filter_date_to').datepicker("setStartDate", startDate);
            var date = new Date(ev.date);
            date.setDate(date.getDate() + 2);
            $('#filter_date_to').datepicker("setDate", date);
            $('.datepicker').hide();

            refresh_calendar(ev.date.getDate() + '/' + (ev.date.getMonth() + 1) + '/' + ev.date.getFullYear(),date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear());

            /*var start_date = new Date(ev.date);
             start_date.setDate(start_date.getDate() + 1);
             $('#top_reservation_filter_date_from').datepicker("setDate", start_date);
             $('#top_reservation_filter_date_to').datepicker("setDate", date);*/
        });

        var filter_date_to =$('#filter_date_to').datepicker({
            format:'dd/mm/yyyy',
            todayBtn: false,
            autoclose: true,
            startDate: '+1d',
            date: end_date,
            language: $('#filter_date_to').attr('data-localization')
        }).data('datepicker');
    }


    var research = function(update)
    {
        show_loading();
        var url = $('#button_research').attr('data-url');
        var result = $('#div_result');

        if (update)
            _page++;
        else
            _page = 1;

        var checked_filters = load_upper_filters(_page);

        $.post(url, checked_filters, function(data) {
            if (update){
                result.append(data);
            }else{
                result.html(data);
            }

            Mycp.manage_favorities(".favorite_off_action");
            Mycp.manage_favorities(".favorite_on_action");
            hide_loading();
            initMaps(".google-maps");
        });

        return false;
    }

    var load_upper_filters = function(page)
    {
        //var own_reservation_type= $("#own_reservation_type").val();
        var own_type_items = [];
        var own_category_items = [];
        var own_price_items = [];
        var own_price_from_items = [];
        var own_price_to_items = [];
        var room_total_items = [];
        var room_type_items = [];
        var room_bathroom_items = [];
        var room_beds_total_items = [];
        var room_windows_total_items = [];
        var others_languages_items = [];
        var others_included_items = [];
        var own_awards = [];
        var own_inmediate_booking = $(':input[type="checkbox"][name="own_inmediate_booking"]').is(':checked');
        var others_not_included_items = [];
        var order_price=$(':input[type="radio"][name="priceOrder"]:checked').val();
        var order_comments='';
        if($('#commentsOrder').hasClass('active'))
            order_comments='ASC';
        var order_books='';
        if($('#booksOrder').hasClass('active'))
            order_books='ASC';

        $('input[name=own_category]:checked').each(function() {
            own_category_items.push($(this).val());
            if (document.getElementById("fu_own_category_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }
        });
        $('input[name=own_awards]:checked').each(function() {
            own_awards.push($(this).val());
            if (document.getElementById("fu_own_awards_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_awards_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_awards'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }
        });

        //$('input[name=own_price]:checked').each(function() {
        //    own_price_items.push($(this).val());
        //    own_price_from_items.push($(this).val());
        //    own_price_to_items.push(parseInt($(this).val()) + 25);
        //
        //    if (document.getElementById("fu_own_price_" + $(this).val()) == null)
        //    {
        //        innerHtml = $("#filter_upper").html();
        //        $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_price'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
        //    }
        //});
        var rangePrice=$('#priceFilter').val();
        if(rangePrice!=''){
            var res = rangePrice.split(",");
            own_price_items.push(parseInt(res[0]));
            own_price_from_items.push(parseInt(res[0]));
            own_price_to_items.push(parseInt(res[1]));
            if (document.getElementById("fu_own_price_" + rangePrice) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + rangePrice + "' data-control-id='#priceFilter' data-value='" + rangePrice + "' data-control-name='own_price'><i class='icon-remove-sign'></i>$(" + rangePrice + ")</a> ");
            }
        }
        $('input[name=own_price]:checked').each(function() {
            own_price_items.push($(this).val());
            own_price_from_items.push($(this).val());
            own_price_to_items.push(parseInt($(this).val()) + 25);

            if (document.getElementById("fu_own_price_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_price_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_price'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=room_total]:checked').each(function() {
            room_total_items.push($(this).val());

            if (document.getElementById("fu_room_total_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_total_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_total'><i class='icon-remove-sign'></i>" + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
            }
        });
        $('input[name=own_type]:checked').each(function() {
            own_type_items.push($(this).val());
            if (document.getElementById("fu_own_type_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=room_type]:checked').each(function() {
            room_type_items.push($(this).val());
            if (document.getElementById("fu_room_type_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_type_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_type'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=room_bathroom]:checked').each(function() {
            room_bathroom_items.push($(this).val());
            if (document.getElementById("fu_room_bathroom_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_bathroom_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='room_bathroom'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }
        });


        var room_climatization = '';
        room_climatization = room_climatization + ((document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked) ? $('#room_airconditioner').attr('data-value') : "");

        if (document.getElementById('room_airconditioner') != null && document.getElementById('room_airconditioner').checked && document.getElementById("fu_room_airconditioner") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_airconditioner' data-control-id='room_airconditioner' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_airconditioner").parent().text() + "</a> ");
        }

        var room_audiovisuals = document.getElementById('room_audiovisuals') != null && document.getElementById('room_audiovisuals').checked;
        if (room_audiovisuals && document.getElementById("fu_room_audiovisuals") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_audiovisuals' data-control-id='room_audiovisuals' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_audiovisuals").parent().text() + "</a> ");
        }

        var room_kids = document.getElementById('room_kids') != null && document.getElementById('room_kids').checked;
        if (room_kids && document.getElementById("fu_room_kids") == null)
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_kids' data-control-id='room_kids' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_kids").parent().text() + "</a> ");

        var room_smoker = document.getElementById('room_smoker') != null && document.getElementById('room_smoker').checked;
        if (room_smoker && document.getElementById("fu_room_smoker") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_smoker' data-control-id='room_smoker' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_smoker").parent().text() + "</a> ");
        }

        var room_safe = document.getElementById('room_safe') != null && document.getElementById('room_safe').checked;
        if (room_safe && document.getElementById("fu_room_safe") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_safe' data-control-id='room_safe' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_safe").parent().text() + "</a> ");
        }

        var room_balcony = document.getElementById('room_balcony') != null && document.getElementById('room_balcony').checked;
        if (room_balcony && document.getElementById("fu_room_balcony") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_balcony' data-control-id='room_balcony' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_balcony").parent().text() + "</a> ");
        }

        var room_terraza = document.getElementById('room_terraza') != null && document.getElementById('room_terraza').checked;
        if (room_terraza && document.getElementById("fu_room_terraza") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_terraza' data-control-id='room_terraza' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_terraza").parent().text() + "</a> ");
        }

        var room_courtyard = document.getElementById('room_courtyard') != null && document.getElementById('room_courtyard').checked;
        if (room_courtyard && document.getElementById("fu_room_courtyard") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_courtyard' data-control-id='room_courtyard' data-control-name='' data-value=''><i class='icon-remove-sign'></i> " + $("#room_courtyard").parent().text() + "</a> ");
        }


        $('input[name=room_beds_total]:checked').each(function() {
            room_beds_total_items.push($(this).val());
            if (document.getElementById("fu_room_beds_total_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_beds_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_beds_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=room_windows_total]:checked').each(function() {
            room_windows_total_items.push($(this).val());
            if (document.getElementById("fu_room_windows_total_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_windows_total_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='room_windows_total'><i class='icon-remove-sign'></i> " + $(this).attr('data-aux-text') + ' ' + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=others_languages]:checked').each(function() {
            others_languages_items.push($(this).val());
            if (document.getElementById("fu_others_languages_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_languages_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_languages'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=others_included]:checked').each(function() {
            others_included_items.push($(this).val());
            if (document.getElementById("fu_others_included_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }
        });

        $('input[name=others_not_included]:checked').each(function() {
            others_not_included_items.push($(this).val());
            if (document.getElementById("fu_others_not_included_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_others_not_included_" + $(this).val() + "' data-value='" + $(this).val() + "' data-control-id='' data-control-name='others_not_included'><i class='icon-remove-sign'></i>" + $(this).parent().text() + "</a> ");
            }
        });

        var room_others_pets = document.getElementById('room_others_pets') != null && document.getElementById('room_others_pets').checked;
        if (room_others_pets && document.getElementById("fu_room_others_pets") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_pets' data-control-id='room_others_pets' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_pets").parent().text() + "</a> ");
        }

        var room_others_internet = document.getElementById('room_others_internet') != null && document.getElementById('room_others_internet').checked;
        if (room_others_internet && document.getElementById("fu_room_others_internet") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_room_others_internet' data-control-id='room_others_internet' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#room_others_internet").parent().text() + "</a> ");
        }

        if (own_inmediate_booking && document.getElementById("fu_own_inmediate_booking") == null)
        {
            innerHtml = $("#filter_upper").html();
            $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_inmediate_booking' data-control-id='own_inmediate_booking' data-control-name='' data-value=''><i class='icon-remove-sign'></i>" + $("#own_inmediate_booking").parent().text() + "</a> ");
        }

        $(".filter_upper_item").click(function() {
            filter_upper($(this));
        });

        if ($("#filter_upper").html() != "")
            $("#filter_upper").css("display", "block");

        var arrival = $('#input_arrival_date').val();
        var departure = $('#input_departure_date').val();
        var guests = $('#input_guests').val();
        var rooms = $('#input_room').val();
        var text = $('#input_text').val();
        var order_price=$(':input[type="radio"][name="priceOrder"]:checked').val();
        var order_comments='';
        if($('#commentsOrder').hasClass('active'))
            order_comments='ASC';
        var order_books='';
        if($('#booksOrder').hasClass('active'))
            order_books='ASC';
        var result = $('#div_result');
        var own_category_items=[];
        $('input[name=own_category]:checked').each(function() {
            own_category_items.push($(this).val());
            if (document.getElementById("fu_own_category_" + $(this).val()) == null)
            {
                innerHtml = $("#filter_upper").html();
                $("#filter_upper").html(innerHtml + "<a class='btn btn-default filter_upper_item' id='fu_own_category_" + $(this).val() + "' data-control-id='' data-value='" + $(this).val() + "' data-control-name='own_category'><i class='icon-remove-sign'></i> " + $(this).parent().text() + "</a> ");
            }
        });

        arrival = (arrival != $('#input_arrival_date').attr('placeholder')) ? create_date(arrival) : null;
        departure = (departure != $('#input_departure_date').attr('placeholder')) ? create_date(departure) : null;
        text = (text != $('#input_text').attr('placeholder')) ? text : null;


        var checked_filters = {
            //"own_reservation_type": (own_reservation_type != null && own_reservation_type != "" && own_reservation_type != "-1" && own_reservation_type != -1) ? own_reservation_type : null,
            'arrival': arrival,
            'departure': departure,
            'guests': guests,
            'rooms': rooms,
            'text': text,

            "own_category": (own_category_items.length > 0) ? own_category_items : null,
            "own_award": (own_awards.length > 0) ? own_awards: null,
            "own_inmediate_booking": (own_inmediate_booking) ? own_inmediate_booking: null,
            "own_type": (own_type_items.length > 0) ? own_type_items : null,
            "own_price": (own_price_items.length > 0) ? own_price_items : null,
            "own_price_from": (own_price_from_items.length > 0) ? own_price_from_items : null,
            "own_price_to": (own_price_to_items.length > 0) ? own_price_to_items : null,
            "own_rooms_number": (room_total_items.length > 0) ? room_total_items : null,
            "room_type": (room_type_items.length > 0) ? room_type_items : null,
            "own_beds_total": (room_beds_total_items.length > 0) ? room_beds_total_items : null,
            "room_bathroom": (room_bathroom_items.length > 0) ? room_bathroom_items : null,
            "room_windows_total": (room_windows_total_items.length > 0) ? room_windows_total_items : null,
            "room_climatization": room_climatization,
            "room_audiovisuals": room_audiovisuals,
            "room_kids": room_kids,
            "room_smoker": room_smoker,
            "room_safe": room_safe,
            "room_balcony": room_balcony,
            "room_terraza": room_terraza,
            "room_courtyard": room_courtyard,
            "own_others_languages": (others_languages_items.length > 0) ? others_languages_items : null,
            "own_others_included": (others_included_items.length > 0) ? others_included_items : null,
            "own_others_not_included": (others_not_included_items.length > 0) ? others_not_included_items : null,
            "own_others_pets": room_others_pets,
            "own_others_internet": room_others_internet,
            "order_price": order_price,
            "order_comments": order_comments,
            "order_books": order_books,
            "page": page


        };
        return checked_filters;
    }

    var filter_upper = function(element)
    {
        var control_id = element.attr("data-control-id");
        if(control_id=='#priceFilter'){
            $('#priceFilter').val('');
        }
        else {
            var control_name = element.attr("data-control-name");
            var item_value = element.attr("data-value");

            if (control_id !== "")
                document.getElementById(control_id).checked = false;

            if (control_name != "" && item_value != "") {
                $('input[name=' + control_name + ']').each(function () {
                    if ($(this).val() == item_value)
                        $(this).removeAttr("checked");
                });
            }
        }
        //alert($("#filter_upper").html());
        /*if ($("#filter_upper").html())
         $("#filter_upper").css("display", "none");*/

        research(false);
        element.remove();
    }

    var remove_filter_up = function(element)
    {
        if (!element.is(":checked"))
        {
            var control_name = element.attr("name");
            var control_id = element.attr("id");
            var control_value = element.val();

            if (control_name != null && document.getElementById("fu_" + control_name + "_" + control_value) != null)
                $("#fu_" + control_name + "_" + control_value).remove();

            if (control_id != null && document.getElementById("fu_" + control_id + "_" + control_value) != null)
                $("#fu_" + control_id + "_" + control_value).remove();

            //alert($("#filter_upper").html());
            /* if ($("#filter_upper").html())
             $("#filter_upper").css("display", "none");*/
        }
    }

    var refresh_filters_statistics = function(checked_filters)
    {
        var url = $('#filters').attr('data-url-statistics');
        var result = $('#filters');
        show_loading();
        $.post(url, checked_filters, function(data) {
            result.html(data);
            //$('#own_reservation_type').val((checked_filters['own_reservation_type'] != null ? checked_filters['own_reservation_type'] : "-1"));
            checkCheckBoxes(checked_filters['own_category'], "own_category");
            checkCheckBoxes(checked_filters['own_type'], "own_type");
            checkCheckBoxes(checked_filters['own_price'], "own_price");
            checkCheckBoxes(checked_filters['own_rooms_number'], "room_total");
            checkCheckBoxes(checked_filters['room_type'], "room_type");
            checkCheckBoxes(checked_filters['own_beds_total'], "room_beds_total");
            checkCheckBoxes(checked_filters['room_bathroom'], "room_bathroom");
            checkCheckBoxes(checked_filters['room_windows_total'], "room_windows_total");

            if (document.getElementById('room_airconditioner') != null)
                document.getElementById('room_airconditioner').checked = checked_filters['room_airconditioner'];

            if (document.getElementById('own_inmediate_booking') != null)
                document.getElementById('own_inmediate_booking').checked = checked_filters['own_inmediate_booking'];

            if (document.getElementById('room_audiovisuals') != null)
                document.getElementById('room_audiovisuals').checked = checked_filters['room_audiovisuals'];

            if (document.getElementById('room_kids') != null)
                document.getElementById('room_kids').checked = checked_filters['room_kids'];

            if (document.getElementById('room_smoker') != null)
                document.getElementById('room_smoker').checked = checked_filters['room_smoker'];

            if (document.getElementById('room_safe') != null)
                document.getElementById('room_safe').checked = checked_filters['room_safe'];

            if (document.getElementById('room_balcony') != null)
                document.getElementById('room_balcony').checked = checked_filters['room_balcony'];

            if (document.getElementById('room_terraza') != null)
                document.getElementById('room_terraza').checked = checked_filters['room_terraza'];

            if (document.getElementById('room_courtyard') != null)
                document.getElementById('room_courtyard').checked = checked_filters['room_courtyard'];

            checkCheckBoxes(checked_filters['others_languages'], "others_languages");
            checkCheckBoxes(checked_filters['others_included'], "others_included");
            checkCheckBoxes(checked_filters['others_not_included'], "others_not_included");

            if (document.getElementById('room_others_pets') != null)
                document.getElementById('room_others_pets').checked = checked_filters['room_others_pets'];

            if (document.getElementById('room_others_internet') != null)
                document.getElementById('room_others_internet').checked = checked_filters['room_others_internet'];

            start();
        });
        return false;
    }

    var checkCheckBoxes = function(array_of_values, name_checkboxes)
    {
        if (array_of_values != null && array_of_values.length > 0)
        {
            for (var i = 0; i < array_of_values.length; i++)
            {
                for (var j = 0; j < document.getElementsByName(name_checkboxes).length; j++)
                    document.getElementsByName(name_checkboxes)[j].checked = (document.getElementsByName(name_checkboxes)[j].value == array_of_values[i]);
            }
        }
    }

    return {
        init: function () {
            inits();
        }
    }
}();
Search.init();
