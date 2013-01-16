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

window.addEvent( 'domready', function() {
	// find each combo list on the page and add them all to a holding array
	var combos = $$('.arc_combo');
	ArcCombos = Array();
	
	combos.each( function(combo) {
		ArcCombos.push( new ArcCombo(combo) );
	});
});

/**
 * Arc Combo class
 * Initialises and deals with the combo list as a whole
 */
var ArcCombo = new Class({
	// Define which combo options class to use
	comboClass: function( a, b, c ) {
		return new ArcComboOptions( a, b, c );
	},
	
	// Here 'element' is the original html select
	// We hide the original html input, determine if it is a multi-select and set the visibility of the new combo list
	initialize: function( element ) {
		this.element = element;
		this.element.setStyle( 'display', 'none' );
		this.multiple = this.element.multiple;
		
		switch( this.element.getProperty('listvisibility') ) {
		case( 'show' ):
			this.listVisibility = 1;
			break;
		
		case( 'hide' ):
			this.listVisibility = -1;
			break;
		
		case( 'auto' ):
		default:
			this.listVisibility = 0;
			break;
		};
		
		// this.onCombo is synonymous with this.list.shown (i.e. is the drop-down list currently being shown)
		this.onCombo = false;
		
		// Set up the combo box inputs
		this.container      = new Element( 'div', {'class': 'arc_combo'} ).injectAfter( element );
		this.chosen         = new ArcComboChosen( this, new Element('div', {'class': 'arc_combo_chosen'}).injectInside(this.container) );
		this.inputContainer = new Element( 'div', {'class': 'arc_combo_inputcontainer'} ).injectInside( this.container );
		this.input          = new ArcComboInput( this, new Element('input', {'class': 'arc_combo_input'}).injectInside(this.inputContainer) );
		
		if( this.listVisibility == 0 ) {
			this.pulldown     = new ArcComboPulldown( this, new Element('div', {'class': 'arc_combo_pulldown'}).injectInside(this.inputContainer) );
			var pulldownW =     parseInt( this.pulldown.element.getSize().size.x );
		}
		else {
			var pulldownW = 0;
		}
		
		this.listContainer  = new Element( 'div', {'class': 'arc_combo_listcontainer'} ).injectInside( this.container );
		this.list           = this.comboClass( this, new Element('div', {'class': 'arc_combo_list', 'styles': {'display': 'none'}}).injectInside(this.listContainer), element );
		
		var containerW = parseInt( this.container.getSize().size.x );
		this.input.element.setStyle( 'width', (containerW - pulldownW - 10)+'px' );
		this.list.element.setStyle( 'width', (containerW)+'px' );
		
		this.preselect();
		
		if( this.listVisibility == 1 ) {
			this.list.show();
		}
		
		window.addEvent( 'arcResetSearch', function() {
			this.deselect();
		}.bind( this ));
		
	},
	
	// Function to delete the combo options (part of our custom garbage collection routine)
	trash: function() {
		this.list.trashPool();
	},
	
	// Choose the item(s) already selected in the original html input
	deselect: function() {
		var c = this.element.getChildren();
		var l = c.length;
		
		for( var i = 0; i < l; i++ ) {
			if( c[i].selected ) {
				c[i].selected = false;
			}
		}
		this.chosen.empty();
		this.input.empty();
		this.list.empty();
		
		this.list.initPool();
		this.list.resetPool();
		this.list.filter( this.input.get() );
		this.element.fireEvent( 'change' );
	},
	
	// Choose the item(s) already selected in the original html input
	preselect: function() {
		var c = this.element.getChildren();
		var l = c.length;
		
		for( var i = 0; i < l; i++ ) {
			if( c[i].selected && c[i].value != '' ) {
				this.chosen.add( c[i].value, c[i].text );
				this.list.add( c[i].value );
			}
		}
		
		this.list.filter( this.input.get() );
		this.element.fireEvent( 'change' );
	},
	
	// Select the considered option in the original html input
	// and add the considered option to the combo chosen list
	add: function( val, text ) {
		if( text != '' ) {
			if( !this.multiple ) {
				this.remove( this.element.value );
			}
			
			this.input.reset();
			this.chosen.add( val, text );
			this.list.add( val );
			this.list.filter( this.input.get() );
			this.element.fireEvent( 'change' );
		}
	},
	
	// De-select the considered option in the original html input
	// and remove the considered option from the combo chosen list
	remove: function( val, text ) {
		if( !this.multiple ) {
			this.element.value = '';
		}
		
		this.chosen.remove( val );
		this.list.remove( val );
		this.list.filter( this.input.get() );
		this.element.fireEvent( 'change' );
	}
});

