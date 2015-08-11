function receiveEmbedMessage( e ) {
	if ( 'height' == e.data.message ) {
		var iframes = document.getElementsByTagName( 'iframe' );
		for( var ii = 0; ii < iframes.length; ii++ ) {
			if ( iframes[ ii ].getAttribute( 'data-password' ) == e.data.password ) {
				var height = e.data.value;
				if ( height > 600 ) {
					height = 600;
				} else if ( height < 100 ) {
					height = 100;
				}

				iframes[ ii ].height = (height) + "px";
			}
		}
	}
}

if ( window.addEventListener ) {
	window.addEventListener( 'message', receiveEmbedMessage, false );
}
else if ( window.attachEvent ) {
	window.attachEvent( 'message', receiveEmbedMessage );
}
