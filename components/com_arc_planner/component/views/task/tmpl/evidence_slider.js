/**
 * @package     Arc
 * @subpackage  Planner
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	// array of divs containting the clicker
	var clickerDivs = $$('div.evidence_clicker_div');
	
	// array of elements to click to activate the slide
	var clickers = $$('a.evidence_slider');
	
	// array of divs for the slide
	var slideDivs = $$('div.evidence_div');
	
	//array to store all of the collapsing divs
	var slidables = new Array();
	
	// unhide the clickers when we're using javascript
	for( var i = 0; i < clickerDivs.length; i++ ) {
		clickerDivs[i].style.display = 'block';
		clickerDivs[i].style.marginBottom = '10px';
		slideDivs[i].style.paddingBottom = '10px';
		slideDivs[i].style.marginBottom = '-10px';
	};
	
	// loop through each clicker in array and add the clicker event to it
	// and attach a slider to it's target div and store these new objects in slidables array
	clickers.each( function(clicker, j) {
		
		// create slide for the named divs
		var eviSlide = new Fx.Slide(slideDivs[j], {
			'duration': 500,
			'onComplete': function() {
				if( this.wrapper.offsetHeight != 0 ) {
					this.wrapper.setStyle( 'height', 'auto' );
				}
			}
		});
		
		// add slide event to each clicker
		clickers[j].addEvent('click', function(e) {
			e = new Event(e);
			eviSlide.toggle();
			
			if( clickerDivs[j].style.marginBottom == '-10px' ) {
				clickerDivs[j].style.marginBottom = '0px'
			}
			else {
				clickerDivs[j].style.marginBottom = '-10px'
			}
			e.stop();
		});
		
		// and store it in the array
		slidables[j] = eviSlide;
		
		// hide all the slideDivs
		for( var k = 0; k < slidables.length; k++ ) {
			slidables[k].hide();
		}
	});
});