/**
 * Arc Combo Chosen class
 * Defines and manages the page element that displays the currently chosen options
 */
var ArcComboChosen = new Class({
	// Adds chosen options area to the page
	initialize: function( combo, element ) {
		this.combo = combo;
		this.element = element;
		
		if( !this.combo.multiple ) {
			var tmp = new Element( 'p', {'style':'padding-top: 3px'} );
			tmp.innerHTML = 'Only 1: ';
			tmp.injectInside( this.element );
		}
	},
	
	// Adds a new option to the chosen area
	add: function( val, text ) {
		var tmp = new ArcComboChosenItem( this.combo, val, text );
		tmp.element.injectInside( this.element );
	},
	
	// Removes an option from the chosen area
	remove: function( val ) {
		var el = this.element.getElementById( val );
		
		if( el ) {
			el.remove();
		}
	},
	
	// Removes all options from the chosen area
	empty: function() {
		this.element.empty();
	}
});

/**
 * Arc Combo Chosen Item class
 * An individual page element div to represent a chosen combo option
 */
var ArcComboChosenItem = new Class({
	// Define the div element
	initialize: function( combo, val, text ) {
		this.combo = combo;
		this.element = new Element( 'div', {'id': val, 'class': 'combo_item'} );
		this.element.innerHTML = text;
		this.element.addEvent( 'mousedown', this.combo.input.suppressAutohide.bind(this.combo.input) );
		this.element.addEvent( 'click',     this.onClick.bind(this) );
		this.element.addEvent( 'mouseover', this.onMouseOver.bind(this) );
		this.element.addEvent( 'mouseout',  this.onMouseOut.bind(this) );
		
		this.val = val;
	},
	
	// Add click event that removes the option
	onClick: function() {
		this.combo.remove( this.val );
		this.combo.input.element.focus();
	},
	
	// Add a rollover style effect to indicate clicking will remove the option
	onMouseOver: function() {
		this.element.addClass( 'over' );
	},
	
	// Return element style to default
	onMouseOut: function() {
		this.element.removeClass( 'over' );
	}
});

/**
 * Arc Combo Input class
 * Defines and manages the new on screen input box that replaces the original html input
 */
var ArcComboInput = new Class({
	// Add events the page element
	initialize: function( combo, element ) {
		this.combo = combo;
		this.element = element;
		this.element.addEvent( 'focus', this.onFocus.bind(this) );
		this.element.addEvent( 'blur',  this.onBlur.bind(this) );
		
		// _all_ key events check to see if we're pressing enter and have the combo box up
		// and stop the form submission if we do for browser consistency
		this.element.addEvent( 'keydown',  this.onKeyDown.bind(this) );  // handle control characters
		this.element.addEvent( 'keypress', this.onKeyPress.bind(this) ); // handle submission prevention in enthusiastic browsers
		this.element.addEvent( 'keyup',    this.onKeyUp.bind(this) );    // handle letters and others
		
		// Pass events on to the original input
		this.element.addEvent( 'click', function(e) {this.combo.element.fireEvent(e.type);}.bind(this) );
		this.element.addEvent( 'focus', function(e) {this.combo.element.fireEvent(e.type);}.bind(this) );
		this.element.addEvent( 'blur',  function(e) {this.combo.element.fireEvent(e.type);}.bind(this) );
		
		// The text entered in the input field
		this.oldVal = this.element.value;
		
		this.focussed = false;
		this.autoshow = true;
		this.autohide = true;
	},
	
	// Stop the list from auto-hiding if this input has focus
	suppressAutohide: function() {
		if( this.focussed ) {
			this.autohide = false;
		}
	},
	
	// Stop the list from ever auto-showing
	suppressAutoshow: function() {
		this.autoshow = false;
	},
	
	// Show the list if the right conditions are met
	onFocus: function() {
		if( this.autoshow && !this.combo.list.shown && (this.combo.listVisibility != -1) ) {
			this.combo.list.show();
		}
		
		this.focussed = true;
		this.autoshow = true;
	},
	
	// Hide the list if the right conditions are met
	onBlur: function() {
		if( this.autohide && this.combo.list.shown && (this.combo.listVisibility != 1) ) {
			this.combo.list.hide();
		}
		this.focussed = false;
		this.autohide = true;
	},
	
	// Check for control characters on key press
	onKeyDown: function( e ) {
		if( (e.keyCode > 10) && (e.keyCode < 42) ) {
			if( e.keyCode == 13 ) { // return == use considered (or first) item in filtered list
				if( this.combo.onCombo ) {
					this.combo.list.makeSelection();
					new Event(e).stop();
				}
			}
			else if( e.keyCode == 27 ) { // esc == hide list
				if( this.combo.listVisibility == 0 ) {
					this.combo.list.hide();
				}
				
				this.reset();
			}
			else if( e.keyCode == 38 ) { // up == move into or up through the list
				this.combo.list.considerPrevious();
				
				if( this.combo.list.getValue() == undefined ) {
					this.reset();
				}
				else if( this.combo.listVisibility == 0 ) {
					this.combo.list.show();
				}
			}
			else if( e.keyCode == 40 ) { // down == move into or down through the list
				this.combo.list.considerNext();
				
				if( this.combo.list.getValue() == undefined ) {
					this.reset();
				}
				else if( this.combo.listVisibility == 0 ) {
					this.combo.list.show();
				}
			}
		}
	},
	
	// Stop form submission on key press (some browsers need this)
	onKeyPress: function( e ) {
		if( (e.keyCode == 13) && this.combo.onCombo ) { new Event(e).stop(); }
	},
	
	// Check for changes to the text on key up (ignoring control characters)
	// Note we don't use onChange as navigating the list changes the displayed value in the text box
	onKeyUp: function( e ) {
		if( (e.keyCode == 13) && this.combo.onCombo ) { new Event(e).stop(); }
		
		if( (e.keyCode < 10) || (e.keyCode > 42) ) {
			if( this.oldVal != this.element.value ) {
				this.oldVal  = this.element.value;
				
				if( this.combo.listVisibility == 0 ) {
					this.combo.list.show();
				}
				
				this.combo.list.filter( this.element.value );
			}
		}
	},
	
	// Return the current combo input value
	get: function() {
		return this.element.value;
	},
	
	// Set the current combo input value
	set: function( val ) {
		this.element.value = val;
	},
	
	// Reset combo input value to whatever it was when we first clicked it
	reset: function() {
		this.element.value = this.oldVal;
	},
	
	// Reset combo input value to an empty string
	empty: function() {
		this.element.value = '';
	}
});

