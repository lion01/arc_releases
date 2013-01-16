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

/**
 * Report Mark Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Attendance_Score extends ApothReportField
{
	function renderHTML( $value )
	{
		$pId = $this->_rptData[$this->_core['lookup_source']];
		
		if( is_null( $value ) ) {
			switch( $this->_config['type'] ) {
			case( 'statutory' ):
			default:
				$m = ApotheosisData::_( 'attendance.attendancePercent', $this->_config['from'], $this->_config['to'], $pId ).' %';
			}
		}
		else {
			$m = htmlspecialchars( $value );
		}
		return parent::renderHTML( $m );
	}
}

/**
 * Report Average Mark Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Attendance_Graph extends ApothReportField
{
	function renderHTML( $value )
	{
		$pId  = $this->_rptData[$this->_core['lookup_source']];
		$from = $this->_config['from'];
		$to   = $this->_config['to'];
		$this->_data = ApotheosisData::_( 'attendance.dataSummary', array('pupil'=>$pId, 'start_date'=>$from, 'end_date'=>$to ) );
		$this->colours = array( '00ff00', '66ff66', 'ff6600', 'ff0000', 'aaaa66', 'c0c0c0' );
		ob_start();
		switch( $this->_config['layout'] ) {
		case( 'subject-percent' ):
		default:
			include( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'panel_all_histo_per.php' );
			break;
		
		case( 'subject' ):
			include( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'panel_all_histo.php' );
			break;
		
		case( 'statutory' ):
			include( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'panel_stat_pie.php' );
			break;
		}
		$html = ob_get_clean();
		unset( $this->_data );
		unset( $this->colours );
		return parent::renderHTML( $html );
	}
}
?>