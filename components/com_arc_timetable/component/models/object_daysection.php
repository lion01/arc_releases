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
 * Timetable Day Section Factory
 */
class ApothFactory_Timetable_DaySection extends ApothFactory
{
	function initialise()
	{
		$this->setDate();
	}
	
	function setDate( $date = null )
	{
		$this->_date = ( is_null($date) ? date( 'Y-m-d H:i:s' ) : $date );
	}
	
	function &getDummy( $id = -1 )
	{
		$trueId = $id.'~'.$id.'~'.$id;
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothTtDaySection( array('pattern'=>$id, 'day_type'=>$id, 'day_section'=>$id) );
			$id = $r->getId();
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified day section, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$idParts = explode( '~', $id, 3 );
			$dbPattern = $db->Quote( $idParts[0] );
			$dbDay     = $db->Quote( $idParts[1] );
			$dbSection = $db->Quote( $idParts[2] );
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_daydetails' )
				."\n".'WHERE `pattern` = '.$dbPattern
				."\n".'  AND `day_type` = '.$dbDay
				."\n".'  AND `day_section` = '.$dbSection;
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothTtDaySection( $data );
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
				
				case( 'section' ):
					$where[] = 'day_section'.$assignPart;
					break;
				}
			}
			
			$query = 'SELECT CONCAT(`pattern`, "~", `day_type`, "~", `day_section`) AS id'.( $init ? ', d.*' : '' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_daydetails' ).' AS `d`'
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY `pattern`, `day_type`, `start_time`';
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
					$r = new ApothTtDaySection( $data[$id] );
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
		$db = &JFactory::getDBO();
		$id = $r->getId();
		$idParts = explode( '~', $id, 3 );
		$isNew = ( ($idParts[0] < 0) && ($idParts[1] < 0) && ($idParts[2] < 0) );
		
		$p = $r->getDatum( 'pattern' );
		$d = $r->getDatum( 'day_type' );
		$s = $r->getDatum( 'day_section' );
		if( ($p < 0) || empty($p) || ($d < 0) || empty($d) || ($s < 0) || empty($s) ) {
			return false; // Composite key must be set (not auto-inc), and foreign key checks must be met.
		}
		$dbPattern = $db->Quote( $p );
		$dbDay     = $db->Quote( $d );
		$dbSection = $db->Quote( $s );
		
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_tt_daydetails' );
			$query2 = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_tt_daydetails' );
			$query2 = 'WHERE `pattern` = '.$dbPattern
				."\n".'  AND `day_type` = '.$dbDay
				."\n".'  AND `day_section` = '.$dbSection;
		}
		
		$to = $r->getDatum( 'valid_to' );
		$dbTo = ( is_null($to) ? 'NULL' : $db->Quote( $to ) );
		
		$query = $query
			."\n".'SET'
			."\n  ".$db->nameQuote( 'pattern' )          .' = '.$dbPattern
			."\n, ".$db->nameQuote( 'day_type' )         .' = '.$dbDay
			."\n, ".$db->nameQuote( 'day_section' )      .' = '.$dbSection
			."\n, ".$db->nameQuote( 'day_section_short' ).' = '.$db->Quote( $r->getDatum('day_section_short') )
			."\n, ".$db->nameQuote( 'ext_period_id' )    .' = '.$db->Quote( $r->getDatum('ext_period_id' )    )
			."\n, ".$db->nameQuote( 'start_time' )       .' = '.$db->Quote( $r->getDatum('start_time' )       )
			."\n, ".$db->nameQuote( 'end_time' )         .' = '.$db->Quote( $r->getDatum('end_time' )         )
			."\n, ".$db->nameQuote( 'has_teacher' )      .' = '.$db->Quote( $r->getDatum('has_teacher' )      )
			."\n, ".$db->nameQuote( 'taught' )           .' = '.$db->Quote( $r->getDatum('taught' )           )
			."\n, ".$db->nameQuote( 'registered' )       .' = '.$db->Quote( $r->getDatum('registered' )       )
			."\n, ".$db->nameQuote( 'statutory' )        .' = '.$db->Quote( $r->getDatum('statutory' )        )
			."\n, ".$db->nameQuote( 'valid_from' )       .' = '.$db->Quote( $r->getDatum('valid_from' )       )
			."\n, ".$db->nameQuote( 'valid_to' )         .' = '.$dbTo
			."\n".$query2;
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		
		// no errors means successful commit
		return ( $db->getErrorMsg() == '' );
	}
}


/**
 * Timetable Day Section Object
 */
class ApothTtDaySection extends JObject
{
	/**
	 * All the data for this day section (equates to a row in the db)
	 * @access protected
	 * @var array
	 */
	var $_core = array();
	
	function __construct( $data )
	{
		$this->_core = $data;
		if( !isset($this->_core['id']) ) {
			$this->_core['id'] = $this->_core['pattern'].'~'.$this->_core['day_type'].'~'.$this->_core['day_section'];
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
	
	function toggleStatutory( $val = null )
	{
		if( is_null($val) ) {
			$this->_core['statutory'] = !$this->_core['statutory'];
		}
		else {
			$this->_core['statutory'] = (bool)$val;
		}
	}
	
	/**
	 * Trigger saving of the object to the database
	 */
	function commit()
	{
		$fSec = ApothFactory::_( 'timetable.DaySection' );
		return $fSec->commitInstance( $this->getId() );
	}
}
?>