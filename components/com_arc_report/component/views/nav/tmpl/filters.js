window.addEvent( 'domready', vivifyFilters );
window.addEvent( 'arcFilterChange', setSearch );

var filterLists = new Array();

function vivifyFilters() {
	filterSlider = new Fx.Style( $('filter' ), 'margin-left', {
		'duration': 700,
		'transition': Fx.Transitions.Cubic.easeOut,
		'onStart': function() { this.doing = true; },
		'onComplete': function() {
			this.doing = false;
			this.isOpen = !this.isOpen;
		}
	} );
	
	var w = '-'+parseInt( $('filter' ).getStyle( 'width' ).replace( 'px', '' ) );
	filterSlider.isOpen = false;
	filterSlider.toggle = function() {
		if( this.isOpen ) {
			filterSlider.start( w, 0 );
		}
		else {
			filterSlider.start( 0, w );
		}
	};
	filterSlider.show = function() {
		filterSlider.set( w );
		this.isOpen = true;
	}
	
	$('filter_reset').addEvent( 'click', function( e ) {
		new Event( e ).stop();
		filterLists.each( function(fList) { fList.reset() } );
		window.fireEvent( 'arcFilterChange' );
	} );
	
	$('filter_toggle').addEvent( 'click', function( e ) {
		new Event( e ).stop();
		if( filterSlider.doing ) {
			return true;
		}
		// change class for state toggle is going _to_
		var link = this.getFirst();
		if( filterSlider.isOpen ) {
			link.addClass( 'closed' );
			link.removeClass( 'open' );
		}
		else {
			link.addClass( 'open' );
			link.removeClass( 'closed' );
		}
		
		filterSlider.toggle();
	} );
	
	
	$$( '.filter_heading' ).each( function( el ) {
		var listDiv = el.getNext().getFirst();
		var aFilterList = new FilterList( listDiv );
		filterLists[filterLists.length] = aFilterList;
		
		el.addEvent( 'click', function( e ) {
			new Event( e ).stop();
			if( aFilterList.isSliding() ) {
				return true;
			}
			aFilterList.toggle();
		} );
	} );
	
	
	var hashPos = window.location.href.indexOf("#");
	if( hashPos != -1 ) {
		filterUrl( window.location.href.substring( hashPos + 1 ) );
	}
	
	$('filter_toggle').setStyle( 'display', 'block' );
//	$('filter_toggle').click();
//	$('filter_head_student').click();
}

function filterUrl( fragment ) {
	var paramSet = Json.evaluate( urldecode( fragment ) );
	
	filterLists.each( function( fList ){
		if( typeof( paramSet[fList.getIdent()] ) == 'undefined' ) {
			vals = [];
		}
		else {
			vals = paramSet[fList.getIdent()];
		}
		
		fList.setValues( vals );
	} );
	
	window.fireEvent( 'arcFilterChange' );
}

function urldecode(url) {
	return decodeURIComponent(url.replace(/\+/g, ' '));
}

function urlencode(url) {
	return encodeURIComponent(url).replace(/ /g, '+');
}
/**
 * Re-sets the search terms to those currently in the filters
 * and loads resultant reports in place of the current displayed set
 */
function setSearch()
{
	// find search params
	var params = {};
	filterLists.each( function( filterList ) {
		var vals = filterList.getValues();
		if( vals != [] ) {
			params[filterList.getIdent()] = vals;
		}
	} );
	
	paramSet = {'params':Json.toString(params)};
	
	sendSearch( paramSet );
}

function sendSearch( paramSet )
{
	// stuff's changing, so stop any now-obsolete xhr requests
	if( (typeof( searchXHR ) == 'object') && searchXHR.running ) {
		searchXHR.cancel();
	}
	// submit new search params
	searchXHR = new Ajax( $( 'set_filters_url' ).getValue(), {
		'method':'post',
		'data': paramSet,
		'onSuccess': function() {
			window.fireEvent( 'arcSearchChange' );
		},
		'onFailure': function() {}
	} );
	searchXHR.request();
}

