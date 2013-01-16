/**
 * @package     Arc
 * @subpackage  Core
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

// ***
// Anywhere we loop through the (potentially very large) option array we use 'for' because
// 'for' is faster than '.each' (http://benhollis.net/blog/2009/12/13/investigating-javascript-array-iteration-performance/)
// ***

/**
 * Extend the existing class to add functionality to add entriely new options
 * This allows us to add new options to the database
 */

window.addEvent( 'domready', function() {
	// find each combo list on the page and add them all to a holding array
	var comboAdds = $$('.arc_combo_add');
	ArcComboAdds = Array();
	
	comboAdds.each( function(comboAdd) {
		ArcComboAdds.push( new ArcComboAdd(comboAdd) );
	});
});

/**
 * Arc Combo Add class
 * Initialises and deals with the combo list as a whole
 * Now with the ability to add new options by extending the Arc Combo class
 */
var ArcComboAdd = ArcCombo.extend({
	// Define which combo options class to use
	comboClass: function( a, b, c ) {
		return new ArcComboAddOptions( a, b, c );
	},
	
	// Process potential new options
	createNew: function( newOpts ) {
		// Get the current combo options as a hash
		this.curOptsLookup = new Hash();
		var l = this.list.pool.length;
		
		for( var i = 0; i < l; i++ ) {
			this.curOptsLookup.set( this.list.pool[i].text, i );
		}
		
		// Loop through new options and process as required
		this.createdOpts = new Array();
		newOpts.each( function(newOpt) {
			// Check if the option is already in the combo list somewhere
			if( this.curOptsLookup.hasKey(newOpt) ) { // yes we have it in the combo list
				// Get the existing option
				var existingOpt = this.list.pool[this.curOptsLookup.get(newOpt)];
				
				// If the existing option isn't selected then select it
				// otherwise briefly highlight it in the chosen area
				if( !existingOpt.selected ) {
					existingOpt.element.fireEvent( 'click' );
				}
				else {
					this.chosen.element.getChildren().each( function(chosenDiv) {
						if( chosenDiv.getText() == newOpt ) {
							chosenDiv.addClass( 'highlight' );
							( function() {chosenDiv.removeClass('highlight')} ).delay( 500 );
						}
					});
				}
			}
			else { // not in the combo list
				// Define a new hash to hold the value/text pairs for the new options hidden input
				if( this.newOptsHiddenHash == undefined ) {
					this.newOptsHiddenHash = new Hash();
				}
				
				// Define values for new (negative) options
				if( this.newVal == undefined ) {
					this.newVal = -1;
				}
				
				// Create new html option, inject it into original html input and store it in holding array
				var newOption = new Element( 'option', {'value': this.newVal} ).inject( this.element );
				newOption.setText( newOpt );
				this.createdOpts.push( newOption );
				this.newOptsHiddenHash.set( this.newVal, newOpt );
				this.newVal--;
			}
		}.bind(this));
		
		// If we have created new options put them into a hidden input
		if( this.createdOpts.length > 0 ) {
			// Check to see if we need to create the hidden input and do so if necessary
			if( this.newOptsHiddenInput == undefined ) {
				// Hidden input
				this.newOptsHiddenInput = new Element( 'input', {
					'type': 'hidden',
					'id': this.element.getProperty( 'id' ) + '_hidden',
					'name': this.element.getProperty( 'id' ) + '_hidden',
					'value': ''
				} ).injectBefore( this.container );
			}
			
			// Get value/text pairs for the new options hidden input, JSON encode and add them
			var newOptsHiddenValue = Json.toString( this.newOptsHiddenHash.obj );
			this.newOptsHiddenInput.setProperty( 'value', newOptsHiddenValue );
		}
	}
});

/**
 * Extend the existing class to redefine the makeSelection() function
 * This allows us to add new options to the database
 */
var ArcComboAddOptions = ArcComboOptions.extend({
	// Add the combo option we just clicked to the used area the original html input
	// Now extended to grab and process the combo input directly
	makeSelection: function() {
		// Perform validity check on a possibly considered option
		this.consideredCheck();
		
		// If no option is considered then process the entered data as new options
		if( (this.considered == undefined) || (this.considered == null) ) {
			// Get the entered text
			var inputText = this.combo.input.get();
			
			// Proceed if there is input to process
			if( (inputText != '') && (inputText != ' ') ) {
				// Collect individual words from the entered text
				var newOpts = new Array();
				
				inputText.split( ' ' ).each( function(newOpt) {
					if( newOpt != '' ) {
						newOpts.include( newOpt );
					}
				});
				
				// Proceed if we have actually found some new options
				if( newOpts.length > 0 ) {
					// Create new options for original html input
					this.combo.createNew( newOpts );
					
					// Add the newly created options to the combo list
					this.combo.createdOpts.each( function(createdOpt) {
						var poolLength = this.addToPool( createdOpt );
						this.filter( this.pool[poolLength - 1].element.getText() );
						this.pool[poolLength - 1].element.fireEvent( 'click' );
					}.bind(this));
					
					return true;
				}
			}
			// No input to process so quit
			else {
				return false;
			}
		}
		// We have an exisiting combo option considered so use as normal
		else {
			this.selectConsidered();
			return true;
		}
	}
});