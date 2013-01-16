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
	// get the form
	adminForm = $('adminForm');
	
	// get the archetype roles row and then remove it from the page
	archRoleRow = $('arch_role_row');
	archRoleRow.removeProperty('id');
	archRoleRow.remove();
	
	// get the unused select list
	unusedRolesSelect = $('unused_roles_list');
	
	// get the used select list
	usedRolesSelect = $('used_roles_list');
	
	// get the div containing the unsed roles list
	unusedRolesSelectDiv = $('role_list_div');
	
	// get the tbody sections of the roles tables
	roleRowTbody = $('roles_tbody');
	
	// add the functionality to move unused roles into the table
	setRoleAddEvents();
	
	// add existing roles into the table
	setExistingRoles();
	
	// add the role remove functionality
	setRoleRemove();
});

/**
 * Add the events to the unused select list option to add it to the list
 */
function setRoleAddEvents()
{
	// add the event onto the list to add options to 'used' list
	unusedRolesSelect.addEvent( 'change', function() {
		
		unusedRolesSelect.getElements('option').each( function(addOption) {
			if( addOption.selected == true ) {
				addOption.remove()
				
				addOption.injectInside( usedRolesSelect );
				// add to the roles list
				newRoleRow = archRoleRow.clone();
				
				var contents = newRoleRow.innerHTML;
				contents = contents.replace( /_role_id_/g, addOption.getProperty('value') );
				contents = contents.replace( /_role_text_/g, addOption.getText() );
				newRoleRow.setHTML( contents );
				newRoleRow.injectInside( roleRowTbody );
				setRoleRemove();
				checkRolesListHide();
			}
			
		} );
		
	} );
	
}

/**
 * add existing roles to the table
 */
function setExistingRoles()
{
	// get the currently held roles
	existingRoles = Json.evaluate( adminForm.getElement('input[name=existing_roles]').getValue() );
	
	// loop through existing roles and add the relevant row to the table
	existingRoles.each( function(role) {
		unusedRolesSelect.getElement('option[value=' + role + ']').selected = true;
		unusedRolesSelect.fireEvent( 'change' );
	});
}

/**
 * Add the remove events to remove buttons
 */
function setRoleRemove()
{
	// get all the remove buttons
	var removeButtons = $('adminForm').getElements('*[class^=remove_role]');
	
	// get all the rows in this table
	var roleRows = roleRowTbody.getElements('tr');
	
	// add remove events to remove images
	removeButtons.each( function(removeButton, i) {
		removeButton.removeEvents();
		removeButton.addEvent( 'click', function() {
			// remove role row
			roleRows[i].remove();
			
			// move used option backinto unused options
			var roleId = roleRows[i].getElement('input[name^=roles]').getProperty('value');
			var usedOption = usedRolesSelect.getElement('option[value=' + roleId + ']');
			usedOption.injectInside( unusedRolesSelect );
			checkRolesListHide();
		});
	});
}

function checkRolesListHide()
{
	// hide the unused select list if empty and vice-versa
	if( unusedRolesSelect.getChildren().length == 0 ) {
		unusedRolesSelectDiv.setStyle( 'display', 'none' );
	}
	else {
		unusedRolesSelectDiv.setStyle( 'display', null );
	}
}