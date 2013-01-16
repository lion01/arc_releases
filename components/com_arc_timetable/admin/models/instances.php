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
 * Timetable Admin Pattern Instances Model
 *
 * @author     Punnet - Arc Team
 * @package    Arc
 * @subpackage Timetable
 * @since      1.6
 */
class TimetableAdminModelInstances extends JModel
{
	/**
	 * Set the currently valid pagination object
	 * 
	 * @param int $limitStart Where to start paging from
	 * @param int $limit  The total number of items to page
	 */
	function setPagination( $limitStart, $limit )
	{
		$total = $this->_loadPagedInstances( true );
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
	 * Fetch a paginated list of pattern instances, loading them if necessary
	 * 
	 * @return array  Array of pattern instance objects
	 */
	function getPagedPatternInstances()
	{
		if( !isset($this->_pagedInstances) ) {
			$info = $this->_loadPagedInstances( false );
			
			$fPin = ApothFactory::_( 'timetable.PatternInstance' );
			
			$this->_pagedInstances = array();
			foreach( $info as $id ) {
				$this->_pagedInstances[] = $fPin->getInstance( $id );
			}
		}
		return $this->_pagedInstances;
	}
	
	function _loadPagedInstances( $numOnly)
	{
		$fPin = ApothFactory::_( 'timetable.PatternInstance' );
		$r = $fPin->getInstances( array(), false );
		
		if( $numOnly ) {
			return count($r);
		}
		else {
			return array_slice( $r, $this->_pagination->limitstart, $this->_pagination->limit );
		}
		
	}
}
?>