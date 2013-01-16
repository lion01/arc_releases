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
	// get the names of the panels containing links
	linkPanelNames = Json.evaluate( $('link_panel_names').getValue() );
	
	// get all the archetype link rows and then remove them from the page
	archLinkRows = $$('.arch_link_row');
	archLinkRows.each( function(archLinkRow) {
		archLinkRow.remove();
	});
	
	// set up the ordering arrows and remove button functionality 
	setLinksCells();
	
	//setup partial links behaviour
	setLinkPartials();
	
	// setup the add panel buttons
	setLinkAddEvents();
	
	// get the tbody sections of the links tables
	linkRowTbodies = $('adminForm').getElements('*[id^=link_row_tbody]');
});

/**
 * Add up/down arrows and remove buttons to each link in a given column
 */
function setLinksCells()
{
	// add up/down arrows
	linkPanelNames.each( function(panel, p) {
		// get an array of each row in the table for the current panel
		window['panelRows' + panel] = $('adminForm').getElements('*[class^=row_' + panel + ']');
		
		// store array of arrow table cells in a global variable
		window['linkArrowsCells' + panel] = $$('.link_arrows_' + panel );
		
		// store array of remove buttons in a global variable
		window['linkRemoveButtons' + panel] = $$('.remove_link_' + panel );
		
		// store array of unused select lists and their containing divs
		window['unusedSelect' + panel] = $('adminForm').getElement('*[id^=unused_select_' + panel + ']');
		window['unusedSelectDiv' + panel] = $('adminForm').getElement('*[id^=unused_select_div_' + panel + ']');
		
		// store array of used select lists
		window['usedSelect' + panel] = $('adminForm').getElement('*[id^=used_select_' + panel + ']');
		
		// add the up / down arrows if needed
		var curArrowsCells = window['linkArrowsCells' + panel];
		var curLength = curArrowsCells.length;
		if( curLength > 1 ) {
			curArrowsCells.each( function(cell, i) {
				// empty any exisiting arrows
				cell.empty();
				
				// set down arrow only
				if( i == 0 ) {
					_getLinkArrowSpan( '&nbsp;' ).injectInside( cell );
					_addLinkDownArrow( cell, panel, i );
				}
				// set up arrow only
				else if( i == (curLength - 1) ) {
					_addLinkUpArrow( cell, panel, i );
					_getLinkArrowSpan( '&nbsp;' ).injectInside( cell );
				}
				// set both
				else {
					_addLinkDownArrow( cell, panel, i );
					_addLinkUpArrow( cell, panel, i );
				}
			});
		}
		else if( curLength == 1 ) {
			curArrowsCells.getLast().empty();
		}
		
		// add remove events to remove images
		var curRemoveButtons = window['linkRemoveButtons' + panel];
		curRemoveButtons.each( function(linkRemoveButton, i) {
			_addLinkRemoveEvent( linkRemoveButton, panel, i );
		});
		
		// hide the unused select list if empty and vice-versa
		if( window['unusedSelect' + panel].getChildren().length == 0 ) {
			window['unusedSelectDiv' + panel].setStyle( 'display', 'none' );
		}
		else {
			window['unusedSelectDiv' + panel].setStyle( 'display', null );
		}
	});
}

/**
 * Add a down arrow to given element
 * 
 * @param element cell  Element to add down arrow to
 * @param int panel  The current panel
 * @param int row  The current row
 */
