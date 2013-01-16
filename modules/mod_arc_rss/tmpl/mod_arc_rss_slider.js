/**
 * @package     Arc
 * @subpackage  Module_RSS
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	//list of target elements
	var list = $$('div.item_data');
	
	//list elements to be clicked on
	var headings = $$('span.item_slide');
	
	//array to store all of the collapsibles
	var collapsibles = new Array();
	
	headings.each( function(heading, i) {
		
		//for each element create a slide effect
		var collapsible = new Fx.Slide(list[i], {
			'duration': 500,
			'onComplete': function() {
				if( this.wrapper.offsetHeight != 0 ) {
					this.wrapper.setStyle( 'height', 'auto' );
				}
			}
		});
		
		//and store it in the array
		collapsibles[i] = collapsible;
		
		//unbold all links
		heading.style.fontWeight = 'normal';
		
		//give each heading a 'link' cursor
		heading.style.cursor = 'pointer';
		
		//add event listener
		heading.onclick = function(){
		
			//open current element
			collapsible.toggle();
			
			//bold toggle the current link
			if(heading.style.fontWeight == 'bold') {
				heading.style.fontWeight = 'normal';
			}
			else {
				heading.style.fontWeight = 'bold';
			}
			
			//hide the rest
			for(var j = 0; j < collapsibles.length; j++){
				if(j!=i) collapsibles[j].slideOut();
			}
			
			//unbold the rest
			for(var k = 0; k < headings.length; k++){
				if(k!=i) headings[k].style.fontWeight = 'normal';
			}
			
			return false;
		}
		
		//collapse all of the list items
		collapsible.hide();
	});
});