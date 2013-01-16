/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	// add click event to reset button
	$('admin_form_reset_button').addEvent('click', function() {
		$('search_group').value = '';
		$('search_person').value = '';
		$('search_valid').value = '';
		adminForm.submit();
	});
});