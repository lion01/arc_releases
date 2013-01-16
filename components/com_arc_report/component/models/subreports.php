
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
 * Report Model Subreports
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportModelSubreports extends JModel
{
	/**
	 * Set up the active cycle
	 * Sets $this->_cycle
	 * @param int $id  The cycle id to set as current
	 */
	function setCycle( $id )
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		$cycle = &$fCyc->getInstance( $id );
		$this->_cycleId = $cycle->getId();
	}
	
	function &getCycle()
	{
		$fCyc = ApothFactory::_( 'report.cycle' );
		return $fCyc->getInstance( $this->_cycleId );
	}
	
	/**
	 * Set up the activity
	 * Sets $this->_activity
	 * @param string $activity  The activity in progress ('write'|'check'|'view')
	 */
	function setActivity( $activity )
	{
		$this->_activity = $activity;
	}
	
	function getActivity()
	{
		return $this->_activity;
	}
	
	/**
	 * Set up the filter values
	 * Sets $this->_requirements
	 * @param string $activity  The activity in progress ('write'|'check'|'view')
	 */
	function setFilterValues( $requirements )
	{
		$u = ApotheosisLib::getUser();
		$personId = $u->person_id;
		
		switch( $this->_activity ) {
		case( 'view' ):
			$role = ApotheosisLibAcl::getRoleId( 'any_report_reader');
			break;
		
		case( 'write' ):
			$role = ApotheosisLibAcl::getRoleId( 'any_report_author');
			break;
		
		case( 'check' ):
			$role = ApotheosisLibAcl::getRoleId( 'any_report_checker');
			break;
		
		default:
			$role = 0;
		}
		$this->_requirements = $requirements;
		$this->_requirements['cycle'] = $this->_cycleId;
		$this->_requirements['person'] = $personId;
		$this->_requirements['role'] = $role;
	}
	
	function setSearch()
	{
		$this->_subreports = &ApothPagination::_( 'report.subreport', $this->_subreports );
		$this->_subreports->setData( $this->_requirements, array( 'group_name', 'reportee_name' ) );
	}
	
	function resetBreadcrumbs( &$fCrumbs )
	{
		$this->_fCrumbs = &$fCrumbs;
		
		$c = $this->getCycle();
		$name = $c->getDatum( 'name' );
		$dep = array( 'report.cycle'=>$c->getId() );
		// Set the base breadcrumb for the list
		if( !isset( $this->_baseCrumb ) ) {
			switch( $this->_activity ) {
			case( 'view' ):
				$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'View '.$name, array( 'action'=>'apoth_report_view_list', 'dependancies'=>$dep ) );
				break;
			
			case( 'write' ):
				$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Write '.$name, array( 'action'=>'apoth_report_write_list', 'dependancies'=>$dep ) );
				break;
			
			case( 'check' ):
				$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Check '.$name, array( 'action'=>'apoth_report_check_list', 'dependancies'=>$dep ) );
				break;
			}
		}
		
		// Set any crumbs for filter conditions
		// **** TODO - once there is a clearer idea of how filter terms come through.
	}
	
	function setPage( $pageId )
	{
		$this->_subreports = &ApothPagination::_( 'report.subreport', $this->_subreports );
		$p = $this->_subreports->setPage( $pageId );
		return ( $pageId == $p );
	}
	
	function getSubreportCount()
	{
		return $this->_subreports->getInstanceCount();
	}
	
	function &getNextSubreport()
	{
		if( !isset( $this->_subreportPage ) ) {
			$this->_subreportPage = $this->_subreports->getPagedInstances();
			$id = reset( $this->_subreportPage );
		}
		else {
			$id = next( $this->_subreportPage );
		}
		
		if( $id === false ) {
			$this->_subreport = null;
			unset( $this->_subreportPage );
		}
		else {
			$fSub = $this->_subreports->getFactory();
			$this->_subreport = $fSub->getInstance( $id );
		}
		
		return $this->_subreport;
	}
	
	function setSubreport( $subreportId )
	{
		$fSub = ApothFactory::_( 'report.subreport' );
		$this->_subreport = $fSub->getInstance( $subreportId );
		return $this->_subreport->getId() == $subreportId;
	}
	
	function getSubreport()
	{
		return $this->_subreport;
	}
}
?>