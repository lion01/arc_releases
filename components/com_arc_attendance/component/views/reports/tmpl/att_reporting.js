/**
 * @package     Arc
 * @subpackage  Attendance
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	
	// hide filter form submit buttons and make the selects do it instead
	$$('.filter_form').each( function( el, id ) {
		var sub = el.getElement('.filter_submit');
		sub.style.display = 'none';
		var s = el.getElement('select');
		s.addEvent( 'change', function() {
			this.submit();
		}.bind(el));
	});
});