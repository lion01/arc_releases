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
	// get the archetype personal row and then remove it from the page
	archPersonalRow = $('arch_personal_row');
	archPersonalRow.remove();
	
	// get the names of current properties
	personalProps = new Array();
	personalPropertyTDs = $$('.personal_property');
	personalPropertyTDs.each( function(personalPropTD) {
		personalProps.include( personalPropTD.getText().trim().toLowerCase() );
	});
	
	// setup the remove functionality 
	setPersonalRemove();
	
	// setup the value checker functionality 
	setPersonalValueChecker();
	
	// setup the add panel buttons
	setPersonalAddButton();
	
	// setup partial properties behaviour
	setPersonalPartials();
	
	// setup padlocks behaviour
	setPersonalPadlocks();
});

/**
 * Add the remove events to remove buttons
 */
function setPersonalRemove()
{
	// get all the remove buttons in this table
	var removeButtons = $('adminForm').getElements('*[class^=remove_personal_]');
	
	// get all the rows in this table
	var personalRows = $$('.row_personal');
	
	// add remove events to remove images
	removeButtons.each( function(removeButton, i) {
		removeButton.removeEvents();
		removeButton.addEvent( 'click', function() {
			// remove personal row
			personalProps.remove( personalRows[i].getFirst().getText() );
			personalRows[i].remove();
		});
	});
}

/**
 * Add the add event to add button
 */
function setPersonalAddButton()
{	
	// get the personal add table row
	personalAddRow = $('personal_add_row');
	
	// get the personal add button
	var addButton = $('add_personal');
	
	// get the input for the new name
	var newPersonalPropInput = $('new_personal_prop_input');
	
	// add the add event
	addButton.addEvent( 'click', function() {
		// get the new property name
		var newProp = newPersonalPropInput.getValue();
		
		// check new property is not empty
		if( newProp == '' ) {
			alert( 'Please enter a new property name before adding it to the profile.' );
		}
		
		// check new property does not already exist
		else if( personalProps.contains( newProp.toLowerCase() ) ) {
			alert( 'The property name '+ newProp + ' already exists. Please choose another.' );
		}
		
		else {
			// clone an archetype personal row and add it
			var newPersonalRow = archPersonalRow.clone();
			var contents = newPersonalRow.innerHTML;
			contents = contents.replace( /_personal_property_/g, newProp );
			newPersonalRow.setHTML( contents );
			newPersonalRow.removeProperty( 'id' );
			newPersonalRow.injectBefore( personalAddRow );
			newPersonalPropInput.value = '';
			personalProps.include( newProp );
			setPersonalRemove();
			setPersonalValueChecker();
		}
	});
}

/**
 * Add the value checker event
 */
function setPersonalValueChecker()
{
	// get all the Values
	var valueValues = $$('.personal_value');
	
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
function setPersonalPartials()
{
	// get all the partial properties
	var partialProps = $$('.personal_partial_property');
	
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
				personalLockCheck( propName, true );
			}
			else {
				span.setStyle( 'color', 'red' );
				var newInput = new Element( 'input', {
					'type': 'hidden',
					'name': 'partials[]',
					'value': propName
				});
				newInput.injectInside( partialProp );
				personalLockCheck( propName, false );
			}
		});
	});
}

/**
 * Add the padlock functionality
 */
function setPersonalPadlocks()
{
	// declare a hash map to store the lockable values in
	lockablePersonalValuesHash = new Hash();
	
	// declare a hash map to store padlocks in
	lockablePersonalPadlocksHash = new Hash();
	
	// get all the padlocks and associated values and enter them into the hash map
	$$('.personal_value_lock').each( function(padlock) {
		var propName = padlock.getParent().getPrevious().getText().trim();
		lockablePersonalValuesHash.set( propName, new Hash({'input': $E('input', padlock.getPrevious()), 'original': $E('input', padlock.getPrevious()).getValue()}) );
		lockablePersonalPadlocksHash.set( propName, padlock );
	});
	
	// add events to the padlocks
	lockablePersonalPadlocksHash.each( function(value, key) {
		value.addEvent( 'click', function() {
			togglePersonalLock( key );
		});
	});
}

/**
 * Toggle the lock status of the given property value
 * 
 * @param string propName  The name of the property
 */
function togglePersonalLock( propName )
{
	var padlock = lockablePersonalPadlocksHash.get( propName );
	var valueInput = lockablePersonalValuesHash.get(propName).get('input');
	var valueInputOriginal = lockablePersonalValuesHash.get(propName).get('original');
	
	if( valueInput.getValue() == '*** Locked ***' ) {
		padlock.setStyle( 'background-position', '0px -16px ' );
		valueInput.removeProperty( 'readonly' );
		valueInput.setStyles({
			'background': null,
			'text-align': null
		});
		valueInput.addClass( 'personal_value' );
		setPersonalValueChecker();
		valueInput.value = ( valueInputOriginal == '*** Locked ***' ) ? '' : valueInputOriginal;
	}
	else {
		padlock.setStyle( 'background-position', null );
		valueInput.setProperty( 'readonly', 'readonly' );
		valueInput.setStyles({
			'background': '#FFFFDD',
			'text-align': 'center'
		});
		valueInput.removeClass( 'personal_value' );
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
function personalLockCheck( propName, makingUniversal )
{
	var valueInput = lockablePersonalValuesHash.get(propName).get('input');
	var currentValue = valueInput.getValue();
	var originalValue = lockablePersonalValuesHash.get(propName).get('original');
	var padlock = lockablePersonalPadlocksHash.get(propName);
	
	// we are making the partial prop into a universal
	if( makingUniversal ) {
		if( (currentValue != '*** Locked ***') && (currentValue != '') ) {
			togglePersonalLock( propName );
			padlock.setStyle( 'display', null );
		}
	}
	// we are resetting the partial prop back to partial
	else {
			if( (originalValue == '*** Locked ***') && (currentValue != '*** Locked ***') ) {
				togglePersonalLock( propName );
			}
			else if( (originalValue != '*** Locked ***') && (currentValue == '*** Locked ***') ) {
				togglePersonalLock( propName );
				padlock.setStyle( 'display', 'none' );
			}
			else if( (originalValue != '*** Locked ***') && (currentValue != '*** Locked ***') ) {
				valueInput.value = originalValue;
				padlock.setStyle( 'display', 'none' );
			}
	}
}