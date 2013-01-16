/**
 * @package     Arc
 * @subpackage  Plugin_Styles
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function() {
	setArcDataJsClass();
	setArcDataHeight();
});

window.addEvent( 'resize', function() {
	setArcDataHeight();
});

function setArcDataJsClass() {
	$('arc_data').className = 'js';
	$('arc_data_left').className = 'js';
	$('arc_data_right').className = 'js';
	$('arc_data_right_inner').className = 'js';
}

function calcArcDataHeight( bodyHeight, viewHeight ) {
	var arcDataSize = $('arc_data').getSize();
	var arcHeight = arcDataSize.size.y;
	
	var reqHeight = viewHeight - ( bodyHeight - arcHeight );
	arcHeight = Math.max( reqHeight, Math.min(arcHeight, 300) );
	
	return arcHeight;
}

function checkScroll( divright, divleft ) {
	if( !divright ) {
		return;
	}
	var lastSeen;
	if( divright.scrollTop != lastSeen ) {
		lastSeen = divleft.scrollTop = divright.scrollTop;
	}
}

/**
 * Return any document’s height.
 * It’s been tested in IE6/7, FF2/3, Safari (Windows), Google Chrome and Opera 9.5.
 * If the actual document’s body height is less than the viewport height then it will return the viewport height instead
 * 
 * Thanks an acknowledgement to James Padolsey
 * @see http://james.padolsey.com/javascript/get-document-height-cross-browser/
 * @returns int  The height of the body / page in pixels
 */
function getDocHeight() {
	var D = document;
	return Math.max(
		Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
		Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
		Math.max(D.body.clientHeight, D.documentElement.clientHeight)
	);
}