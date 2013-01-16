window.fireEvent( 'vivifySubreportReady' );

function vivifySubreport( p )
{
	if( p.hasClass( 'vivified' ) ) {
		return false;
	}
	p.addClass( 'vivified' );
	
	var overlay = new Element( 'div', {
		'class': 'activity_overlay',
		'styles': {
			'display': 'block',
			'position': 'absolute',
			'top': '0px',
			'left': '0px',
			'z-index': 1000,
			'width': '100%',
			'height': '100%'
		}
	});
	overlay.innerHTML = '<div>Working...</div>';
	p.adopt( overlay );
	overlay.fade = new Fx.Style( overlay, 'opacity', {
		duration: 300,
		wait: false,
		onStart: function() { if( this.now == 0 ) { overlay.setStyle( 'display', 'block' ); } },
		onComplete: function() { if( this.now == 0 ) { overlay.setStyle( 'display', 'none' ); } }
	} ).set( 0 );
	
	p.lock = function() {
		overlay.fade.start( 1 ); // fade in the notification overlay
	}
	
	p.unlock = function( reload ) {
		reload = ( typeof( reload ) == 'undefined' ? true : reload );
		
		if( reload ) {
			new Ajax( $( 'ajax_single_url' ).value.replace( /(%7E|~)SUBREPORT(%7E|~)/, p.getProperty( 'id' ).match( /sub_(.*)/ )[1] ), {
				'method':'get',
				'onSuccess': function() {
					if( this.response.text.test( /No Report$/ ) ) {
						overlay.innerHTML = '<div>Problem...</div>';
					}
					else {
						// create then adopt the elements from the response
						// mustn't just append the html as that throws away the old DOM elements
						var el = new Element( 'div' );
						el.innerHTML = this.response.text;
						
						p.getChildren().each( function( child ) {
							if( !child.hasClass( 'activity_overlay' ) ) {
								child.remove();
							}
						} );
						p.adopt( el.getElement( '.subreport' ).getChildren() );
						window.fireEvent( 'subreportsLoaded' ); // make active parts active
						p.unlock( false );
					}
				},
				'onFailure': function() {
					overlay.innerHTML = '<div>Serious Problem...</div>';
				}
			} ).request();
		}
		else {
			overlay.fade.stop();
			overlay.fade.start( 0 ); // fade out the notification overlay
		}
	}
}