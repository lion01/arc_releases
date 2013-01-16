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
	window.setInterval("checkScroll($('arc_data_right_inner'), $('arc_data_left'))", 10);
});

function setArcDataHeight() {
	$('arc_data_left').setStyle('height', '100%');
	var bodyHeight = getDocHeight();
	var viewHeight = document.body.clientHeight;
	var newHeight = calcArcDataHeight( bodyHeight, viewHeight );
	$('arc_data_left').setStyle('height', newHeight);
	$('arc_data_right').setStyle('height', newHeight);
	$('arc_data_right_inner').setStyle('height', newHeight);
}