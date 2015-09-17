function receiveEmbedMessage( e ) {
	var iframes = document.getElementsByTagName( 'iframe' );
	for ( var ii = 0; ii < iframes.length; ii++ ) {
		if ( iframes[ ii ].getAttribute( 'data-secret' ) === e.data.secret ) {
			var source = iframes[ ii ];

			// Resize the iframe on request.
			if ( 'height' === e.data.message ) {
				var height = e.data.value;
				if ( height > 1000 ) {
					height = 1000;
				} else if ( height < 100 ) {
					height = 100;
				}

				source.height = (height) + "px";
			}

			// Link to a specific URL on request.
			if ( 'link' === e.data.message ) {
				var sourceURL = document.createElement( 'a' ), targetURL = document.createElement( 'a' );
				sourceURL.href = source.getAttribute( 'src' );
				targetURL.href = e.data.value;

				// Only continue if link hostname matches iframe's hostname.
				if ( targetURL.host === sourceURL.host && document.activeElement === source ) {
					location.href = e.data.value;
				}
			}
		}
	}
}

window.addEventListener( 'message', receiveEmbedMessage, false );

window.onload = function () {
	var isIE10 = 10 === new Function( "/*@cc_on return @_jscript_version; @*/" )(),
		isIE11 = !! navigator.userAgent.match( /Trident.*rv\:11\./ );

	// Remove security attribute from iframes in IE10 and IE11.
	if ( isIE10 || isIE11 ) {
		var iframes = document.querySelectorAll( 'iframe[security]' ), iframeClone;
		for ( var i = 0; i < iframes.length; i++ ) {
			iframeClone = iframes[ i ].cloneNode( true );
			iframeClone.removeAttribute( 'security' );
			iframes[ i ].parentNode.insertBefore( iframeClone, iframes[ i ].nextSibling );
			iframes[ i ].parentNode.removeChild( iframes[ i ] );
		}
	}
};
