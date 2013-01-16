/**
 * @package     Arc
 * @subpackage  Message
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	var moreAll = $('moreAll');
	var extraAll = $('extraAll');
	if( (moreAll != null) && (extraAll != null) ) {
		moreAll.addEvent( 'click', function( e ) {
			new Event( e ).stop();
			moreAll.setStyle( 'display', 'none' );
			extraAll.setStyle( 'display', 'inline' );
		} );
	}
	
	var moreSome = $('moreSome');
	var extraSome = $('extraSome');
	if( (moreSome != null) && (extraSome != null) ) {
		moreSome.addEvent( 'click', function( e ) {
			new Event( e ).stop();
			moreSome.setStyle( 'display', 'none' );
			extraSome.setStyle( 'display', 'inline' );
		} );
	}
} );