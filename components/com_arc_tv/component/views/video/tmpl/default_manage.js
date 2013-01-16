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
	// show the upload frame if present
	iframe = $( 'upload_frame' );
	if( iframe !== null ) {
		iframe.setStyle( 'display', '' );
	}
	
	// pick up the delete video file and response message ajax spinner divs
	deleteSpinnerDiv = $( 'ajax_delete_div' );
	messageSpinnerDiv = $( 'ajax_message_div' );
	
	// pick up the hidden inputs containing the video id and status
	vidIdInput = $( 'video_id' );
	vidStatusInput = $( 'video_status' );
	
	// initialise accordion
	initAccordion();
	
	// intercept form submission
	manageForm = $( 'manage_video_form' );
	manageFormSaveButton = $( 'manage_form_save_button' );
	manageFormSubmitButton = $( 'manage_form_submit_button' );
	manageFormApproveButton = $( 'manage_form_approve_button' );
	manageFormRejectButton = $( 'manage_form_reject_button' );
	if( manageFormSaveButton !== null ) {
		setFormSubmitButtonClicker( manageFormSaveButton, 'save' );
	}
	if( manageFormSubmitButton !== null ) {
		setFormSubmitButtonClicker( manageFormSubmitButton, 'submit' );
	}
	if( manageFormApproveButton !== null ) {
		setFormSubmitButtonClicker( manageFormApproveButton, 'approve' );
	}
	if( manageFormRejectButton !== null ) {
		setFormSubmitButtonClicker( manageFormRejectButton, 'reject' );
	}
	
	// add sensitivity to 'remove video files' link
	remVidFilesLink = $( 'remove_vid_files_link' );
	if( remVidFilesLink !== null ) {
		setRemFilesClicker();
	}
	
	// initialise roles
	rolesTableDiv = $( 'manage_roles_table_div' );
	rolesTableBody = $( 'manage_form_roles_tbody' );
	rolesInput = $( 'manage_roles_input' );
	existingRoles = Json.evaluate( rolesInput.getValue() );
	if( existingRoles.length > 0 ) {
		initRoles();
	}
	else {
		rolesTableDiv.setStyle( 'display', 'none' );
	}
	
	// add sensitivity to 'add roles' link
	addRolesLink = $( 'add_roles_link' );
	if( addRolesLink !== null ) {
		siteId = $( 'manage_roles_site_id' ).getValue();
		rolesComboInput = $( 'manage_roles_roles_input' );
		namesComboInput = $( 'manage_roles_people_input' );
		
		rolesComboInput.addEvent( 'change', checkAddRolesLink );
		namesComboInput.addEvent( 'change', checkAddRolesLink );
		
		setAddRolesClicker();
	}
});

/**
 * Create the accordion effect for the management page panes
 */
function initAccordion()
{
	// list of target elements
	list = $$( 'div.manage_slider' );
	
	// list elements to be clicked on
	var clickers = $$( 'span.manage_pane_clicker' );
	
	// array to store all of the collapsibles
	collapsibles = new Array();
	
	clickers.each( function(clicker, i) {
		// for each div create a slide effect
		var collapsible = new Fx.Slide( list[i], {
			'duration': 500,
			'onComplete': function() {
				if( this.wrapper.offsetHeight != 0 ) {
					this.wrapper.setStyle( 'height', 'auto' );
				}
				
				// if this slider has just closed and video status is not 1 or 3 on then save it
				var curStatus = vidStatusInput.getValue();
				if( (this.wrapper.offsetHeight == 0) && !((curStatus == 1) || (curStatus == 3)) ) {
					submitForm( 'save', list[i] );
				}
				// if this slider has just closed and video status is 1 or 3 then DON'T save it
				else if( (this.wrapper.offsetHeight == 0) && ((curStatus == 1) || (curStatus == 3)) ) {
					if( $('manage_file_div') != null ) {
						var noSwooshDiv = $( 'ajax_noswoosh_file_div' );
					}
					else if( $('manage_mod_div') != null ) {
						var noSwooshDiv = $( 'ajax_noswoosh_mod_div' );
					}
					
					messageSpinnerDiv.setStyle( 'display', 'none' );
					noSwooshDiv.setStyle( 'display', 'block' );
				}
			}
		});
		
		// and store it in the array
		collapsibles[i] = collapsible;
		
		// give each clicker a 'link' cursor
		clicker.setStyle( 'cursor', 'pointer' );
		
		// add event listener
		clicker.addEvent( 'click', function(e) {
			// prevent the default onClick event from firing / bubbling
			e = new Event(e).stop();
			
			// toggle current element
			collapsible.toggle();
			
			// hide the rest
			for( var j = 0; j < collapsibles.length; j++ ) {
				if( (j != i) && (collapsibles[j].open == true) ) {
					collapsibles[j].slideOut();
				}
			}
		});
		
		// collapse all but the first div
		if( i != 0 ) {
			collapsible.hide();
		}
	});
}

