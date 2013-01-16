window.addEvent( 'domready', function( e ) {
	$( 'doInsert' ).addEvent( 'click', function( e ) {
		new Event( e ).stop();
		var win = window.parent.document.getElementById('sbox-window');
		
		var toInsert = '';
		$$( '.stmt_text' ).each( function( el ) {
			var checkbox = el.getPrevious().getElement( 'input' );
			if( checkbox.getProperty( 'checked' ) ) {
				var val = el.getElement( 'div' ).innerHTML;
				
				el.getElement( 'div' ).getChildren().each( function( ch ) {
					val = val.replace( ch.outerHTML, ch.getValue() );
				} );
				
				toInsert += val+"\r"
			}
		} );
		window.top.statementInsertText( toInsert );
		win.close();
	} );
	
	$$( '.stmt_text' ).each( function( el ) {
		var checkbox = el.getPrevious().getElement( 'input' );
		
		el.addEvent( 'click', function( e ) {
			checkbox.setProperty( 'checked', !checkbox.getProperty( 'checked' ) );
		} );
		
		el.getElement( 'div' ).getChildren().each( function( elC ) {
			elC.addEvent( 'click', function( e ) {
				new Event( e ).stop();
				checkbox.setProperty( 'checked', true );
			} );
		} );
	} );
} );