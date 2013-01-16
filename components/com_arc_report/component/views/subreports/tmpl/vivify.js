var domIsReady = false;
window.addEvent( 'domready', function() { domIsReady = true; } );
window.addEvent( 'subreportsLoaded', vivifySubreports );
window.addEvent( 'subreportsLoaded', vivifyControlSets );
window.addEvent( 'vivifySubreportReady', vivifySubreports );
window.addEvent( 'vivifyControlSetReady', vivifyControlSets );


function vivifySubreports()
{
	if( domIsReady && (typeof( vivifySubreport ) == 'function') ) {
		$$( '.subreport' ).each( vivifySubreport );
	}
}

function vivifyControlSets()
{
	if( domIsReady && (typeof( vivifyControlSet ) == 'function' ) ) {
		$$( '.subreport_controls' ).each( vivifyControlSet );
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