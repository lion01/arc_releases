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
 * Timetable Enrolment Factory
 */
class ApothFactory_Timetable_Enrolment extends ApothFactory
{
	function initialise()
	{
		$this->setDate();
	}
	
	function setDate( $date = null )
	{
		$this->_date = ( is_null($date) ? date( 'Y-m-d H:i:s' ) : $date );
	}
	
	function &getDummy()
	{
		$id = -1;
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$r = new ApothTtEnrolment( array('id'=>$id) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified enrolment, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_group_members' )
				."\n".'WHERE `id` = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothTtEnrolment( $data );
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
				case( 'valid_from' ):
				case( 'valid_to' ):
					if( !isset($where['date']) ) {
						$where['date'] = ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $requirements['valid_from'], $requirements['valid_to'] );
					}
					break;
				
				case( 'valid_on' ):
					$where[] = ApotheosisLibDb::dateCheckSql( 'gm.valid_from', 'gm.valid_to', $val, $val );
					break;
				
				case( 'group_name'):
					$joins[] = 'INNER JOIN '.$db->nameQuote( '#__apoth_cm_courses' ).' AS c'
						."\n".'  ON c.id = gm.group_id'
						."\n".' AND c.fullname LIKE "%'.$db->getEscaped( $val ).'%"';
					break;
				
				case( 'person_id' ):
				case( 'group_id' ):
				case( 'role' ):
					$where[] = 'gm.'.$db->nameQuote( $col ).$assignPart;
					break;
				}
			}
			
			$query = 'SELECT '.( $init ? 'gm.*' : 'gm.id' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_group_members' ).' AS gm'
				.( empty($joins) ? '' : "\n ".implode("\n ", $joins) )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) )
				."\n".'ORDER BY gm.valid_from DESC, gm.id ASC';
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
					$r = new ApothTtEnrolment( $data[$id] );
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
		$isNew = ( $id < 0 );
		
		if( $isNew ) {
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_tt_group_members' );
			$query2 = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_tt_group_members' );
			$query2 = 'WHERE `id` = '.$db->Quote( $id );
		}
		
		$to = $r->getDatum( 'valid_to' );
		$dbTo = is_null($to) ? 'NULL' : $db->Quote( $to );
		
		$query = $query
			."\n".'SET'
			."\n  ".$db->nameQuote( 'group_id' )   .' = '.$db->Quote( $r->getDatum('group_id' )   )
			."\n, ".$db->nameQuote( 'person_id' )  .' = '.$db->Quote( $r->getDatum('person_id' )  )
			."\n, ".$db->nameQuote( 'role' )       .' = '.$db->Quote( $r->getDatum('role' )       )
			."\n, ".$db->nameQuote( 'is_admin' )   .' = '.$db->Quote( $r->getDatum('is_admin' )   ) // *** Titikaka
			."\n, ".$db->nameQuote( 'is_teacher' ) .' = '.$db->Quote( $r->getDatum('is_teacher' ) ) // *** Titikaka
			."\n, ".$db->nameQuote( 'is_student' ) .' = '.$db->Quote( $r->getDatum('is_student' ) ) // *** Titikaka
			."\n, ".$db->nameQuote( 'is_watcher' ) .' = '.$db->Quote( $r->getDatum('is_watcher' ) ) // *** Titikaka
			."\n, ".$db->nameQuote( 'valid_from' ) .' = '.$db->Quote( $r->getDatum('valid_from' ) )
			."\n, ".$db->nameQuote( 'valid_to' )   .' = '.$dbTo
			."\n".$query2;
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		
		// no errors means successful commit
		return ( $db->getErrorMsg() == '' );
	}
}


/**
 * Timetable Enrolment Object
 */
class ApothTtEnrolment extends JObject
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
	
	function setGroup( $val )
	{
		$this->_core['group_id'] = $val;
	}
	
	function setPerson( $val )
	{
		$this->_core['person_id'] = $val;
	}
	
	function setRole( $val )
	{
		$this->_core['role'] = $val;
		
		// Manage the titikaka - old role markers
		$this->_core['is_admin'] = 0;
		$this->_core['is_teacher'] = 0;
		$this->_core['is_student'] = 0;
		$this->_core['is_watcher'] = 0;
		switch( $val ) {
		case( ApotheosisLibAcl::getRoleId( 'group_supervisor_admin' ) ):
			$this->_core['is_admin'] = 1;
			break;
			
		case( ApotheosisLibAcl::getRoleId( 'group_supervisor_teacher' ) ):
			$this->_core['is_teacher'] = 1;
			break;
			
		case( ApotheosisLibAcl::getRoleId( 'group_participant_student' ) ):
			$this->_core['is_student'] = 1;
			break;
			
		case( ApotheosisLibAcl::getRoleId( 'group_participant_watcher' ) ):
			$this->_core['is_watcher'] = 1;
			break;
		}
	}
	
	function setDates( $from, $to )
	{
		if( empty($from) ) {
			$this->_core['valid_from'] = null;
		}
		else {
			$this->_core['valid_from'] = date( 'Y-m-d H:i:s', strtotime( $from ) );
		}
		
		if( empty($to) ) {
			$this->_core['valid_to'] = null;
		}
		else {
			$this->_core['valid_to'] = date( 'Y-m-d H:i:s', strtotime( $to ) );
		}
	}
	
	function isCurrent()
	{
		$t = time();
		return ( strtotime( $this->_core['valid_from'] ) < $t
		 && ((strtotime( $this->_core['valid_to'] ) > $t) || is_null($this->_core['valid_to'])) );
	}
	
	/**
	 * Sets the end date for the enrolment to the current time
	 * or the valid-from time + 1 second if that's in the future
	 * 
	 * @return boolean  Was the change successfully committed to the db?
	 */
	function terminate()
	{
		$t = time();
		if( is_null($this->_core['valid_to']) || (strtotime($this->_core['valid_to']) > $t) ) {
			$vf = strtotime( $this->_core['valid_from'] ) + 1;
			if( $t < $vf ) {
				$t = $vf;
			}
			$this->_core['valid_to'] = date( 'Y-m-d H:i:s', $t );
		}
		return $this->commit();
	}
	
	/**
	 * Trigger saving of the object to the database
	 */
	function commit()
	{
		$fInc = ApothFactory::_( 'timetable.Enrolment' );
		return $fInc->commitInstance( $this->getId() );
	}
}
?>