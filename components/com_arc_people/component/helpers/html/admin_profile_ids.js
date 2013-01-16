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
	archIdRow = $('arch_ids_row');
	archIdRow.remove();
	
	// get the names of current properties
	idProps = new Array();
	idPropertyTDs = $$('.ids_property');
	idPropertyTDs.each( function(idPropTD) {
		idProps.include( idPropTD.getText().trim() );
	});
	
	// setup the remove functionality 
	setIdRemove();
	
	// setup the value checker functionality 
	setIdValueChecker();
	
	// setup the add panel buttons
	setIdAddButton();
	
	// setup partial properties behaviour
	setIdPartials();
	
	// setup padlocks behaviour
	setIdPadlocks();
});

/**
 * Add the remove events to remove buttons
 */
function setIdRemove()
{
	// get all the remove buttons in this table
	var removeButtons = $('adminForm').getElements('*[class^=remove_ids_]');
	
	// get all the rows in this table
	var idsRows = $$('.row_ids');
	
	// add remove events to remove images
	removeButtons.each( function(removeButton, i) {
		removeButton.removeEvents();
		removeButton.addEvent( 'click', function() {
			// remove id row
			idProps.remove( idsRows[i].getFirst().getText() );
			idsRows[i].remove();
		});
	});
}

/**
 * Add the add event to add button
 */
function setIdAddButton()
{	
	// get the ID add table row
	idAddRow = $('ids_add_row');
	
	// get the ID add button
	var addButton = $('add_ids');
	
	// get the input for the new name
	var newIdPropInput = $('new_ids_prop_input');
	
	// make all text entry in the new ID input box uppercase
	newIdPropInput.addEvent( 'keyup', function() {
		this.value = this.getValue().toUpperCase();
	});
	
	// add the add event
	addButton.addEvent( 'click', function() {
		// get the new property name
		var newProp = newIdPropInput.getValue();
		
		// check new property is not empty
		if( newProp == '' ) {
			alert( 'Please enter a new property name before adding it to the profile.' );
		}
		
		// check new property does not already exist
		else if( idProps.contains( newProp ) ) {
			alert( 'The property name '+ newProp + ' already exists. Please choose another.' );
		}
		
		else {
			// clone an archetype ID row and add it
			var newIdRow = archIdRow.clone();
			var contents = newIdRow.innerHTML;
			contents = contents.replace( /_ids_property_/g, newProp );
			newIdRow.setHTML( contents );
			newIdRow.removeProperty( 'id' );
			newIdRow.injectBefore( idAddRow );
			newIdPropInput.value = '';
			idProps.include( newProp );
			setIdRemove();
			setIdValueChecker();
		}
	});
}

/**
 * Add the value checker event
 */
function setIdValueChecker()
{
	// get all the Values
	var valueValues = $$('.ids_value');
	
	// add the checking event
	valueValues.each( function(valueValue) {
		valueValue.removeEvents();
		valueValue.addEvent( 'keyup', function() {
			if( valueValue.getValue().toLowerCase() == '*** locked ***' ) {
				valueValue.value = '';
				alert( '*** Locked *** is a restricted phrase.' );
			}
		});
	});
}

/**
 * Add the functionality to toggle partial properties into universal properties
 */
function setIdPartials()
{
	// get all the partial properties
	var partialProps = $$('.ids_partial_property');
	
	// add the click event
	partialProps.each( function(partialProp) {
		var span = $E( 'span', partialProp );
		var propName = span.getText().trim();
		var hiddenInput = $E( 'input', partialProp );
		
		partialProp.addEvent( 'click', function() {
			var hiddenInput = $E( 'input', partialProp );
			if( hiddenInput != undefined ) {
				span.setStyle( 'color', 'green' );
				hiddenInput.remove();
				idLockCheck( propName, true );
			}
			else {
				span.setStyle( 'color', 'red' );
				var newInput = new Element( 'input', {
					'type': 'hidden',
					'name': 'partials[]',
					'value': propName
				});
				newInput.injectInside( partialProp );
				idLockCheck( propName, false );
			}
		});
	});
}

/**
 * Add the padlock functionality
 */
function setIdPadlocks()
{
	// declare a hash map to store the lockable values in
	lockableIdsValuesHash = new Hash();
	
	// declare a hash map to store padlocks in
	lockableIdsPadlocksHash = new Hash();
	
	// get all the padlocks and associated values and enter them into the hash map
	$$('.ids_value_lock').each( function(padlock) {
		var propName = padlock.getParent().getPrevious().getText().trim();
		lockableIdsValuesHash.set( propName, new Hash({'input': $E('input', padlock.getPrevious()), 'original': $E('input', padlock.getPrevious()).getValue()}) );
		lockableIdsPadlocksHash.set( propName, padlock );
	});
	
	// add events to the padlocks
	lockableIdsPadlocksHash.each( function(value, key) {
		value.addEvent( 'click', function() {
			toggleIdLock( key );
		});
	});
}

/**
 * Toggle the lock status of the given property value
 * 
 * @param string propName  The name of the property
 */
function toggleIdLock( propName )
{
	var padlock = lockableIdsPadlocksHash.get( propName );
	var valueInput = lockableIdsValuesHash.get(propName).get('input');
	var valueInputOriginal = lockableIdsValuesHash.get(propName).get('original');
	
	if( valueInput.getValue() == '*** Locked ***' ) {
		padlock.setStyle( 'background-position', '0px -16px ' );
		valueInput.removeProperty( 'readonly' );
		valueInput.setStyles({
			'background': null,
			'text-align': null
		});
		valueInput.addClass( 'ids_value' );
		setIdValueChecker();
		valueInput.value = ( valueInputOriginal == '*** Locked ***' ) ? '' : valueInputOriginal;
	}
	else {
		padlock.setStyle( 'background-position', null );
		valueInput.setProperty( 'readonly', 'readonly' );
		valueInput.setStyles({
			'background': '#FFFFDD',
			'text-align': 'center'
		});
		valueInput.removeClass( 'ids_value' );
		valueInput.removeEvents();
		valueInput.value = '*** Locked ***';
	}
}

/**
 * Determine if a property value should be locked after toggling the universal status
 * 
 * @param string propName  The name of the property
 * @param boolean makingUniversal  Are we making the property universal in this action?
 */
function idLockCheck( propName, makingUniversal )
{
	var valueInput = lockableIdsValuesHash.get(propName).get('input');
	var currentValue = valueInput.getValue();
	var originalValue = lockableIdsValuesHash.get(propName).get('original');
	var padlock = lockableIdsPadlocksHash.get(propName);
	
	// we are making the partial prop into a universal
	if( makingUniversal ) {
		if( (currentValue != '*** Locked ***') && (currentValue != '') ) {
			toggleIdLock( propName );
			padlock.setStyle( 'display', null );
		}
	}
	// we are resetting the partial prop back to partial
	else {
			if( (originalValue == '*** Locked ***') && (currentValue != '*** Locked ***') ) {
				toggleIdLock( propName );
			}
			else if( (originalValue != '*** Locked ***') && (currentValue == '*** Locked ***') ) {
				toggleIdLock( propName );
				padlock.setStyle( 'display', 'none' );
			}
			else if( (originalValue != '*** Locked ***') && (currentValue != '*** Locked ***') ) {
				valueInput.value = originalValue;
				padlock.setStyle( 'display', 'none' );
			}
	}
}