/**
 * Actualise any dummy video
 * Hide the upload form
 * Enable the progress bar
 */
function startFileUpload()
{
	fileGettingProgress = false;
	fileUploadStarted = false;
	fileCopyStarted = false;
	fileUploadSize = 1;
	
	iframe = $( 'upload_frame' );
	var fileDoc = ( iframe.contentDocument ) ? iframe.contentDocument : iframe.contentWindow.document;
	var fileForm = fileDoc.getElementById( 'upload_form' );
	var uploadIdInput = fileDoc.getElementById( 'uid' );
	var vidIdInputFrame = fileDoc.getElementById( 'vidId_file_input' );
	var vidId = vidIdInput.getValue();
	
	// dummy videos must be made actual before uploading file
	if( vidId < 0 ) {
		new Ajax( $('save_url').getProperty('value'), {
			'method': 'post',
			'onComplete': function() {
				var data = Json.evaluate( this.response.text, true );
				var newId = data.id;
				
				if( (typeof(newId) != 'undefined') && (newId > 0) ) {
					updateIdValues( newId );
					
					fileForm.submit();
					var f = function() { uploadProgress( uploadIdInput.value ); };
					filePeriodical = f.periodical( 250 );
				}
				else {
					alert( 'Encountered a problem uploading the video' );
				}
			}
		}).request();
	}
	else {
		updateIdValues( vidId );
		fileForm.submit();
		var f = function() { uploadProgress( uploadIdInput.value ); };
		filePeriodical = f.periodical( 250 );
	}
	iframe.setStyle( 'display', 'none' );
	$( 'upload_bar' ).setStyle( 'display', 'block' );
	
	return false;
}

/**
 * Maintain the progress bars by polling the video server for upload / copy progress
 */
function uploadProgress( uploadId )
{
	if( fileGettingProgress ) {
		return true;
	}
	fileGettingProgress = true;
	
	new Json.Remote( $('progress_url').value.replace(/~UPLOADID~/, uploadId), {
		'method': 'get',
		'onFailure': function() {
			fileGettingProgress = false;
		},
		'onComplete': function( d ) {
			fileGettingProgress = false;
			dUp = d.upload;
			dCo = d.copy;
			if( typeof(dUp) == 'object' ) {
				// There is data on the progress of the upload, so render the progress bar 
				if( !fileUploadStarted ) {
					fileUploadStarted = true;
					fileUploadSize = dUp.bytes_total;
				}
				var pc = (dUp.bytes_uploaded / dUp.bytes_total) * 100;
				if( pc > 100 ) { pc = 100; }
				$( 'upload_progress' ).setStyle( 'width', pc+'%' );
				$( 'upload_text_inner' ).setText( secondsToHms(dUp.est_sec) );
			}
			else if( (dUp == true) && (typeof(dCo)== 'object') ) {
				// when the upload is done, make sure its bar is full, then start tracking the copy
				if( !fileCopyStarted ) {
					fileCopyStarted = true;
					fileCopySize = 0;
					$( 'upload_progress' ).setStyle( 'width', '100%' );
					$( 'upload_text_inner' ).setText( 'Adding to encoder queue' );
				}
				
				if( (dCo.size >= (fileUploadSize-1000)) && (dCo.size == fileCopySize) ) {
					// Accounting roughly for the form overhead
					// at least as many bytes as were expected have been copied
					// and no more are being copied now, so copy is probably finished
					clearInterval( filePeriodical );
					$( 'upload_copy_progress' ).setStyle( 'width', '100%' );
					$( 'upload_text_inner' ).setText( 'Done' );
				}
				else {
					// still copying the file to its proper place, so update bar
					fileCopySize = dCo.size;
					var pc = (dCo.size / fileUploadSize) * 100;
					if( pc > 100 ) { pc = 100; }
					$( 'upload_copy_progress' ).setStyle( 'width', pc+'%' );
				}
			}
		}
	}).send();
}

