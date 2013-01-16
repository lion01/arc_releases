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
 * Timetable Pattern Factory
 */
class ApothFactory_Timetable_Pattern extends ApothFactory
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
			$r = new ApothTtPattern( array('id'=>$id) );
			$this->_addInstance( $id, $r );
		}
		return $r;
	}
	
	/**
	 * Retrieves the identified pattern, creating the object if it didn't already exist
	 * @param $id
	 */
	function &getInstance( $id )
	{
		$r = &$this->_getInstance( $id );
		if( is_null($r) ) {
			$db = &JFactory::getDBO();
			$query = 'SELECT *'
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_patterns' )
				."\n".'WHERE `id` = '.$db->Quote( $id );
			$db->setQuery($query);
			$data = $db->loadAssoc();
			
			$r = new ApothTtPattern( $data );
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
						$where['date'] = ApotheosisLibDb::dateCheckSql( 'valid_from', 'valid_to', $requirements['valid_from'], $requirements['valid_to'] );
					}
					break;
				
				case( 'start_date' ):
					$col = 'valid_from';
				case( 'start_day' ):
				case( 'name' ):
					$where[] = $db->nameQuote( $col ).$assignPart;
					break;
				}
			}
			
			$query = 'SELECT '.( $init ? '*' : 'id' )
				."\n".'FROM '.$db->nameQuote( '#__apoth_tt_patterns' )
				.( empty($where) ? '' : "\n WHERE ".implode("\n AND ", $where) );
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
					$r = new ApothTtPattern( $data[$id] );
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
			$query = 'INSERT INTO '.$db->nameQuote( '#__apoth_tt_patterns' );
			$query2 = '';
		}
		else {
			$query = 'UPDATE '.$db->nameQuote( '#__apoth_tt_patterns' );
			$query2 = 'WHERE `id` = '.$db->Quote( $id );
		}
		
		$to = $r->getDatum( 'valid_to' );
		$dbTo = is_null($to) ? 'NULL' : $db->Quote( $to );
		$ext = $r->getDatum( 'ext_model_id' );
		$dbExt = is_null($ext) ? 'NULL' : $db->Quote( $ext );
		
		$query = $query
			."\n".'SET'
			."\n, ".$db->nameQuote( 'id' )          .' = '.$db->Quote( $r->getDatum('id' )           )
			."\n, ".$db->nameQuote( 'ext_model_id' ).' = '.$db->Quote( $r->getDatum('ext_model_id' ) )
			."\n, ".$db->nameQuote( 'name' )        .' = '.$db->Quote( $r->getDatum('name' )         )
			."\n, ".$db->nameQuote( 'format' )      .' = '.$db->Quote( $r->getDatum('format' )       )
			."\n, ".$db->nameQuote( 'start_day' )   .' = '.$db->Quote( $r->getDatum('start_day' )    )
			."\n, ".$db->nameQuote( 'valid_from' )  .' = '.$db->Quote( $r->getDatum('valid_from' )   )
			."\n, ".$db->nameQuote( 'valid_to' )    .' = '.$dbTo
			."\n".$query2;
		$db->setQuery( $query );
		$db->Query();
//		debugQuery( $db );
		
		// no errors means successful commit
		return ( $db->getErrorMsg() == '' );
	}
}


/**
 * Timetable Pattern Object
 */
class ApothTtPattern extends JObject
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
	
	function getFormat()
	{
		return ( isset($this->_core['format']) ? $this->_core['format'] : '' );
	}
	
	/**
	 * Trigger saving of the object to the database
	 */
	function commit()
	{
		$fEnr = ApothFactory::_( 'timetable.Pattern' );
		return $fEnr->commitInstance( $this->getId() );
	}
}
?>