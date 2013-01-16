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
 * Report Mark Field
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
}

/**
 * Report Average Mark Field
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
	
	function _getGraphLink( $sIds, $h1, $h2, $labels = true )
	{
		$graphLink = JURI::Base().'components'.DS.'com_arc_behaviour'.DS.'views'.DS.'reports'.DS.'tmpl'.DS.'graph.php?w=%1$s&h1=%2$s&h2=%3$s&file=%4$s&labels=%5$s';
		$datFileName = $this->report->getDataFile( $sIds );
		return sprintf( $graphLink, 200, $h1, $h2, base64_encode( $datFileName ), ($labels ? 1 : 0) );
	}
}
?>