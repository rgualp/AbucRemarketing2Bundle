/**
 * Created by ernest on 10/7/16.
 */

var NavbarScrollFixed = (function() {
    var _content, _fixedClass, _height, didScroll = false, makeFunction = true;
    var _inRange,_outRange;

    var inits = function(content, fixedClass, height, inRange, outRange)  {
        _content = $(content);
        _height = height;
        _fixedClass = fixedClass;
        _inRange = inRange;
        _outRange = outRange;

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
            if (_inRange != undefined){
                if (makeFunction){
                    _inRange();
                    makeFunction = false;
                }
            }

        }
        else {
            _content.each(function () {
                $(this).removeClass(_fixedClass);
            });
            if (_outRange != undefined){
                _outRange();
                makeFunction = true;
            }


        }
        didScroll = false;
    }
    var scrollY = function() {
        return window.pageYOffset || document.documentElement.scrollTop;
    }

    return {
        init: function (content, fixedClass, height, inRange, outRange) {
            inits(content, fixedClass, height, inRange, outRange);
        }
    }

})();
