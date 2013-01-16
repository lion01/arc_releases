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
		plgSystemArc_log::startTimer( 'attendance '.get_class().' renderHTML' );
		$pId = $this->_rptData[$this->_core['lookup_source']];
		
		if( is_null( $value ) ) {
			switch( $this->_config['type'] ) {
			case( 'statutory' ):
			default:
				$m = ApotheosisData::_( 'attendance.attendancePercent', $this->_config['from'], $this->_config['to'], $pId ).' %';
				break;
			}
		}
		else {
			$m = htmlspecialchars( $value );
		}
		plgSystemArc_log::stopTimer( 'attendance '.get_class().' renderHTML' );
		return parent::renderHTML( $m );
	}
	
	function renderPDF( $pdf, $value )
	{
		$pId = $this->_rptData[$this->_core['lookup_source']];
		
		if( is_null( $value ) ) {
			switch( $this->_config['type'] ) {
			case( 'statutory_present' ):
				$data = ApotheosisData::_( 'attendance.dataSummary', array( 'start_date'=>$this->_config['from'], 'end_date'=>$this->_config['to'], 'pupil'=>$pId ) ) ;
				
				$p = $data['statutory']['Present']
				   + $data['statutory']['Approved educational activity'];
				$a = $data['statutory']['Unauthorised absence'];
				
				$t = $p + $a;
				$txt = $p / $t * 100;
				break;
			
			case( 'statutory_absent' ):
				$data = ApotheosisData::_( 'attendance.dataSummary', array( 'start_date'=>$this->_config['from'], 'end_date'=>$this->_config['to'], 'pupil'=>$pId ) ) ;
				
				$p = $data['statutory']['Present']
				   + $data['statutory']['Approved educational activity'];
				$a = $data['statutory']['Unauthorised absence'];
				
				$t = $p + $a;
				$txt = $a / $t * 100;
				break;
			}
			$txt = number_format( $txt, 0 ).'%';
			
			if( isset( $this->_config['with_average'] ) && $this->_config['with_average'] ) {
				switch( $this->_config['type'] ) {
				case( 'statutory_present' ):
					$data = ApotheosisData::_( 'attendance.dataSummary', array( 'start_date'=>$this->_config['from'], 'end_date'=>$this->_config['to'] ) ) ;
					
					$p = $data['statutory']['Present']
					   + $data['statutory']['Approved educational activity'];
					$a = $data['statutory']['Unauthorised absence'];
					
					$t = $p + $a;
					$txtAvg = $p / $t * 100;
					break;
				
				case( 'statutory_absent' ):
					$data = ApotheosisData::_( 'attendance.dataSummary', array( 'start_date'=>$this->_config['from'], 'end_date'=>$this->_config['to'] ) ) ;
					
					$p = $data['statutory']['Present']
					   + $data['statutory']['Approved educational activity'];
					$a = $data['statutory']['Unauthorised absence'];
					
					$t = $p + $a;
					$txtAvg = $a / $t * 100;
					break;
				}
				$txtAvg = number_format( $txtAvg, 0 ).'%';
				$txt .= ' ('.$txtAvg.')';
			}
		}
		else {
			$txt = htmlspecialchars( $value );
		}
		return parent::renderPDF( $pdf, $txt );
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
		plgSystemArc_log::startTimer( 'attendance '.get_class().' renderHTML' );
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
		plgSystemArc_log::stopTimer( 'attendance '.get_class().' renderHTML' );
		return parent::renderHTML( $html );
	}
	
	function renderPDF( $pdf, $value )
	{
		$pId  = $this->_rptData[$this->_core['lookup_source']];
		$from = $this->_config['from'];
		$to   = $this->_config['to'];
		$this->_data = ApotheosisData::_( 'attendance.dataSummary', array('pupil'=>$pId, 'start_date'=>$from, 'end_date'=>$to ) );
		$this->colours = array( '00ff00', '66ff66', 'ff6600', 'ff0000', 'aaaa66', 'c0c0c0' );
		
		switch( $this->_config['layout'] ) {
		case( 'subject-percent' ):
		default:
			include( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'pdf_all_histo_per.php' );
			$this->imgSrc = $this->allHistoPerImageUrl;
			break;
		
		case( 'statutory' ):
			include( JPATH_SITE.DS.'components'.DS.'com_arc_attendance'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'pdf_stat_pie.php' );
			$this->imgSrc = $this->statPieImageUrl;
			break;
		}
		unset( $this->_data );
		unset( $this->colours );
		
		
		$l = $this->_core['print_l'] + $this->_boundBox['l'];
		$t = $this->_core['print_t'] + $this->_boundBox['t'];
		$w = $this->_core['print_width'];
		$h = $this->_core['print_height'];
		$r = $this->_boundBox['r'] + ( $this->_boundBox['w'] - ( $this->_core['print_l'] + $this->_core['print_width'] ) );
		
		if( $this->_core['print_border'] ) {
			$pdf->rect( $l, $t, $w, $h);
		}
		
		$l += $this->_core['print_pad_l'];
		$t += $this->_core['print_pad_t'];
		$r += $this->_core['print_pad_r'];
		
		$config = &JFactory::getConfig();
		$dirName = $config->getValue('config.tmp_path');
		$tmpName = tempnam( $dirName, 'att_'.time().'_' );
		copy( $this->imgSrc, $tmpName );
		
		$pdf->image( $tmpName, $l, $t, $w, $h, 'png', '', '', true );
		
		unlink( $tmpName );
	}
}
?>