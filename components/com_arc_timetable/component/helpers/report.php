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

/**
 * Report GroupTeacher Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.9.1
 */
class ApothReportField_Timetable_GroupTeacher extends ApothReportField
{
	function renderHTML( $value )
	{
		$vals = $this->getValues();
		$txt = $vals['gName'].' - '.$vals['tName'];
		
		return parent::renderHTML( $txt );
	}
	
	function renderPDF( $pdf, $value )
	{
		$vals = $this->getValues();
		$sep = ( (isset($this->_config['multiline']) && $this->_config['multiline'] ) ? '<br />' : ' - ' );
		
		if( isset($this->_config['teacher_only']) && $this->_config['teacher_only'] ) {
			$txt = $vals['tName'];
		}
		else {
			$txt = $vals['gName'].$sep.$vals['tName'];
		}
		parent::renderPDF( $pdf, $txt );
	}
	
	function getValues()
	{
		$gIdRpt  = $this->_rptData[$this->_core['lookup_source']];
		$gIdOrig = ApotheosisData::_( 'report.lookupGroup', $gIdRpt );
		$tId = ( isset($this->_config['teacher_id']) ? $this->_config['teacher_id'] : $this->_rptData['teacher_id'] );
		
		if( isset( $this->_config['related'] ) ) {
			$gIdRpt = ApotheosisData::_( $this->_config['related'], $gIdRpt );
			
			$tIds = ApotheosisData::_( 'timetable.teachers', $gIdRpt );
			if( !empty( $tIds ) ) {
				$tId = reset( $tIds );
			}
		}
		elseif( isset( $this->_config['tutor_group'] ) ) {
			$gIdOrig = $gIdRpt = ApotheosisData::_( 'timetable.tutorgroup', $this->_rptData[$this->_config['tutor_group']] );
			
			$tIds = ApotheosisData::_( 'timetable.teachers', $gIdRpt );
			$tId = reset( $tIds );
		}
		
		$gName = ApotheosisData::_( 'course.name', $gIdRpt );
		$tName = ApotheosisData::_( 'people.displayName', $tId, 'teacher' );
		
		return array( 'gName'=>$gName, 'tName'=>$tName );
	}
}
?>