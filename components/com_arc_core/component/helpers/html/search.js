/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function() {
	// define what we want to slide
	var mySlide = new Fx.Slide('search_container', {
		'duration': 500,
		'onComplete': function() {
			if( this.wrapper.offsetHeight != 0 ) {
				this.wrapper.setStyle( 'height', 'auto' );
			}
		}
	});
	
	// if we have an arc_data table set its height now
	if( $('arc_data') != null ) {
		mySlide.addEvent( 'onComplete', setArcDataHeight );
	}
	
	// add slide event to our toggle graphic and set the hidden input to remember this setting
	$( 'toggle' ).addEvent( 'click', function(e) {
		mySlide.toggle();
		if( mySlide.open ) {
			$('arrow').innerHTML = '<img src="components/com_arc_core/images/sort1.png" />';
			document.cookie='markBook_search=hide';
		}
		else {
			$('arrow').innerHTML = '<img src="components/com_arc_core/images/sort0.png" />';
			document.cookie='markBook_search=show';
		}
	});
	
	// add reset search form event to reset button
	$( 'search_reset' ).addEvent( 'click', function() {
		window.fireEvent( 'arcResetSearch' );
	});
	
	cookie = readCookie('markBook_search');
	if( cookie == 'hide' ) {
		$('toggle').fireEvent( 'click', $('toggle') );
	}
});

window.addEvent( 'arcResetSearch', function() {
	// get hold of the search form
	var form = $( 'search_container' );
	
	// loop through each found default and update its corresponding input
	form.getElements('.search_default').each( function(def) {
		var name = def.name.replace( /(^search_default_|\[\]$)/g, '' );
		
		// get the single type input by id or multi type input by class (radio / checkbox etc)
		// and set values apprpriately
		if( ((input = form.getElement('#' + name)) != null) && (input.getProperty('type') == 'button') ) {
			input.fireEvent( 'click' );
		}
		else if( (input = form.getElement('#' + name)) != null ) {
			input.setProperty( 'value', def.getValue() );
			if( input.getProperty('value') != def.getValue() ) {
				input.setProperty( 'value', '' );
			}
		}
		else {
			form.getElements( '.' + name ).each( function(input) {
				if( input.getProperty('value') == def.getValue() ) {
					input.setProperty( 'checked', 'checked' );
				}
				else {
					input.removeProperty( 'checked' );
				}
			});
		}
	});
} );

function readCookie( name )
{
	var nameEQ = name + "=";
	var ca = document.cookie.split( ';' );
	
	for( var i=0; i < ca.length; i++ ) {
		var c = ca[i];
		while( c.charAt(0) == ' ' ) {
			c = c.substring( 1, c.length );
		}
		if( c.indexOf(nameEQ) == 0 ) {
			return c.substring( nameEQ.length, c.length );
		}
	}
	
	return null;
}