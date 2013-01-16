window.addEvent( 'subreportsLoaded', vivifyFieldStatements );
window.addEvent( 'subreportSaving', resetFieldStatements );
if( typeof( domIsReady ) != 'undefined' ) { vivifyFieldStatements(); }

function vivifyFieldStatements()
{
	$$( '.report_statement_text' ).each( vivifyFieldStatementText );
	$$( '.report_statement_bank' ).each( vivifyFieldStatementBank );
}

function vivifyFieldStatementText( el )
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
	
	el.addEvent( 'keyup', function() {
		el.selectionStartPersistent = el.selectionStart;
		el.selectionEndPersistent = el.selectionEnd;
	} );
	el.addEvent( 'mouseup', function() {
		el.selectionStartPersistent = el.selectionStart;
		el.selectionEndPersistent = el.selectionEnd;
	} );
}

function vivifyFieldStatementBank( el )
{
	if( el.hasClass( 'vivified' ) ) {
		return false;
	}
	el.addClass( 'vivified' );
	
	var p = getSubreportParentDiv( el );
	
	el.addEvent( 'click', function( e ) {
		new Event( e ).stop();
		p.lock();
		
		var sb = SqueezeBox.fromElement( el );
		sb.addEvent( 'onClose', function() {
			p.unlock( false );
		} );
		
		statementInsertText = function( txt )
		{
			el.getPrevious().focus();
			_statementInsertText( el.getPrevious(), txt );
		}
	} );
}

function _statementInsertText( el, txt )
{
	var selStart = ( ( typeof( el.selectionStartPersistent ) == 'undefined' ) ? el.value.length : el.selectionStartPersistent );
	var selEnd   = ( ( typeof( el.selectionEndPersistent   ) == 'undefined' ) ? el.value.length : el.selectionEndPersistent   );
	
	el.value = el.value.substring( 0, selStart ) + txt + el.value.substring( selEnd );
}

function resetFieldStatements()
{
	$$( '.report_statement_text' ).each( function( el ) {
		if( el.getProperty( 'value' ) == el.getProperty( 'title' ) ) {
			el.setProperty( 'value', '' );
		}
	} );
}
