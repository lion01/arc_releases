window.addEvent( 'subreportsLoaded', vivifyFieldMores );
if( typeof( domIsReady ) != 'undefined' ) { vivifyFieldMores(); }

function vivifyFieldMores()
{
	$$( '.toggler' ).each( vivifyFieldMore );
}

function vivifyFieldMore( t )
{
	if( t.hasClass( 'vivified' ) ) {
		return false;
	}
	t.addClass( 'vivified' );
	
	var p = getSubreportParentDiv( t );
	var m = p.getElement( '.p_more_wrapper' );
	var slider = new Fx.Slide( m, {
		'duration': 700,
		'transition': Fx.Transitions.Cubic.easeOut,
		'onStart': function() { this.doing = true; },
		'onComplete': function() {
			if( this.wrapper.offsetHeight != 0 ) {
				this.wrapper.setStyle( 'height', 'auto' );
			}
			this.doing = false;
		}
	});
	
	slider.hide();
	m.fresh = true;
	
	t.addEvent( 'click', function( e ) {
		new Event( e ).stop();
		if( slider.doing || slider.doing2 ) {
			return true;
		}
		slider.toggle();
		
		// swap the text for the toggle control
		var newText = t.getProperty( 'otherText' );
		if( typeof( newText ) != 'null' ) {
			var oldText = t.getText();
			t.setText( newText );
			t.setProperty( 'otherText', oldText );
		}
		
		if( m.fresh ) {
			m.fresh = false;
			m.setStyle( 'display', 'block' );
			new Ajax( $( 'ajax_more_url' ).value.replace( /(%7E|~)SUBREPORT(%7E|~)/, p.getProperty( 'id' ).match( /sub_(.*)/ )[1] ), {
				'method':'get',
				'update':m,
				'onSuccess':function() {
					
					window.fireEvent( 'subreportsLoaded' ); // make active parts active
					if( slider.open != slider.doing ) {
						var oldH = slider.wrapper.getStyle( 'height' );
						var newH = m.getStyle( 'height' );
						new Fx.Style( slider.wrapper, 'height', {
							'duration': 700,
							'transition': Fx.Transitions.Cubic.easeOut,
							'onStart': function() { slider.doing2 = true; },
							'onComplete': function() { slider.doing2 = false; }
						} ).start( oldH, newH );
					}
					
				}
			}).request();
		}
	});
	
	// on single subreport pages, the "more" section needs to be auto-expanded
	if( window.location.href.test( /view=subreport($|[^s])/ ) ) {
		t.click();
	}
}