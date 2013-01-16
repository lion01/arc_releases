<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php echo $this->state->message; ?>
<script language="javascript">

function switchCase() {
	chkBox = document.getElementById("jsconvert");

	// getting the value
	selObj = document.getElementById("property");
	for (var i = 0, len = selObj.options.length; i < len; i++) {
		if(selObj.options[i].selected == true) {
			selObjOption = selObj.options[i];
		}
	}
	// the first character
	ch = selObjOption.value.substring(0,1);
	
	// the rest of the value
	rest = selObjOption.value.substring(1);
	
	// convert first character to uppercase
	if(chkBox.checked == true) {
		up = ch.toUpperCase();
	}
	else {
		up = ch.toLowerCase();
	}
	
	// concatenate the uppercase with the rest
	newValue = up + rest;
	selObjOption.value = newValue;
	selObjOption.text = newValue;
}

</script>

<?php
$task = (!is_null(JRequest::getVar('task')) ? JRequest::getVar('task') : NULL);

switch($task) {
	case('edit'):
		echo $this->loadTemplate('edit');
		break;
	case('add'):
		echo $this->loadTemplate('new');
		break;
	
	default:
		echo $this->loadTemplate('list');
		break;
}
?>