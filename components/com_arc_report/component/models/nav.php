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
 * Report Model Home
 *
 * @author     Punnet - Arc Team <arc_developers@pun.net>
 * @package    Arc
 * @subpackage Report
 * @since      1.8
 */
class ReportModelNav extends JModel
{
	var $filterValues = array();
	var $filterValuesOrdered = array();
	
	function __construct( $config = array() )
	{
		parent::__construct( $config );
		$this->_activity = 'view';
	}
	
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
	
	/**
	 * Set up the activity
	 * Sets $this->_activity
	 * @param string $activity  The activity in progress ('write'|'check'|'view')
	 */
	function setActivity( $activity )
	{
		$this->_activity = $activity;
	}
	
	function setListValues( $ident, $requirements )
	{
		$this->listValues = array();
		$forSubject = $forGroup = $forStudent = array();
		
		$u = ApotheosisLib::getUser();
		$pId = $u->person_id;
		
		switch( $this->_activity ) {
		case( 'write' ):
			$role = 'report_author';
			break;
			
		case( 'check' ):
			$role = 'report_checker';
			break;
			
		default:
		case( 'view' ):
			$role = 'report_reader';
			break;
		}
		$rId = ApotheosisLibAcl::getRoleId( $role );
		
		$rptGroups = ApotheosisData::_( 'report.groups', array( 'cycle'=>$this->_cycleId, 'person_id'=>$pId, 'role_id'=>$rId ) );
		
		switch( $ident ) {
		case( 'subject' ):
			$restricted = false;
			if( !empty($requirements['subject']) ) {
				$forSubject = ApotheosisData::_( 'course.groups', $requirements['subject'] );
			}
			if( !empty($requirements['group']) ) {
				$restricted = true;
				$forGroup   = ApotheosisData::_( 'course.subjects', $requirements['group'] );
			}
			if( !empty($requirements['student']) ) {
				$restricted = true;
				$enrolled = ApotheosisData::_( 'timetable.enrolments', array( 'person_id'=>$requirements['student'] ) );
				foreach( $enrolled as $row ) {
					$studentGroups[] = $row['group_id'];
				}
				$studentGroups = array_intersect( $studentGroups, $rptGroups );
				$forStudent = ApotheosisData::_( 'course.subjects', $studentGroups );
			}
			
			if( $restricted ) {
				$ids = array_unique( array_merge( $forSubject, $forGroup, $forStudent ) );
			}
			else {
				$ids = ApotheosisData::_( 'course.subjects', $rptGroups );
			}
			$names = ApotheosisData::_( 'course.names', $ids );
			
			foreach( $names as $id=>$name ) {
				$this->listValues[] = array( 'id'=>$id, 'text'=>$name );
			}
			break;
		
		case( 'group' ):
			$restricted = false;
			if( !empty($requirements['subject']) ) {
				$restricted = true;
				$forSubject = ApotheosisData::_( 'course.descendants', $requirements['subject'], $rptGroups );
			}
			if( !empty($requirements['group']) ) {
				$forGroup   = ApotheosisData::_( 'course.groups', $requirements['group'] );
			}
			if( !empty($requirements['student']) ) {
				$restricted = true;
				$enrolled = ApotheosisData::_( 'timetable.enrolments', array( 'person_id'=>$requirements['student'] ) );
				foreach( $enrolled as $row ) {
					$studentGroups[] = $row['group_id'];
				}
				$studentGroups = array_intersect( $studentGroups, $rptGroups );
				$forStudent = ApotheosisData::_( 'course.groups', $studentGroups );
			}
			
			if( $restricted ) {
				$ids = array_unique( array_merge( $forSubject, $forGroup, $forStudent ) );
			}
			else {
				$subjects = ApotheosisData::_( 'course.subjects', $rptGroups );
				$descs = ApotheosisData::_( 'course.descendants', $subjects, $rptGroups );
				$ids = array_diff( $descs, $subjects );
			}
			$names = ApotheosisData::_( 'course.names', $ids );
			
			foreach( $names as $id=>$name ) {
				$this->listValues[] = array( 'id'=>$id, 'text'=>$name );
			}
			break;
		
		case( 'student' ):
			$restricted = false;
			$reporteeRole = ApotheosisLibAcl::getRoleId( 'report_reportee' );
			if( !empty($requirements['subject']) ) {
				$restricted = true;
				$subjGroups = ApotheosisData::_( 'course.descendants', $requirements['subject'], $rptGroups );
				$enrolled = ApotheosisData::_( 'timetable.enrolments', array( 'groups'=>$subjGroups, 'role'=>$reporteeRole ) );
				
				foreach( $enrolled as $row ) {
					$forSubject[] = $row['person_id'];
				}
			}
			if( !empty($requirements['group']) ) {
				$restricted = true;
				$subjGroups = ApotheosisData::_( 'course.groups', $requirements['group'], $rptGroups );
				$enrolled = ApotheosisData::_( 'timetable.enrolments', array( 'groups'=>$subjGroups, 'role'=>$reporteeRole ) );
				foreach( $enrolled as $row ) {
					$forGroup[] = $row['person_id'];
				}
			}
			if( !empty($requirements['student']) ) {
				$forStudent = $requirements['student'];
			}
			
			if( $restricted ) {
				$ids = array_unique( array_merge( $forSubject, $forGroup, $forStudent ) );
			}
			else {
				$ids = ApotheosisData::_( 'timetable.members', $rptGroups, $reporteeRole );
			}
			
			$people = ApotheosisData::_( 'people.displayNames', $ids );
			foreach( $people as $pId=>$name ) {
				$this->listValues[] = array( 'id'=>$pId, 'text'=>$name );
			}
			break;
		
		case( 'status' ):
			$data = ApotheosisData::_( 'report.statuses', array() );
			foreach( $data as $row ) {
				$row['text'] = $row['status'];
				unset( $row['status'] );
				$this->listValues[] = $row;
			} 
			break;
		}
	}
	
