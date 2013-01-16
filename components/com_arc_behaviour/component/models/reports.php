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

jimport( 'joomla.application.component.model' );

/**
 * Message Hub Controller
 */
class BehaviourModelReports extends JModel
{
	function setReport( $requirements, $series )
	{
		if( !isset($requirements['start_date']) ) { $requirements['start_date'] = ApotheosisLib::getEarlyDate(); }
		if( !isset($requirements['end_date'])   ) { $requirements['end_date']   = date( 'Y-m-d' ); }
		if( isset($requirements['groups']) ) {
			$requirements['groups'] = ApotheosisData::_( 'course.descendants', $requirements['groups'] );
		}
		
		$this->fRpt = ApothFactory::_( 'behaviour.Report' );
		$this->rpt = $this->fRpt->getInstance( 'main' );
		$this->rpt->init( $requirements, $series );
	}
	
	function &getReport()
	{
		return $this->rpt;
	}
	
	function setCumulativeScores( $requirements )
	{
		// work out which pupils we are looking at
		if( isset($requirements['group']) ) {
			$e = ApotheosisData::_( 'timetable.studentEnrolments', $requirements );
			$pIds = array();
			foreach( $e as $enrolment ) {
				$pIds[$enrolment->person_id] = true;
			}
			$pIds = array_keys( $this->_cScores );
		}
		elseif( isset($requirements['person_id']) ) {
			$pIds = $requirements['person_id'];
		}
		else {
			$pIds = array();
		}
		
		$this->_cScores = ApotheosisData::_( 'behaviour.cumulativeScore', $pIds, $requirements['start_date'], $requirements['end_date'] );
	}
	
	function getCumulativeScores()
	{
		return $this->_cScores;
	}
	
	function cleanTemps()
	{
		$this->rpt->removeDataFiles();
	}
}