(function() {
	var container = document.querySelector( 'div.container' ),
		triggerBttn = document.getElementById( 'trigger-overlay' ),
		triggerBttnResgister = document.getElementById( 'trigger-overlay-register' ),
		triggerBttnLoginResgister = document.getElementById( 'trigger-overlay-login-register' ),
		overlay = document.querySelector( 'div.overlay.login' ),
		overlayRegister = document.querySelector( 'div.overlay.register' ),
		closeBttnRegister= overlayRegister.querySelector( 'button.overlay-close.register'),
		closeBttn = overlay.querySelector( 'button.overlay-close.login' );
		transEndEventNames = {
			'WebkitTransition': 'webkitTransitionEnd',
			'MozTransition': 'transitionend',
			'OTransition': 'oTransitionEnd',
			'msTransition': 'MSTransitionEnd',
			'transition': 'transitionend'
		},
		transEndEventName = transEndEventNames[ Modernizr.prefixed( 'transition' ) ],
		support = { transitions : Modernizr.csstransitions };

	function toggleOverlay() {
		/*----------------Register--------------------*/
		if( classie.has( overlayRegister, 'open' ) ) {
			classie.remove( overlayRegister, 'open' );
			classie.remove( container, 'overlay-open' );
			classie.add( overlayRegister, 'close' );
			var onEndTransitionFn = function( ev ) {
				if( support.transitions ) {
					if( ev.propertyName !== 'visibility' ) return;
					this.removeEventListener( transEndEventName, onEndTransitionFn );
				}
				classie.remove( overlayRegister, 'close' );
			};
			if( support.transitions ) {
				overlayRegister.addEventListener( transEndEventName, onEndTransitionFn );
			}
			else {
				onEndTransitionFn();
			}
		}
		/*--------------Login-------------------------*/
		if( classie.has( overlay, 'open' ) ) {
			classie.remove( overlay, 'open' );
			classie.remove( container, 'overlay-open' );
			classie.add( overlay, 'close' );
			var onEndTransitionFn = function( ev ) {
				if( support.transitions ) {
					if( ev.propertyName !== 'visibility' ) return;
					this.removeEventListener( transEndEventName, onEndTransitionFn );
				}
				classie.remove( overlay, 'close' );
			};
			if( support.transitions ) {
				overlay.addEventListener( transEndEventName, onEndTransitionFn );
			}
			else {
				onEndTransitionFn();
			}
		}
		else if( !classie.has( overlay, 'close' ) ) {
			classie.add( overlay, 'open' );
			classie.add( container, 'overlay-open' );
		}
	};
	function toggleOverlayRegister() {
		/*--------------Login-------------------------*/
		if( classie.has( overlay, 'open' ) ) {
			classie.remove( overlay, 'open' );
			classie.remove( container, 'overlay-open' );
			classie.add( overlay, 'close' );
			var onEndTransitionFn = function( ev ) {
				if( support.transitions ) {
					if( ev.propertyName !== 'visibility' ) return;
					this.removeEventListener( transEndEventName, onEndTransitionFn );
				}
				classie.remove( overlay, 'close' );
			};
			if( support.transitions ) {
				overlay.addEventListener( transEndEventName, onEndTransitionFn );
			}
			else {
				onEndTransitionFn();
			}
		}
		/*----------------Register--------------------*/
		if( classie.has( overlayRegister, 'open' ) ) {
			classie.remove( overlayRegister, 'open' );
			classie.remove( container, 'overlay-open' );
			classie.add( overlayRegister, 'close' );
			var onEndTransitionFn = function( ev ) {
				if( support.transitions ) {
					if( ev.propertyName !== 'visibility' ) return;
					this.removeEventListener( transEndEventName, onEndTransitionFn );
				}
				classie.remove( overlayRegister, 'close' );
			};
			if( support.transitions ) {
				overlayRegister.addEventListener( transEndEventName, onEndTransitionFn );
			}
			else {
				onEndTransitionFn();
			}
		}
		else if( !classie.has( overlayRegister, 'close' ) ) {
			classie.add( overlayRegister, 'open' );
			classie.add( overlayRegister, 'overlay-open' );
		}
	};
	triggerBttn.addEventListener( 'click', toggleOverlay );
	triggerBttnLoginResgister.addEventListener( 'click', toggleOverlay );
	triggerBttnResgister.addEventListener( 'click', toggleOverlayRegister );
	closeBttn.addEventListener( 'click', toggleOverlay );
	closeBttnRegister.addEventListener( 'click', toggleOverlay );
})();