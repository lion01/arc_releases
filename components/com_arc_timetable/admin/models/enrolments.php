<?php
/**
 * @package     Arc
 * @subpackage  Timetable
 * @copyright   Copyright (C) 2005 Punnet. All rights reserved. See COPYRIGHT_ARC.txt
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL. See LICENSE_ARC.txt
 * Arc is free software: you can redistribute it and/or modify
 * it under the terms of version 3 of the GNU General Public License
 * as published by the Free Software Foundation.
 * The disclaimer of warranty as stated in the GPL applies to this program
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Give us access to the joomla model class
jimport( 'joomla.application.component.model' );

/**
 * Timetable Admin Enrolments Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminModelEnrolments extends JModel
{
	function setSearchGroup( $val )
	{
		if( empty($val) ) {
			unset($this->_search['group']);
		}
		else {
			$this->_search['group_name'] = $val;
		}
	}
	
	function setSearchPerson( $val )
	{
		if( empty($val) ) {
			unset($this->_search['person_id']);
		}
		else {
			$this->_search['person_id'] = $val;
		}
	}
	
	function setSearchValid( $val )
	{
		if( empty($val) ) {
			unset($this->_search['valid_on']);
		}
		else {
			$this->_search['valid_on'] = $val;
		}
	}
	
	
	function getSearchGroup()
	{
		return ( isset($this->_search['group_name']) ? $this->_search['group_name'] : null );
	}
	
	function getSearchPerson()
	{
		return ( isset($this->_search['person_id']) ? $this->_search['person_id'] : null );
	}
	
	function getSearchValid()
	{
		return ( isset($this->_search['valid_on']) ? $this->_search['valid_on'] : null );
	}
	
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedEnrolments( true );
		$this->_pagination = new JPagination( $total, $limitStart, $limit );
	}
	
	/**
	 * Retrieve the currently valid pagination object
	 * 
	 * @return object $this->_pagination  The pagination object
	 */
	function &getPagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * Fetch a paginated list of enrolments, loading them if necessary
	 * 
	 * @return array  Array of enrolment objects
	 */
	function getPagedEnrolments()
	{
		if( !isset($this->_pagedEnrolments) ) {
			$info = $this->_loadPagedEnrolments( false );
			
			$fEnrol = ApothFactory::_( 'timetable.Enrolment' );
			
			$this->_pagedEnrolments = array();
			foreach( $info as $id ) {
				$this->_pagedEnrolments[] = $fEnrol->getInstance( $id );
			}
		}
		return $this->_pagedEnrolments;
	}
	
	function _loadPagedEnrolments( $numOnly)
	{
		if( empty( $this->_search) ) {
			$this->_search = array( 'valid_on'=>date('Y-m-d H:i:s') );
		}
		$search = $this->_search;
		
		$fEnrol = ApothFactory::_( 'timetable.Enrolment' );
		$r = $fEnrol->getInstances( $this->_search, false );
		
		if( $numOnly ) {
			return count($r);
		}
		else {
			return array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
	}
	
	function indexToEnrolmentId( $index )
	{
		$this->getPagedEnrolments();
		return $this->_pagedEnrolments[$index]->getId();
	}
	
	// #####  Enrolment modification  #####
	
	function setEnrolment( $id = null )
	{
		$fEnrol = ApothFactory::_( 'timetable.Enrolment' );
		if( is_null($id) || ( $id < 0 ) ) {
			$this->_enrolment = $fEnrol->getDummy();
		}
		else {
			$this->_enrolment = $fEnrol->getInstance( $id );
		}
	}
	
	function getEnrolment()
	{
		return $this->_enrolment;
	}
	
	function save( $data )
	{
		if( !isset($this->_enrolment) ) {
			return false;
		}
		$this->_enrolment->setGroup( $data['group_id'] );
		$this->_enrolment->setPerson( $data['person_id'] );
		$this->_enrolment->setRole( $data['role'] );
		$this->_enrolment->setDates( $data['valid_from'], $data['valid_to'] );
		return $this->_enrolment->commit();
	}
	
	function terminate( $indices )
	{
		$this->getPagedEnrolments();
		$retVal = true;
		foreach( $indices as $i ) {
			$retVal = $this->_pagedEnrolments[$i]->terminate() && $retVal;
		}
		return $retVal;
	}
}
?>