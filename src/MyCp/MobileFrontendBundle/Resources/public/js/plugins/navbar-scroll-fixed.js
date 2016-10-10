/**
 * Created by ernest on 10/7/16.
 */

var NavbarScrollFixed = (function() {
    var _content, _fixedClass, _height, didScroll = false;

    var inits = function(content, fixedClass, height)  {
        _content = $(content);
        _height = height;
        _fixedClass = fixedClass;

        window.addEventListener( 'scroll', function( event ) {
            if( !didScroll ) {
                didScroll = true;
                setTimeout( scrollPage, 250 );
            }
        }, false );
    }
    var scrollPage = function() {
        var sy = scrollY();
        if ( sy >= _height ) {
            _content.each(function () {
                $(this).addClass(_fixedClass);
            });
        }
        else {
            _content.each(function () {
                $(this).removeClass(_fixedClass);
            });
        }
        didScroll = false;
    }
    var scrollY = function() {
        return window.pageYOffset || document.documentElement.scrollTop;
    }

    return {
        init: function (content, fixedClass, height) {
            inits(content, fixedClass, height);
        }
    }

})();
