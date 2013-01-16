/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

window.addEvent('domready', function() {
	// the admin form
	var adminForm = $('adminForm');
	
	// admin form search input
	var searchInput = $('search');
	
	// admin form search button
	var searchButton = $('admin_form_search_button');
	
	// admin form reset button
	var resetButton = $('admin_form_reset_button');
	
	// admin form sender filter input
	var senderInput = $('sender');
	
	// admin form pupil filter input
	var pupilInput = $('pupil');
	
	// add change event to search input
	searchInput.addEvent('change', function() {
		adminForm.submit();
	});
	
	// add click event to search button
	searchButton.addEvent('click', function() {
		adminForm.submit();
	});
	
	// add click event to reset button
	resetButton.addEvent('click', function() {
		searchInput.value = '';
		senderInput.value = '';
		pupilInput.value = '';
		adminForm.submit();
	});
	
	// add change event to sender filter drop-down
	senderInput.addEvent('change', function() {
		adminForm.submit();
	});
	
	// add change event to pupil filter drop-down
	pupilInput.addEvent('change', function() {
		adminForm.submit();
	});
});