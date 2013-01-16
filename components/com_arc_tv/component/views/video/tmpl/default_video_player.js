/**
 * @package     Arc
 * @subpackage  TV
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function() {
	var vidEls = $$('video');
	var canPlay = false;
	// Check all video elements on the page
	Array.each( vidEls, function(vidEl) {
		var sources = $ES( 'source', vidEl );
		// Check all sources for that video element
		Array.each( sources, function(src) {
			if ( vidEl.canPlayType ) {
				// Video element can play videos (HTML5 browser) - Check it can play that particular type
				var answer = vidEl.canPlayType( src.getProperty('type') );
				if( answer != '' ) {
					// Browser can play video fine
					canPlay = true;
				} // else - video can't be played by this browser, check next source
			}
		});
		
		if (!canPlay) {
			// The video can't be played, remove the sources and then replace video with embed code.
			Array.each( sources, function(src) {
				src.remove();
			});
			var flash = $E( 'embed', vidEl );
			flash.injectTop( $('player_div') );
			var oldVidEl = vidEl.remove();
		}
		else {
			// The video can be played.
		}
	});
});