/**
 * Arc Combo Pulldown class
 * Control to show/hide the list
 */
var ArcComboPulldown = new Class({
	// Create the page element
	initialize: function( combo, element ) {
		this.combo = combo;
		this.element = element;
		this.element.addEvent( 'mousedown', this.combo.input.suppressAutohide.bind(this.combo.input) );
		this.element.addEvent( 'click', this.onClick.bind(this) );
	},
	
	// Define what we do when the pulldown element has been clicked
	onClick: function() {
		this.combo.list.toggle();
		this.combo.input.suppressAutoshow();
		this.combo.input.element.focus();
	}
});

/**
 * Arc Combo Options class
 * Creates and manages the list of combo options
 */
var ArcComboOptions = new Class({
	// ##### Initialisation #####
	
	// Set up the list of combo options
	initialize: function( combo, element, original ) {
		this.combo = combo;
		this.element = element;
		this.original = original;
		this.shown = false;
		
		this.initPool();
		this.resetPool();
		this.element.addEvent( 'mousedown', this.combo.input.suppressAutohide.bind(this.combo.input) );
		this.element.addEvent( 'click', this.onClick.bind(this) );
		
		// Pass events on to the original input
		this.element.addEvent( 'click',  function(e) {this.combo.element.fireEvent(e.type);}.bind(this) );
		
		this.wasConsidered = undefined;
		this.considered = undefined;
	},
	
	// Loop through original html select list and grab the options
	initPool: function() {
		this.pool = new Array();
		var l = this.original.options.length;
		
		for( var i = 0; i < l; i++ ) {
			this.addToPool( this.original.options[i] );
		}
	},
	
	// Add the given original html select option to the this combo list as a new combo option
	addToPool: function( htmlOption ) {
		return this.pool.push( new ArcComboOption(this.combo, htmlOption) );
	},
	
	// Loop through our new list of combo options and set them as not a positive filter match
	resetPool: function() {
		var l = this.pool.length;
		
		for( var i = 0; i < l; i++ ) {
			this.pool[i].used = false;
		}
	},
	
	// Loop through our pool from end to start and delete (part of our custom garbage collection)
	trashPool: function() {
		var l = this.pool.length;
		
		for( var i = l-1; i >= 0; i-- ) {
			this.pool[i].trash();
			this.pool[i] = null;
		}
		
		Garbage.elements.remove( null );
	},
	
	// Remove all items in the list (both internal and on-page)
	empty: function() {
		this.trashPool();
		this.element.empty();
		this.wasConsidered = undefined;
		this.considered = undefined;
	},
	
	// Move the focus to the combo input if this option is clicked
	onClick: function() {
		this.combo.input.element.focus();
	},
	
	// #####  Value / consideration handlers  #####
	
	// Change the highlighted status of the combo options as we navigate around list
	onChange: function( e ) {
		if( this.wasConsidered != undefined ) {
			this.wasConsidered.removeClass( 'current' );
		}
		
		if( this.considered != undefined ) {
			this.considered.addClass( 'current' );
			this.option = this.considered.getOption();
			this.scrollOn( this.element, this.option.element );
		}
		
//		if( this.considered == undefined ) {
//			this.combo.input.set( '' );
//		}
//		else {
//			this.combo.input.set( this.option.text );
//		}
	},
	
	// Add the combo option we just clicked to the used area the original html input
	makeSelection: function() {
		// Perform validity check on a possibly considered option
		this.consideredCheck();
		
		// If no option is currently considered then try and get the first option
		if( (this.considered == undefined) || (this.considered == null) ) {
			this.considerElement( this.element.getFirst() );
		}
		
		// If we still have no considered option then quit
		if( this.considered == undefined ) {
			return false;
		}
		// Otherwise select the considered option
		else {
			this.selectConsidered();
			return true;
		}
	},
	
	// If we have a considered option, is it in use? If not then stop considering it
	consideredCheck: function() {
		if( this.considered ) {
			var unsetConsidered = true;
			var l = this.pool.length;
			
			for( var i = 0; i < l; i++ ) {
				if( this.pool[i].element == this.considered ) {
					if( this.pool[i].used ) {
						// Our considered element is in the filtered shortlist so don't unconsider it
						unsetConsidered = false;
					}
					break;
				}
			}
			
			// If we are set to unconsider the considered option then do so
			if( unsetConsidered ) {
				this.considerElement( undefined );
			}
		}
	},
	
	// Select the currently considered option
	selectConsidered: function() {
		var v = this.option.value;
		var t = this.option.text;
		this.combo.add( v, t );
		this.considerElement( undefined );
	},
	
	// As we mouse around the combo options change the previous and currently considered options
	considerElement: function( el ) {
		this.wasConsidered = this.considered;
		this.considered = el;
		this.onChange();
	},
	
	// Consider the next option in the list and update previous/currently considered options
	considerNext: function() {
		if( this.considered == undefined ) {
			this.considerElement( this.element.getFirst() );
		}
		else {
			this.considerElement( this.considered.getNext() );
		}
	},
	
	// Consider the previous option in the list and update previous/currently considered options
	considerPrevious: function() {
		if( this.considered == undefined ) {
			this.considerElement( this.element.getLast() );
		}
		else {
			this.considerElement( this.considered.getPrevious() );
		}
	},
	
	// Return the value of the currently considered option
	getValue: function() {
		if( this.considered == undefined ) {
			return undefined;
		}
		else {
			return this.option.value;
		}
	},
	
	// #####  List content handlers  #####
	
	// Mark this option as selected in the original html input
	// Mark the corresponding combo item as selected
	add: function( val ) {
		var c = this.original.options;
		var l = c.length;
		
		for( var i = 0; i < l; i++ ) {
			if( c[i].value == val ) {
				if( this.combo.multiple ) {
					c[i].selected = true;
				}
				else {
					this.original.value = val;
					this.original.selectedIndex = i;
				}
				this.pool[i].selected = true;
				break;
			}
		}
	},
	
	// Mark this option as not selected in the original html input
	// Mark the corresponding combo item as not selected
	remove: function( val ) {
		var c = this.original.options;
		var l = c.length;
		
		for( var i = 0; i < l; i++ ) {
			if( c[i].value == val ) {
				if( this.combo.multiple ) {
					c[i].selected = false;
				}
				else {
					this.original.value = '';
					this.original.selectedIndex = 0;
				}
				this.pool[i].selected = false;
				break;
			}
		}
	},
	
	// Filter the option list based on the text given
	filter: function( text ) {
		text = text.trim();
		
		// work out the checks we need to apply
		var checks = text.split( ' ' );
		var numChecks = checks.length;
		
		for( var j = 0; j < numChecks; j++ ) {
			checks[j] = new RegExp( checks[j], 'i' );
		}
		
		var l = this.pool.length;
		var optPtr = this.element.getFirst(); // first option in the combo list
		var useIt;
		
		// Work through all the options adding and removing as needed
		for( var i = 0; i < l; i++ ) {
			// don't use options already selected
			if( this.pool[i].selected ) {
				useIt = false;
			}
			// always include the empty lines which act as separators
			else if( this.pool[i].text == '' ) {
				useIt = true;
			}
			// does this unselected, normal item match the search?
			else {
				useIt = true;
				for( var j = 0; j < numChecks; j++ ) {
					if( !this.pool[i].text.match(checks[j]) ) {
						j = numChecks;
						useIt = false;
					}
				}
			}
			
			// do what's needed to make the list contain all and only matching items
			if( this.pool[i].used ) {
				// remove things in use that shouldn't be
				var n = optPtr.getNext();
				if( !useIt ) {
					this.pool[i].used = false;
					optPtr.remove();
				}
				optPtr = n;
				delete n;
			}
			else if( useIt ) {
				// add things that now match
				this.pool[i].used = true;
				if( optPtr == undefined ) {
					this.pool[i].element.injectInside( this.element );
				}
				else {
					this.pool[i].element.injectBefore( optPtr );
				}
			}
		}
	},
	
	// #####  Display handlers  #####
	
	// Toggle the visibility state of the combo list
	toggle: function() {
		this.shown = !this.shown;
		this.updateDisplay();
	},
	
	// Set the combo list to show
	show: function() {
		this.combo.onCombo = true;
		this.shown = true;
		this.updateDisplay();
	},
	
	// Set the combo list to hide
	hide: function() {
		this.combo.onCombo = false;
		this.shown = false;
		this.updateDisplay();
	},
	
	// Hide or show the combo list based on its current shown status
	updateDisplay: function() {
		if( this.shown ) {
			this.element.setStyle( 'display', 'block' );
		}
		else {
			this.element.setStyle( 'display', 'none' );
		}
	},
	
	// Move to next logical item in list after an action
	scrollOn: function( container, child ) {
		dimContainer = container.getCoordinates();
		dimChild = child.getCoordinates( [container] );
		dimChildRel = child.getCoordinates();
		var targetTop = null; // assume we're not going to go anywhere
		
		// go up
		if( dimChild.top < dimContainer.top ) {
			var targetTop = dimChildRel.top - dimContainer.top;
		}
		// go down
		else if( dimChild.bottom > dimContainer.bottom ) {
			var targetTop = ( dimChildRel.bottom - dimContainer.height ) - dimContainer.top;
		}
		
		if( targetTop != null ) {
			container.scrollTop = targetTop;
		}
	}
});

