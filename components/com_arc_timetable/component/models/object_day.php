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

/**
 * Timetable Day Factory
 */
class ApothFactory_Timetable_Day extends ApothFactory
{
	function initialise()
	{
		$this->setDate();
	}
	
	function setDate( $date = null )
	{
		$this->_date = ( is_null($date) ? date( 'Y-m-d H:i:s' ) : $date );
	}
	
	function &getDummy( $id = -1)
	{
		$trueId = $id.'~'.$id;
		$r = &$this->_getInstance( $trueId );
		if( is_null($r) ) {
			$r = new ApothTtDay( array('pattern'=>$id, 'day_type'=>$id) );
			$id = $r->getId();
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified day, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$idParts = explode( '~', $id, 2 );
			$r = new ApothTtDay( array('pattern'=>$idParts[0], 'day_type'=>$idParts[1]) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	function &getInstances( $requirements, $init = true )
	{
		$sId = $this->_getSearchId( $requirements );
		$ids = $this->_getInstances($sId);
		if( is_null($ids) ) {
			$db = &JFactory::getDBO();
			$where = array();
			foreach( $requirements as $col=>$val ) {
				if( is_array($val) ) {
					if( empty($val) ) {
						continue;
					}
					foreach( $val as $k=>$v ) {
						$val[$k] = $db->Quote( $v );
					}
					$assignPart = ' IN ('.implode( ', ',$val ).')';
				}
				elseif( is_null($val) ) {
					$assignPart = ' IS NULL';
				}
				else {
					$assignPart = ' = '.$db->Quote( $val );
				}
				switch( $col ) {
				case( 'pattern' ):
					$where[] = 'pattern'.$assignPart;
					break;
				
				case( 'day' ):
					$where[] = 'day_type'.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT DISTINCT CONCAT(`pattern`, "~", `day_type`) AS id, pattern, day_type'
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_daydetails' )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY `pattern` DESC, `day_type` ASC';
			$db->setQuery($query);
			$data = $db->loadAssocList( 'id' );
//			debugQuery( $db, $data );
			$ids = array_keys( $data );
			$this->_addInstances( $sId, $ids );
			
			if( $init ) {
				$existing = $this->_getInstances();
				$newIds = array_diff( $ids, $existing );
				
				// initialise and cache
				foreach( $newIds as $id ) {
					$r = new ApothTtDay( $data[$id] );
					$this->_addInstance( $id, $r );
					unset( $r );
				}
			}
		}
		
		return $ids;
	}
	
	/**
	 * Commits the instance to the db,
	 * updates the cached instance,
	 * clears the search cache if we've added a new instance
	 *  (the newly created instance may match any of the searches we preveiously executed)
	 * 
	 * @param $id
	 */
	function commitInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			return false;
		}
		
		$retVal = true;
		$sections = $r->getSections();
		foreach( $sections as $section ) {
			$retVal = $section->commit() && $retVal;
		}
		
		return $retVal;
	}
}


/**
 * Timetable Day Object
 */
class ApothTtDay extends JObject
{
	/**
	 * All the data for this day (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_core = $data;
		if( !isset($this->_core['id']) ) {
			$this->_core['id'] = $this->_core['pattern'].'~'.$this->_core['day_type'];
		}
	}
	
	/**
	 * Accessor function to retrieve id
	 */
	function getId()
	{
		return $this->_core['id'];
	}
	
	/**
	 * Accessor function to retrieve core data
	 */
	function getDatum( $key )
	{
		return ( isset($this->_core[$key]) ? $this->_core[$key] : null );
	}
		
	/**
	 * Accessor function to retrieve day sections in this day type
	 */
	function getSections()
	{
		if( !isset($this->_sections) ) {
			$fSec = ApothFactory::_( 'timetable.DaySection' );
			$this->_sections = $fSec->getInstances( array( 'pattern'=>$this->_core['pattern'], 'day'=>$this->_core['day_type'] ) );
		}
		return $this->_sections;
	}
	
	function getStart()
	{
		$fSec = ApothFactory::_( 'timetable.DaySection' );
		if( !isset($this->_sections) ) {
			$this->_sections = $fSec->getInstances( array( 'pattern'=>$this->_core['pattern'], 'day'=>$this->_core['day_type'] ) );
		}
		$first = $fSec->getInstance( reset($this->_sections) );
		return $first->getDatum( 'start_time' );
	}
	
	function getEnd()
	{
		$fSec = ApothFactory::_( 'timetable.DaySection' );
		if( !isset($this->_sections) ) {
			$this->_sections = $fSec->getInstances( array( 'pattern'=>$this->_core['pattern'], 'day'=>$this->_core['day_type'] ) );
		}
		$first = $fSec->getInstance( end($this->_sections) );
		return $first->getDatum( 'end_time' );
	}
	
	
	/**
	 * Trigger saving of the object to the database
	 */
	function commit()
	{
		$fDay = ApothFactory::_( 'timetable.Day' );
		return $fDay->commitInstance( $this->getId() );
	}
}
?>