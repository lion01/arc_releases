
<?php
/**
 * @package     Arc
 * @subpackage  Report
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Report Model Writecheck
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportModelWritecheck extends JModel
{
	/**
	 * Find and store the active cycles
	 * Sets $this->_cycles
	 */
	function setCycles()
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		$this->_cycles = $fCyc->getInstances( array( 'active'=>true ) );
		return true;
	}
	
	function getNextCycle()
	{
		if( !isset( $this->_cycle ) || is_null( $this->_cycle ) ) {
			$id = reset( $this->_cycles );
		}
		else {
			$id = next( $this->_cycles );
		}
		
		if( $id === false ) {
			$this->_cycle = null;
		}
		else {
			$fCyc = ApothFactory::_( 'report.cycle' );
			$this->_cycle = $fCyc->getInstance( $id );
		}
		return $this->_cycle;
	}
	
	function setWriteProgress()
	{
		$fSub = ApothFactory::_( 'report.subreport' );
		$this->_progress['write'] = array();
		
		$u = ApotheosisLib::getUser();
		$personId = $u->person_id;
		$writeRole = ApotheosisLibAcl::getRoleId( 'any_report_author' );
		$groups = ApotheosisData::_( 'report.groups', array( 'person_id'=>$personId, 'role_id'=>$writeRole ) );
		
		foreach( $this->_cycles as $cId ) {
			$req = array( 'cycle'=>$cId, 'group'=>$groups );
			$this->_progress['write'][$cId]['total']        = count( $fSub->getInstances( $req ) );
			
			$req['status'] = ARC_REPORT_STATUS_NASCENT;
			$this->_progress['write'][$cId][$req['status']] = count( $fSub->getInstances( $req ) );
			$req['status'] = ARC_REPORT_STATUS_INCOMPLETE;
			$this->_progress['write'][$cId][$req['status']] = count( $fSub->getInstances( $req ) );
			$req['status'] = ARC_REPORT_STATUS_SUBMITTED;
			$this->_progress['write'][$cId][$req['status']] = count( $fSub->getInstances( $req ) );
			$req['status'] = ARC_REPORT_STATUS_REJECTED;
			$this->_progress['write'][$cId][$req['status']] = count( $fSub->getInstances( $req ) );
		}
	}
	
	function getWriteProgress()
	{
		return $this->_progress['write'];
	}
	
	function setCheckProgress()
	{
		$fSub = ApothFactory::_( 'report.subreport' );
		$this->_progress['check'] = array();
		
		$u = ApotheosisLib::getUser();
		$personId = $u->person_id;
		$checkRole = ApotheosisLibAcl::getRoleId( 'any_report_checker' );
		$groups = ApotheosisData::_( 'report.groups', array( 'person_id'=>$personId, 'role_id'=>$checkRole ) );
		
		foreach( $this->_cycles as $cId ) {
			$req = array( 'cycle'=>$cId, 'group'=>$groups );
			$this->_progress['check'][$cId]['total']        = count( $fSub->getInstances( $req ) );
			
			$req['status'] = ARC_REPORT_STATUS_SUBMITTED;
			$this->_progress['check'][$cId][$req['status']] = count( $fSub->getInstances( $req ) );
			$req['status'] = ARC_REPORT_STATUS_APPROVED;
			$this->_progress['check'][$cId][$req['status']] = count( $fSub->getInstances( $req ) );
		}
	}
	
	function getCheckProgress()
	{
		return $this->_progress['check'];
	}
	
}
?>