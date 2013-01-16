/**
 * @package     Arc
 * @subpackage  Course
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

function submitbutton( pressbutton )
{
	var adminForm = $( 'adminForm' );
	var checkStrings = check_strings;
	var ser = new PHP_Serializer();
	var labels = $$( 'label' );
	var badInputs = Array();
	var alertStrings = Array();
	
	// If we pressed the cancel button then submit and end script
	if( pressbutton == 'cancel' ) {
		submitform( pressbutton );
		return;
	}
	
	// Validate the input fields
	if( adminForm.fullname.getValue() == "" ) {
		badInputs.include( 'fullname' );
	}
	if( adminForm.description.getValue() == "" ) {
		badInputs.include( 'description' );
	}
	if( adminForm.shortname.getValue() == "" ) {
		badInputs.include( 'shortname' );
	}
	if( adminForm.type.getValue() == "" ) {
		badInputs.include( 'type' );
	}
	if( ser.unserialize(adminForm.parent.getValue()) == "" ) {
		badInputs.include( 'parent' );
	}
	if( adminForm.start_date.getValue() == "" ) {
		badInputs.include( 'start_date' );
	}
	
	// Loop through the labels and check if input is bad, colour label and prep alert accordingly
	labels.each( function(label) {
		var input = label.getProperty( 'for' );
		if( badInputs.contains( input ) ) {
			label.setStyle( 'color', 'red' );
			alertStrings.include( checkStrings.get(input) )
		}
		else {
			label.setStyle( 'color', null );
		}
	});
	
	// Check for bad inputs and alert or submit the form
	if( alertStrings.getLast() != null ) {
		var alertString = alertStrings.join( '\r\n\t' );
		alert( 'You must supply the following information:\r\n\t' + alertString );
	}
	else {
		submitform( pressbutton );
	}
}