/**
 * Helper function to convert seconds to hours, minutes and seconds
 * @param d number of seconds to convert
 * @returns Hms string
 */
function secondsToHms(d) {
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 3600 % 60);
	return ((h > 0 ? h + ":" : "")
		+ (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:")
		+ (s < 10 ? "0" : "") + s);
}

/**
 * Add an onClick event to the remove video files link to intercept it
 * We then return an XHR response instead
 */
function setRemFilesClicker()
{
	remVidFilesLink.addEvent( 'click', function(e) {
		// prevent the default onClick event from firing / bubbling
		e = new Event(e).stop();
		
		// call the function
		delSrcFiles();
	});
}

/**
 * Call the php controller method to delete the specified remote video files
 */
function delSrcFiles()
{
	// ask for delete confirmation
	var delOK = confirm( 'Are you sure you want to delete all the files associated with this video?' );
	
	if( delOK ) {
		// get the file upload div
		var fileManUpDiv = $( 'manage_file_upload_div' );
		
		// get the file formats div
		var fileManFormatsDiv = $( 'manage_file_formats_div' );
		
		// call the remote file deletion script
		var raw = new XHR({
			'onRequest': function() {
				// hide message div
				messageSpinnerDiv.setStyle( 'display', 'none' );
				
				// hide noSwoosh div
				$( 'ajax_noswoosh_file_div' ).setStyle( 'display', 'none' );
				$( 'ajax_noswoosh_mod_div' ).setStyle( 'display', 'none' );
				
				// add ajax spinner
				deleteSpinnerDiv.setStyle( 'display', 'block' );
			},
			'onSuccess': function() {
				// remove ajax spinner
				deleteSpinnerDiv.setStyle( 'display', 'none' );
				
				// capture and process XHR response
				var response = Json.evaluate( raw.response.text );
				
				// determine success of remote file deletion
				if( response.success ) {
					// hide the formats div (and mod div if it was shown)
					fileManFormatsDiv.setStyle( 'display', 'none' );
					
					// unhide the file upload div
					fileManUpDiv.setStyle( 'display', '' );
				}
				
				// output the message regardless of success or failure
				messageSpinnerDiv.empty();
				messageSpinnerDiv.setText( response.message );
				messageSpinnerDiv.setStyle( 'display', 'block' );
			},
			'onFailure': function() {
				// remove ajax spinner
				deleteSpinnerDiv.setStyle( 'display', 'none' );
			}
		}).send( remVidFilesLink.getProperty('href'), '&format=raw' );
	}
}

/**
 * Renders any existing roles into the roles table
 */
function initRoles()
{
	existingRoles.each( function(roleInfo) {
		addRoleToTable( roleInfo );
	});
}

/**
 * Adds the current roles/names matrix to the list of credits in the html table
 */
function addRoleToTable( roleInfo )
{
	var curRolesRows = rolesTableBody.getChildren();
	var existingRole = curRolesRows.some( function(row) {
		if( row.getChildren().length == 3 ) {
			roleCell = row.getFirst();
			if( roleCell.getText() == roleInfo.role ) {
				roleRow = new Element( 'tr' );
				roleRow.injectAfter( row );
				return true;
			}
		}
	});
	
	if( !existingRole ) {
		roleRow = new Element( 'tr' );
		roleRow.injectInside( rolesTableBody );
		
		roleCell = new Element( 'td', {'rowspan': 0} );
		roleCell.setText( roleInfo.role );
		roleCell.injectInside( roleRow );
	}
	
	roleCell.setProperty( 'rowspan', (roleCell.getProperty('rowspan') + 1) );
	
	var nameCell = new Element( 'td' );
	nameCell.setText( roleInfo.person_name );
	nameCell.injectInside( roleRow );
	
	var delCell = new Element( 'td' );
	delCell.injectInside( roleRow );
	var remLink = new Element( 'a', {
		'href': '#',
		'events': {
			'click': makeClickFunc( roleRow, roleCell )
		}
	});
	remLink.info = roleInfo;
	remLink.setText( 'Remove' );
	remLink.injectInside( delCell );
	
	rolesTableDiv.setStyle( 'display', '' );
}

