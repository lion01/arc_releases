/**
 * @package     Arc
 * @subpackage  Homepage
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

function panelReady() {
	
	// make modal links work for this ajaxed panel
	$$('a.panel_modal').each( function(el) {
		el.addEvent('click', function(e) {
			new Event(e).stop();
			SqueezeBox.fromElement(el);
		});
	});

}

//call this function with a slight delay
panelReady.delay(200);