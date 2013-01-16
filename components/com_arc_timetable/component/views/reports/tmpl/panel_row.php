<?php
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

defined( '_JEXEC' ) or die( 'Restricted access' );

$oddCol = false;
foreach( $this->_data as $entry ) {
	$linkDeps = array( 'timetable.sections'=>$entry['day_section'], 'attendance.groups'=>$entry['id'] );
	$attLink = '<a href="'.ApotheosisLib::getActionLink( $this->regActionId, $linkDeps ).'">Register</a>';
	
	echo '<td class="'.( ($oddCol = !$oddCol) ? 'oddcol' : 'evencol' ).'">'
		.$entry['fullname'].( ($entry['room_id'] != '') ? ' ('.$entry['room_id'].')' : '')
		.( !is_null($entry['ext_course_id_2']) ? '<br /><a href="'.$this->extLink.$entry['ext_course_id_2'].'">VLE</a>' : '' )
		.( (!is_null($entry['ext_course_id_2']) && $this->isTeacher) ? ' / ' : '<br />' )
		.( $this->isTeacher ? $attLink : '' )
		.'</td>'."\n";
}
?>