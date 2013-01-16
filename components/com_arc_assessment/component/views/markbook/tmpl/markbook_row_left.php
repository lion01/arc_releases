<?php
/**
 * @package     Arc
 * @subpackage  Assessment
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

	$new = false;
	$pId = $this->row['person'];
	$gId = $this->row['group'];
	
	if( !isset($this->rooms[$gId]) ) {
		if( !isset($this->_daynum) ) {
			$this->_daynum = ApotheosisLibCycles::dateToCycleDay();
		}
		$tmp = ApotheosisData::_( 'timetable.lesson', array( 'group'=>$gId, 'day'=>$this->_daynum ) );
		$this->rooms[$gId] = ( empty($tmp) ? '' : $tmp[0]->room_id ); // room id if we found one, false otherwise 
	}
	
	echo "\n".'<tr class="'.( $this->edits ? 'pupil_entry' : 'pupil' ).(($this->oddrow) ? ' oddrow' : '').'">';
	echo "\n".'<td>'.ApotheosisData::_( 'course.name', $gId ).'</td>';
	echo "\n".'<td>';
	if($new) {
		echo '<span style="color: green;">*(NEW)</span> ';
	}
	echo JHTML::_('arc_behaviour.addIncident', array('student_id'=>$pId, 'group_id'=>$gId, 'room_id'=>$this->rooms[$gId]) );
	echo ApotheosisData::_( 'people.displayname', $pId ).'</td>';
	$this->oddrow = !$this->oddrow;
?>