<?php
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

defined( '_JEXEC' ) or die( 'Restricted access' );

// add default CSS
$this->addPath = JURI::base().'components'.DS.'com_arc_attendance'.DS.'views'.DS.'ereg'.DS.'tmpl'.DS;
JHTML::stylesheet( 'default.css', $this->addPath );

JHTML::_('behavior.mootools'); 
JHTML::_('behavior.modal'); 
JHTML::_('behavior.tooltip');
?>
<script language="javascript" type="text/javascript">

function show(id) {
	var i = 1;
	field = document.getElementById( id + i );
	while ( field != null ) {
	     field.className= 'regHeading';
	     i = i + 1;
	     field = document.getElementById( id + i );
	}
}

function hide(id) {
	var i = 1;
	field = document.getElementById( id + i );
	while ( field != null ) {
	     field.className = 'regHeading hide';
	     //field.style.display = 'none';
	     i = i + 1;
	     field = document.getElementById( id + i );
	}
}

</script>

<h3>E-Registration</h3>
<?php if ($this->state->search) {
	echo $this->loadTemplate( 'search_'.JRequest::getCmd( 'scope', 'recent' ));
}
?>

<hr />
<?php
switch($this->layout) {
case('class'):
case('register'):
	echo $this->loadTemplate($this->layout);
	break;

default:
	echo 'There are no registers to display<br />';
	echo 'Would you like to <a href="index.php?option=com_arc_attendance&view=ereg&scope=recent&Itemid=206" >search for a register</a> instead?';
	break;
}

?>