/**
 * Utility function to keep DOM object references
 */
function makeClickFunc( row, cell )
{
	return function(e) {
		// prevent the default onClick event from firing / bubbling
		e = new Event(e).stop();
		
		// call remove function
		removeRoll( row, cell, e.target.info );
		}
}

/**
 * Check to see if we should show the 'Add Roles' link / button
 */
function checkAddRolesLink() {
	var haveRoles = false;
	var haveNames = false;
	
	haveRoles = rolesComboInput.getValue().some( function(role) {
		return ( role != '' );
	});
	
	haveNames = namesComboInput.getValue().some( function(name) {
		return ( name != '' );
	});
	
	if( haveRoles && haveNames ) {
		addRolesLink.setStyle( 'display', '' );
	}
	else {
		addRolesLink.setStyle( 'display', 'none' );
	}
}

/**
 * Add an onClick event to the 'add roles' button
 */
function setAddRolesClicker()
{
	addRolesLink.addEvent( 'click', function(e) {
		// prevent the default onClick event from firing / bubbling
		e = new Event(e).stop();
		
		// gather up all combinations of roles
		var roles = rolesComboInput.getValue();
		var rolesComboOptions = rolesComboInput.getChildren();
		var names = namesComboInput.getValue();
		var namesComboOptions = namesComboInput.getChildren();
		var curRoles = Json.evaluate( rolesInput.getValue() );
		var newRoles = new Array();
		
		// build up a matrix of all the possible role/name combinations
		roles.each( function(roleId) {
			for( var i = 0; i < rolesComboOptions.length; i++ ) {
				if( rolesComboOptions[i].getProperty( 'value' ) == roleId ) {
					var role = rolesComboOptions[i].getText();
					break;
				}
			};
			
			names.each( function(personId) {
				if( personId != '' ) {
					for( var j = 0; j < namesComboOptions.length; j++ ) {
						if( namesComboOptions[j].getProperty( 'value' ) == personId ) {
							var name = namesComboOptions[j].getText();
							break;
						}
					};
					newRoles.include( {'id':roleId, 'role':role, 'person_id':personId, 'person_name':name, 'site_id':siteId} );
				}
			});
		});
		
		// call the relevant functions if the new role really is new
		newRoles.each( function(newRole) {
			var personRoleExists = curRoles.some( function(curRole) {
				return ( (curRole.id == newRole.id) && (curRole.person_id == newRole.person_id) && (curRole.site_id == newRole.site_id) );
			});
			
			if( !personRoleExists ) {
				addRoleToTable( newRole );
				curRoles.include( newRole );
				rolesInput.value = Json.toString( curRoles );
			}
		});
		
		// unselect all the chosen roles
		var chosenRolesDivs = rolesComboInput.getParent().getElement( 'div[class=arc_combo_chosen]' ).getChildren();
		var rolesPulldown = rolesComboInput.getParent().getElement( 'div[class=arc_combo_pulldown]' );
		chosenRolesDivs.each( function(roleDiv) {
			roleDiv.fireEvent( 'click' );
		});
		rolesPulldown.fireEvent( 'click' );
		
		// unselect all the chosen names
		var chosenNamesDivs = namesComboInput.getParent().getElement( 'div[class=arc_combo_chosen]' ).getChildren();
		var namesPulldown = namesComboInput.getParent().getElement( 'div[class=arc_combo_pulldown]' );
		chosenNamesDivs.each( function(nameDiv) {
			nameDiv.fireEvent( 'click' );
		});
		namesPulldown.fireEvent( 'click' );
	});
}

/**
 * Remove the specified role from the html table and hidden input
 */
