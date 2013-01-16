/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

/**
 * set up all the clicker events
 */
window.addEvent('domready', function() {
	if( !$defined($('threadListUrl')) ) { return; }
	
	// ajax url for the thread list
	var threadListUrl = $('threadListUrl').getValue();
	
	// ajax loading image
	var loadImg = $('loadImg').getValue();
	
	// array of divs containting the clicker
	var clickerDivs = $$('div.messages_clicker_div');
	
	// array of elements to click to activate the slide
	var clickers = $$('a.messages_clicker');
	
	// array of divs for the slide
	var slideDivs = $$('div.messages_slider');
	
	//array to store all of the collapsing divs
	slidables = new Array();
	
	// unhide the clickers when we're using javascript
	for( var i = 0; i < clickerDivs.length; i++ ) {
		clickerDivs[i].style.display = 'block';
	};
	
	// loop through each clicker in array and add the clicker event to it
	// and attach a slider to it's target div and store these new objects in slidables array
	clickers.each( function(clicker, j) {
		// create slide for the named divs
		var msgSlide = new Fx.Slide(slideDivs[j], {
			'duration': 500,
			'onComplete': function() {
				var d = slideDivs[j];
				// see if we have loaded the message list, and if not load it
				var msg_ids = d.getElement('.message_id_list');
				if( $defined(msg_ids) ) {
					var ids = msg_ids.getValue();
					d.setHTML( '<img src="'+loadImg+'" style="vertical-align: bottom;" /> Loading...' );
					new Ajax( threadListUrl, {
						'method': 'post',
						'update': slideDivs[j],
						'data': 'msgIds='+ids+'&restrict=0',
						'evalScripts': true,
						'onComplete': function() {
							makeActiveThreads( slideDivs[j], this );
						}.bind( this )
					}).request();
				}
				if( this.wrapper.offsetHeight != 0 ) {
					this.wrapper.setStyle( 'height', 'auto' );
				}
			}
		});
		
		// add slide event to each clicker
		clickers[j].addEvent('click', function(e) {
			e = new Event(e);
			msgSlide.toggle();
			e.stop();
		});
		
		// and store it in the array
		slidables[j] = msgSlide;
	});
	
	// hide all the slideDivs
	// do this in chunks of 100 to avoid "long-running script" warnings
	var t = 1000; // time to delay between blocks
	var block = 100; // max size of block of hideables
	var num = Math.floor(slidables.length / block); // number of blocks
	for( var k = 0; k < num; k++ ) {
		var start = k*block;
		setTimeout( "hideSlides( "+start+", "+(start+block)+")", t*k );
	}
	if( num*block != slidables.length ) {
		var start = num*block;
		setTimeout( "hideSlides( "+start+", "+slidables.length+")", t*num );
	}
});

function hideSlides( start, end )
{
	for( var k = start; k < end; k++ ) {
		slidables[k].hide();
	}
}

/**
 * Add ajax and any other events to thread display
 * @param elem DOM-element  The element (eg div) which contains the thread rows
 */
function makeActiveThreads( elem, slider )
{
	if( !$defined($('threadUrl')) ) { return; }
	
	// ajax url for the thread list
	var threadUrl = $('threadUrl').getValue();
	
	//ajax loading image
	var loadImg = $('loadImg').getValue();
	
	var clickers = elem.getElements('.thread_toggle');
	clickers.each( function(clicker, j) {
		clickers[j].removeEvents( 'click' );
		clickers[j].addEvent( 'click' , function(e) {
			e = new Event(e);
			e.stop();
			
			// prepare variables
			var url = clickers[j].getProperty( 'href' );
			url = url.match( /threadId=[^&]*/ );
			thread = url[0].replace( 'threadId=', '' );
			var threadClass = 't_'+thread;
			var tmp = new Element( 'div' );
			var tmpRow = new Element( 'tr' );
			var row = clickers[j].getParent().getParent();
			row.addClass( threadClass );
			
			// Load up the new rows
			new Ajax( threadUrl, {
				'method': 'post',
				'update': tmp,
				'data': 'threadId='+thread,
				'evalScripts': true,
				'onComplete': function() {
					// remove the old rows
					var oldRows = elem.getElements( '.'+threadClass );
					tmpRow.injectBefore( row );
					oldRows.each( function( r ) {
						r.remove();
					});
					
					// insert the new rows
					var rows = tmp.getElements( 'tr' );
					rows.each( function( r ) {
						r.addClass( threadClass );
						r.injectBefore( tmpRow );
					});
					tmpRow.remove();
					
					// clean up display
					makeActiveThreads( elem, slider );
				}
			}).request();
		});
	});
}

window.addEvent('domready', function() {
	var selects = $ES( 'select[multiple]' );
//	var selects = $ES( '#incidents' );
	
	console.log( $E('body') );
	
	if( selects.length > 0 ) {
		optTipDiv = new Element( 'div', {
			'class': 'arc_option_tip',
			'style': 'position: absolute; display: none; top: 0px; left: 0px; background: white'
			} ).inject( $E('body'), 'inside' );
	}
	
	
	console.log( selects );
	
	selects.each( function( list ) {
		var w = 20;
		var l = list.options.length;
		for( var i = 0; i < l; i++ ) {
			if( list.options[i].text.length > w ) {
				list.options[i].addEvent( 'mouseover', showOptionTip );
				list.options[i].addEvent( 'mouseout', hideOptionTip );
			}
		}
		
	});
});

function showOptionTip( e )
{
	e = new Event( e );
	optTipDiv.setText( this.text );
	optTipDiv.setStyle( 'top', e.page.y );
	optTipDiv.setStyle( 'left', e.page.x );
	optTipDiv.setStyle( 'display', 'block' );
}
function hideOptionTip()
{
	optTipDiv.setText( '' );
	optTipDiv.setStyle( 'display', 'none' );
	optTipDiv.setStyle( 'top', 0 );
	optTipDiv.setStyle( 'left', 0 );
}