var FilterList = new Class({
	// add dynamic loading and sliding to the page element
	initialize: function( element ) {
		this.element = element;
		this.ident = this.element.getProperty( 'id' ).match( /[^_]*$/ )[0];
		this.firedChange = false;
		
		this.minHeight = 50;
		this.wrapperDiv = this.element.getParent();
		this.overlayDiv = this.element.getNext();
		this.loadDiv = this.overlayDiv.getFirst();
		
		this.overlayDiv.setStyle( 'height', '100%' );
		this.overlayDiv.setStyle( 'width', '100%' );
		this.overlayDiv.setStyle( 'position', 'absolute' );
		this.overlayDiv.setStyle( 'top', '0px' );
		this.overlayDiv.setStyle( 'left', '0px' );
		this.overlayDiv.setStyle( 'display', 'none' );
		
		var that = this;
		this.overlayDiv.fade = new Fx.Style( this.overlayDiv, 'opacity', {
			'duration': 300,
			'wait': false,
			'onStart': function() {
				if( this.now == 0 ) {
					that.loadDiv.setStyle( 'display', 'none' );
					that.overlayDiv.setStyle( 'display', 'block' );
				}
			},
			'onComplete': function() {
				if( this.now == 0 ) {
					that.overlayDiv.setStyle( 'display', 'none' );
				}
			}
		} ).set( 0 );
		
		this.slider = new Fx.Style( this.wrapperDiv, 'height', {
			'duration': 700,
			'transition': Fx.Transitions.Cubic.easeOut,
			'onStart': function() { this.doing = true; },
			'onComplete': function() {
				this.doing = false;
				this.isOpen = this.element.getSize().size.y > 0;
			},
			'hide': function() {
				this.set( 0 );
				this.isOpen = false;
				this.doing = false;
			},
			'initialize': function( el ) {
				this.hide();
				this.element.setStyle( 'overflow', 'hidden' );
				this.element.setStyle( 'position', 'relative' );
				this.isOpen = false;
			},
		} );
		
		this.hasData = false;
		this.values = new Hash();
		
		window.addEvent( 'arcFilterChange', function() {
			this.onArcFilterChange();
		}.bind( this ) );
		window.addEvent( 'arcSearchChange', function() {
			this.onArcSearchChange();
		}.bind( this ) );
	},
	
	// get the ident for this list
	getIdent: function() {
		return this.ident;
	},
	
	// get the selected values in this list as an array
	getValues: function() {
		return this.values.keys();
	},
	
	// get the height of the value list
	getListHeight: function() {
		return this.element.getSize().size.y;
	},
	
	// get the height the loader / blanking overlay should be set to
	getLoaderHeight: function() {
		return Math.max( this.wrapperDiv.getSize().size.y, this.minHeight )
	},
	
	/**
	 * Take an array of values and set them as the only values in the filter
	 */
	setValues: function( valList ) {
		this.values = new Hash();
		valList.each( function(optId) {
			this.values.set( optId, true );
		}.bind( this ) );
	},
	
	// Does this filter list have the given value selected?
	hasValue: function( k ) {
		return this.values.hasKey( k );
	},
	
	// transition the list from the size it is to the size given
	resize: function( to ) {
		this.slider.stop();
		var from = this.slider.element.getSize().size.y;
		this.slider.start( from, to );
	},
	
	// open / close the slider of options
	toggle: function() {
		if( this.slider.isOpen ) {
			this.resize( 0 )
		}
		else {
			if( !this.hasData ) {
				this.disableFilterList();
				this.loadFilterList();
			}
			else {
				this.resize( this.getListHeight() );
			}
		}
	},
	
	// indicate if the slider is currently moving
	isSliding: function() {
		return this.slider.doing;
	},
	
	// blank out the filter list (used when its values will need to be reloaded)
	disableFilterList: function() {
		this.overlayDiv.fade.start( 1 );
		this.resize( this.getLoaderHeight() );
	},
	
	// remove the blanking plate (used when the values have been reloaded)
	enableFilterList: function() {
		this.resize( this.element.getSize().size.y );
		this.overlayDiv.fade.start( 0 );
	},
	
	// Clear out the old, load in some new (via XHR)
	loadFilterList: function() {
		this.loadDiv.setStyle( 'display', 'block' );
		var tm = (this.getLoaderHeight() - this.loadDiv.getSize().size.y ) / 2;
		this.loadDiv.setStyle( 'margin-top', tm+'px' );
		
		this.params = {
			'listIdent':this.ident
		};
		filterLists.each( function( filterList ) {
			var vals = filterList.getValues();
			if( vals != [] ) {
				this.params[filterList.getIdent()] = vals;
			}
		}.bind( this ) );
		
		this.url = $('filter_url').getValue().replace( /(%7E|~)PARAMS(%7E|~)/, encodeURI( Json.toString( this.params ) ) );
		
		var filterList = this;
		this.xhrLoader = new XHR( {
			'method':'get',
			'onSuccess':function() { filterList.populateFilterList( Json.evaluate( this.response.text, true ) ); },
			'onFailure':function() { console.log( 'failed' ); },
		} );
		this.xhrLoader.send( this.url );
	},
	
	// Convert data into options and put them in the list
	populateFilterList: function( data ) {
		this.element.empty();
		data.each( function( option ) {
			this.element.adopt( new FilterListOpt( this, option ) );
		}.bind( this ) );
		this.hasData = true;
		
		this.enableFilterList();
	},
	
	// unselect all values and hide the list
	reset: function() {
		this.hasData = false;
		this.values = new Hash();
		this.slider.hide();
		this.element.empty();
	},
	
	// This list has changed, so update value list, then others must be refreshed
	onChange: function( optId, val ) {
		if( val ) {
			this.values.set( optId, true );
		}
		else {
			this.values.remove( optId );
		}
		this.firedChange = true;
		window.fireEvent( 'arcFilterChange' );
	},
	
	// when another list changes, displayed lists must be reloaded, non-displayed ones must be taken out of the game
	onArcFilterChange: function() {
		// stuff's changing, so stop any now-obsolete xhr requests
		if( (typeof( this.xhrLoader ) == 'object') && this.xhrLoader.running ) {
			this.xhrLoader.cancel();
		}
		
		if( !this.firedChange ) {
			if( this.slider.isOpen ) {
				this.disableFilterList();
			}
			this.hasData = false;
		}
	},
	
	// once the server has been told the new filter conditions, the filters can be reloaded
	onArcSearchChange: function() {
		// refresh the data in the list if this list isn't the one that's changed
		if( this.firedChange ) {
			this.firedChange = false;
		}
		else {
			if( this.slider.isOpen == true ) {
				this.loadFilterList();
			}
		}
	}
});

var FilterListOpt = new Class({
	// create element and set up events
	initialize: function( parent, option ) {
		this.parent = parent;
		this.optId = option.id;
		var id = 'filter_'+parent.getIdent()+'_'+option.id;
		
		this.element = new Element( 'div', {'class':'filter_opt'} );
		this.input = new Element( 'input', {'type':'checkbox', 'id':id, 'name':parent.getIdent()+'['+option.id+']'} );
		if( this.parent.hasValue( option.id ) ) {
			this.input.checked = 'checked';
		}
		this.label = new Element( 'label', {'for':id} );
		this.label.innerHTML = option.text;
		
		this.element.adopt( this.input );
		this.element.adopt( this.label );
		
		this.input.addEvent( 'change', this.onChange.bind( this ) );
		
		return this.element
	},
	
	onChange: function( e ) {
		this.parent.onChange( this.optId, Boolean( this.input.getValue() ) );
	}
});