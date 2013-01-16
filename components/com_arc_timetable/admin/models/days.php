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
 * Timetable Admin Days Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminModelDays extends JModel
{
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPaginationForDays( $limitStart, $limit )
	{
		$total = $this->_loadPagedDays( true );
		$this->_pagination = new JPagination( $total, $limitStart, $limit );
	}
	
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPaginationForSections( $limitStart, $limit )
	{
		$total = $this->_loadPagedSections( true );
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
	 * Fetch a paginated list of days, loading them if necessary
	 * 
	 * @return array  Array of day objects
	 */
	function getPagedDays()
	{
		if( !isset($this->_pagedDays) ) {
			$info = $this->_loadPagedDays( false );
			
			$fPat = ApothFactory::_( 'timetable.Day' );
			
			$this->_pagedDays = array();
			foreach( $info as $id ) {
				$this->_pagedDays[] = $fPat->getInstance( $id );
			}
		}
		return $this->_pagedDays;
	}
	
	function _loadPagedDays( $numOnly)
	{
		$fPat = ApothFactory::_( 'timetable.Day' );
		$r = $fPat->getInstances( array(), false );
		
		if( $numOnly ) {
			return count($r);
		}
		else {
			return array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
	}
	
	function setDays( $indices )
	{
		if( !is_array($indices) ) {
			$indices = array($indices);
		}
		$this->getPagedDays();
		$this->_days = array();
		foreach( $indices as $index ) {
			$this->_days[$index] = $this->_pagedDays[$index];
		}
	}
	
	function getDays()
	{
		return $this->_days;
	}
	
	function getDay()
	{
		return reset( $this->_days );
	}
	
	/**
	 * Fetch a paginated list of day sections, loading them if necessary
	 * 
	 * @return array  Array of day objects
	 */
	function getPagedSections()
	{
		if( !isset($this->_pagedSections) ) {
			$info = $this->_loadPagedSections( false );
			
			$fPat = ApothFactory::_( 'timetable.DaySection' );
			
			$this->_pagedSections = array();
			foreach( $info as $id ) {
				$this->_pagedSections[] = $fPat->getInstance( $id );
			}
		}
		return $this->_pagedSections;
	}
	
	function _loadPagedSections( $numOnly)
	{
		$day = $this->getDay();
		$fPat = ApothFactory::_( 'timetable.DaySection' );
		$r = $fPat->getInstances( array( 'pattern'=>$day->getDatum( 'pattern' ), 'day'=>$day->getDatum( 'day_type' ) ), false );
		
		if( $numOnly ) {
			return count($r);
		}
		else {
			return array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
	}
	
	function setSections( $indices )
	{
		if( !is_array($indices) ) {
			$indices = array($indices);
		}
		$this->getPagedSections();
		$this->_sections = array();
		foreach( $indices as $index ) {
			$this->_sections[] = $this->_pagedSections[$index];
		}
	}
	
	function getSections()
	{
		return $this->_sections;
	}
	
	function getSection()
	{
		return reset( $this->_sections );
	}
	
	function toggleSection()
	{
		$s = &$this->getSection();
		$s->toggleStatutory();
		$s->commit();
	}
}
?>