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
?>
<div style="text-align: center;">
<?php
// call in the mooTools/Arc tooltip behaviour
static $firstTime = true;
if( $firstTime ) {
	JHTML::_( 'Arc.tip', 'inc_tip' );
	$firstTime = false;
}

if( $this->showIncidents && !empty($this->incidents) ) {
	echo $this->loadTemplate( 'messages' );
}
if($this->showRecent) {
	echo JHTML::_( 'arc_attendance.marks', $this->mark );
}
?>
</div>