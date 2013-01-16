var loadingMore = false;
var allScripts = new Array();

/**
 * Set up listeners and Load in the first page of data
 * We make sure the pageId is reset before listening for scroll events
 * so that any auto-fired scroll events don't give odd output.
 */
window.addEvent( 'domready', function() {
	_resetPageId( $( 'ajax_page_url' ) );
	loadingMore = false;
	window.addEvent( 'scroll', onScrollCheck );
	loadMore();
});

window.addEvent( 'subreportsLoaded', injectScripts );

/**
 * Scroll event handler
 * See if the window is nearly out of data and load more if needed.
 */
function onScrollCheck() {
	dToBottom = window.getScrollHeight() - ( window.getScrollTop() + window.getHeight() );
	if( dToBottom < 100 ) {
		loadMore();
	}
}

/**
 * Load in the next page of subreports
 */
function loadMore()
{
	if( !loadingMore ) {
		loadingMore = true;
		$( 'list_loader' ).setStyle( 'display', 'block' );
		
		new Ajax( $( 'ajax_page_url' ).value, {
			'method':'get',
			'onSuccess': function() {
				if( this.response.text.test( /~~End~~$/ ) ) {
					$( 'list_footer' ).setStyle( 'display', 'block' );
					$( 'list_loader' ).setStyle( 'display', 'none' );
				}
				else {
					// create then adopt the elements from the response
					// mustn't just append the html as that throws away the old DOM elements
					var el = new Element( 'div' );
					el.innerHTML = this.response.text;
					$( 'list_container' ).adopt( el.getChildren() );
					$( 'list_loader' ).setStyle( 'display', 'none' );
					_incPageId( $( 'ajax_page_url' ) );
					window.fireEvent( 'subreportsLoaded' ); // make active parts active
					loadingMore = false;
					window.fireEvent( 'scroll' ); // check that what has been loaded is enough to fill the page
				}
			},
			'onFailure': function() {
				$( 'list_error' ).setStyle( 'display', 'block' );
				$( 'list_loader' ).setStyle( 'display', 'none' );
			}
		} ).request();
	}
}

/**
 * Utility to reset which page of subreports to load
 * @param Element el  The DOM element which holds the link to reset
 */
function _resetPageId( el )
{
	el.value = el.value.replace( /(pageId=)([^&]*)/, '$1'+'0' );
}

/**
 * Utility to increment which page of subreports to load
 * @param Element el  The DOM element which holds the link to increment
 */
function _incPageId( el )
{
	var parts = el.value.match( /(pageId=)([^&]*)/ );
	nextPage = parseInt( parts[2] ) + 1;
	el.value = el.value.replace( /(pageId=)([^&]*)/, '$1'+nextPage );
}

/**
 * Inject the scripts given by the loaded page of subreports
 * into the main list page so any fields in the subreports that
 * need active parts can be made active
 */
function injectScripts()
{
	var el = $E( '.scriptnames');
	if( (typeof( el ) == 'undefined') || (el == null) ) {
		return true;
	}
	var names = Json.evaluate( el.getText() );
	names.each( function( n ) {
		if( !allScripts.contains( n ) ) {
			allScripts.include( n );
			new Asset.javascript( n );
		}
	} );
	el.remove();
}