/**
 * Arc Combo Option class
 * Defines an individual combo list option (a single pool item)
 */
var ArcComboOption = new Class({
	// Set up the option's page element
	initialize: function( combo, option ) {
		this.combo = combo;
		this.element = new Element( 'div' );
		this.garbPos = Garbage.elements.length - 1;
		this.value = option.value;
		
		if( typeof(option.text) == 'undefined' ) {
			this.text = option.getText();
		}
		else {
			this.text = option.text;
		}
		
		this.element.getOption = this.getOption.bind( this );
		this.element.addEvent( 'click', this.onClick.bind(this) );
		this.element.addEvent( 'mouseenter', this.onMouseEnter.bind(this) );
		
		this.element.setHTML( (this.text == '' ? '&nbsp;' : this.text) );
	},
	
	// Delete this individual combo list option (part of our custom garbage collection) 
	trash: function() {
		if( this.element.$events ) {
			this.element.fireEvent('trash').removeEvents();
		}
		
		for( var p in this.element.$tmp ) {
			this.element.$tmp[p] = null;
		}
		
		for( var d in Element.prototype ) {
			this.element[d] = null;
		}
		
		Garbage.elements[this.garbPos] = null;
		this.element.htmlElement = this.element.$tmp = this.element = null;
	},
	
	// Return this option
	getOption: function() {
		return this;
	},
	
	// Mark this option as considered (in response to a mouse enter event)
	onMouseEnter: function() {
		this.combo.list.considerElement( this.element );
	},
	
	// Mark this option as considered (in response to a mouse click event)
	// Start the procedure of adding this option to the original html input and chosen area
	onClick: function() {
		this.combo.list.considerElement( this.element );
		this.combo.list.makeSelection();
		this.combo.input.element.focus();
	}
});

// Do our own custom memory cleanup as the mootools 1.11 default garbage collector
// spends entire seconds doing indexOf to find the thing it's trying to remove
// ... that caused IE to give its "slow script" warning
function ArcComboCleanup() {
	ArcCombos.each( function(c) {
		c.trash();
	});
}

// Queue up the custom cleanup as we attempt to move away from the page
window.addListener( 'beforeunload', function() {
	ArcComboCleanup();
});