function removeRoll( roleRow, roleCell, roleInfo )
{
	var numOfCells = roleRow.getChildren().length;
	var nextRoleRow = roleRow.getNext();
	var curRoles = Json.evaluate( rolesInput.getValue() );
	
	// if we are removing a row with the roleCell in it then we need to copy the roleCell to the next row if appropriate
	if( (numOfCells == 3) && (nextRoleRow != null) && (nextRoleRow.getChildren().length == 2) ) {
		var roleCell = roleCell.clone().injectTop( nextRoleRow );
		
		var curRow = nextRoleRow;
		do {
			var remLink = curRow.getLast().getElement( 'a' );
			remLink.removeEvents( 'click' );
			remLink.addEvent( 'click', makeClickFunc(curRow, roleCell) );
			
			curRow = curRow.getNext();
		}
		while( (curRow != null) && (curRow.getChildren().length == 2) );
	}
	
	roleCell.setProperty( 'rowspan', (roleCell.getProperty('rowspan') - 1) );
	roleRow.remove();
	
	// remove role from the hidden input
	curRoles.some( function(curRole) {
		if( (curRole.id == roleInfo.id) && (curRole.person_id == roleInfo.person_id) && (curRole.site_id == roleInfo.site_id) ) {
			curRoles.remove( curRole );
			rolesInput.value = Json.toString( curRoles );
			return true;
		}
	});
	
	// check if no more rows in tbody then hide table
	if( rolesTableBody.getChildren() == 0 ) {
		rolesTableDiv.setStyle( 'display', 'none' );
	}
}

/**
 * Add sensitivity to a form submit button
 * Stop the page reload and instead send relevant info to submitForm
 */
function setFormSubmitButtonClicker( el, task )
{
	el.addEvent( 'click', function(e) {
		// prevent the default onClick event from firing / bubbling
		e = new Event(e).stop();
		if( task == 'save' ) {
			saveQueryString = $('save_url').getValue();
		}
		else if( task == 'submit' ) {
			saveQueryString = $('submit_url').getValue();
		}
		else if( task == 'approve' ) {
			saveQueryString = $('approve_url').getValue();
		}
		else if( task == 'reject' ) {
			saveQueryString = $('reject_url').getValue();
		}
		
		// determine which pane to send with the form submission
		var submitted = false;
		if( (task == 'save') || (task == 'submit') ) {
			for( var j = 0; j < collapsibles.length; j++ ) {
				if( collapsibles[j].open == true ) {
				submitForm( task, list[j] );
					submitted = true;
					break;
				}
			}
		}
		else if( (task == 'approve') || (task == 'reject') ) {
			submitForm( task, $('manage_mod_div') );
			submitted = true;
		}
		
		// if no open pane was found then submit without element
		if( !submitted ) {
			submitForm( task );
		}
	});
}

/**
 * Filter inputs to those on closing pane or the open pane if the main save button is clicked
 * Then submit the form via ajax containing only the filtered inputs
 * 
 * @param string task  Which task are performing
 * @param element element  Which page element are we submitting
 */
function submitForm( task, element )
{
	if( typeof(saveQueryString) == 'undefined' ) {
		saveQueryString = $('save_url').getValue();
	}
	
	// initialise data to send with ajax request
	var inputString = new Array();
	
	// if we have been given a pane to process then do it now
	if( typeof(element) != 'undefined' ) {
		// get all the inputs in the form
		inputString = manageForm.toQueryString().split( '&' );
		
		// loop through the inputs and only keep the ones we want
		for( i = (inputString.length - 1); i >= 0; i-- ) {
			var inputArray = inputString[i].split( '=' );
			var inputSearch = inputArray[0].replace( /(manage_|\[\])/gi, '' );
			var numInElement = element.getElements( '[name^=manage_' + inputSearch + ']' ).length;
			
			if( numInElement < 1 ) {
				inputString.splice( i, 1 );
			}
		}
	}
	
	// add video id to the input string array
	var vidId = vidIdInput.getValue();
	inputString.push( 'video_id=' + vidId );
	
	// reassemble the input string
	inputString = inputString.join( '&' );
	
	// Ajax the save function in the controller
	var raw = new Ajax( saveQueryString, {
		'data': inputString,
		'onRequest': function() {
			// hide message div
			messageSpinnerDiv.setStyle( 'display', 'none' );
			
			// hide noSwoosh div
			$( 'ajax_noswoosh_file_div' ).setStyle( 'display', 'none' );
			$( 'ajax_noswoosh_mod_div' ).setStyle( 'display', 'none' );
			
			// add ajax spinner
			$( 'ajax_' + task + '_div' ).setStyle( 'display', 'block' );
		},
		'onSuccess': function() {
			// remove ajax spinner
			$( 'ajax_' + task + '_div' ).setStyle( 'display', 'none' );
			
			// capture and process XHR response
			var response = Json.evaluate( raw.response.text );
			messageSpinnerDiv.empty();
			messageSpinnerDiv.setText( response.message );
			messageSpinnerDiv.setStyle( 'display', 'block' );
			updateIdValues( response.id );
			vidStatusInput.setProperty( 'value', response.status );
			
			// if we just successfully moderated a video, update the sidebar div
			if( ((task == 'approve') || (task == 'reject')) && (response.next_id != vidId) ) {
				// ajax in a fresh status div without checking for ID
				updateStatusDiv( false );
			}
			else {
				// ajax in a fresh status div but check for ID
				updateStatusDiv( true );
			}
		},
		'onFailure': function() {
			// remove ajax spinner
			$( 'ajax_' + task + '_div' ).setStyle( 'display', 'none' );
		}
	}).request();
}