function _addLinkDownArrow( cell, panel, row )
{
	var span = _getLinkArrowSpan();
	span.injectInside( cell );
	
	var html = new Element( 'img', {
		'styles': {
			'cursor': 'pointer'
		},
		'events': {
			'click': function() {
				window['panelRows' + panel][row].injectAfter( window['panelRows' + panel][row + 1] );
				setLinksCells();
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
 * @param int panel  The current panel
 * @param int row  The current row
 */
function _addLinkUpArrow( cell, panel, row )
{
	var span = _getLinkArrowSpan();
	span.injectTop( cell );
	
	var html = new Element( 'img', {
		'styles': {
			'cursor': 'pointer'
		},
		'events': {
			'click': function() {
				window['panelRows' + panel][row].injectBefore( window['panelRows' + panel][row - 1] );
				setLinksCells();
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
function _getLinkArrowSpan( blank )
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
 * @param element linkRemoveButton  The remove button element
 * @param int panel  The current panel
 * @param int row  The current row
 */
function _addLinkRemoveEvent( linkRemoveButton, panel, row )
{
	var linkId = linkRemoveButton.className.match( /link_id_(.*)(?=$|\W)/ ).getLast();
	var linkName = window['panelRows' + panel][row].getFirst().getText();
	
	linkRemoveButton.removeEvents();
	linkRemoveButton.addEvent( 'click', function() {
		// remove link from list
		window['panelRows' + panel][row].remove();
		
		// grab the option from the used select list
		var option = $( 'used_link_' + panel + '_' + linkId );
		
		// remove it from the used list
		option.remove();
		
		// insert it into the unused list and update its element id
		option.injectInside( window['unusedSelect' + panel] );
		option.setProperty( 'id', option.getProperty('id').replace('used_', 'unused_') );
		
		// refresh
		setLinksCells();
	});
}

/**
 * Add the functionality to toggle partial links into universal links
 */
function setLinkPartials()
{
	// get all the partial links
	var partialLinks = $$('.partial_link');
	
	// add the click event
	partialLinks.each( function(partialLink) {
		var span = $E( 'span', partialLink );
		var linkName = span.getText().trim();
		
		partialLink.addEvent( 'click', function() {
			var hiddenInput = $E( 'input', partialLink );
			if( hiddenInput != undefined ) {
				span.setStyle( 'color', 'green' );
				hiddenInput.remove();
			}
			else {
				span.setStyle( 'color', 'red' );
				var newInput = new Element( 'input', {
					'type': 'hidden',
					'name': 'partials[]',
					'value': linkName
				});
				newInput.injectInside( partialLink );
			}
		});
	});
}

/**
 * Add the events to each unused select list option to add it to the list
 */
function setLinkAddEvents()
{
	// get all the options in the unused select list
	addOptions = $('adminForm').getElements('*[id^=unused_link_]');
	
	// add the add event to each select option
	addOptions.each( function(addOption) {
		// get the info we need
		var parts = addOption.getProperty( 'id' ).split( '_' );
		var panel = parts[2];
		var linkId = parts[3];
		var title = addOption.getProperty( 'title' );
		var linkName = addOption.getText();
		
		addOption.removeEvents();
		addOption.addEvent( 'click', function() {
			// hide tool-tip
			addOption.fireEvent( 'mouseleave' );
			
			// remove from unused select list
			addOption.remove();
			
			// add to the used select list
			addOption.injectInside( window['usedSelect' + panel] );
			addOption.setProperty( 'id', addOption.getProperty('id').replace('unused_', 'used_') );
			
			// add to the link list
			archLinkRows.each( function(archLinkRow) {
				if( archLinkRow.hasClass('row_' + panel) ) {
					newListRow = archLinkRow.clone();
				}
			});
			var contents = newListRow.innerHTML;
			contents = contents.replace( /_link_title_/g, title );
			contents = contents.replace( /_link_text_/g, linkName );
			contents = contents.replace( /_link_id_/g, linkId );
			newListRow.setHTML( contents );
			newListRow.removeClass( 'arch_link_row' );
			linkRowTbodies.each( function(linkRowTbody) {
				if( linkRowTbody.getProperty( 'id') == 'link_row_tbody_' + panel ) {
					newListRow.injectInside( linkRowTbody );
				}
			});
			new Tips( newListRow.getElement('span[class=hasTip]') );
			
			// refresh
			setLinksCells();
		});
	});
}