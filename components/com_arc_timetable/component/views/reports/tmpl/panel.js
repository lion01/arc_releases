/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

function panelReady() {
	
	// arc tips for timetable panel attendance
	var ttCodeTip = new Tips($$('.arcTip'), {
		'className': 'custom',
		'initialize': function() {
			this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 500, wait: false}).set(0);
		},
		'onShow': function(toolTip) {
			this.fx.start(1);
		},
		'onHide': function(toolTip) {
			this.fx.start(0);
		}
	});
	
}

//call this function with a slight delay
panelReady.delay(200);