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

function editFormInit( c ) {
	c = (c) ? c : window.document;
	
	// ### edit form slider
	// div containting the clicker
	var clickerDiv = c.getElement('#edit_form_clicker_div');
	
	// element to click to activate the slide
	var clicker = c.getElement('#edit_form_clicker');
	
	// div for the slide
	var slideDiv = c.getElement('#edit_form_div');
	
	// div for section immediately below slider
	var sheetDiv = c.getElement('#sheet_div');
	
	// create slide for the named div
	var editSlide = new Fx.Slide(slideDiv, {
		'duration': 500,
		'onComplete': function() {
			if( this.wrapper.offsetHeight != 0 ) {
				this.wrapper.setStyle( 'height', 'auto' );
			}
		}
	});
	
	// unhide the clicker when we're using javascript
	clickerDiv.style.display = 'block';
	
	// pull next section up to cover hidden slide div
	sheetDiv.style.marginTop = '-12px';
	
	// add slide event to the clicker
	clicker.addEvent('click', function(e) {
		e = new Event(e);
		
		// activte the slide
		editSlide.toggle();
		
		// reset next section pull up if needed
		if( sheetDiv.style.marginTop == '-12px' ) {
			sheetDiv.style.marginTop = '0px';
		}
		else {
			sheetDiv.style.marginTop = '-12px';
		}
		
		e.stop();
	});
	
	// hide the slider
	editSlide.hide();
	
	// ### column input selector
	// master input box
	var masterInput = c.getElement('#master_edit_input');
	
	// input boxes
	var slaveInputs = c.getElements('input.edit_input');
	
	//add event to master input
	if( masterInput != null ) {
		masterInput.addEvent('click', function(e) {
			
			if( masterInput.checked == true ) {
				slaveInputs.each( function(slave, i) {
					slaveInputs[i].checked = true
				});
			}
			else {
				slaveInputs.each( function(slave, i) {
					slaveInputs[i].checked = false
				});
			}
		});
	}
};