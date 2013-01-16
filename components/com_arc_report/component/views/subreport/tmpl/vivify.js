var domIsReady = false;
window.addEvent( 'domready', function() {
	domIsReady = true;
	window.fireEvent( 'subreportsLoaded' );
} );

window.addEvent( 'subreportsLoaded', viviSub );
window.addEvent( 'subreportsLoaded', viviControlSet );
window.addEvent( 'vivifySubreportReady', viviSub );
window.addEvent( 'vivifyControlSetReady', viviControlSet );

function viviSub()
{
	if( domIsReady && (typeof( vivifySubreport ) == 'function') ) {
		vivifySubreport( $E( '.subreport' ) );
	}
}

function viviControlSet()
{
	if( domIsReady && (typeof( vivifyControlSet ) == 'function' ) ) {
		vivifyControlSet( $E( '.subreport_controls' ) );
	}
}

function getSubreportParentDiv( el )
{
	var bod = $$( 'body' );
	bod = bod[0];
	while( !el.hasClass('subreport') && el ) {
		var el = el.getParent();
	}
	return el;
}