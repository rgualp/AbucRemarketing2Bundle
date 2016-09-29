/**
 * Created by mgleon on 9/21/15.
 */
(function ($) {
    $.fn.adjustContainer = function (options) {
        this.each(function(index, container){
            var initializedHeight = null;
            container = $(container);
            var html = $('html');

            var onResizeWindow = function(){
                if(container.css('display') == 'none'){
                    return;
                }
                var sh = $(window).height();
                var htmlh = null;
                var ch = null;

                if(initializedHeight === null){
                    htmlh = html.height();
                    ch = container.height();

                    initializedHeight = ch;
                }
                else{
                    container.height(initializedHeight);

                    htmlh = html.height();
                    ch = container.height();
                }

                if(htmlh < sh){
                    var h = ch + (sh - htmlh);
                    container.height(h);
                }
            };
            onResizeWindow();
            $(window).resize(onResizeWindow);
        });

        return {
            update : function(){
                $(window).trigger('resize');
            }
        };
    };
})(jQuery);