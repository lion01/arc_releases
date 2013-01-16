/**
 * @package     Arc
 * @subpackage  **subpackage_name**
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent( 'domready', function() {
	// form submission (hide buttons to prevent multiple submissions
	$('add_inc_form').addEvent( 'submit', disableButtons );
} );

function disableButtons()
{
	$('msg_sec3').setStyle( 'display', 'none' );
	$('msg_sec4').setStyle( 'display', 'block' );
}