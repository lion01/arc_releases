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
<h1 style="text-indent:10px;">Attendance Code Meanings</h1>
<table cellspacing="4px">
<tr><th>Code</th><th>Physical Meaning</th><th>School Meaning</th></tr>
<?php
	$this->showRecent = true;
	foreach( $this->attendanceMarks as $sectionName=>$section) {
/* We now only have one register type so no need to show what type of register this is for
		echo '<tr colspan="3"><th>'.htmlspecialchars($sectionName).'</th></tr>';
// */
		foreach( $section as $code ) {
			$this->mark = $code;
			echo '<tr><td>'.$this->loadTemplate('code').'</td>'
				."\n".'<td>'.htmlspecialchars($this->mark->ph_meaning).'</td>'
				."\n".'<td>'.htmlspecialchars($this->mark->sc_meaning).'</td></tr>';
		}
	} 
?>
</table>