/**
 Dashboard script to handle the entire layout and base functions
 **/
var Dashboard = function () {
   /**
    * Dashboard form init plugins
    */
    var initPlugins=function(){
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
       $('.input-date').datepicker({
           todayBtn: "linked",
           keyboardNavigation: false,
           forceParse: false,
           calendarWeeks: true,
           autoclose: true,
           format:'dd/mm/yyyy'
       });
       $("#priceFilter").slider({});


    }
    var onclickBtnMoreFilter=function(){
        $('#more-filter').addClass('hide');
        $('#icon-back').on('click',function(){
            if($('#more-filter').hasClass('hide')){
                $('#more-filter').removeClass('hide');
                $('#icon-top').removeClass('hide');
                $('#icon-back').addClass('hide');
                $('#text-more').addClass('hide');
            }
        });
        $('#icon-top').on('click',function(){
            $('#more-filter').addClass('hide');
            $('#icon-top').addClass('hide');
            $('#icon-back').removeClass('hide');
            $('#text-more').removeClass('hide');
        });
    }
    var onclickBtnSearch=function(){
        $('#btn_search').on('click',function(){
            var data={};
            var form = $("#form-filter-ownership");
            var result = $('#search-result');
            var _url = $(this).data('url');

            form.serializeArray().map(function(x){data[x.name] = x.value;});
            HoldOn.open();
            $.post(_url, data, function(data) {
                HoldOn.close();
                result.html(data);
                $('#search-result').removeClass('hide');
                $('#search-result').slimScroll({
                    height: '580px',
                    railOpacity: 0.9,
                    color: '#0d3044',
                    opacity : 1,
                    alwaysVisible : true
                });
                $('#priceFilter').infinitescroll({
                    navSelector  	: "#next:last",
                    nextSelector 	: "a#next:last",
                    itemSelector 	: "#priceFilter div",
                    debug		 	: true,
                    dataType	 	: 'html',
                    maxPage         : 3,
                    prefill			: true,
                    path: ["http://nuvique/infinite-scroll/test/index", ".html"],
                    path: function(index) {
                        return "index" + index + ".html";
                    }
                    // behavior		: 'twitter',
                    // appendCallback	: false, // USE FOR PREPENDING
                    // pathParse     	: function( pathStr, nextPage ){ return pathStr.replace('2', nextPage ); }
                }, function(newElements, data, url){
                });

            });
        });

    }
    return {
        //main function to initiate template pages
        init: function () {
            initPlugins();
            onclickBtnMoreFilter();
            onclickBtnSearch();
        }
    };
}();
Dashboard.init();

