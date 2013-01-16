
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
	var $_requirementsUsed = array();
	
	/**
	 * Set up the active cycle
	 * Sets $this->_cycleId
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
	 * Set up the search results
	 * Sets $this->_subreports (paginator) and its data
	 * @param string $activity  The activity in progress ('write'|'check'|'view')
	 */
	function setSearch( $filters = array() )
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
		
		$requirements = $filters;
		$requirements['cycle'] = $this->_cycleId;
		$requirements['role'] = $role;
		$requirements['role_person'] = $personId;
		if( $requirements !== $this->_requirementsUsed ) {
			$this->_requirementsUsed = $requirements;
			$this->_subreports = &ApothPagination::_( 'report.subreport', $this->_subreports );
			$this->_subreports->setData( $requirements, array( 'group_name'=>'a', 'reportee_name'=>'a' ) );
		}
	}
	
	/**
	 * Set the breadcrumb to get back to this page
	 * pay no attention to filters here as they are dealt with in the nav model
	 * 
	 * @param unknown_type $fCrumbs
	 */
	function resetBreadcrumbs( &$fCrumbs )
	{
		$c = $this->getCycle();
		$name = $c->getDatum( 'name' );
		$dep = array( 'report.cycle'=>$c->getId() );
		// Set the base breadcrumb for the list
		switch( $this->_activity ) {
		case( 'view' ):
			$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'View '.$name, ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_view_list', $dep ) );
			break;
		
		case( 'write' ):
			$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Write '.$name, ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_write_list', $dep ) );
			break;
		
		case( 'check' ):
			$fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, 'Check '.$name, ApotheosisLibAcl::getUserLinkAllowed( 'apoth_report_check_list', $dep ) );
			break;
		}
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