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

window.addEvent('domready', function() {
	// the admin form
	var adminForm = $('adminForm');
	
	// admin form search input
	var searchInput = $('search');
	
	// admin form search button
	var searchButton = $('admin_form_search_button');
	
	// admin form reset button
	var resetButton = $('admin_form_reset_button');
	
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
		typeInput.value = '';
		adminForm.submit();
	});
});