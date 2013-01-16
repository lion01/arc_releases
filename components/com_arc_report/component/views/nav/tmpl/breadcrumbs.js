window.addEvent( 'arcFilterChange', cancelReloadCrumbs );
window.addEvent( 'arcSearchChange', reloadCrumbs );

var loadCrumbsXHR;

function cancelReloadCrumbs()
{
	// stuff's changing, so stop any now-obsolete xhr requests
	if( (typeof( loadCrumbsXHR ) == 'object') && loadCrumbsXHR.running ) {
		loadCrumbsXHR.cancel();
	}
}

function reloadCrumbs() {
	loadCrumbsXHR = new Ajax( $('breadcrumb_url').getValue(), {
		method: 'get',
		update: $('breadcrumbs_wrapper'),
		evalScripts: true
	} );
	loadCrumbsXHR.request();
}

function filterCrumbClick( e ) {
	if( this.href.match( /[^#]*/ )[0] != window.location.href.match( /[^#]*/ )[0] ) {
		return true;
	}
	
	new Event( e ).stop();
	var hashPos = this.href.indexOf("#");
	if( hashPos != -1 && (typeof( filterUrl ) == 'function') ) {
		filterUrl( this.href.substring( hashPos + 1 ) );
	}
}