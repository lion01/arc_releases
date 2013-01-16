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
	// get the template application select
	var templateSelect = $( 'template_to_apply' );
	
	// add the onChange event to reload page with template applied to existing profile/template
	templateSelect.addEvent( 'change', function() {
		// get the type of profile (profile or template)
		var profileType = $( 'adminForm' ).getElement( 'input[name=type]' ).getValue();
		
		if( profileType == 'profile' ) {
			submitbutton( 'edit_profile' );
		}
		else if( profileType == 'template' ) {
			submitbutton( 'edit_template' );
		}
	});
});