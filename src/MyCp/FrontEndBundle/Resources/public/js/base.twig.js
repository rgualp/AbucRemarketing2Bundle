/**
 * Created by ernest on 3/19/17.
 */

var BaseTwigJs = function () {
    var inits = function () {

        $('#delete_button_reservation').on('click',function(){
            string_url= frontend_remove_from_whislist;
            data_remove=$(this).attr('data');
            $('.btn_delete_reservation').attr('disabled','disable');
            $('#'+$(this).attr('btn_launch')).attr('class','in_process');
            $('#confirmation_modal_delete').modal('hide');
            $.ajax({
                type: "POST",
                url: string_url,
                data:{'data':data_remove},
                success: function(response){
                    $('#whish-list').html(response);
                    $('#count-cart').text($('#total-cart').data('counter'));
                }
            });
        });

        var options = [];
        $( '.dropdown-cesta a' ).on( 'click', function( event ) {
            var $target = $( event.currentTarget ),
                val = $target.attr( 'data-value' ),
                $inp = $target.find( 'input' ),
                idx;

            if ( ( idx = options.indexOf( val ) ) > -1 ) {
                options.splice( idx, 1 );
                setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
            } else {
                options.push( val );
                setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
            }

            $( event.target ).blur();
            return false;
        });

        topmenucont = $('#top-menu-cont');
        topmenu = $("#top-menu").offset().top;
        $('#top-menu').affix({
            offset: {
                top: function() {
                    topmenucont.html = "&nbsp;";
                    return topmenu;
                }
            }
        });

        if (showMarquesina){

            //NavbarScrollFixed.init(".marquesina", "fixed-top", $(".marquesina").height() + 90, showMarqee, hideMarqee);
            $("#marquee-close").on("click", function (e) {
                e.preventDefault();
                $(".marquesina").hide();
            });

        }
        NavbarScrollFixed.init(".search", "fixed-top", $(".search").height() + 90, showMarqee, hideMarqee);


        $('#homeCarrousel a[href="#lastAdded"]').click(function () {
            loadLastAdded();
        });

        $('#homeCarrousel a[href="#economic"]').click(function () {
            loadAccommodationsByCategory("Económica", "economic", "economy");
        });

        $('#homeCarrousel a[href="#medium"]').click(function () {
            loadAccommodationsByCategory("Rango medio", "medium", "mid_range");
        });

        $('#homeCarrousel a[href="#premium"]').click(function () {
            loadAccommodationsByCategory("Premium", "premium", "premium");
        });
    }


    var loadLastAdded = function()
    {
        var hasContent = $("#lastAdded").attr("data-has-content");

        if(hasContent == "false") {
            var url = $("#lastAdded").attr("data-url");
            $.post(url, null,
                function (data) {
                    $("#lastAdded").html(data);
                    $('#homeCarrousel a[href="#lastAdded"]').tab('show');
                    $("#lastAdded").attr("data-has-content", "true");
                    manage_favorities(".favorite_off_action");
                    manage_favorities(".favorite_on_action");
                });
        }

        return false;
    }

    var loadAccommodationsByCategory = function(category, id, realCategory)
    {
        var hasContent = $("#" + id).attr("data-has-content");

        if(hasContent == "false") {
            var url = $("#" + id).attr("data-url");
            $.post(url,
                {
                    'category': category,
                    'elementId': id,
                    'realCategory': realCategory
                },
                function (data) {
                    $("#" + id).html(data);
                    $('#homeCarrousel a[href="#' + id+ '"]').tab('show');
                    $("#" + id).attr("data-has-content", "true");
                    manage_favorities(".favorite_off_action");
                    manage_favorities(".favorite_on_action");
                });
        }

        return false;
    }

    var showMarqee = function(){
        if ($("#top-menu").find(".breadcrumb-top").length != 0){
            $(".marquesina").css("top","78px");
        }
        $(".marquesina").removeClass('animated bounceInDown').addClass('animated bounceInDown').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
            $(this).removeClass('animated bounceInDown');
        });
    }
    var hideMarqee = function(){
        if ($("#top-menu").find(".breadcrumb-top").length != 0){
            $(".marquesina").css("top","0");
        }
    }

    return {
        init: function () {
            inits();
        }
    }
}();
