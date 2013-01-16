/**
 * @package     Arc
 * @subpackage  People
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function()
{
	// get the archetype id row and then remove it from the page
	archTemplateRow = $('arch_row');
	archTemplateRow.remove();
	
	// get the names of current properties
	templateIds = new Array();
	templateIdTDs = $$('.template_id');
	templateIdTDs.each( function(templateIdTD) {
		templateIds.include( templateIdTD.getText().toLowerCase() );
	});
	
	// setup the remove functionality 
	setTemplateIdRemove();
	
	// setup the add panel buttons
	setTemplateIdAddButton();
});

/**
 * Add the remove events to remove buttons
 */
function setTemplateIdRemove()
{
	// get all the remove buttons in this table
	var removeButtons = $('adminForm').getElements('*[class^=remove_]');
	
	// get all the rows in this table
	var rows = $$('.row');
	
	// add remove events to remove images
	removeButtons.each( function(removeButton, i) {
		removeButton.removeEvents();
		removeButton.addEvent( 'click', function() {
			// remove template id row
			templateIds.remove( rows[i].getFirst().getText() );
			rows[i].remove();
		});
	});
}

/**
 * Add the add event to add button
 */
function setTemplateIdAddButton()
{	
	// get the template ID add table row
	templateIdAddRow = $('template_add_row');
	
	// get the template ID add button
	var addButton = $('add_template');
	
	// get the input for the new name
	var newTemplateIdInput = $('new_template_id_input');
	
	// add the add event
	addButton.addEvent( 'click', function(e) {
		// get the new template name
		var newTemplateId = newTemplateIdInput.getValue();
		
		// check new property is not empty
		if( newTemplateId == '' ) {
			alert( 'Please enter a new template name before adding it to the list.' );
		}
		
		// check new property does not already exist
		else if( templateIds.contains( newTemplateId.toLowerCase() ) ) {
			alert( 'The template name '+ newTemplateId + ' already exists. Please choose another.' );
		}
		
		else {
			// clone an archetype template ID row and add it
			var newTemplateIdRow = archTemplateRow.clone();
			var contents = newTemplateIdRow.innerHTML;
			contents = contents.replace( /_template_id_/g, newTemplateId.toLowerCase() );
			newTemplateIdRow.setHTML( contents );
			newTemplateIdRow.removeProperty( 'id' );
			newTemplateIdRow.injectBefore( templateIdAddRow );
			newTemplateIdInput.value = '';
			templateIds.include( newTemplateId.toLowerCase() );
			setTemplateIdRemove();
		}
	});
}