/**
 * Ajax in a new status div
 * 
 * @param idCheck boolean  Should we perform an idCheck for pagination persistence?
 */
function updateStatusDiv( idCheck )
{
	// Get the status div
	var statusDiv = $( 'manage_status_div' );
	
	// Set the query string
	var updateQueryString = $( 'status_url' ).getValue();
	updateQueryString = updateQueryString.replace( 'js.idcheck.replace', (idCheck ? '1' : '0') );
	
	// set the post data
	var inputString = 'video_id=' + vidIdInput.getValue();
	
	// Ajax in the updated status div
	var updateStatusAjax = new Ajax( updateQueryString, {
		'data': inputString,
		'update': statusDiv
	});
	
	// If we aren't going to check IDs then we must be moderating,
	// so Ajax in a new sidebar after the status div
	if( !idCheck ) {
		updateStatusAjax.addEvent( 'onSuccess', function() {
			updateSidebarDiv();
		});
	}
	
	updateStatusAjax.request();
}

/**
 * Ajax in a new sidebar div
 */
function updateSidebarDiv()
{
	// Get the sidebar div
	var sidebarDiv = $( 'sidebar_div' );
	
	// Set the query string
	var sidebarQueryString = $( 'sidebar_url' ).getValue();
	
	// Ajax in the updated status div
	new Ajax( sidebarQueryString, {
		'update': sidebarDiv
	}).request();
}

/**
 * Update all the various video ID values on the page (hidden inputs, url's etc)
 * 
 * @param int newVidId  The new video ID with which to update
 */
function updateIdValues( newVidId )
{
	// vidIdInput
	vidIdInput.setProperty( 'value', newVidId );
	
	// embedded urls (save_url, submit_url, approve_url, reject_url, status_url)
	$( 'save_url' ).setProperty( 'value', $('save_url').getValue().replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	$( 'submit_url' ).setProperty( 'value', $('submit_url').getValue().replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	$( 'approve_url' ).setProperty( 'value', $('approve_url').getValue().replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	$( 'reject_url' ).setProperty( 'value', $('reject_url').getValue().replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	$( 'status_url' ).setProperty( 'value', $('status_url').getValue().replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	
	// progress_url (which may or may not be present)
	if( $('progress_url') != null ) {
		$( 'progress_url' ).setProperty( 'value', $('progress_url').getValue().replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	}
	
	// video file removal link (which may or may not be present)
	if( $('remove_vid_files_link') != null ) {
		$( 'remove_vid_files_link' ).setProperty( 'href', $('remove_vid_files_link').getProperty('href').replace(/vidId=[^&]+/g, 'vidId='+newVidId) );
	}
	
	// vidIdInputFrame (which may or may not be present)
	var iframe = $( 'upload_frame' );
	if( iframe !== null ) {
		var fileDoc = ( iframe.contentDocument ) ? iframe.contentDocument : iframe.contentWindow.document;
		var vidIdInputFrame = fileDoc.getElementById( 'vidId_file_input' );
		vidIdInputFrame.value = newVidId;
	}
}