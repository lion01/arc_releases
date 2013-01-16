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

require_once( JPATH_COMPONENT.DS.'models'.DS.'extension.php' );

 /*
 * Extension Manager Ereg Summary Model
 *
 * @author		lightinthedark <code@lightinthedark.org.uk>
 * @package		Apotheosis
 * @subpackage	Attendance
 * @since		0.1
 */
class AttendanceModelEreg extends AttendanceModel
{
	var $regType = '';
	
	function setRegType( $type )
	{
		if( $type != $this->regType ) {
			unset($this->_uncommon_attendanceMarks);
		}
		$this->regType = $type;
	}
	
	function getCommonMarks()
	{
		if (!isset($this->_common_attendanceMarks)) {
			$this->_common_attendanceMarks = array();
			$tmp = ApotheosisAttendanceData::getCodeObjects( array('is_common'=>'1', 'type'=>( $this->regType == 'pseudo' ? 'normal' : $this->regType ) ) );
			foreach($tmp as $v) {
				$this->_common_attendanceMarks[$v->code] = $v;
			}
		}
		return $this->_common_attendanceMarks;
	}
	
	function getUncommonMarks()
	{
		if (!isset($this->_uncommon_attendanceMarks)) {
			$this->_uncommon_attendanceMarks = array();
			$tmp = ApotheosisAttendanceData::getCodeObjects( array('is_common'=>'0', 'type'=>( $this->regType == 'pseudo' ? 'normal' : $this->regType ) ) );
			foreach($tmp as $v) {
				$this->_uncommon_attendanceMarks[$v->code] = $v;
			}
		}
		return $this->_uncommon_attendanceMarks;
	}
	
}
?>
