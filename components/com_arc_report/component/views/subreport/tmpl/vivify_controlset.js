window.fireEvent( 'vivifyControlSetReady' );

function vivifyControlSet( el )
{
	if( el.hasClass( 'vivified' ) ) {
		return false;
	}
	el.addClass( 'vivified' );
	
	var p = el.getParent();
	var pForm = p.getParent();
	pForm.addEvent( 'submit', function( e ) {
		new Event( e ).stop();
		alert( 'Please use the controls at the bottom right of the report' );
	});
	
	el.getElements( 'a.control' ).each( function( l ) {
		l.addEvent( 'click', function( e ) {
			new Event( e ).stop();
			p.lock();
			
			if( l.hasClass( 'reject' ) ) {
				// the reject button needs to open an interstitial instead
				var sb = SqueezeBox.fromElement( l );
				sb.addEvent( 'onClose', p.unlock );
			}
			else {
				window.fireEvent( 'subreportSaving' );
				// all the other buttons go off to save the data immediately
				// send the data to be stored
				new Ajax( l.getProperty( 'href' ), {
					'method':'post',
					'data':pForm.toQueryString(),
					'onSuccess': function() {
						if( this.response.text.test( /saved$/ ) || true ) {
							
							// the preview button needs to open an interstitial
							if( l.hasClass( 'preview' ) ) {
								var l2 = l.clone();
								l2.setProperty( 'href', l.getNext().getProperty( 'value' ) );
								SqueezeBox.fromElement(l2);
							}
							
						}
						else {
							alert( 'Saving the report failed. Please try again.');
						}
						p.unlock();
					},
					'onFailure': function() {
						alert( 'Saving the report failed. Please preserve any entered values and reload the page.');
						p.unlock();
					}
				} ).request();
			}
			
		});
	});
}