window.addEvent( 'subreportsLoaded', vivifyFieldTextareas );
window.addEvent( 'subreportSaving', resetFieldTextareas );
if( typeof( domIsReady ) != 'undefined' ) { vivifyFieldTextareas(); }

function vivifyFieldTextareas()
{
	$$( '.report_textarea' ).each( vivifyFieldTextarea );
}

function vivifyFieldTextarea( el )
{
	if( el.hasClass( 'vivified' ) ) {
		return false;
	}
	el.addClass( 'vivified' );
	
	if( el.getProperty( 'value' ) == '' ) {
		el.setProperty( 'value', el.getProperty( 'title' ) );
		el.setStyle( 'font-style', 'italic' );
		el.setStyle( 'color', 'grey' );
	}
	el.addEvent( 'focus', function() {
		if( el.getProperty( 'value' ) == el.getProperty( 'title' ) ) {
			el.setProperty( 'value', '' );
			el.setStyle( 'font-style', '' );
			el.setStyle( 'color', '' );
		}
	} );
	el.addEvent( 'blur', function() {
		if( el.getProperty( 'value' ) == '' ) {
			el.setProperty( 'value', el.getProperty( 'title' ) );
			el.setStyle( 'font-style', 'italic' );
			el.setStyle( 'color', 'grey' );
		}
	} );
}

function resetFieldTextareas()
{
	$$( '.report_textarea' ).each( function( el ) {
		if( el.getProperty( 'value' ) == el.getProperty( 'title' ) ) {
			el.setProperty( 'value', '' );
		}
	} );
}
