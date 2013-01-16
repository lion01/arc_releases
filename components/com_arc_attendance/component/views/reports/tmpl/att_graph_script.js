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

function graphSwitching( c ) {
	c = (c) ? c : window.document;
	
	// get div elements by ID (mooTools style)
	var statDiv =      c.getElement('#att_panel_stat');    // statutory div
	var allDiv  =      c.getElement('#att_panel_all');     // all div
	var allPerDiv  =   c.getElement('#att_panel_all_per'); // all div percent
	var statButtons =  c.getElement('#att_panel_buttons'); // hover buttons
	var statDivExists = ( statDiv == null ) ? false : true; // do we have stat data?
	
	// Early exit if we have no content for the script to act on and we are on the homepage
	if( !statDivExists && !summarySwitching ) { return; }
	
	// get button elements by ID (mooTools style)
	var statButton    = c.getElement('#stat_pie_button');      // statutory pie chart button
	var allButton     = c.getElement('#all_histo_button');     // all stacked histogram button
	var allPerButton  = c.getElement('#all_per_histo_button'); // all percentage stacked histogram button
	
	// default settings
	if( statDivExists ) {
		statDiv.style.display = 'inline';
		allDiv.style.display = 'none';
	}
	else {
		allDiv.style.display = 'inline';
		statButton.style.display = 'none';
	}
	allPerDiv.style.display = 'none';
	statButtons.style.display = 'block';
	
	// add events to statButton if we have stat data
	if( statDivExists ) {
		statButton.addEvent( 'mouseover', function() {
			statDiv.style.display = 'inline';
			allDiv.style.display = 'none';
			allPerDiv.style.display = 'none';
			document.body.style.cursor = 'crosshair';
		});
		statButton.addEvent( 'mouseout', function() {
			document.body.style.cursor = 'auto';
		});
	}
	
	// add events to allButton
	allButton.addEvent( 'mouseover', function() {
		if( statDivExists ) {
			statDiv.style.display = 'none';
		}
		allDiv.style.display = 'inline';
		allPerDiv.style.display = 'none';
		document.body.style.cursor = 'crosshair';
	});
	allButton.addEvent( 'mouseout', function() {
		document.body.style.cursor = 'auto';
	});
	
	// add events to allPerButton
	allPerButton.addEvent( 'mouseover', function() {
		if( statDivExists ) {
			statDiv.style.display = 'none';
		}
		allDiv.style.display = 'none';
		allPerDiv.style.display = 'inline';
		document.body.style.cursor = 'crosshair';
	});
	allPerButton.addEvent( 'mouseout', function() {
		document.body.style.cursor = 'auto';
	});
	
	// arc tips for gChart summary images
	var attPanelTip = new Tips(c.getElements('.att_panel_img'), {
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