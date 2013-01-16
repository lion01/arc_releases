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
?>
<table>
<?php
$userCheck = false;
$oddCol = false;
$first = true;

foreach( $this->_datasets as $dataset ) {
	$this->_data = $this->model->getToday( $dataset );
	if( $first ) {
		$first = false;
		foreach( $this->_data as $entry ) {
			if( !$userCheck ) {
				$requirements = array( 'option'=>'com_arc_attendance', 'view'=>'ereg', 'scope'=>'recent', 'task'=>'search' );
				$dependancies = array( 'day_section'=>'', 'normal_class'=>'' );
				$this->regActionId = ApotheosisLib::getActionId( $requirements, $dependancies );
				
				$uId = $entry['j_user_id'];
				$this->isTeacher = ApotheosisLibAcl::getUserPermitted( $uId, $this->regActionId );
				$userCheck = true;
			}
			if( isset($this->attMarks[$this->dataKey][$entry['day_section']]) ) {
				$attMark = $this->attMarks[$this->dataKey][$entry['day_section']]['att_code'];
			}
			else {
				$attMark = null;
			}
			echo '<th class="'.( ($oddCol = !$oddCol) ? 'oddcol' : 'evencol' ).' att_img">'.$entry['day_section']
			.( $this->isTeacher ? '' : ' '.JHTML::_( 'arc_attendance.marks', $attMark ) )
			.'</th>';
		}
	}
	echo '<tr>'.$this->loadTemplate( 'row' ).'</tr>';
}
?>
</table>
