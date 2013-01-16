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

window.addEvent( 'domready', function()
{
	// get all the archetype panel rows and then remove them from the page
	archPanelRows = $$('.arch_panel_row');
	archPanelRows.each( function(archPanelRow) {
		archPanelRow.remove();
	});
	
	// get all the archetype panel add rows and then remove them from the page
	archPanelAdd = $('arch_panel_add');
	archPanelAdd.remove();
	
	// set the active / hidden state and fill in the main form input values
	setValues();
	
	// setup partial panels behaviour
	setPanelPartials();
	
	// get the number of homepage panel columns
	panelColCount = $('panel_cols_count').getValue();
	
	// setup the ordering arrows and remove button functionality 
	setCells();
	
	// get the tbody section of the Unsued Panels table
	addPanelTbody = $('add_panel_tbody');
	
	// setup the add panel buttons
	setAddButtons();
	
	// get the tbody sections of the panels tables
	panelRowTbodies = $('adminForm').getElements('*[id^=panel_row_tbody]');
});

/**
 * Set the values for main form inputs and add toggle event to change visibility
 */
function setValues()
{
	// get all the id inputs
	idInputs = $('adminForm').getElements('*[id^=panel_id]');
	
	// get all the col inputs
	colInputs = $('adminForm').getElements('*[id^=panel_col]');
	
	// get all the shown inputs
	shownInputs = $('adminForm').getElements('*[id^=panel_shown]');
	
	// get all the hidden inputs
	hiddenInputs = $('adminForm').getElements('*[id^=panel_cats]');
	
	// get all the input divs
	inputDivsTmp = $('adminForm').getElements('*[id^=panel_div]');
	
	// turn inputDivs array into a hash map and include the original value
	inputDivs = new Array();
	inputDivsTmp.each( function(inputDiv, i) {
		inputDivs[i] = new Hash( {
			'div': inputDiv,
			'original': shownInputs[i].getValue()
		});
	});
	
	// add click event to each input div
	inputDivs.each( function(divHash, i) {
		var div = divHash.get('div');
		div.removeEvents();
		div.addEvent( 'click', function() {
			toggle( i );
		});
		
		// set mouse pointer
		div.setStyle( 'cursor', 'pointer' );
		
		// set the main value whilst we are here
		setValue( i );
	});
}

/**
 * Toggle the active state of a given panel
 * 
 * @param int i  Array index of the panel
 */
function toggle( i )
{
	// get current state of the panel
	var curState = shownInputs[i].getValue();
	
	if( curState == 0 ) {
		if( inputDivs[i].get('original') == 2 ) {
			inputDivs[i].get('div').innerHTML = '<span style="color: orange;">Mixed</span>';
			shownInputs[i].value = 2;
		}
		else {
			inputDivs[i].get('div').innerHTML = '<span style="color: green;">Active</span>';
			shownInputs[i].value = 1;
		}
	}
	else if( curState == 1 ) {
		inputDivs[i].get('div').innerHTML = '<span style="color: red;">Hidden</span>';
		shownInputs[i].value = 0;
	}
	else if( curState == 2 ) {
		inputDivs[i].get('div').innerHTML = '<span style="color: green;">Active</span>';
		shownInputs[i].value = 1;
	}
	
	setValue( i );
}

/**
 * Set the "shown" value for the panel and recode the field value
 * 
 * @param bool i  Int bool (0/1) value for form field
 */
function setValue( i )
{
	var id = idInputs[i].value;
	var col = colInputs[i].value;
	var shown = shownInputs[i].value;
	
	hiddenInputs[i].value = 'id=' + id + '\n' + 'col=' + col + '\n' + 'shown=' + shown;
}

/**
 * Add the functionality to toggle partial panels into universal panels
 */
function setPanelPartials()
{
	// get all the partial panels
	var partialPanels = $$('.partial_panel');
	
	// add the click event
	partialPanels.each( function(partialPanel) {
		var span = $E( 'span', partialPanel );
		var panelName = span.getText().trim();
		var hiddenInput = $E( 'input', partialPanel );
		
		partialPanel.addEvent( 'click', function() {
			var hiddenInput = $E( 'input', partialPanel );
			if( hiddenInput != undefined ) {
				span.setStyle( 'color', 'green' );
				hiddenInput.remove();
			}
			else {
				span.setStyle( 'color', 'red' );
				var newInput = new Element( 'input', {
					'type': 'hidden',
					'name': 'partials[]',
					'value': panelName
				});
				newInput.injectInside( partialPanel );
			}
		});
	});
}

/**
 * Add up/down arrows to each panel in a given column
 */
