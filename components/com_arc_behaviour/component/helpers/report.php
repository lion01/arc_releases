<?php
/**
 * @package     Arc
 * @subpackage  Behaviour
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Behaviour Score Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Behaviour_Score extends ApothReportField
{
	function renderHTML( $value )
	{
		plgSystemArc_log::startTimer( 'behaviour '.get_class().' renderHTML' );
		$pId = $this->_rptData[$this->_core['lookup_source']];
		
		if( is_null( $value ) ) {
			$m = ApotheosisData::_( 'behaviour.personScore', $pId, $this->_config['from'], $this->_config['to'] );
		}
		else {
			$m = htmlspecialchars( $value );
		}
		plgSystemArc_log::stopTimer( 'behaviour '.get_class().' renderHTML' );
		return parent::renderHTML( $m );
	}
	
	function renderPDF( $pdf, $value )
	{
		plgSystemArc_log::startTimer( 'behaviour '.get_class().' renderHTML' );
		$pId = $this->_rptData[$this->_core['lookup_source']];
		
		if( is_null( $value ) ) {
			$m = ApotheosisData::_( 'behaviour.personScore', $pId, $this->_config['from'], $this->_config['to'] );
			if( isset( $this->_config['with_average'] ) ) {
				$m .= ' ('
					.number_format( ApotheosisData::_( 'behaviour.personScore', null, $this->_config['from'], $this->_config['to'] ) )
					.')';
			}
		}
		else {
			$m = htmlspecialchars( $value );
		}
		plgSystemArc_log::stopTimer( 'behaviour '.get_class().' renderHTML' );
		return parent::renderPDF( $pdf, $m );
	}
}

/**
 * Behaviour Tally Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Behaviour_Tally extends ApothReportField
{
	function renderHTML( $value )
	{
		$pId = $this->_rptData[$this->_core['lookup_source']];
		$color = ( isset( $this->_config['color'] ) ? $this->_config['color'] : null );
		
		if( is_null( $value ) ) {
			$m = ApotheosisData::_( 'behaviour.personTally', $pId, $this->_config['from'], $this->_config['to'] );
		}
		else {
			$m = htmlspecialchars( $value );
		}
		return parent::renderHTML( $m );
	}
	
	function renderPDF( $pdf, $value )
	{
		$pId = $this->_rptData[$this->_core['lookup_source']];
		$color = ( isset( $this->_config['color'] ) ? $this->_config['color'] : null );
		
		if( is_null( $value ) ) {
			$m = ApotheosisData::_( 'behaviour.personTally', $pId, $color, $this->_config['from'], $this->_config['to'] );
			
			if( isset( $this->_config['with_average'] ) ) {
				$m .= ' ('
					.number_format( ApotheosisData::_( 'behaviour.personTally', null, $color, $this->_config['from'], $this->_config['to'] ), 0 )
					.')';
			}
		}
		else {
			$m = htmlspecialchars( $value );
		}
		return parent::renderPDF( $pdf, $m );
	}
}

/**
 * Behaviour Graph Field
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ApothReportField_Behaviour_Graph extends ApothReportField
{
	function renderHTML( $value )
	{
		plgSystemArc_log::startTimer( 'behaviour '.get_class().' renderHTML' );
		$requirements = array(
			'person_id'=>$this->_rptData[$this->_core['lookup_source']],
			'start_date'=>$this->_config['from'],
			'end_date'=>$this->_config['to']
		);
		$series = 'person_id';
		
		$fRpt = ApothFactory::_( 'behaviour.Report' );
		$this->report = $fRpt->getInstance( 'main' );
		$this->report->init( $requirements, $series );
		$this->report->removeDataFiles();
		
		$this->seriesIds = $this->report->getSeriesIds();
		
		ob_start();
		include( JPATH_SITE.DS.'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'panel.php' );
		$html = ob_get_clean();
		
		plgSystemArc_log::stopTimer( 'behaviour '.get_class().' renderHTML' );
		return parent::renderHTML( $html );
	}
	
	function renderPDF( $pdf, $value )
	{
		$requirements = array(
			'person_id'=>$this->_rptData[$this->_core['lookup_source']],
			'start_date'=>$this->_config['from'],
			'end_date'=>$this->_config['to']
		);
		$series = 'person_id';
		
		$fRpt = ApothFactory::_( 'behaviour.Report' );
		$this->report = $fRpt->getInstance( 'main' );
		$this->report->init( $requirements, $series );
		$this->report->removeDataFiles();
		
		$this->seriesIds = $this->report->getSeriesIds();
		
		
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
		
		$scaleRes = 5;
		$h1 = $h*$scaleRes;
		$h2 = 0;
		$pw = $w*$scaleRes;
		
		$this->imgSrc = $this->_getGraphLink( $this->seriesIds, $h1, $h2, $pw );
		
		$config = &JFactory::getConfig();
		$dirName = $config->getValue('config.tmp_path');
		$tmpName = tempnam( $dirName, 'behav_'.time().'_' );
		copy( $this->imgSrc, $tmpName );
		
		$pdf->image( $tmpName, $l, $t, $w, $h, 'png', '', '', true );
		
		unlink( $tmpName );
	}
	
	function _getGraphLink( $sIds, $h1, $h2, $w = 200, $labels = true )
	{
		$graphLink = JURI::Base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'graph.php?w=%1$s&h1=%2$s&h2=%3$s&file=%4$s&labels=%5$s';
		$datFileName = $this->report->getDataFile( $sIds );
		return sprintf( $graphLink, $w, $h1, $h2, base64_encode( $datFileName ), ($labels ? 1 : 0) );
	}
}
?>