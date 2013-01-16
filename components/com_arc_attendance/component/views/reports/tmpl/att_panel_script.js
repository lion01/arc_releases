/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

// call this function with a slight delay
function callDelay()
{
	if( window.loaded && (typeof(graphSwitching) == 'function') ) {
		graphSwitching.delay( 200 );
	}
	else {
		callDelay.delay( 100 );
	}
}

// initiate call delay
callDelay();