/**
 Dashboard script to handle the entire layout and base functions
 **/
var Dashboard = function () {
    var start=0;
    var limit=4;
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
            var data_params={};
            var form = $("#form-filter-ownership");
            var result = $('#list-ownership');
            result.html();
            var _url = $(this).data('url');

            form.serializeArray().map(function(x){data_params[x.name] = x.value;});
            HoldOn.open();
            data_params['start']=start;
            data_params['limit']=limit;
            $.post(_url, data_params, function(response) {
                HoldOn.close();
                result.html(response);
                start=limit+1;
                limit=limit+5;
                $('#search-result').removeClass('hide');
                $('#search-result').slimScroll({
                    height: '580px',
                    railOpacity: 0.9,
                    color: '#0d3044',
                    opacity : 1,
                    alwaysVisible : true
                });
            });
        });

    }
    var infiniteScroll=function(){
        $('#search-result').scroll(function () {
            if ($('#search-result').scrollTop() >=$('#list-ownership').height()-$('#search-result').height()){
               var data_params={};
                var form = $("#form-filter-ownership");
                var result = $('#list-ownership');
                var _url = $('#btn_search').data('url');
                data_params['start']=start;
                data_params['limit']=limit;
                form.serializeArray().map(function(x){data_params[x.name] = x.value;});
                HoldOn.open();
                $.post(_url, data_params, function(response) {
                    slice
                    $('.slimScrollBar').css('top', $('.slimScrollBar').css('top')*1-50+'px');
                    HoldOn.close();
                    result.append(response);
                    start=limit+1;
                    limit=limit+5;
                });
            }
        })
    }
    return {
        //main function to initiate template pages
        init: function () {
            initPlugins();
            onclickBtnMoreFilter();
            onclickBtnSearch();
            infiniteScroll();
        },
        setStart:function(a){
            start=a
        },
        getStart:function(a){
            return start;
        },
        setLimit:function(a){
            limit=a
        },
        getLimit:function(a){
            return limit;
        }
    };
}();
Dashboard.init();

