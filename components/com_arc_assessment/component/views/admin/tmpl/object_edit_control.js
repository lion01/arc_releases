/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

var objEditControl = new Class({
	// class initializer
	initialize: function( i, aspId, boundaries )
	{
		this.index = i;
		this.aspId = aspId;
		this.boundaryObj = new objEditBoundary( boundaries );
	},
	
	// first time setup
	setUp: function()
	{
		this.slideDiv = $( 'boundary_slider_'+this.aspId );
		this.clicker = $( 'boundary_edit_'+this.aspId );
		this.clickerCell = this.clicker.getParent();
		this.boundaryDiv = $( 'boundary_div_'+this.aspId );
		this.markStyleSelect = $( 'asp' + this.aspId + 'mark_style' );
		this.displayStyleSelect = $( 'asp' + this.aspId + 'display_style' );
		// if mark style is comment/text then set display as -- As Marked -- and disable input
		if( markstyle_info.get( this.boundaryObj.getMarkStyle() ).type == 'text' ) {
			this.displayAsMarked( true );
		}
		
		// div containing 'Marks' text and optional range boxes
		this.marksDiv = new Element( 'div', {
			'styles': {
				'margin': '0px',
				'padding': '0px',
				'height': '2em',
				'line-height': '2em'
			}
		});
		
		// div to contain mark labels 
		this.labelTop = new Element( 'div', {
			'styles': {
				'width': '500px',
				'margin': '0px',
				'padding': '0px'
			}
		});
		
		// div to contain mark nobs 
		this.sliderTop = new Element( 'div', {
			'styles': {
				'width': '500px',
				'height': '11px',
				'margin': '0px',
				'padding': '0px'
			}
		});
		
		// div which is the sliding line
		var sliderBase = new Element( 'div', {
			'styles': {
				'border': '1px solid black',
				'width': '500px',
				'height': '5px',
				'margin': '0px',
				'padding': '0px'
			}
		});
		
		// div to contain display nobs
		this.sliderBottom = new Element( 'div', {
			'styles': {
				'width': '500px',
				'height': '11px',
				'margin': '0px',
				'padding': '0px'
			}
		});
		
		// div to contain mark labels 
		this.labelBottom = new Element( 'div', {
			'styles': {
				'width': '500px',
				'margin': '0px',
				'padding': '0px'
			}
		});
		
		// div containing 'Display' text and optional range boxes
		this.displayDiv = new Element( 'div', {
			'styles': {
				'margin': '0px',
				'padding': '0px',
				'height': '2em',
				'line-height': '2em'
			}
		});
		
		// include new HTML in the holding div
		this.boundaryDiv.adopt( this.marksDiv );
		this.boundaryDiv.adopt( this.labelTop );
		this.boundaryDiv.adopt( this.sliderTop );
		this.boundaryDiv.adopt( sliderBase );
		this.boundaryDiv.adopt( this.sliderBottom );
		this.boundaryDiv.adopt( this.labelBottom );
		this.boundaryDiv.adopt( this.displayDiv );
		
		// draw the nobs
		this.createNobs( 'marks' );
		this.createNobs( 'display' );
		
		// create slide for the slide div
		this.editSlide = new Fx.Slide( this.slideDiv, {
			'duration': 500,
			'onComplete': function() {
				if( this.wrapper.offsetHeight != 0 ) {
					this.wrapper.setStyle( 'height', 'auto' );
				}
			}
		});
		
		// close this slide immediately after setting it up
		this.editSlide.hide();
		
		// add slide event to the clicker
		this.clicker.addEvent( 'click', function(e) {
			e = new Event(e);
			
			// call each controller to close their slides
			// and tell us if you are doing that
			var doWeWait = false;
			controllers.each( function(controller, i) {
				if( i != this.index ) {
					doWeWait = ( doWeWait || controller.closeSlide() );
					controller.highlightClicker( false );
				}
			}.bind(this));
			
			// toggle, with delay if needed
			doWeWait ? this.toggleMe.delay( 800, this ) : this.toggleMe(); 
			
			e.stop();
		}.bind(this));
		
		// add style change events
		this.addStyleChangeEvents( 'marks' );
		this.addStyleChangeEvents( 'display' );
	},
	
	// add onChange events to style form selects
	addStyleChangeEvents: function( markOrDisplay )
	{
		if( markOrDisplay == 'marks' ) {
			var selectBox = this.markStyleSelect;
			var styleSetFunc = this.boundaryObj.setMarkStyle.bind( this.boundaryObj );
		}
		else if( markOrDisplay == 'display' ) {
			var selectBox = this.displayStyleSelect;
			var styleSetFunc = this.boundaryObj.setDisplayStyle.bind( this.boundaryObj );
		}
		
		selectBox.addEvent( 'change', function(e) {
			e = new Event(e);
			var redrawDelay = 0;
			
//			// close other sliders if open and tell us to wait
//			var doWeWait = false;
//			controllers.each( function(controller, i) {
//				if( i != this.index ) {
//					doWeWait = ( doWeWait || controller.closeSlide() );
//					controller.highlightClicker( false );
//				}
//			}.bind(this));
//			
//			if( doWeWait ) {
//				// waiting for other slides to close and I must there be closed
//				// so set long delay and slide me in after usual slide in delay
//				redrawDelay = 2000;
//				this.slideMeIn.delay( 800 , this );
//			}
//			else if( !this.editSlide.open ) {
//				// not waiting for other slides and I am not open
//				// so set short delay and slide me in
//				redrawDelay = 1200; 
//				this.editSlide.slideIn();
//			}
//			
//			// highlight this clickers cell
//			this.highlightClicker.delay( redrawDelay, this, true );
			
			// update the boundary obj
			styleSetFunc( selectBox.getValue() );
			
			// redraw nobs
			this.createNobs.delay( redrawDelay , this, 'marks' );
			this.createNobs.delay( redrawDelay , this, 'display' );
			
			// handle the cases of a changing markstyle affecting display style also
			if( (markOrDisplay == 'marks') && (this.boundaryObj.getDisplayStyle() === '') ) {
				this.displayAsMarked( markstyle_info.get( this.boundaryObj.getMarkStyle() ).type == 'text' );
			}
			
			// update the form input
			this.updateFormInput();
			
			e.stop();
		}.bind(this));
	},
	
	// create series of nobs from boundary object data
	createNobs: function( markOrDisplay )
	{
		// set nob type specific variables
		if( markOrDisplay == 'marks' ) {
			this.titleDiv = this.marksDiv;
			this.labelDiv = this.labelTop;
			this.sliderDiv = this.sliderTop;
			var isDragable = true;
			this.style = this.boundaryObj.getMarkStyle();
			var styleTextHead = '<b>Marks</b>: ';
			var commentHTML = 'Comments';
			var nobs = this.boundaryObj.getMarkValues();
			var resetNumRange = this.boundaryObj.setMarksNumRange.bind( this.boundaryObj );
		}
		else if( markOrDisplay == 'display' ) {
			this.titleDiv = this.displayDiv;
			this.labelDiv = this.labelBottom;
			this.sliderDiv = this.sliderBottom;
			var isDragable = true;
			this.style = this.boundaryObj.getDisplayStyle();
			if( this.style === '' ) {
				this.style = this.boundaryObj.getMarkStyle();
				var isDragable = false;
				var asMarked = true;
			}
			var styleTextHead = '<b>Display</b>: ';
			var commentHTML = 'Mark style is comments, no display style available';
			var nobs = this.boundaryObj.getDisplayBounds();
			var resetNumRange = this.boundaryObj.setDisplayNumRange.bind( this.boundaryObj );
		}
		this.nobsNumber = nobs.length;
		this.titleDiv.empty();
		
		// set the style text and add it it to title div
		var styleText = styleTextHead + markstyle_info.get( this.style ).label;
		var styleTextSpan = new Element( 'span', {
			'styles': {
				'margin': '0px',
				'padding': '0px'
			}
		});
		styleTextSpan.setHTML( styleText );
		styleTextSpan.injectInside( this.titleDiv );
		
		// clear out any existing nobs and labels
		this.sliderDiv.empty();
		this.labelDiv.empty();
		
		// show label and slider divs as normal
		this.sliderDiv.setStyle( 'display', 'inline' );
		this.labelDiv.setStyle( 'text-align', 'inherit' );
		
		// capture the type of mark
		this.markStyleType = markstyle_info.get( this.style ).type
		
		// create nobs
		if( this.markStyleType == 'mark' ) {
			
			// initialise minimum scale value
			this.minValue = 0;
			
			// loop through nobs array and generate each full nob
			nobs.each( function(valueHash, i) {
				this.createNob( markOrDisplay, isDragable, valueHash, nobs[i-1], nobs[i+1] );
			}, this);
		}
		// create comment
		else if( this.markStyleType == 'text' ) {
			this.createComment( commentHTML );
		}
		// create numeric
		else if( this.markStyleType == 'numeric' ) {
			// define elements for numeric input
			var rangeSpan = new Element( 'span', {
				'styles': {
					'margin': '0px',
					'padding': '0px'
				}
			});
			var numLower = new Element( 'input', {
				'styles': {
					'margin': '0px',
					'padding': '0px'
				},
				'id': 'numLower' + this.aspId + markOrDisplay,
				'type': 'text',
				'size': 3,
				'maxlength': 3,
				'value': nobs[0].get( 'mark' ),
				'events': {
					'keyup': function() {
						// update the range
						resetNumRange( numLower.value, numHigher.value );
						// redraw nobs
						this.createNobs( 'marks' );
						this.createNobs( 'display' );
						// update form input
						this.updateFormInput();
						// get focus back
						$( 'numLower' + this.aspId + markOrDisplay ).focus();
					}.bind( this )
				}
			});
			var numHigher = new Element( 'input', {
				'styles': {
					'margin': '0px',
					'padding': '0px'
				},
				'id': 'numHigher' + this.aspId + markOrDisplay,
				'type': 'text',
				'size': 3,
				'maxlength': 3,
				'value': nobs.getLast().get( 'mark' ),
				'events': {
					'keyup': function() {
						// update the range
						resetNumRange( numLower.value, numHigher.value );
						// redraw nobs
						this.createNobs( 'marks' );
						this.createNobs( 'display' );
						// update form input
						this.updateFormInput();
						// get focus back
						$( 'numHigher' + this.aspId + markOrDisplay ).focus();
					}.bind( this )
				}
			});
			
			// set inputs to be disabled if dealing with 'percent' mark style
			if( this.style == 'percent' ) {
				numLower.setProperty( 'disabled', 'disabled' );
				numHigher.setProperty( 'disabled', 'disabled' );
			}
			
			// set inputs to be disabled if display style is -- As Marked --
			if( (markOrDisplay == 'display') && (asMarked) ) {
				numLower.setProperty( 'disabled', 'disabled' );
				numHigher.setProperty( 'disabled', 'disabled' );
			}
			
			// include input boxes on the page
			numLower.injectInside( rangeSpan );
			rangeSpan.appendText( ' to ' );
			numHigher.injectInside( rangeSpan );
			rangeSpan.injectInside( this.titleDiv );
			
			// centre the range span boxes in the title div
			var titleDivToCentre = this.titleDiv.getCoordinates().width / 2;
			var textSpanWidth = styleTextSpan.getCoordinates().width;
			var rangeSpanHalfWidth = rangeSpan.getCoordinates().width / 2;
			rangeSpanMargin = titleDivToCentre - textSpanWidth - rangeSpanHalfWidth;
			rangeSpan.setStyle( 'margin-left', rangeSpanMargin );
			
			// loop through nobs array and generate each full nob
			nobs.each( function(valueHash, i) {
				this.createNob( markOrDisplay, false, valueHash, nobs[i-1], nobs[i+1] );
			}, this);
		}
	},
	
	// create an individual nob
	createNob: function( markOrDisplay, isDragable, valueHash, prevHash, nextHash )
	{
		// set nob type specific variables
		if( markOrDisplay == 'marks' ) {
			var inactiveNobImg = img_path + 'sort1.png';
			var activeNobImg = img_path + 'sort1_active.png';
			var toolTipOffsets = { x: 0, y: -48 };
			var labelLeftProp = ( valueHash.get('value') * 5 ) + 'px';
			var valueSetFunc = this.boundaryObj.setMarkValue.bind( this.boundaryObj );
		}
		else if( (markOrDisplay == 'display') && (this.markStyleType != 'numeric') ) {
			var inactiveNobImg = img_path + 'sort0.png';
			var activeNobImg = img_path + 'sort0_active.png';
			var toolTipOffsets = { x: 0, y: 22 };
			if( prevHash == undefined ) {
				var prevValue = 0;
			}
			else {
				var prevValue = prevHash.get( 'value' ).toFloat();
			}
			var thisValue = valueHash.get( 'value' ).toFloat();
			var halfPoint = ( thisValue + prevValue ) * 2.5;
			var labelLeftProp = halfPoint + 'px';
			var valueSetFunc = this.boundaryObj.setDisplayBound.bind( this.boundaryObj );
		}
		else if( markOrDisplay == 'display' && (this.markStyleType == 'numeric') ) {
			var inactiveNobImg = img_path + 'sort0.png';
			var activeNobImg = img_path + 'sort0_active.png';
			var toolTipOffsets = { x: 0, y: 22 };
			var labelLeftProp = ( valueHash.get('value') * 5 ) + 'px';
			var valueSetFunc = this.boundaryObj.setDisplayBound.bind( this.boundaryObj );
		}
		
		// define the nob div
		var nobDiv = new Element( 'div', {
			'styles': {
				'display': 'inline-block',
				'position': 'relative',
				'left': (valueHash.get( 'value' ) * 5) + 'px',
				'width': '11px',
				'height': '11px',
				'margin': '0px -6px 0px -5px',
				'padding': '0px',
				'background': 'url(\'' + inactiveNobImg + '\')'
			}
		});
		
		// add associated tooltip to nobDiv, if required
		if( this.markStyleType != 'numeric' ) {
			nobDiv.setProperty( 'title', this.getTitleValue( markOrDisplay, valueHash.get('value') ) ),
			valueHash.extend({
				'tip': new Tips( nobDiv, {
					'offsets': toolTipOffsets
				}) 
			})
		}
		
		// add nob div and its label
		valueHash.extend({
			// add the nobDiv
			'el': nobDiv,
			// div for the label
			'label': new Element( 'div', {
				'styles': {
					'display': 'inline-block',
					'position': 'relative',
					'margin': '0px',
					'padding': '0px'
				}
			})
		});
		
		// add nob div to the page inside its slider div
		valueHash.get( 'el' ).injectInside( this.sliderDiv );
		
		// add labels to nob
		var labelDiv = valueHash.get( 'label' );
		labelDiv.setHTML( valueHash.get( 'mark' ) );
		labelDiv.injectInside( this.labelDiv );
		var labelCoords = labelDiv.getSize();
		var labelWidth = labelCoords.size.x;
		labelDiv.setStyles({
			'left': labelLeftProp,
			'margin-left': '-' + (labelWidth / 2) + 'px',
			'margin-right': '-' + (labelWidth / 2) + 'px'
		});
		
		// add drag event to nob if required
		if( isDragable ) {
			var maxValue = ( (nextHash == undefined) ? 100 : (nextHash.get('value') - 1 ) ) * 5;
			var tmpDragger = new Drag.Base( valueHash.get( 'el' ), {
				'limit': {
					x:[this.minValue, maxValue],
					y:[0, 0]
				},
				'grid': 5,
				'onStart': function() {
					// highlight the pointer
					valueHash.get( 'el' ).setStyle( 'background', 'url(\'' + activeNobImg + '\')' );
				},
				'onDrag': function() {
					var posNow = valueHash.get( 'dragger' ).value.now.x;
					
					// update the tooltip
					valueHash.get( 'tip' ).toolTip.textContent = this.getTitleValue( markOrDisplay, (posNow / 5) );
					
					// move the label
					if( markOrDisplay == 'marks' ) {
						valueHash.get( 'label' ).setStyle( 'left', posNow );
					}
					else if( markOrDisplay == 'display' ) {
						if( prevHash == undefined ) {
							var prevNobValue = 0;
						}
						else {
							var prevNobValue = prevHash.get( 'value' ).toFloat();
						}
						valueHash.get( 'label' ).setStyle( 'left', (prevNobValue + (posNow / 5)) * 2.5 );
						
						// move the label next label up, if there is one
						if( nextHash != undefined ) {
							var newValue = ( (posNow / 5) + nextHash.get( 'value' ).toFloat() ) * 2.5;
							nextHash.get( 'label' ).setStyle( 'left', newValue );
						}
					}
				}.bind( this ),
				'onComplete': function() {
					// update limits for nobs on each side of this one
					var posNow = valueHash.get( 'dragger' ).value.now.x;
					if( nextHash != undefined ) {
						nextHash.get( 'dragger' ).options.limit.x[0] = posNow + 5;
					}
					if( prevHash != undefined ) {
						prevHash.get( 'dragger' ).options.limit.x[1] = posNow - 5;
					}
					
					// change value property in boundary obj for this mark
					valueSetFunc( valueHash.get( 'mark' ), (posNow / 5) );
					
					// update the form input
					this.updateFormInput()
					
					// reset the nob highlight
					valueHash.get( 'el' ).setStyle( 'background', 'url(\'' + inactiveNobImg + '\')' );
				}.bind( this )
			});
			valueHash.set( 'dragger', tmpDragger );
			this.minValue = ( valueHash.get('value') * 5 ) + 5;
		}
	},
	
	// create a layout with the supplied comment text
	createComment: function( commentHTML )
	{
		// hide slider div and set 'comment' text in label div
		this.sliderDiv.setStyle( 'display', 'none' );
		this.labelDiv.setStyle( 'text-align', 'center' );
		this.labelDiv.setHTML( commentHTML );
	},
	
	// set display style select box to be -- As Marked -- and disable it
	displayAsMarked: function( lock )
	{
		if( lock ) {
			this.displayStyleSelect.setProperties({
				'value': '',
				'disabled': 'disabled'
			});
		}
		else {
			this.displayStyleSelect.removeProperty( 'disabled' );
		}
	},
	
	// udpate form input with Json encoded boundary object
	updateFormInput: function()
	{
		var input = $( 'asp_' + this.aspId + '_boundary_data' );
		input.value = this.boundaryObj.toString();
	},
	
	// close slider and report if this is going to happen or not
	closeSlide: function()
	{
		retVal = false;
		
		if( this.editSlide.open ) {
			retVal = true;
			this.editSlide.slideOut();
		}
		
		return retVal;
	},
	
	// toggle my own slider
	toggleMe: function()
	{
		// clicker cell highlighting control
		if( this.editSlide.open ) {
			var delay = 0;
		}
		else {
			var delay = 1200;
		}
		this.toggleHighlightClicker.delay( delay, this );
		
		// perform the slider toggle
		this.editSlide.toggle();
	},
	
	// slide in my own slider
	slideMeIn: function()
	{
		this.editSlide.slideIn();
	},
	
	// set the highlight on the table cell containing the clicker
	highlightClicker: function( state )
	{
		if( state ) {
			this.clickerCell.addClass( 'clicker_on' );
		}
		else {
			this.clickerCell.removeClass( 'clicker_on' );
		}
	},
	
	// toggle the highlight of the table cell containing the clicker
	toggleHighlightClicker: function()
	{
		this.clickerCell.toggleClass( 'clicker_on' );
	},
	
	// work out what my title should be for tooltip
	getTitleValue: function( markOrDisplay, myValue )
	{
		var otherMarkOrDisplay = ( (markOrDisplay == 'marks') ? 'display' : 'marks' );
		
		if( otherMarkOrDisplay == 'marks' ) {
			var otherType = markstyle_info.get( this.boundaryObj.getMarkStyle() ).type;
			var otherValues = this.boundaryObj.getMarkValues( true );
		}
		else if( otherMarkOrDisplay == 'display' ) {
			var otherType = markstyle_info.get( this.boundaryObj.getDisplayStyle() ).type;
			var otherValues = this.boundaryObj.getDisplayBounds( true );
		}
		
		if( otherType != 'numeric' ) {
			var value = myValue;
		}
		else {
			var lowMark = otherValues[0].get( 'mark' ).toInt();
			var highMark = otherValues[1].get( 'mark' ).toInt();
			var diff = highMark - lowMark;
			var value = ((diff * (myValue / 100)) + lowMark).round(2);
		}
		
		return value;
	}
});