	function getListValues()
	{
		return $this->listValues;
	}
	
	/**
	 * Stores the filter values for use by searches and breadcrumbs
	 * 
	 * @param array $requirements  Associative array of filter-list=>selected-values
	 */
	function setFilterValues( $requirements )
	{
		$this->filterValues = array();
		
		if( !empty( $requirements['cycle'] ) ) {
			if( !is_array( $requirements['cycle'] ) ) {
				$requirements['cycle'] = array( $requirements['cycle'] );
			}
			$this->filterValues['cycle'] = $requirements['cycle'];
		}
		if( !empty( $requirements['subject'] ) ) {
			if( !is_array( $requirements['subject'] ) ) {
				$requirements['subject'] = array( $requirements['subject'] );
			}
			$this->filterValues['subject'] = $requirements['subject'];
		}
		if( !empty( $requirements['group'] ) ) {
			if( !is_array( $requirements['group'] ) ) {
				$requirements['group'] = array( $requirements['group'] );
			}
			$this->filterValues['group'] = $requirements['group'];
		}
		if( !empty( $requirements['student'] ) ) {
			if( !is_array( $requirements['student'] ) ) {
				$requirements['student'] = array( $requirements['student'] );
			}
			$this->filterValues['reportee'] = $requirements['student'];
		}
		if( !empty( $requirements['status'] ) ) {
			if( !is_array( $requirements['status'] ) ) {
				$requirements['status'] = array( $requirements['status'] );
			}
			$this->filterValues['status'] = $requirements['status'];
		}
		
		foreach( $this->filterValuesOrdered as $k=>$v ) {
			$parts = explode( '.', $v, 2 );
			if( isset($requirements[$parts[0]]) && is_array($requirements[$parts[0]])
			 && ( ( $pos = array_search( $parts[1], $requirements[$parts[0]] ) ) !== false ) ) {
				unset( $requirements[$parts[0]][$pos] );
			}
			else {
				unset( $this->filterValuesOrdered[$k] );
			}
		}
		foreach( $requirements as $list=>$values ) {
			foreach( $values as $k=>$v ) {
				$this->filterValuesOrdered[] = $list.'.'.$v;
			}
		}
	}
	
	function getFilterValues()
	{
		return $this->filterValues;
	}
	
	/**
	 * Updates the marker of where solid breadcrumbs end and filter breadcrumbs begin
	 * Then sets filter breadcrumbs
	 * 
	 * @param unknown_type $fCrumbs
	 */
	function resetFilterCrumbs( &$fCrumbs )
	{
		if( !isset( $this->_baseCrumbId ) ) {
			$c = $fCrumbs->getTail( ARC_REPORT_CRUMB_TRAIL );
			$this->_baseCrumbId = $c->getId();
		}
		
		$this->setFilterCrumbs( $fCrumbs );
	}
	
	/**
	 * Uses the filter values to add breadcrumbs to the trail
	 * 
	 * @param unknown_type $fCrumbs
	 */
	function setFilterCrumbs( &$fCrumbs )
	{
		$baseCrumb = $fCrumbs->getInstance( $this->_baseCrumbId );
		// remove all previously set filter-generated crumbs
		$fCrumbs->curtailTrail( ARC_REPORT_CRUMB_TRAIL, $this->_baseCrumbId );
		
		$urlParts = explode( '?', $baseCrumb->getURL(), 2 );
		$baseUrl = $baseCrumb->getURL();
		foreach( $this->filterValuesOrdered as $k=>$v ) {
			$parts = explode( '.', $v, 2 );
			$cumulative[$parts[0]][] = $parts[1];
			
			switch( $parts[0] ) {
			case( 'subject' ):
			case( 'group' ):
				$label = ApotheosisData::_( 'course.name', $parts[1] );
				break;
			
			case( 'student' ):
				$label = ApotheosisData::_( 'people.displayName', $parts[1] );
				break;
			
			case( 'status' ):
				$label = ApotheosisData::_( 'report.status', $parts[1] );
				break;
			}
			
			$url = $baseUrl.'#'.urlencode( json_encode( $cumulative ) );
			
			$newCrumb = $fCrumbs->addBreadCrumb( ARC_REPORT_CRUMB_TRAIL, $label, $url );
			$newCrumb->setDatum( 'jscallback', 'filterCrumbClick' );
		}
	}
}
?>