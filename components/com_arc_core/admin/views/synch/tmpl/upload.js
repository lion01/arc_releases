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

window.addEvent( 'domready', function()
{
	// get the highlightable words
	words = $$('.h_word');
	
	// highlight on mouseover
	words.each( function(word) {
		word.addEvent( 'mouseover', function() {
			word.setStyle( 'font-weight', 'bold' );
			document.body.style.cursor = 'pointer';
		});
	});
	
	// un-highlight on mouseout
	words.each( function(word) {
		word.addEvent( 'mouseout', function() {
			word.setStyle( 'font-weight', 'normal' );
			document.body.style.cursor = 'auto';
		});
	});
});