function setCells()
{
	// add up/down arrows
	for( col = 1; col <= panelColCount; col++ ) {
		// get an array of each row in the table for the current column
		window['colRows' + col] = $('adminForm').getElements('*[class^=row_' + col + ']');
		
		// store array of arrow table cells in a global variable
		window['arrowsCells' + col] = $$('.arrows_' + col );
		
		// store array of remove buttons in a global variable
		window['removeButtons' + col] = $$('.remove_panel_' + col );
		
		// add the up / down arrows if needed
		var curArrowsCells = window['arrowsCells' + col];
		var curLength = curArrowsCells.length;
		if( curLength > 1 ) {
			curArrowsCells.each( function(cell, i) {
				// empty any exisiting arrows
				cell.empty();
				
				// set down arrow only
				if( i == 0 ) {
					_getArrowSpan( '&nbsp;' ).injectInside( cell );
					_addDownArrow( cell, col, i );
				}
				// set up arrow only
				else if( i == (curLength - 1) ) {
					_addUpArrow( cell, col, i );
					_getArrowSpan( '&nbsp;' ).injectInside( cell );
				}
				// set both
				else {
					_addDownArrow( cell, col, i );
					_addUpArrow( cell, col, i );
				}
			});
		}
		else if( curLength == 1 ) {
			curArrowsCells.getLast().empty();
		}
		
		// add remove events to remove images
		var curRemoveButtons = window['removeButtons' + col];
		curRemoveButtons.each( function(removeButton, i) {
			_addRemoveEvent( removeButton, col, i );
		});
	}
}

/**
 * Add a down arrow to given element
 * 
 * @param element cell  Element to add down arrow to
 * @param int col  The current column
 * @param int row  The current row
 */
function _addDownArrow( cell, col, row )
{
	var span = _getArrowSpan();
	span.injectInside( cell );
	
	var html = new Element( 'img', {
		'styles': {
			'cursor': 'pointer'
		},
		'events': {
			'click': function() {
				window['colRows' + col][row].injectAfter( window['colRows' + col][row + 1] );
				setCells();
			}
		},
		'width': '16',
		'height': '16',
		'border': '0',
		'alt': 'Move Down',
		'title': 'Move Down',
		'src': 'images/downarrow.png'
	});
	html.injectInside( span );
}

/**
 * Add an up arrow to given element
 * 
 * @param element cell  Element to add up arrow to
 * @param int col  The current column
 * @param int row  The current row
 */
function _addUpArrow( cell, col, row )
{
	var span = _getArrowSpan();
	span.injectTop( cell );
	
	var html = new Element( 'img', {
		'styles': {
			'cursor': 'pointer'
		},
		'events': {
			'click': function() {
				window['colRows' + col][row].injectBefore( window['colRows' + col][row - 1] );
				setCells();
			}
		},
		'width': '16',
		'height': '16',
		'border': '0',
		'alt': 'Move Down',
		'title': 'Move Down',
		'src': 'images/uparrow.png'
	});
	
	html.injectInside( span );
}

/**
 * Get a span for an ordering arrow
 * 
 * @param string blank  Optional contents for the span, inserted as HTML
 * @return element arrowSpan  the span element
 */
function _getArrowSpan( blank )
{
	var arrowSpan = new Element( 'span', {
		'styles': {
			'display': 'block',
			'float': 'left',
			'text-align': 'center',
			'width': '16px'
		}
	});
	
	// add contents if any
	if( blank != undefined ) {
		arrowSpan.setHTML( blank );
	}
	
	return arrowSpan;
}

/**
 * Add the remove event to a remove button
 * 
 * @param element removeButton  The remove button element
 * @param int col  The current column
 * @param int row  The current row
 */
function _addRemoveEvent( removeButton, col, row )
{
	var panelId = removeButton.className.match( /panel_id_(.*)(?=$|\W)/ ).getLast();
	var panelName = window['colRows' + col][row].getFirst().getText();
	
	removeButton.removeEvents();
	removeButton.addEvent( 'click', function() {
		// remove panel
		window['colRows' + col][row].remove();
		setCells();
		
		// add the panel to the unused list
		var unusedPanel = archPanelAdd.clone();
		var contents = unusedPanel.innerHTML;
		contents = contents.replace( /_panel_name_/g, panelName );
		contents = contents.replace( /_panel_id_/g, panelId );
		unusedPanel.setHTML( contents );
		unusedPanel.removeProperty( 'id' );
		unusedPanel.injectInside( addPanelTbody );
		setAddButtons();
	});
}

/**
 * Set up the functionality of the add buttons in the unused panel table
 */
function setAddButtons()
{
	// get all the add panel buttons
	addButtons = $('adminForm').getElements('*[class^=add_panel]');
	
	// add the add event to each button
	addButtons.each( function(addButton) {
		// get the info we need
		var parts = addButton.className.split( '_' );
		var panelId = parts[2];
		var col = parts[3];
		var parentRow = addButton.getParent().getParent();
		var panelName = parentRow.getFirst().getText();
		
		addButton.removeEvents();
		addButton.addEvent( 'click', function() {
			// remove from unused panels
			parentRow.remove();
			
			// add to the relevant column
			var newPanel = archPanelRows[col - 1].clone();
			var contents = newPanel.innerHTML;
			contents = contents.replace( /_panel_name_/g, panelName );
			contents = contents.replace( /_panel_id_/g, panelId );
			newPanel.setHTML( contents );
			newPanel.removeClass( 'arch_panel_row' );
			newPanel.injectInside( panelRowTbodies[col - 1] );
			setValues();
			setCells();
		});
	});
}