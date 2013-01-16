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
Date, Day Section, Group Name, Attendance Code
<?php foreach($this->tt as $date=>$row) {
	foreach( $row as $details ) {
		$key = $details->date.'~*~'.$details->day_section.'~*~'.$details->person_id.'~*~'.$details->course;
		echo '"'.$details->date.'", '
			.$details->day_section.', '
			.$details->coursename.', '
			.(array_key_exists($key, $this->marks) ? $this->marks[$key]->att_code : '--')
			."\n";
	}
} ?>
